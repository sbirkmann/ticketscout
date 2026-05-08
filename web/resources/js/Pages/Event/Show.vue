<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    event: Object
});

const selectedTickets = ref({});
const selectedAddons = ref({});

// Pre-select 1 standard ticket
if (props.event.ticket_categories) {
    const def = props.event.ticket_categories.find(c => c.is_default);
    if (def) selectedTickets.value[def.id] = 1;
}

function maxForCategory(cat) {
    const available = cat.quantity != null ? cat.quantity - (cat.sold || 0) : 10;
    return Math.min(10, Math.max(0, available));
}

function increment(catId, cat) {
    const max = maxForCategory(cat);
    const cur = selectedTickets.value[catId] || 0;
    if (cur < max) selectedTickets.value[catId] = cur + 1;
}

function decrement(catId) {
    const cur = selectedTickets.value[catId] || 0;
    if (cur > 0) selectedTickets.value[catId] = cur - 1;
}

function incrementAddon(addonId) {
    selectedAddons.value[addonId] = Math.min(10, (selectedAddons.value[addonId] || 0) + 1);
}

function decrementAddon(addonId) {
    const cur = selectedAddons.value[addonId] || 0;
    if (cur > 0) selectedAddons.value[addonId] = cur - 1;
}

const totalAmount = computed(() => {
    let total = 0;
    if (props.event.ticket_categories) {
        props.event.ticket_categories.forEach(cat => {
            if (selectedTickets.value[cat.id]) {
                total += parseFloat(cat.price) * selectedTickets.value[cat.id];
            }
        });
    }
    if (props.event.addons) {
        props.event.addons.forEach(addon => {
            if (selectedAddons.value[addon.id]) {
                total += parseFloat(addon.price) * selectedAddons.value[addon.id];
            }
        });
    }
    return total.toFixed(2);
});

const hasTickets = computed(() => Object.values(selectedTickets.value).some(v => v > 0));

const allDates = computed(() => {
    let dates = [];
    if (props.event.parentEvent) {
        dates.push(props.event.parentEvent);
        if (props.event.parentEvent.siblingDates) {
            dates = [...dates, ...props.event.parentEvent.siblingDates];
        }
    } else {
        dates.push(props.event);
        if (props.event.siblingDates) {
            dates = [...dates, ...props.event.siblingDates];
        }
    }
    // Filter unique and sort
    const uniqueDates = Array.from(new Map(dates.map(item => [item.id, item])).values());
    return uniqueDates.sort((a, b) => new Date(a.start_date) - new Date(b.start_date));
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', {
        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
    });
}

function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('de-DE', {
        hour: '2-digit', minute: '2-digit'
    });
}

function checkout() {
    // Build cart data and navigate to checkout index
    const ticketsParam = JSON.stringify(selectedTickets.value);
    const addonsParam = JSON.stringify(selectedAddons.value);
    router.get(route('checkout.index', props.event.slug), { tickets: ticketsParam, addons: addonsParam });
}
</script>

<template>
    <Head :title="`${event.title} – Tickets kaufen | Ticketsout24`">
        <meta name="description" :content="`${event.title} am ${formatDate(event.start_date)} in ${event.location?.name}. Jetzt Tickets sichern auf Ticketsout24!`" />
    </Head>

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <!-- Hero Image -->
        <div class="relative h-[50vh] min-h-72 bg-surface-900 overflow-hidden">
            <img v-if="event.image_path" :src="`/storage/${event.image_path}`" class="w-full h-full object-cover opacity-50" />
            <div class="absolute inset-0 bg-gradient-to-t from-surface-900 via-surface-900/50 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
                <div v-if="event.category" class="inline-block bg-brand-500 text-white text-xs font-bold px-3 py-1 rounded-full mb-4">
                    {{ event.category.name }}
                </div>
                <h1 class="font-display font-black text-4xl md:text-6xl text-white drop-shadow-lg leading-tight">{{ event.title }}</h1>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

                <!-- Left: Content -->
                <div class="lg:col-span-2 space-y-10">

                    <!-- Date & Location info bar -->
                    <div class="flex flex-wrap gap-6 text-surface-700">
                        <div class="flex items-center gap-3 bg-white rounded-2xl px-5 py-3 shadow-sm border border-surface-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <div>
                                <div class="text-xs text-surface-500 font-medium">Datum</div>
                                <div class="font-bold text-surface-900">{{ formatDate(event.start_date) }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-white rounded-2xl px-5 py-3 shadow-sm border border-surface-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <div class="text-xs text-surface-500 font-medium">Uhrzeit</div>
                                <div class="font-bold text-surface-900">{{ formatTime(event.start_date) }} Uhr</div>
                            </div>
                        </div>
                        <div v-if="event.location" class="flex items-center gap-3 bg-white rounded-2xl px-5 py-3 shadow-sm border border-surface-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <div>
                                <div class="text-xs text-surface-500 font-medium">Location</div>
                                <Link :href="route('locations.show', event.location.slug)" class="font-bold text-brand-600 hover:underline">{{ event.location.name }}</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                        <h2 class="font-display font-bold text-2xl text-surface-900 mb-4">Über das Event</h2>
                        <p class="text-surface-600 leading-relaxed whitespace-pre-wrap text-lg">{{ event.description }}</p>

                        <!-- Gallery -->
                        <div v-if="event.gallery_images && event.gallery_images.length" class="mt-8">
                            <h3 class="font-display font-bold text-xl text-surface-900 mb-4">Bildergalerie</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div v-for="(img, idx) in event.gallery_images" :key="idx" class="aspect-square rounded-2xl overflow-hidden bg-surface-200 border border-surface-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                                    <img :src="`/storage/${img}`" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="event.tags && event.tags.length" class="flex flex-wrap gap-2 mt-6">
                            <span v-for="tag in event.tags" :key="tag" class="bg-surface-100 text-surface-600 px-3 py-1 rounded-full text-sm font-medium">
                                #{{ tag }}
                            </span>
                        </div>
                    </div>

                    <!-- Artists -->
                    <div v-if="event.artists && event.artists.length" class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                        <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">Künstler & Line-Up</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <Link v-for="artist in event.artists" :key="artist.id" :href="artist.has_landing_page ? route('artists.show', artist.slug) : '#'" class="group flex flex-col items-center text-center p-4 rounded-2xl hover:bg-surface-50 transition-colors border border-surface-100">
                                <div class="w-20 h-20 rounded-full bg-surface-200 mb-3 overflow-hidden border-2 border-white shadow-md">
                                    <img v-if="artist.image_path" :src="`/storage/${artist.image_path}`" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-surface-700 to-surface-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>
                                    </div>
                                </div>
                                <div class="font-bold text-surface-900 group-hover:text-brand-600 transition-colors text-sm">{{ artist.name }}</div>
                                <div v-if="artist.pivot?.role" class="text-xs text-brand-500 font-medium mt-1">{{ artist.pivot.role }}</div>
                                <div v-if="artist.genre" class="text-xs text-surface-500 mt-0.5">{{ artist.genre }}</div>
                            </Link>
                        </div>
                    </div>

                    <!-- Weitere Termine (Series) -->
                    <div v-if="allDates.length > 1" class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                        <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">Weitere Termine</h2>
                        <div class="space-y-3">
                            <Link v-for="dEvent in allDates" :key="dEvent.id" :href="route('event.show', dEvent.slug)" 
                                  class="flex items-center justify-between p-4 rounded-2xl border transition-colors"
                                  :class="dEvent.id === event.id ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-surface-200 hover:border-brand-300 hover:bg-surface-50'">
                                <div class="flex items-center gap-4">
                                    <div class="bg-white border border-surface-200 rounded-xl p-2 text-center min-w-[60px] shadow-sm">
                                        <div class="text-xs text-brand-600 font-bold uppercase">{{ new Date(dEvent.start_date).toLocaleDateString('de-DE', { month: 'short' }) }}</div>
                                        <div class="text-xl font-black text-surface-900 leading-none mt-1">{{ new Date(dEvent.start_date).getDate() }}</div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-surface-900" :class="{'text-brand-700': dEvent.id === event.id}">
                                            {{ dEvent.title }}
                                        </div>
                                        <div class="text-sm text-surface-500 mt-1 flex items-center gap-2">
                                            <span>{{ formatTime(dEvent.start_date) }} Uhr</span>
                                            <span v-if="dEvent.id === event.id" class="bg-brand-100 text-brand-700 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">Aktuell ausgewählt</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-brand-600 font-medium text-sm pr-2 flex items-center gap-1">
                                    <span v-if="dEvent.id !== event.id">Ansehen</span>
                                    <svg v-if="dEvent.id !== event.id" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Location & Map -->
                    <div v-if="event.location" class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                        <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">Veranstaltungsort & Anfahrt</h2>
                        <div class="flex flex-col sm:flex-row gap-6 items-start mb-6">
                            <div class="flex-1">
                                <h3 class="font-bold text-xl text-surface-900 mb-2">{{ event.location.name }}</h3>
                                <p class="text-surface-600">{{ event.location.address }}</p>
                                <p class="text-surface-600">{{ event.location.zip }} {{ event.location.city }}, {{ event.location.country }}</p>
                                <Link :href="route('locations.show', event.location.slug)" class="inline-flex items-center gap-1 text-brand-600 font-medium hover:underline mt-3 text-sm">
                                    Mehr zur Location
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </Link>
                            </div>
                        </div>
                        <div class="h-64 rounded-2xl overflow-hidden border border-surface-200">
                            <iframe
                                width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                                :src="`https://maps.google.com/maps?width=100%25&height=400&hl=de&q=${encodeURIComponent(event.location.address + ', ' + event.location.zip + ' ' + event.location.city)}&t=&z=14&ie=UTF8&iwloc=B&output=embed`"
                                class="w-full h-full grayscale contrast-125 opacity-80"
                            ></iframe>
                        </div>
                    </div>

                </div>

                <!-- Right: Ticket Purchase Sidebar (Sticky) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-surface-200 p-8 sticky top-28">
                        <h3 class="font-display font-bold text-2xl text-surface-900 mb-6">Tickets buchen</h3>

                        <div v-if="event.ticket_categories && event.ticket_categories.length" class="space-y-3 mb-6">
                            <div v-for="cat in event.ticket_categories" :key="cat.id"
                                class="p-4 border rounded-2xl transition-all"
                                :class="(selectedTickets[cat.id] || 0) > 0 ? 'border-brand-400 bg-brand-50' : 'border-surface-200 bg-surface-50'">
                                <div class="flex justify-between items-center">
                                    <div class="flex-1 mr-4">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <h4 class="font-bold text-surface-900 text-sm">{{ cat.name }}</h4>
                                            <span v-if="cat.is_default" class="text-xs bg-brand-100 text-brand-600 px-2 py-0.5 rounded-full font-medium">Standard</span>
                                        </div>
                                        <p class="text-brand-600 font-bold">{{ parseFloat(cat.price).toFixed(2).replace('.',',') }} €</p>
                                        <p v-if="cat.quantity != null" class="text-xs text-surface-400 mt-0.5">
                                            {{ Math.max(0, cat.quantity - (cat.sold || 0)) }} verfügbar
                                        </p>
                                    </div>
                                    <!-- +/- Stepper -->
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button @click="decrement(cat.id)"
                                            :disabled="(selectedTickets[cat.id] || 0) === 0"
                                            class="w-9 h-9 rounded-full border-2 border-surface-300 flex items-center justify-center font-bold text-lg text-surface-700 hover:border-brand-400 hover:text-brand-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                                            −
                                        </button>
                                        <span class="w-8 text-center font-bold text-surface-900">{{ selectedTickets[cat.id] || 0 }}</span>
                                        <button @click="increment(cat.id, cat)"
                                            :disabled="(selectedTickets[cat.id] || 0) >= maxForCategory(cat)"
                                            class="w-9 h-9 rounded-full border-2 border-surface-300 flex items-center justify-center font-bold text-lg text-surface-700 hover:border-brand-400 hover:text-brand-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-surface-500 italic mb-6 p-4 bg-surface-50 rounded-2xl text-center">
                            Aktuell keine Tickets verfügbar.
                        </div>

                        <template v-if="event.addons && event.addons.length">
                            <p class="text-xs font-bold text-surface-500 uppercase tracking-wider mb-3 mt-2">Extras & Upgrades</p>
                            <div class="space-y-3 mb-6">
                                <div v-for="addon in event.addons" :key="addon.id"
                                    class="flex justify-between items-center p-3 border rounded-xl transition-all"
                                    :class="(selectedAddons[addon.id] || 0) > 0 ? 'border-brand-300 bg-brand-50' : 'border-surface-200 bg-surface-50'">
                                    <div>
                                        <div class="font-medium text-surface-900 text-sm">{{ addon.name }}</div>
                                        <div class="text-brand-600 font-medium text-sm">+ {{ parseFloat(addon.price).toFixed(2).replace('.',',') }} €</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button @click="decrementAddon(addon.id)" :disabled="(selectedAddons[addon.id] || 0) === 0"
                                            class="w-8 h-8 rounded-full border-2 border-surface-300 flex items-center justify-center font-bold text-surface-700 hover:border-brand-400 hover:text-brand-600 disabled:opacity-30 transition-all">−</button>
                                        <span class="w-6 text-center font-bold text-surface-900 text-sm">{{ selectedAddons[addon.id] || 0 }}</span>
                                        <button @click="incrementAddon(addon.id)"
                                            class="w-8 h-8 rounded-full border-2 border-surface-300 flex items-center justify-center font-bold text-surface-700 hover:border-brand-400 hover:text-brand-600 transition-all">+</button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Sibling Dates -->
                        <div v-if="event.siblingDates && event.siblingDates.length" class="mb-4">
                            <p class="text-xs font-bold text-surface-500 uppercase tracking-wider mb-2">Weitere Termine</p>
                            <div class="space-y-2">
                                <Link v-for="sibling in event.siblingDates" :key="sibling.id"
                                    :href="route('event.show', sibling.slug)"
                                    class="flex items-center justify-between p-3 border border-surface-200 rounded-xl hover:border-brand-300 hover:bg-brand-50 transition-all text-sm">
                                    <span class="font-medium text-surface-800">{{ formatDate(sibling.start_date) }}</span>
                                    <span class="text-brand-600 font-bold text-xs">Tickets →</span>
                                </Link>
                            </div>
                        </div>

                        <div class="border-t border-surface-200 pt-5">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-surface-600 font-medium">Gesamt</span>
                                <span class="font-display font-black text-3xl text-surface-900">{{ totalAmount.replace('.',',') }} €</span>
                            </div>
                            <p class="text-xs text-surface-400 mb-4">inkl. MwSt.</p>

                            <button @click="checkout" :disabled="!hasTickets" class="w-full bg-brand-500 text-white font-bold py-4 rounded-2xl shadow-md hover:bg-brand-600 transition-colors disabled:opacity-40 disabled:cursor-not-allowed text-lg">
                                Jetzt buchen
                            </button>
                            <p class="text-xs text-center text-surface-500 mt-3">🔒 Sicher bezahlen mit Stripe</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <Footer />
    </div>
</template>
