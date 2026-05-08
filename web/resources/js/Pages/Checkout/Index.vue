<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { loadStripe } from '@stripe/stripe-js';

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
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

// Steps: 1=Übersicht, 2=Kontakt, 3=Zahlung
const step = ref(1);
const authMode = ref(authUser.value ? 'account' : 'guest'); // 'guest', 'login', 'account'
const processing = ref(false);
const errorMsg = ref('');

// Stripe
let stripe = null;
let elements = null;
let paymentElement = null;
const clientSecret = ref('');

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
    agb_accepted: false,
    voucher_code: '',
    cart: props.cartItems,
    subtotal: props.subtotal,
    tax_rate: props.taxRate,
    tax_amount: props.taxAmount,
});

// Attendee names per ticket slot
const attendeeNames = ref({});
props.cartItems.forEach(item => {
    if (item.type === 'ticket') {
        attendeeNames.value[item.id] = Array(item.qty).fill('');
    }
});

const needsAttendeeNames = computed(() =>
    props.cartItems.some(i => i.type === 'ticket' && i.requires_attendee_name)
);

// Voucher
const voucherApplied = ref(null);
const voucherInput = ref('');
const voucherError = ref('');

async function applyVoucher() {
    voucherError.value = '';
    try {
        const res = await fetch('/api/voucher/check', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': page.props.csrfToken ?? document.querySelector('meta[name=csrf-token]')?.content },
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
    return Math.min(voucherApplied.value.balance, props.subtotal);
});

const totalAfterVoucher = computed(() =>
    Math.max(0, props.subtotal - voucherDiscount.value)
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

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <!-- Progress Bar -->
        <div class="bg-white border-b border-surface-200 sticky top-20 z-30">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center gap-4">
                    <div v-for="(label, i) in ['Übersicht', 'Kontakt', 'Zahlung']" :key="i"
                        class="flex items-center gap-2" :class="i < 2 ? 'flex-1' : ''">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-colors"
                            :class="step > i+1 ? 'bg-green-500 text-white' : step === i+1 ? 'bg-brand-500 text-white' : 'bg-surface-200 text-surface-500'">
                            <svg v-if="step > i+1" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span v-else>{{ i+1 }}</span>
                        </div>
                        <span class="text-sm font-medium hidden sm:block" :class="step === i+1 ? 'text-brand-600' : 'text-surface-500'">{{ label }}</span>
                        <div v-if="i < 2" class="flex-1 h-0.5 mx-2" :class="step > i+1 ? 'bg-green-400' : 'bg-surface-200'"></div>
                    </div>
                </div>
            </div>
        </div>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Main Column -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- STEP 1: Übersicht -->
                    <div v-if="step === 1">
                        <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">Deine Bestellung</h2>

                        <!-- Addon Upsell (if event has addons not yet selected) -->
                        <div v-if="event.addons && event.addons.length && !cartItems.some(i => i.type === 'addon')" class="bg-gradient-to-r from-brand-50 to-orange-50 border border-brand-200 rounded-2xl p-5 mb-6">
                            <p class="font-bold text-brand-700 mb-1">🎁 Upgrade dein Erlebnis!</p>
                            <p class="text-sm text-brand-600 mb-3">Erweitere deine Bestellung mit optionalen Extras.</p>
                            <Link :href="route('event.show', event.slug)" class="text-sm font-bold text-brand-600 underline">← Zum Event zurück & Extras hinzufügen</Link>
                        </div>

                        <!-- Cart Items -->
                        <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-surface-100">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-surface-200 shrink-0">
                                        <img v-if="event.image_path" :src="`/storage/${event.image_path}`" class="w-full h-full object-cover" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-surface-900">{{ event.title }}</p>
                                        <p class="text-sm text-surface-500">{{ formatDate(event.start_date) }}</p>
                                        <p v-if="event.location" class="text-sm text-surface-500">📍 {{ event.location.name }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="divide-y divide-surface-100">
                                <div v-for="item in cartItems" :key="item.id" class="flex justify-between items-center px-6 py-4">
                                    <div>
                                        <p class="font-medium text-surface-900">{{ item.qty }}× {{ item.name }}</p>
                                        <p class="text-sm text-surface-500">{{ formatCurrency(item.price) }} / Stück</p>
                                    </div>
                                    <span class="font-bold text-surface-900">{{ formatCurrency(item.total) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Voucher -->
                        <div class="bg-white rounded-2xl border border-surface-200 shadow-sm p-5">
                            <p class="font-bold text-surface-800 mb-3">Gutschein einlösen</p>
                            <div class="flex gap-3">
                                <input v-model="voucherInput" type="text" placeholder="Gutschein-Code" class="flex-1 rounded-xl border-surface-300 text-sm focus:ring-brand-400 focus:border-brand-400" />
                                <button @click="applyVoucher" class="bg-surface-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-surface-800 transition-colors">Einlösen</button>
                            </div>
                            <p v-if="voucherError" class="text-red-500 text-xs mt-2">{{ voucherError }}</p>
                            <p v-if="voucherApplied" class="text-green-600 text-xs mt-2 font-bold">✓ Gutschein aktiv – {{ formatCurrency(voucherDiscount) }} werden abgezogen.</p>
                        </div>

                        <button @click="step = 2" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-4 rounded-2xl transition-colors shadow-md text-lg mt-2">
                            Weiter zu deinen Daten →
                        </button>
                    </div>

                    <!-- STEP 2: Kontakt & Adresse -->
                    <div v-if="step === 2">
                        <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">Deine Daten</h2>

                        <!-- Auth mode tabs (only if not logged in) -->
                        <div v-if="!authUser" class="flex bg-surface-100 p-1 rounded-2xl mb-6 gap-1">
                            <button v-for="(label, mode) in { guest: 'Als Gast', login: 'Anmelden', register: 'Registrieren' }" :key="mode"
                                @click="authMode = mode; form.checkout_type = mode"
                                class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all"
                                :class="authMode === mode ? 'bg-white shadow text-brand-600' : 'text-surface-500 hover:text-surface-800'">
                                {{ label }}
                            </button>
                        </div>

                        <div v-if="authMode === 'login'" class="bg-white rounded-3xl border border-surface-200 p-8 text-center">
                            <p class="text-surface-600 mb-4">Melde dich an, um dein Konto für die Bestellung zu verwenden.</p>
                            <a :href="route('login') + '?redirect=' + encodeURIComponent(window.location.href)" class="inline-block bg-brand-500 text-white px-6 py-3 rounded-xl font-bold hover:bg-brand-600 transition-colors">Zur Anmeldung</a>
                        </div>

                        <form v-else @submit.prevent="goToPayment" class="space-y-5">
                            <div v-if="authUser" class="bg-brand-50 border border-brand-200 rounded-2xl p-4 text-sm text-brand-700">
                                <strong>Angemeldet als {{ authUser.name }}</strong> ({{ authUser.email }})
                            </div>

                            <div v-if="!authUser">
                                <label class="block text-sm font-bold text-surface-700 mb-1">E-Mail-Adresse *</label>
                                <input v-model="form.guest_email" type="email" required class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" />
                                <p v-if="form.errors.guest_email" class="text-red-500 text-xs mt-1">{{ form.errors.guest_email }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 mb-1">Vorname *</label>
                                    <input v-model="form.billing_first_name" type="text" required class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 mb-1">Nachname *</label>
                                    <input v-model="form.billing_last_name" type="text" required class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-1">Firma (optional)</label>
                                <input v-model="form.billing_company" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-1">Straße & Hausnummer *</label>
                                <input v-model="form.billing_street" type="text" required class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" />
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 mb-1">PLZ *</label>
                                    <input v-model="form.billing_zip" type="text" required class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" />
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-bold text-surface-700 mb-1">Stadt *</label>
                                    <input v-model="form.billing_city" type="text" required class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-1">Land *</label>
                                <select v-model="form.billing_country" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400">
                                    <option value="DE">Deutschland</option>
                                    <option value="AT">Österreich</option>
                                    <option value="CH">Schweiz</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-1">Telefon (optional)</label>
                                <input v-model="form.billing_phone" type="tel" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" />
                            </div>

                            <!-- Named Tickets -->
                            <div v-if="needsAttendeeNames" class="bg-surface-50 rounded-2xl border border-surface-200 p-5">
                                <p class="font-bold text-surface-800 mb-3">Gästenamen für personalisierte Tickets</p>
                                <template v-for="item in cartItems.filter(i => i.type === 'ticket' && i.requires_attendee_name)" :key="item.id">
                                    <div v-for="n in item.qty" :key="n" class="mb-3">
                                        <label class="block text-xs font-medium text-surface-600 mb-1">{{ item.name }} – Gast {{ n }}</label>
                                        <input v-model="attendeeNames[item.id][n-1]" type="text" placeholder="Vor- und Nachname" class="w-full rounded-xl border-surface-300 text-sm focus:ring-brand-400 focus:border-brand-400" />
                                    </div>
                                </template>
                            </div>

                            <!-- AGB & Datenschutz -->
                            <div class="flex items-start gap-3 bg-surface-50 rounded-2xl border border-surface-200 p-4">
                                <input id="agb" v-model="form.agb_accepted" type="checkbox" required class="mt-0.5 rounded border-surface-300 text-brand-500 focus:ring-brand-400 w-5 h-5 shrink-0" />
                                <label for="agb" class="text-sm text-surface-700 leading-relaxed">
                                    Ich habe die <Link :href="route('agb')" target="_blank" class="text-brand-600 underline font-medium">AGB</Link> und
                                    <Link :href="route('datenschutz')" target="_blank" class="text-brand-600 underline font-medium">Datenschutzerklärung</Link>
                                    gelesen und stimme ihnen zu. *
                                </label>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="step = 1" class="flex-none px-6 py-4 bg-surface-100 text-surface-700 font-bold rounded-2xl hover:bg-surface-200 transition-colors">
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
                        <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">Zahlung</h2>

                        <div class="bg-white rounded-3xl border border-surface-200 shadow-sm p-6 mb-6">
                            <div id="stripe-payment-element" class="min-h-20"></div>
                        </div>

                        <p v-if="errorMsg" class="text-red-500 text-sm mb-4 bg-red-50 p-4 rounded-2xl border border-red-200">{{ errorMsg }}</p>

                        <div class="flex gap-3">
                            <button @click="step = 2" class="flex-none px-6 py-4 bg-surface-100 text-surface-700 font-bold rounded-2xl hover:bg-surface-200 transition-colors">
                                ← Zurück
                            </button>
                            <button @click="submitPayment" :disabled="processing" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-bold py-4 rounded-2xl transition-colors shadow-md text-lg disabled:opacity-50 flex items-center justify-center gap-3">
                                <svg v-if="processing" class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span>{{ processing ? 'Zahlung wird verarbeitet...' : `Jetzt ${formatCurrency(totalAfterVoucher)} zahlen` }}</span>
                            </button>
                        </div>

                        <p class="text-xs text-center text-surface-500 mt-4">
                            🔒 Sichere Zahlung via Stripe. Deine Kartendaten werden verschlüsselt übertragen.
                        </p>
                    </div>

                </div>

                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl border border-surface-200 shadow-sm p-6 sticky top-40">
                        <h3 class="font-display font-bold text-lg text-surface-900 mb-4 border-b border-surface-100 pb-4">Zusammenfassung</h3>

                        <div class="space-y-2 text-sm mb-4">
                            <div v-for="item in cartItems" :key="item.id" class="flex justify-between text-surface-700">
                                <span>{{ item.qty }}× {{ item.name }}</span>
                                <span class="font-medium">{{ formatCurrency(item.total) }}</span>
                            </div>
                        </div>

                        <div class="border-t border-surface-100 pt-4 space-y-2 text-sm">
                            <div class="flex justify-between text-surface-600">
                                <span>Nettobetrag</span>
                                <span>{{ formatCurrency(netAmount) }}</span>
                            </div>
                            <div v-if="!taxExempt" class="flex justify-between text-surface-600">
                                <span>MwSt. {{ taxRate }}%</span>
                                <span>{{ formatCurrency(taxAmount) }}</span>
                            </div>
                            <div v-if="taxExempt" class="text-xs text-surface-500">Steuerbefreit gem. §19 UStG</div>
                            <div v-if="voucherDiscount > 0" class="flex justify-between text-green-600 font-medium">
                                <span>Gutschein</span>
                                <span>– {{ formatCurrency(voucherDiscount) }}</span>
                            </div>
                        </div>

                        <div class="border-t border-surface-200 pt-4 mt-4">
                            <div class="flex justify-between items-end">
                                <span class="font-bold text-surface-900">Gesamt</span>
                                <span class="font-display font-black text-2xl text-surface-900">{{ formatCurrency(totalAfterVoucher) }}</span>
                            </div>
                            <p class="text-xs text-surface-500 mt-1">inkl. aller Steuern und Gebühren</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <Footer />
    </div>
</template>
