<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import axios from 'axios';
import { cartStore } from '@/Stores/cartStore';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    event: Object,
    socialProof: {
        type: Object,
        default: () => ({ viewing_now: 0, sold_out_percentage: 0, show_sold_out_badge: false })
    },
    can_review: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const isFavorited = ref(props.event.is_favorited || false);
const isLoadingFavorite = ref(false);

const toggleFavorite = async () => {
    if (!page.props.auth.user) {
        window.location.href = route('login');
        return;
    }
    
    isLoadingFavorite.value = true;
    try {
        const response = await axios.post(route('events.favorite', props.event.id));
        isFavorited.value = response.data.is_favorite;
    } catch (error) {
        console.error("Error toggling favorite", error);
    } finally {
        isLoadingFavorite.value = false;
    }
};

const selectedTickets = ref({});
const selectedAddons = ref({});

// Pre-select 1 standard ticket
if (props.event.ticket_categories) {
    const def = props.event.ticket_categories.find(c => c.is_default);
    if (def) selectedTickets.value[def.id] = 1;
}

function maxForCategory(cat) {
    if (cat.quantity == null) return 10;
    const available = cat.quantity - (cat.sold || 0);
    return Math.min(10, Math.max(0, available));
}

const isSoldOut = computed(() => {
    if (!props.event.ticket_categories || props.event.ticket_categories.length === 0) return true;
    return props.event.ticket_categories.every(cat => {
        if (cat.quantity == null) return false;
        return (cat.quantity - (cat.sold || 0)) <= 0;
    });
});

const waitlistForm = useForm({
    name: '',
    email: '',
});

function submitWaitlist() {
    waitlistForm.post(route('event.waitlist', props.event.id), {
        preserveScroll: true,
        onSuccess: () => {
            waitlistForm.reset();
        }
    });
}

const reviewForm = useForm({
    rating: 5,
    comment: ''
});

function submitReview() {
    reviewForm.post(route('events.review', props.event.id), {
        preserveScroll: true,
        onSuccess: () => {
            reviewForm.reset();
        }
    });
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
    availableAddons.value.forEach(addon => {
        if (selectedAddons.value[addon.id]) {
            total += parseFloat(addon.price) * selectedAddons.value[addon.id];
        }
    });
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

const showConfirmEventChange = ref(false);

function checkout() {
    if (cartStore.event && cartStore.event.id !== props.event.id && cartStore.items.length > 0) {
        showConfirmEventChange.value = true;
        return;
    }
    proceedToCheckout();
}

function confirmEventChange() {
    cartStore.clearCart();
    showConfirmEventChange.value = false;
    proceedToCheckout();
}

function proceedToCheckout() {
    // Build cart items for store
    const items = [];
    
    // Tickets
    if (props.event.ticket_categories) {
        props.event.ticket_categories.forEach(cat => {
            if (selectedTickets.value[cat.id]) {
                items.push({
                    type: 'ticket',
                    id: cat.id,
                    name: cat.name,
                    price: parseFloat(cat.price),
                    qty: selectedTickets.value[cat.id],
                    total: round2(parseFloat(cat.price) * selectedTickets.value[cat.id]),
                    requires_attendee_name: cat.requires_attendee_name || false
                });
            }
        });
    }

    // Addons
    availableAddons.value.forEach(addon => {
        if (selectedAddons.value[addon.id]) {
            items.push({
                type: 'addon',
                id: addon.id,
                name: addon.name + ' (Add-on)',
                price: parseFloat(addon.price),
                qty: selectedAddons.value[addon.id],
                total: round2(parseFloat(addon.price) * selectedAddons.value[addon.id])
            });
        }
    });

    cartStore.setItems(items, { 
        id: props.event.id, 
        title: props.event.title, 
        slug: props.event.slug,
        image_path: props.event.image_path,
        location: props.event.location
    });

    router.get(route('checkout.index', props.event.slug));
}

function round2(v) { return Math.round(v * 100) / 100; }


const availableAddons = computed(() => {
    if (!props.event.addons) return [];
    return props.event.addons.filter(addon => {
        if (!addon.ticket_categories || addon.ticket_categories.length === 0) {
            return true; // Global addon
        }
        // Check if any of the addon's categories are currently selected
        return addon.ticket_categories.some(cat => selectedTickets.value[cat.id] > 0);
    });
});

const minPrice = computed(() => {
    if (!props.event.ticket_categories || props.event.ticket_categories.length === 0) return 0;
    return Math.min(...props.event.ticket_categories.map(c => parseFloat(c.price)));
});

const schemaMarkup = computed(() => {
    const schema = {
        "@context": "https://schema.org",
        "@type": "Event",
        "name": props.event.title,
        "startDate": props.event.start_date,
        "eventStatus": "https://schema.org/EventScheduled",
        "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
        "description": props.event.description || props.event.title,
        "image": props.event.image_path ? [`${window.location.origin}/storage/${props.event.image_path}`] : [],
        "offers": {
            "@type": "Offer",
            "url": window.location.href,
            "price": minPrice.value,
            "priceCurrency": "EUR",
            "availability": "https://schema.org/InStock",
            "validFrom": props.event.created_at
        }
    };
    
    if (props.event.end_date) {
        schema.endDate = props.event.end_date;
    }
    
    if (props.event.location) {
        schema.location = {
            "@type": "Place",
            "name": props.event.location.name,
            "address": {
                "@type": "PostalAddress",
                "streetAddress": props.event.location.address,
                "addressLocality": props.event.location.city,
                "postalCode": props.event.location.zip,
                "addressCountry": props.event.location.country || "DE"
            }
        };
    }
    
    if (props.event.artists && props.event.artists.length > 0) {
        schema.performer = props.event.artists.map(artist => ({
            "@type": "PerformingGroup",
            "name": artist.name
        }));
    }
    
    return JSON.stringify(schema);
});
</script>

<template>
    <Head>
        <title>{{ event.title }} – Tickets kaufen | Ticketsout24</title>
        <meta name="description" :content="`${event.title} am ${formatDate(event.start_date)} in ${event.location?.name}. Jetzt Tickets sichern auf Ticketsout24!`" />
        <component is="script" type="application/ld+json" v-html="schemaMarkup"></component>
    </Head>

    <div class="min-h-screen bg-surface-50 dark:bg-surface-950 dark:bg-surface-950 font-sans selection:bg-brand-500 selection:text-white transition-colors duration-200">
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
                    
                    <!-- Social Proof / Urgency -->
                    <div class="bg-red-50 border border-red-100 rounded-2xl px-5 py-3 flex items-center gap-3 animate-pulse shadow-sm">
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        <p class="text-sm font-bold text-red-700 uppercase tracking-tight">
                            Gerade beliebt: {{ Math.floor(Math.random() * (18 - 4 + 1) + 4) }} Personen schauen sich dieses Event gerade an!
                        </p>
                    </div>

                    <!-- Date & Location info bar -->
                    <div class="flex flex-wrap gap-6 text-surface-700">
                        <div class="flex items-center gap-3 bg-white dark:bg-surface-900 rounded-2xl px-5 py-3 shadow-sm border border-surface-200 dark:border-surface-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <div>
                                <div class="text-xs text-surface-500 dark:text-surface-400 font-medium">Datum</div>
                                <div class="font-bold text-surface-900 dark:text-white">{{ formatDate(event.start_date) }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-white dark:bg-surface-900 rounded-2xl px-5 py-3 shadow-sm border border-surface-200 dark:border-surface-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <div class="text-xs text-surface-500 dark:text-surface-400 font-medium">Uhrzeit</div>
                                <div class="font-bold text-surface-900 dark:text-white">{{ formatTime(event.start_date) }} Uhr</div>
                            </div>
                        </div>
                        <a :href="route('event.ics', event.slug)" class="flex items-center gap-3 bg-white dark:bg-surface-900 hover:bg-brand-50 rounded-2xl px-5 py-3 shadow-sm border border-surface-200 dark:border-surface-800 transition-colors group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-surface-400 group-hover:text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <div>
                                <div class="font-bold text-brand-600 group-hover:text-brand-700">ICS Export</div>
                                <div class="text-xs text-surface-500 dark:text-surface-400">Für deinen Kalender</div>
                            </div>
                        </a>
                        
                        <button @click="toggleFavorite" :disabled="isLoadingFavorite" class="flex items-center gap-3 bg-white dark:bg-surface-900 hover:bg-brand-50 rounded-2xl px-5 py-3 shadow-sm border border-surface-200 dark:border-surface-800 transition-colors group text-left disabled:opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-colors" :class="isFavorited ? 'text-red-500 fill-current' : 'text-surface-400 group-hover:text-red-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" :stroke-width="isFavorited ? 0 : 2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <div>
                                <div class="font-bold text-surface-900 dark:text-white group-hover:text-brand-700" :class="{'text-brand-600': isFavorited}">{{ isFavorited ? 'Gemerkt' : 'Merken' }}</div>
                                <div class="text-xs text-surface-500 dark:text-surface-400">Favoriten</div>
                            </div>
                        </button>
                        <div v-if="event.location" class="flex items-center gap-3 bg-white dark:bg-surface-900 rounded-2xl px-5 py-3 shadow-sm border border-surface-200 dark:border-surface-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <div>
                                <div class="text-xs text-surface-500 dark:text-surface-400 font-medium">Location</div>
                                <Link :href="route('locations.show', event.location.slug)" class="font-bold text-brand-600 hover:underline">{{ event.location.name }}</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-white dark:bg-surface-900 rounded-3xl p-8 shadow-sm border border-surface-200 dark:border-surface-800">
                        <h2 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-4">Über das Event</h2>
                        <p class="text-surface-600 dark:text-surface-300 leading-relaxed whitespace-pre-wrap text-lg">{{ event.description }}</p>

                        <!-- Gallery -->
                        <div v-if="event.gallery_images && event.gallery_images.length" class="mt-8">
                            <h3 class="font-display font-bold text-xl text-surface-900 dark:text-white mb-4">Bildergalerie</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div v-for="(img, idx) in event.gallery_images" :key="idx" class="aspect-square rounded-2xl overflow-hidden bg-surface-200 border border-surface-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                                    <img :src="`/storage/${img}`" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="event.tags && event.tags.length" class="flex flex-wrap gap-2 mt-6">
                            <span v-for="tag in event.tags" :key="tag" class="bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 px-3 py-1 rounded-full text-sm font-medium">
                                #{{ tag }}
                            </span>
                        </div>
                    </div>

                    <!-- Artists -->
                    <div v-if="event.artists && event.artists.length" class="bg-white dark:bg-surface-900 rounded-3xl p-8 shadow-sm border border-surface-200 dark:border-surface-800">
                        <h2 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-6">Künstler & Line-Up</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <Link v-for="artist in event.artists" :key="artist.id" :href="artist.has_landing_page ? route('artists.show', artist.slug) : '#'" class="group flex flex-col items-center text-center p-4 rounded-2xl hover:bg-surface-50 dark:bg-surface-950 transition-colors border border-surface-100">
                                <div class="w-20 h-20 rounded-full bg-surface-200 mb-3 overflow-hidden border-2 border-white shadow-md">
                                    <img v-if="artist.image_path" :src="`/storage/${artist.image_path}`" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-surface-700 to-surface-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>
                                    </div>
                                </div>
                                <div class="font-bold text-surface-900 dark:text-white group-hover:text-brand-600 transition-colors text-sm">{{ artist.name }}</div>
                                <div v-if="artist.pivot?.role" class="text-xs text-brand-500 font-medium mt-1">{{ artist.pivot.role }}</div>
                                <div v-if="artist.genre" class="text-xs text-surface-500 dark:text-surface-400 mt-0.5">{{ artist.genre }}</div>
                            </Link>
                        </div>
                    </div>

                    <!-- Weitere Termine (Series) -->
                    <div v-if="allDates.length > 1" class="bg-white dark:bg-surface-900 rounded-3xl p-8 shadow-sm border border-surface-200 dark:border-surface-800">
                        <h2 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-6">Weitere Termine</h2>
                        <div class="space-y-3">
                            <Link v-for="dEvent in allDates" :key="dEvent.id" :href="route('event.show', dEvent.slug)" 
                                  class="flex items-center justify-between p-4 rounded-2xl border transition-colors"
                                  :class="dEvent.id === event.id ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-surface-200 dark:border-surface-800 hover:border-brand-300 hover:bg-surface-50 dark:bg-surface-950'">
                                <div class="flex items-center gap-4">
                                    <div class="bg-white dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl p-2 text-center min-w-[60px] shadow-sm">
                                        <div class="text-xs text-brand-600 font-bold uppercase">{{ new Date(dEvent.start_date).toLocaleDateString('de-DE', { month: 'short' }) }}</div>
                                        <div class="text-xl font-black text-surface-900 dark:text-white leading-none mt-1">{{ new Date(dEvent.start_date).getDate() }}</div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-surface-900 dark:text-white" :class="{'text-brand-700': dEvent.id === event.id}">
                                            {{ dEvent.title }}
                                        </div>
                                        <div class="text-sm text-surface-500 dark:text-surface-400 mt-1 flex items-center gap-2">
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
                    <div v-if="event.location" class="bg-white dark:bg-surface-900 rounded-3xl p-8 shadow-sm border border-surface-200 dark:border-surface-800">
                        <h2 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-6">Veranstaltungsort & Anfahrt</h2>
                        <div class="flex flex-col sm:flex-row gap-6 items-start mb-6">
                            <div class="flex-1">
                                <h3 class="font-bold text-xl text-surface-900 dark:text-white mb-2">{{ event.location.name }}</h3>
                                <p class="text-surface-600 dark:text-surface-300">{{ event.location.address }}</p>
                                <p class="text-surface-600 dark:text-surface-300">{{ event.location.zip }} {{ event.location.city }}, {{ event.location.country }}</p>
                                <Link :href="route('locations.show', event.location.slug)" class="inline-flex items-center gap-1 text-brand-600 font-medium hover:underline mt-3 text-sm">
                                    Mehr zur Location
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </Link>
                            </div>
                        </div>
                        <div class="h-64 rounded-2xl overflow-hidden border border-surface-200 dark:border-surface-800">
                            <iframe
                                width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                                :src="`https://maps.google.com/maps?width=100%25&height=400&hl=de&q=${encodeURIComponent(event.location.address + ', ' + event.location.zip + ' ' + event.location.city)}&t=&z=14&ie=UTF8&iwloc=B&output=embed`"
                                class="w-full h-full grayscale contrast-125 opacity-80"
                            ></iframe>
                        </div>
                    </div>
                    <!-- Reviews Section -->
                    <div class="bg-white dark:bg-surface-900 rounded-3xl p-8 shadow-sm border border-surface-200 dark:border-surface-800">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="font-display font-bold text-2xl text-surface-900 dark:text-white">Bewertungen</h2>
                            <div v-if="event.average_rating > 0" class="flex items-center gap-2">
                                <span class="text-2xl font-bold text-surface-900 dark:text-white">{{ event.average_rating }}</span>
                                <div class="flex text-yellow-400">
                                    <svg v-for="i in 5" :key="i" class="w-5 h-5" :class="i <= Math.round(event.average_rating) ? 'text-yellow-400' : 'text-surface-300'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                </div>
                                <span class="text-sm text-surface-500">({{ event.reviews?.length || 0 }})</span>
                            </div>
                        </div>

                        <!-- Review Form -->
                        <div v-if="can_review" class="mb-8 p-6 bg-surface-50 dark:bg-surface-950 rounded-2xl border border-surface-200 dark:border-surface-800">
                            <h3 class="font-bold text-lg text-surface-900 dark:text-white mb-4">Wie hat dir das Event gefallen?</h3>
                            
                            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-xl mb-4 text-sm font-medium">
                                {{ $page.props.flash.success }}
                            </div>
                            <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl mb-4 text-sm font-medium">
                                {{ $page.props.flash.error }}
                            </div>

                            <form @submit.prevent="submitReview" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 mb-2">Deine Bewertung</label>
                                    <div class="flex gap-2">
                                        <button type="button" v-for="star in 5" :key="star" @click="reviewForm.rating = star" class="focus:outline-none transition-transform hover:scale-110">
                                            <svg class="w-8 h-8" :class="star <= reviewForm.rating ? 'text-yellow-400' : 'text-surface-300 dark:text-surface-600'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        </button>
                                    </div>
                                    <p v-if="reviewForm.errors.rating" class="text-red-500 text-xs mt-1">{{ reviewForm.errors.rating }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 mb-1">Kommentar (optional)</label>
                                    <textarea v-model="reviewForm.comment" rows="3" class="w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Teile deine Erfahrungen mit anderen..."></textarea>
                                    <p v-if="reviewForm.errors.comment" class="text-red-500 text-xs mt-1">{{ reviewForm.errors.comment }}</p>
                                </div>
                                
                                <button type="submit" :disabled="reviewForm.processing" class="bg-brand-500 text-white font-bold py-2.5 px-6 rounded-xl shadow hover:bg-brand-600 transition-colors disabled:opacity-50">
                                    {{ reviewForm.processing ? 'Wird gesendet...' : 'Bewertung absenden' }}
                                </button>
                            </form>
                        </div>

                        <!-- Review List -->
                        <div v-if="event.reviews && event.reviews.length > 0" class="space-y-6">
                            <div v-for="review in event.reviews" :key="review.id" class="border-b border-surface-200 dark:border-surface-800 pb-6 last:border-0 last:pb-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-lg">
                                        {{ review.user?.name?.charAt(0) || 'U' }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-surface-900 dark:text-white">{{ review.user?.name || 'Anonym' }}</div>
                                        <div class="flex items-center gap-2">
                                            <div class="flex text-yellow-400">
                                                <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= review.rating ? 'text-yellow-400' : 'text-surface-300'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            </div>
                                            <span class="text-xs text-surface-500">{{ new Date(review.created_at).toLocaleDateString('de-DE') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="review.comment" class="text-surface-700 dark:text-surface-300 ml-13 pl-13">{{ review.comment }}</p>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-surface-500">
                            Noch keine Bewertungen vorhanden. Sei der Erste!
                        </div>
                    </div>

                </div>

                <!-- Right: Ticket Purchase Sidebar (Sticky) -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-surface-900 rounded-3xl shadow-sm border border-surface-200 dark:border-surface-800 p-8 sticky top-28">
                        <!-- FOMO Banners -->
                        <div class="space-y-3 mb-6">
                            <div v-if="socialProof.viewing_now > 0" class="bg-red-50 border border-red-100 rounded-xl p-3 flex items-start gap-3 animate-pulse">
                                <span class="text-xl">🔥</span>
                                <div>
                                    <p class="text-sm font-bold text-red-800">Gefragt!</p>
                                    <p class="text-xs text-red-600 mt-0.5">{{ socialProof.viewing_now }} Personen schauen sich dieses Event gerade an.</p>
                                </div>
                            </div>

                            <div v-if="socialProof.show_sold_out_badge" class="bg-orange-50 border border-orange-100 rounded-xl p-3 flex items-start gap-3">
                                <span class="text-xl">🎫</span>
                                <div class="w-full">
                                    <p class="text-sm font-bold text-orange-800">Tickets werden knapp</p>
                                    <p class="text-xs text-orange-600 mt-0.5 mb-2">Bereits {{ socialProof.sold_out_percentage }}% aller Tickets sind verkauft.</p>
                                    <div class="w-full bg-orange-200 rounded-full h-1.5">
                                        <div class="bg-orange-500 h-1.5 rounded-full" :style="{ width: socialProof.sold_out_percentage + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!isSoldOut">
                            <h3 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-6">Tickets buchen</h3>

                            <div v-if="event.ticket_categories && event.ticket_categories.length" class="space-y-3 mb-6">
                                <div v-for="cat in event.ticket_categories" :key="cat.id"
                                    class="p-4 border rounded-2xl transition-all"
                                    :class="(selectedTickets[cat.id] || 0) > 0 ? 'border-brand-400 bg-brand-50' : 'border-surface-200 dark:border-surface-800 bg-surface-50 dark:bg-surface-950'">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1 mr-4">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <h4 class="font-bold text-surface-900 dark:text-white text-sm">{{ cat.name }}</h4>
                                                <span v-if="cat.is_default" class="text-xs bg-brand-100 text-brand-600 px-2 py-0.5 rounded-full font-medium">Standard</span>
                                            </div>
                                            <p class="text-brand-600 font-bold">{{ parseFloat(cat.price).toFixed(2).replace('.',',') }} €</p>
                                            <p v-if="cat.quantity != null && event.show_remaining_tickets !== false" class="text-xs mt-0.5" :class="(cat.quantity - (cat.sold || 0)) < 10 ? 'text-red-500 font-bold' : 'text-surface-400'">
                                                <span v-if="(cat.quantity - (cat.sold || 0)) <= 0">Ausverkauft!</span>
                                                <span v-else-if="(cat.quantity - (cat.sold || 0)) < 10">Nur noch {{ Math.max(0, cat.quantity - (cat.sold || 0)) }} Tickets verfügbar!</span>
                                                <span v-else>{{ Math.max(0, cat.quantity - (cat.sold || 0)) }} verfügbar</span>
                                            </p>
                                        </div>
                                        <!-- +/- Stepper -->
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button @click="decrement(cat.id)"
                                                :disabled="(selectedTickets[cat.id] || 0) === 0"
                                                class="w-9 h-9 rounded-full border-2 border-surface-300 flex items-center justify-center font-bold text-lg text-surface-700 hover:border-brand-400 hover:text-brand-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                                                −
                                            </button>
                                            <span class="w-8 text-center font-bold text-surface-900 dark:text-white">{{ selectedTickets[cat.id] || 0 }}</span>
                                            <button @click="increment(cat.id, cat)"
                                                :disabled="(selectedTickets[cat.id] || 0) >= maxForCategory(cat)"
                                                class="w-9 h-9 rounded-full border-2 border-surface-300 flex items-center justify-center font-bold text-lg text-surface-700 hover:border-brand-400 hover:text-brand-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-surface-500 dark:text-surface-400 italic mb-6 p-4 bg-surface-50 dark:bg-surface-950 rounded-2xl text-center">
                                Aktuell keine Tickets verfügbar.
                            </div>

                            <template v-if="availableAddons.length">
                                <p class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-wider mb-3 mt-2">Extras & Upgrades</p>
                                <div class="space-y-3 mb-6">
                                    <div v-for="addon in availableAddons" :key="addon.id"
                                        class="flex justify-between items-center p-3 border rounded-xl transition-all"
                                        :class="(selectedAddons[addon.id] || 0) > 0 ? 'border-brand-300 bg-brand-50' : 'border-surface-200 dark:border-surface-800 bg-surface-50 dark:bg-surface-950'">
                                        <div>
                                            <div class="font-medium text-surface-900 dark:text-white text-sm">{{ addon.name }}</div>
                                            <div class="text-brand-600 font-medium text-sm">+ {{ parseFloat(addon.price).toFixed(2).replace('.',',') }} €</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click="decrementAddon(addon.id)" :disabled="(selectedAddons[addon.id] || 0) === 0"
                                                class="w-8 h-8 rounded-full border-2 border-surface-300 flex items-center justify-center font-bold text-surface-700 hover:border-brand-400 hover:text-brand-600 disabled:opacity-30 transition-all">−</button>
                                            <span class="w-6 text-center font-bold text-surface-900 dark:text-white text-sm">{{ selectedAddons[addon.id] || 0 }}</span>
                                            <button @click="incrementAddon(addon.id)"
                                                class="w-8 h-8 rounded-full border-2 border-surface-300 flex items-center justify-center font-bold text-surface-700 hover:border-brand-400 hover:text-brand-600 transition-all">+</button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Sibling Dates -->
                            <div v-if="event.siblingDates && event.siblingDates.length" class="mb-4">
                                <p class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-wider mb-2">Weitere Termine</p>
                                <div class="space-y-2">
                                    <Link v-for="sibling in event.siblingDates" :key="sibling.id"
                                        :href="route('event.show', sibling.slug)"
                                        class="flex items-center justify-between p-3 border border-surface-200 dark:border-surface-800 rounded-xl hover:border-brand-300 hover:bg-brand-50 transition-all text-sm">
                                        <span class="font-medium text-surface-800">{{ formatDate(sibling.start_date) }}</span>
                                        <span class="text-brand-600 font-bold text-xs">Tickets →</span>
                                    </Link>
                                </div>
                            </div>

                            <div class="border-t border-surface-200 dark:border-surface-800 pt-5">
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-surface-600 dark:text-surface-300 font-medium">Gesamt</span>
                                    <span class="font-display font-black text-3xl text-surface-900 dark:text-white">{{ totalAmount.replace('.',',') }} €</span>
                                </div>
                                <p class="text-xs text-surface-400 mb-4">inkl. MwSt.</p>

                                <button @click="checkout" :disabled="!hasTickets" class="w-full bg-brand-500 text-white font-bold py-4 rounded-2xl shadow-md hover:bg-brand-600 transition-colors disabled:opacity-40 disabled:cursor-not-allowed text-lg">
                                    Jetzt buchen
                                </button>
                                <p class="text-xs text-center text-surface-500 dark:text-surface-400 mt-3">🔒 Sicher bezahlen mit Stripe</p>
                            </div>
                        </div>

                        <!-- Waitlist Form (Sold Out) -->
                        <div v-else>
                            <div class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-6 text-center">
                                <h3 class="font-display font-bold text-xl text-red-900 mb-1">Leider ausverkauft</h3>
                                <p class="text-red-700 text-sm">Alle Tickets für diesen Termin sind vergriffen.</p>
                            </div>

                            <h3 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-4">Warteliste beitreten</h3>
                            <p class="text-surface-600 dark:text-surface-300 text-sm mb-6">Trage dich in die Warteliste ein, und wir informieren dich sofort, sobald wieder Tickets (z.B. durch Stornierungen) verfügbar werden.</p>

                            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-xl mb-6 text-sm font-medium">
                                {{ $page.props.flash.success }}
                            </div>

                            <form @submit.prevent="submitWaitlist" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 mb-1">Name</label>
                                    <input v-model="waitlistForm.name" type="text" class="w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-surface-700 mb-1">E-Mail Adresse</label>
                                    <input v-model="waitlistForm.email" type="email" class="w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" required />
                                </div>
                                <button type="submit" :disabled="waitlistForm.processing" class="w-full bg-surface-900 text-white font-bold py-3.5 rounded-xl shadow hover:bg-surface-800 transition-colors disabled:opacity-50 mt-2">
                                    {{ waitlistForm.processing ? 'Bitte warten...' : 'In Warteliste eintragen' }}
                                </button>
                            </form>

                            <!-- Sibling Dates -->
                            <div v-if="event.siblingDates && event.siblingDates.length" class="mt-8 border-t border-surface-200 dark:border-surface-800 pt-6">
                                <p class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-wider mb-3">Ausweichen auf andere Termine</p>
                                <div class="space-y-2">
                                    <Link v-for="sibling in event.siblingDates" :key="sibling.id"
                                        :href="route('event.show', sibling.slug)"
                                        class="flex items-center justify-between p-3 border border-surface-200 dark:border-surface-800 rounded-xl hover:border-brand-300 hover:bg-brand-50 transition-all text-sm">
                                        <span class="font-medium text-surface-800">{{ formatDate(sibling.start_date) }}</span>
                                        <span class="text-brand-600 font-bold text-xs">Ansehen →</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <Footer />

        <!-- Event Change Confirmation Modal -->
        <Modal :show="showConfirmEventChange" @close="showConfirmEventChange = false">
            <div class="p-6">
                <h2 class="text-lg font-bold text-surface-900 dark:text-white mb-4">Warenkorb leeren?</h2>
                <p class="text-surface-600 dark:text-surface-300 mb-6">
                    Du hast bereits Tickets für <strong>{{ cartStore.event?.title }}</strong> im Warenkorb. 
                    Möchtest du diese entfernen, um Tickets für <strong>{{ event.title }}</strong> hinzuzufügen?
                </p>
                <div class="flex justify-end gap-3">
                    <button @click="showConfirmEventChange = false" class="px-4 py-2 bg-surface-100 dark:bg-surface-800 hover:bg-surface-200 text-surface-700 rounded-xl font-bold transition-colors">
                        Abbrechen
                    </button>
                    <button @click="confirmEventChange" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold transition-colors shadow-sm">
                        Ja, Warenkorb leeren
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

