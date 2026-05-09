<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import SeatSelector from '@/Components/SeatSelector.vue';
import { loadStripe } from '@stripe/stripe-js';
import { cartStore } from '@/Stores/cartStore';

const props = defineProps({
    event: Object,
    cartItems: Array,
    subtotal: Number,
    netAmount: Number,
    taxAmount: Number,
    taxRate: Number,
    taxExempt: Boolean,
    platformFee: Number,
    stripeKey: String,
    seatingPlan: Object,
    groupToken: String,
    groupPrice: Number,
    listingId: [Number, String],
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

// Steps: 1=Sitzplatz (wenn plan), 2=Übersicht, 3=Kontakt, 4=Zahlung
const step = ref((props.seatingPlan && !props.groupToken && !props.listingId) ? 0 : 1); // 0 = Saalplan-Auswahl
const selectedSeats = ref([]);
const addonWarnings = ref([]); // addons that may no longer apply
function onSeatsSelected(seats) { selectedSeats.value = seats; }
function onCartUpdated(newCartItems) {
    const addons = form.cart.filter(i => i.type === 'addon');
    const newCategoryNames = newCartItems.map(i => i.name);

    // Check each selected addon: does it still fit the new ticket categories?
    const warnings = [];
    const validAddons = [];
    addons.forEach(addon => {
        // Find the addon definition from event.addons
        const def = props.event?.addons?.find(a => a.id === addon.id);
        if (!def) { validAddons.push(addon); return; }

        // If addon has ticket category restrictions, check them
        const restrictions = def.ticket_categories ?? [];
        if (restrictions.length > 0) {
            const allowed = restrictions.some(tc => newCategoryNames.includes(tc.name));
            if (!allowed) {
                warnings.push({ addon, allowedCategories: restrictions.map(tc => tc.name) });
                return; // drop this addon
            }
        }
        validAddons.push(addon);
    });

    addonWarnings.value = warnings;
    form.cart = [...newCartItems, ...validAddons];
    form.subtotal = form.cart.reduce((s, i) => s + i.total, 0);
}
const authMode = ref(authUser.value ? 'account' : 'guest');
const processing = ref(false);
const errorMsg = ref('');

// Stripe
let stripe = null;
let elements = null;
let paymentElement = null;
const clientSecret = ref('');

// Initialize cart from store if it matches this event, otherwise use props
const initialCart = (cartStore.event?.id === props.event.id && cartStore.items.length > 0) 
    ? cartStore.items 
    : props.cartItems;

const form = useForm({
    checkout_type: authUser.value ? 'account' : 'guest',
    guest_email: authUser.value?.email ?? '',
    billing_first_name: '',
    billing_last_name: '',
    billing_company: '',
    billing_street: '',
    billing_zip: '',
    billing_city: '',
    billing_country: 'DE',
    billing_phone: '',
    is_gift: false,
    gift_recipient_name: '',
    gift_message: '',
    agb_accepted: false,
    voucher_code: '',
    promo_code: '',
    use_loyalty_points: 0,
    cart: initialCart,
    subtotal: initialCart.reduce((s, i) => s + i.total, 0) || props.subtotal,
    tax_rate: props.taxRate,
    tax_amount: props.taxAmount,
    group_token: props.groupToken || null,
    listing_id: props.listingId || null,
});

// Update store when form cart changes
watch(() => form.cart, (newCart) => {
    cartStore.setItems(newCart, { 
        id: props.event.id, 
        title: props.event.title, 
        slug: props.event.slug,
        image_path: props.event.image_path,
        location: props.event.location
    });
}, { deep: true });


// Attendee names per ticket slot
const attendeeNames = ref({});
props.cartItems.forEach(item => {
    if (item.type === 'ticket') {
        attendeeNames.value[item.id] = Array(item.qty).fill('');
    }
});

const needsAttendeeNames = computed(() =>
    form.cart.some(i => i.type === 'ticket' && i.requires_attendee_name)
);

const addonCart = ref({}); // { addonId: qty }
const ticketCount = computed(() => form.cart.filter(i => i.type === 'ticket').reduce((s, i) => s + i.qty, 0));

function toggleAddon(addonId, addonName, addonPrice, delta) {
    const addonDef = props.event.addons?.find(a => a.id == addonId);
    const current = addonCart.value[addonId] ?? 0;
    let next = Math.max(0, current + delta);

    // Enforce Limits
    if (delta > 0 && addonDef) {
        if (addonDef.max_qty && next > addonDef.max_qty) {
            next = addonDef.max_qty;
            alert(`Maximale Anzahl für ${addonName} erreicht (${addonDef.max_qty}).`);
        }
        if (addonDef.max_per_ticket) {
            const limit = ticketCount.value;
            if (next > limit) {
                next = limit;
                alert(`Du kannst maximal ein ${addonName} pro Ticket wählen (Max: ${limit}).`);
            }
        }
    }

    if (next === 0) {
        delete addonCart.value[addonId];
    } else {
        addonCart.value[addonId] = next;
    }
    // Sync into form cart so it goes with the order
    const existingIdx = form.cart.findIndex(i => i.type === 'addon' && i.id === addonId);
    if (next === 0) {
        if (existingIdx !== -1) form.cart.splice(existingIdx, 1);
    } else {
        const entry = { type: 'addon', id: addonId, name: addonName + ' (Add-on)', price: addonPrice, qty: next, total: round2(addonPrice * next) };
        if (existingIdx !== -1) form.cart[existingIdx] = entry;
        else form.cart.push(entry);
    }
    // Recompute subtotal
    form.subtotal = form.cart.reduce((s, i) => s + i.total, 0);
}
function round2(v) { return Math.round(v * 100) / 100; }
const addonTotal = computed(() => Object.entries(addonCart.value).reduce((sum, [id, qty]) => {
    const addon = props.event.addons?.find(a => a.id == id);
    return sum + (addon ? addon.price * qty : 0);
}, 0));

// Promo Code
const promoApplied = ref(null);
const promoInput = ref('');
const promoError = ref('');

async function applyPromo() {
    promoError.value = '';
    try {
        const res = await fetch(`/checkout/${props.event.slug}/validate-promo`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ promo_code: promoInput.value, subtotal: props.subtotal })
        });
        const data = await res.json();
        if (data.valid) {
            promoApplied.value = data;
            form.promo_code = data.code;
        } else {
            promoError.value = data.message;
        }
    } catch { promoError.value = 'Fehler beim Prüfen des Rabattcodes.'; }
}

const promoDiscount = computed(() => {
    if (!promoApplied.value) return 0;
    return promoApplied.value.discount;
});

// Voucher
const voucherApplied = ref(null);
const voucherInput = ref('');
const voucherError = ref('');

async function applyVoucher() {
    voucherError.value = '';
    try {
        const res = await fetch('/api/voucher/check', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ code: voucherInput.value })
        });
        const data = await res.json();
        if (data.valid) {
            voucherApplied.value = data;
            form.voucher_code = voucherInput.value;
        } else {
            voucherError.value = 'Gutscheincode ungültig oder abgelaufen.';
        }
    } catch { voucherError.value = 'Fehler beim Prüfen des Gutscheins.'; }
}

const voucherDiscount = computed(() => {
    if (!voucherApplied.value) return 0;
    return Math.min(voucherApplied.value.balance, Math.max(0, form.subtotal - promoDiscount.value));
});

const loyaltyDiscount = computed(() => {
    if (!form.use_loyalty_points) return 0;
    const pointsValue = form.use_loyalty_points / 100;
    return Math.min(pointsValue, Math.max(0, form.subtotal - promoDiscount.value - voucherDiscount.value));
});

const totalAfterDiscounts = computed(() =>
    Math.max(0, form.subtotal - promoDiscount.value - voucherDiscount.value - loyaltyDiscount.value)
);

function formatCurrency(val) {
    return parseFloat(val).toFixed(2).replace('.', ',') + ' €';
}

function formatDate(d) {
    return new Date(d).toLocaleDateString('de-DE', {
        weekday:'short', day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'
    });
}

async function goToPayment() {
    processing.value = true;
    errorMsg.value = '';
    try {
        const res = await fetch(`/checkout/${props.event.slug}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            },
            body: JSON.stringify(form.data())
        });
        const data = await res.json();
        if (!data.clientSecret) {
            errorMsg.value = data.message || 'Fehler beim Erstellen der Zahlung.';
            processing.value = false;
            return;
        }
        clientSecret.value = data.clientSecret;
        step.value = 3;

        // Mount Stripe after DOM update
        await nextTick();
        stripe = await loadStripe(props.stripeKey);
        elements = stripe.elements({ clientSecret: data.clientSecret, locale: 'de' });
        paymentElement = elements.create('payment', {
            layout: 'tabs',
            defaultValues: {
                billingDetails: {
                    name: `${form.billing_first_name} ${form.billing_last_name}`,
                    email: form.guest_email,
                    address: {
                        line1: form.billing_street,
                        postal_code: form.billing_zip,
                        city: form.billing_city,
                        country: form.billing_country,
                    }
                }
            }
        });
        paymentElement.mount('#stripe-payment-element');
    } catch(e) {
        errorMsg.value = 'Netzwerkfehler. Bitte versuche es erneut.';
    } finally {
        processing.value = false;
    }
}

async function submitPayment() {
    processing.value = true;
    errorMsg.value = '';
    const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: window.location.origin + `/checkout/${props.event.slug}/complete`,
        }
    });
    if (error) {
        errorMsg.value = error.message;
        processing.value = false;
    }
}

// import nextTick
import { nextTick } from 'vue';
</script>

<template>
    <Head :title="`Checkout – ${event.title} | Ticketsout24`" />

    <div class="min-h-screen bg-surface-50 dark:bg-surface-950 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <!-- Progress Bar -->
        <div class="bg-white dark:bg-surface-900 border-b border-surface-200 dark:border-surface-800 sticky top-20 z-30">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center gap-4">
                    <div v-for="(label, i) in ['Übersicht', 'Kontakt', 'Zahlung']" :key="i"
                        class="flex items-center gap-2" :class="i < 2 ? 'flex-1' : ''">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-colors"
                            :class="step > i+1 ? 'bg-green-500 text-white' : step === i+1 ? 'bg-brand-500 text-white' : 'bg-surface-200 text-surface-500 dark:text-surface-400'">
                            <svg v-if="step > i+1" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span v-else>{{ i+1 }}</span>
                        </div>
                        <span class="text-sm font-medium hidden sm:block" :class="step === i+1 ? 'text-brand-600' : 'text-surface-500 dark:text-surface-400'">{{ label }}</span>
                        <div v-if="i < 2" class="flex-1 h-0.5 mx-2" :class="step > i+1 ? 'bg-green-400' : 'bg-surface-200'"></div>
                    </div>
                </div>
            </div>
        </div>

        <main class="mx-auto px-4 sm:px-6 lg:px-8 py-10" :class="step === 0 ? 'max-w-6xl' : 'max-w-4xl'">

            <!-- STEP 0: Sitzplatz wählen (full width, outside grid) -->
            <div v-if="step === 0 && seatingPlan" class="space-y-6 mb-6">
                <div class="bg-white dark:bg-surface-900 rounded-3xl border border-surface-200 dark:border-surface-800 shadow-sm p-8 md:p-12">
                    <h3 class="font-display font-black text-2xl text-surface-900 dark:text-white mb-1">🗺️ Sitzplatz wählen</h3>
                    <p class="text-surface-500 dark:text-surface-400 mb-8">Klicke auf deinen Wunschplatz, um ihn auszuwählen. Mehrfachauswahl möglich.</p>
                    <SeatSelector
                        :seating-plan="seatingPlan"
                        :ticket-categories="event.ticketCategories"
                        :cart-items="form.cart"
                        @seats-selected="onSeatsSelected"
                        @cart-updated="onCartUpdated"
                    />
                </div>
                <div class="flex justify-end">
                    <button @click="step = 1"
                        class="bg-brand-500 hover:bg-brand-600 text-white px-10 py-4 rounded-2xl font-bold text-lg transition-colors shadow-md"
                        :class="selectedSeats.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                        :disabled="selectedSeats.length === 0">
                        Weiter mit {{ selectedSeats.length }} Platz{{ selectedSeats.length !== 1 ? 'en' : '' }} →
                    </button>
                </div>
            </div>

            <div v-if="step > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Main Column -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- STEP 1: Übersicht -->
                    <div v-if="step === 1">
                        <h2 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-6">Deine Bestellung</h2>

                        <!-- Selected seats summary if plan exists -->
                        <div v-if="seatingPlan && selectedSeats.length > 0" class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 mb-4 flex items-center justify-between">
                            <div>
                                <p class="font-bold text-indigo-800 text-sm mb-2">🪑 Gewählte Plätze</p>
                                <div class="flex flex-wrap gap-1.5 mt-1">
                                    <span v-for="seat in selectedSeats" :key="seat.id"
                                        class="inline-flex items-center gap-1 bg-white dark:bg-surface-900 border border-indigo-200 text-indigo-700 rounded-lg px-2 py-1 text-xs font-semibold">
                                        <span class="font-black">{{ seat.label }}</span>
                                        <span class="text-indigo-400">·</span>
                                        <span>{{ seat.category }}</span>
                                        <span class="text-indigo-400">·</span>
                                        <span class="text-indigo-600 font-bold">
                                            {{ parseFloat(seat.price_override ?? event.ticketCategories?.find(c => c.name === seat.category)?.price ?? 0).toFixed(2).replace('.', ',') }} €
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <button @click="step = 0" class="text-xs text-indigo-500 underline hover:text-indigo-700 shrink-0">Ändern</button>
                        </div>

                        <!-- Addons: direkt im Checkout auswählen -->
                        <div v-if="event.addons && event.addons.length" class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-bold text-surface-800 dark:text-surface-100">🎁 Upgrade dein Erlebnis</p>
                                <span v-if="addonTotal > 0" class="text-xs font-bold text-brand-600 bg-brand-50 px-2 py-1 rounded-full">+{{ addonTotal.toFixed(2).replace('.', ',') }} €</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="addon in event.addons" :key="addon.id"
                                    class="bg-white dark:bg-surface-900 border-2 rounded-2xl p-4 transition-all"
                                    :class="addonCart[addon.id] ? 'border-brand-400 shadow-sm' : 'border-surface-200 dark:border-surface-800'">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <p class="font-bold text-surface-900 dark:text-white text-sm">{{ addon.name }}</p>
                                            <p class="text-brand-600 font-black text-lg mt-0.5">{{ parseFloat(addon.price).toFixed(2).replace('.', ',') }} €</p>
                                            <p v-if="addon.description" class="text-xs text-surface-400 mt-1">{{ addon.description }}</p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0 mt-1">
                                            <button v-if="addonCart[addon.id]" @click="toggleAddon(addon.id, addon.name, addon.price, -1)"
                                                class="w-8 h-8 rounded-full bg-surface-100 dark:bg-surface-800 hover:bg-surface-200 font-bold text-surface-700 dark:text-surface-200 flex items-center justify-center transition-colors">
                                                −
                                            </button>
                                            <span v-if="addonCart[addon.id]" class="font-bold text-surface-900 dark:text-white w-4 text-center">{{ addonCart[addon.id] }}</span>
                                            <button @click="toggleAddon(addon.id, addon.name, addon.price, +1)"
                                                :disabled="addon.max_qty && (addonCart[addon.id] ?? 0) >= addon.max_qty || addon.max_per_ticket && (addonCart[addon.id] ?? 0) >= ticketCount"
                                                class="w-8 h-8 rounded-full font-bold flex items-center justify-center transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                                                :class="addonCart[addon.id] ? 'bg-brand-500 hover:bg-brand-600 text-white' : 'bg-surface-100 dark:bg-surface-800 hover:bg-surface-200 text-surface-700 dark:text-surface-200'">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="addon.max_qty || addon.max_per_ticket" class="mt-2 text-[10px] text-surface-400 uppercase font-bold tracking-tight">
                                        Limit: {{ addon.max_qty ? addon.max_qty + ' gesamt' : '' }} {{ addon.max_qty && addon.max_per_ticket ? ' / ' : '' }} {{ addon.max_per_ticket ? '1 pro Ticket' : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Addon Warnings (if categories changed and addons dropped) -->
                        <div v-if="addonWarnings.length > 0" class="mb-6 space-y-3">
                            <div v-for="(warn, idx) in addonWarnings" :key="idx" 
                                class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex gap-3">
                                <span class="text-xl">⚠️</span>
                                <div>
                                    <p class="text-sm font-bold text-amber-800">Add-on entfernt: {{ warn.addon.name }}</p>
                                    <p class="text-xs text-amber-700 mt-0.5">
                                        Dieses Upgrade ist nur für folgende Kategorien verfügbar: 
                                        <strong>{{ warn.allowedCategories.join(', ') }}</strong>.
                                        Da du deine Platzwahl geändert hast, wurde es aus dem Warenkorb entfernt.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-surface-900 rounded-3xl border border-surface-200 dark:border-surface-800 shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-surface-100">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-surface-200 shrink-0">
                                        <img v-if="event.image_path" :src="`/storage/${event.image_path}`" class="w-full h-full object-cover" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-surface-900 dark:text-white">{{ event.title }}</p>
                                        <p class="text-sm text-surface-500 dark:text-surface-400">{{ formatDate(event.start_date) }}</p>
                                        <p v-if="event.location" class="text-sm text-surface-500 dark:text-surface-400">📍 {{ event.location.name }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="divide-y divide-surface-100">
                                <div v-for="item in form.cart" :key="item.id + '_' + item.name" class="flex justify-between items-center px-6 py-4 hover:bg-surface-50 dark:bg-surface-950 transition-colors">
                                    <div>
                                        <p class="font-bold text-surface-900 dark:text-white">
                                            {{ item.qty }}× {{ item.type === 'ticket' ? 'Ticket' : '' }} {{ item.name }}
                                        </p>
                                        <p v-if="item.type === 'ticket'" class="text-xs text-surface-500 dark:text-surface-400 font-medium">Kategorie: {{ item.name }}</p>
                                        <p class="text-xs text-surface-400 mt-0.5">{{ formatCurrency(item.price) }} / Stück</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-black text-surface-900 dark:text-white text-lg">{{ formatCurrency(item.total) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Discounts -->
                        <div class="mt-6 bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 shadow-sm p-5 space-y-4">
                            <!-- Promo Code -->
                            <div>
                                <p class="font-bold text-surface-800 dark:text-surface-100 mb-2">Rabattcode</p>
                                <div class="flex gap-3">
                                    <input v-model="promoInput" type="text" placeholder="z.B. SOMMER24" class="flex-1 rounded-xl border-surface-300 dark:border-surface-700 text-sm focus:ring-brand-400 focus:border-brand-400 uppercase" />
                                    <button type="button" @click="applyPromo" class="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 px-4 py-2 rounded-xl text-sm font-bold hover:bg-surface-200 transition-colors">Einlösen</button>
                                </div>
                                <p v-if="promoError" class="text-red-500 text-xs mt-2">{{ promoError }}</p>
                                <p v-if="promoApplied" class="text-brand-600 text-xs mt-2 font-bold">✓ {{ promoApplied.message }} – {{ formatCurrency(promoDiscount) }} Rabatt.</p>
                            </div>

                            <div class="h-px bg-surface-100 dark:bg-surface-800"></div>

                            <!-- Value Voucher -->
                            <div>
                                <p class="font-bold text-surface-800 dark:text-surface-100 mb-2">Wert-Gutschein</p>
                                <div class="flex gap-3">
                                    <input v-model="voucherInput" type="text" placeholder="Gutscheincode (Geschenk)" class="flex-1 rounded-xl border-surface-300 dark:border-surface-700 text-sm focus:ring-brand-400 focus:border-brand-400" />
                                    <button type="button" @click="applyVoucher" class="bg-surface-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-surface-800 transition-colors">Einlösen</button>
                                </div>
                                <p v-if="voucherError" class="text-red-500 text-xs mt-2">{{ voucherError }}</p>
                                <p v-if="voucherApplied" class="text-green-600 text-xs mt-2 font-bold">✓ Gutschein aktiv – {{ formatCurrency(voucherDiscount) }} werden abgezogen.</p>
                            </div>
                            
                            <template v-if="authUser && authUser.loyalty_points > 0">
                                <div class="h-px bg-surface-100 dark:bg-surface-800"></div>
                                <!-- Loyalty Points -->
                                <div>
                                    <p class="font-bold text-surface-800 dark:text-surface-100 mb-2">Treuepunkte einlösen</p>
                                    <p class="text-xs text-surface-500 dark:text-surface-400 mb-3">Du hast <strong>{{ authUser.loyalty_points }} Punkte</strong> (entspricht {{ formatCurrency(authUser.loyalty_points / 100) }}).</p>
                                    <div class="flex gap-3">
                                        <input v-model="form.use_loyalty_points" type="number" min="0" :max="authUser.loyalty_points" class="flex-1 rounded-xl border-surface-300 dark:border-surface-700 text-sm focus:ring-brand-400 focus:border-brand-400" />
                                        <button type="button" @click="form.use_loyalty_points = authUser.loyalty_points" class="bg-surface-100 dark:bg-surface-800 text-surface-800 dark:text-surface-100 px-4 py-2 rounded-xl text-sm font-bold hover:bg-surface-200 transition-colors">Alle nutzen</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button @click="step = 2" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-4 rounded-2xl transition-colors shadow-md text-lg mt-6">
                            Weiter zu deinen Daten →
                        </button>
                    </div>

                    <!-- STEP 2: Kontakt & Adresse -->
                    <div v-if="step === 2">
                        <h2 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-6">Deine Daten</h2>

                        <!-- Auth mode tabs (only if not logged in) -->
                        <div v-if="!authUser" class="flex bg-surface-100 dark:bg-surface-800 p-1 rounded-2xl mb-6 gap-1">
                            <button v-for="(label, mode) in { guest: 'Als Gast', login: 'Anmelden', register: 'Registrieren' }" :key="mode"
                                @click="authMode = mode; form.checkout_type = mode"
                                class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all"
                                :class="authMode === mode ? 'bg-white dark:bg-surface-900 shadow text-brand-600' : 'text-surface-500 dark:text-surface-400 hover:text-surface-800 dark:text-surface-100'">
                                {{ label }}
                            </button>
                        </div>

                        <div v-if="authMode === 'login'" class="bg-white dark:bg-surface-900 rounded-3xl border border-surface-200 dark:border-surface-800 p-8 text-center">
                            <p class="text-surface-600 dark:text-surface-300 mb-4">Melde dich an, um dein Konto für die Bestellung zu verwenden.</p>
                            <a :href="route('login') + '?redirect=' + encodeURIComponent(typeof window !== 'undefined' ? window.location.href : '')" class="inline-block bg-brand-500 text-white px-6 py-3 rounded-xl font-bold hover:bg-brand-600 transition-colors">Zur Anmeldung</a>
                        </div>

                        <form v-else @submit.prevent="goToPayment" class="space-y-5">
                            <div v-if="authUser" class="bg-brand-50 border border-brand-200 rounded-2xl p-4 text-sm text-brand-700">
                                <strong>Angemeldet als {{ authUser.name }}</strong> ({{ authUser.email }})
                            </div>

                            <div v-if="!authUser">
                                <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">E-Mail-Adresse *</label>
                                <input v-model="form.guest_email" type="email" required class="w-full rounded-xl border-surface-300 dark:border-surface-700 focus:ring-brand-400 focus:border-brand-400" />
                                <p v-if="form.errors.guest_email" class="text-red-500 text-xs mt-1">{{ form.errors.guest_email }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">Vorname *</label>
                                    <input v-model="form.billing_first_name" type="text" required class="w-full rounded-xl border-surface-300 dark:border-surface-700 focus:ring-brand-400 focus:border-brand-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">Nachname *</label>
                                    <input v-model="form.billing_last_name" type="text" required class="w-full rounded-xl border-surface-300 dark:border-surface-700 focus:ring-brand-400 focus:border-brand-400" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">Firma (optional)</label>
                                <input v-model="form.billing_company" type="text" class="w-full rounded-xl border-surface-300 dark:border-surface-700 focus:ring-brand-400 focus:border-brand-400" />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">Straße & Hausnummer *</label>
                                <input v-model="form.billing_street" type="text" required class="w-full rounded-xl border-surface-300 dark:border-surface-700 focus:ring-brand-400 focus:border-brand-400" />
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">PLZ *</label>
                                    <input v-model="form.billing_zip" type="text" required class="w-full rounded-xl border-surface-300 dark:border-surface-700 focus:ring-brand-400 focus:border-brand-400" />
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">Stadt *</label>
                                    <input v-model="form.billing_city" type="text" required class="w-full rounded-xl border-surface-300 dark:border-surface-700 focus:ring-brand-400 focus:border-brand-400" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">Land *</label>
                                <select v-model="form.billing_country" class="w-full rounded-xl border-surface-300 dark:border-surface-700 focus:ring-brand-400 focus:border-brand-400">
                                    <option value="DE">Deutschland</option>
                                    <option value="AT">Österreich</option>
                                    <option value="CH">Schweiz</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">Telefon (optional)</label>
                                <input v-model="form.billing_phone" type="tel" class="w-full rounded-xl border-surface-300 dark:border-surface-700 focus:ring-brand-400 focus:border-brand-400" />
                            </div>

                            <!-- Named Tickets -->
                            <div v-if="needsAttendeeNames" class="bg-surface-50 dark:bg-surface-950 rounded-2xl border border-surface-200 dark:border-surface-800 p-5">
                                <p class="font-bold text-surface-800 dark:text-surface-100 mb-3">Gästenamen für personalisierte Tickets</p>
                                <template v-for="item in form.cart.filter(i => i.type === 'ticket' && i.requires_attendee_name)" :key="item.id">
                                    <div v-for="n in item.qty" :key="n" class="mb-3">
                                        <label class="block text-xs font-medium text-surface-600 dark:text-surface-300 mb-1">{{ item.name }} – Gast {{ n }}</label>
                                        <input v-model="attendeeNames[item.id][n-1]" type="text" placeholder="Vor- und Nachname" class="w-full rounded-xl border-surface-300 dark:border-surface-700 text-sm focus:ring-brand-400 focus:border-brand-400" />
                                    </div>
                                </template>
                            </div>

                            <!-- Gift Option -->
                            <div class="bg-gradient-to-r from-pink-50 to-purple-50 rounded-2xl border border-pink-200 p-5">
                                <div class="flex items-start gap-3">
                                    <input id="is_gift" v-model="form.is_gift" type="checkbox" class="mt-1 rounded border-pink-300 text-pink-500 focus:ring-pink-400 w-5 h-5 shrink-0" />
                                    <div class="flex-1">
                                        <label for="is_gift" class="font-bold text-surface-900 dark:text-white cursor-pointer block">Dies ist ein Geschenk 🎁</label>
                                        <p class="text-sm text-surface-600 dark:text-surface-300 mt-1 mb-4">Möchtest du diese Tickets verschenken? Wir drucken deine Grußnachricht direkt auf die Tickets!</p>
                                        
                                        <div v-if="form.is_gift" class="space-y-4 animate-fade-in">
                                            <div>
                                                <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">Name des Empfängers</label>
                                                <input v-model="form.gift_recipient_name" type="text" placeholder="Z.B. Maria Musterfrau" class="w-full rounded-xl border-pink-200 focus:ring-pink-400 focus:border-pink-400" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-surface-700 dark:text-surface-200 mb-1">Deine Grußnachricht</label>
                                                <textarea v-model="form.gift_message" rows="3" placeholder="Herzlichen Glückwunsch zum Geburtstag! Viel Spaß beim Event..." class="w-full rounded-xl border-pink-200 focus:ring-pink-400 focus:border-pink-400"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- AGB & Datenschutz -->
                            <div class="flex items-start gap-3 bg-surface-50 dark:bg-surface-950 rounded-2xl border border-surface-200 dark:border-surface-800 p-4">
                                <input id="agb" v-model="form.agb_accepted" type="checkbox" required class="mt-0.5 rounded border-surface-300 dark:border-surface-700 text-brand-500 focus:ring-brand-400 w-5 h-5 shrink-0" />
                                <label for="agb" class="text-sm text-surface-700 dark:text-surface-200 leading-relaxed">
                                    Ich habe die <Link :href="route('agb')" target="_blank" class="text-brand-600 underline font-medium">AGB</Link> und
                                    <Link :href="route('datenschutz')" target="_blank" class="text-brand-600 underline font-medium">Datenschutzerklärung</Link>
                                    gelesen und stimme ihnen zu. *
                                </label>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="step = 1" class="flex-none px-6 py-4 bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-bold rounded-2xl hover:bg-surface-200 transition-colors">
                                    ← Zurück
                                </button>
                                <button type="submit" :disabled="processing || !form.agb_accepted" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-bold py-4 rounded-2xl transition-colors shadow-md text-lg disabled:opacity-50">
                                    <span v-if="processing">Wird verarbeitet...</span>
                                    <span v-else>Weiter zur Zahlung →</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- STEP 3: Stripe Zahlung -->
                    <div v-if="step === 3">
                        <h2 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-6">Zahlung</h2>

                        <div class="bg-white dark:bg-surface-900 rounded-3xl border border-surface-200 dark:border-surface-800 shadow-sm p-6 mb-6">
                            <div id="stripe-payment-element" class="min-h-20"></div>
                        </div>

                        <p v-if="errorMsg" class="text-red-500 text-sm mb-4 bg-red-50 p-4 rounded-2xl border border-red-200">{{ errorMsg }}</p>

                        <div class="flex gap-3">
                            <button @click="step = 2" class="flex-none px-6 py-4 bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-bold rounded-2xl hover:bg-surface-200 transition-colors">
                                ← Zurück
                            </button>
                            <button @click="submitPayment" :disabled="processing" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-bold py-4 rounded-2xl transition-colors shadow-md text-lg disabled:opacity-50 flex items-center justify-center gap-3">
                                <svg v-if="processing" class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span>{{ processing ? 'Zahlung wird verarbeitet...' : `Jetzt ${formatCurrency(totalAfterDiscounts)} zahlen` }}</span>
                            </button>
                        </div>

                        <p class="text-xs text-center text-surface-500 dark:text-surface-400 mt-4">
                            🔒 Sichere Zahlung via Stripe. Deine Kartendaten werden verschlüsselt übertragen.
                        </p>
                    </div>

                </div>

                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-surface-900 rounded-3xl border border-surface-200 dark:border-surface-800 shadow-sm p-6 sticky top-40">
                        <h3 class="font-display font-bold text-lg text-surface-900 dark:text-white mb-4 border-b border-surface-100 pb-4">Zusammenfassung</h3>

                        <div class="space-y-2 text-sm mb-4">
                            <div v-for="item in form.cart" :key="item.id + '_' + item.name" class="flex justify-between text-surface-700 dark:text-surface-200">
                                <span>{{ item.qty }}× {{ item.name }}</span>
                                <span class="font-medium">{{ formatCurrency(item.total) }}</span>
                            </div>
                        </div>

                        <div class="border-t border-surface-100 pt-4 space-y-2 text-sm">
                            <div class="flex justify-between text-surface-600 dark:text-surface-300">
                                <span>Nettobetrag</span>
                                <span>{{ formatCurrency(netAmount) }}</span>
                            </div>
                            <div v-if="!taxExempt" class="flex justify-between text-surface-600 dark:text-surface-300">
                                <span>MwSt. {{ taxRate }}%</span>
                                <span>{{ formatCurrency(taxAmount) }}</span>
                            </div>
                            <div v-if="taxExempt" class="text-xs text-surface-500 dark:text-surface-400">Steuerbefreit gem. §19 UStG</div>
                            <div v-if="promoDiscount > 0" class="flex justify-between text-brand-600 font-medium">
                                <span>Rabattcode ({{ form.promo_code }})</span>
                                <span>– {{ formatCurrency(promoDiscount) }}</span>
                            </div>
                            <div v-if="voucherDiscount > 0" class="flex justify-between text-green-600 font-medium">
                                <span>Wert-Gutschein</span>
                                <span>– {{ formatCurrency(voucherDiscount) }}</span>
                            </div>
                            <div v-if="loyaltyDiscount > 0" class="flex justify-between text-purple-600 font-medium">
                                <span>Treuepunkte eingelöst</span>
                                <span>– {{ formatCurrency(loyaltyDiscount) }}</span>
                            </div>
                        </div>

                        <div class="border-t border-surface-200 dark:border-surface-800 pt-4 mt-4">
                            <div class="flex justify-between items-end">
                                <span class="font-bold text-surface-900 dark:text-white">Gesamt</span>
                                <span class="font-display font-black text-2xl text-surface-900 dark:text-white">{{ formatCurrency(totalAfterDiscounts) }}</span>
                            </div>
                            <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">inkl. aller Steuern und Gebühren</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <Footer />
    </div>
</template>

