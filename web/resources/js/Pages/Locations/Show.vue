<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    location: Object,
    events: Array
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', {
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function formatPrice(price) {
    if (price == null) return null;
    return parseFloat(price).toFixed(2).replace('.', ',') + ' €';
}
</script>

<template>
    <Head :title="`${location.name} – Tickets & Events | Ticketsout24`" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <div class="relative h-96 bg-surface-900">
            <img v-if="location.banner_image_path" :src="`/storage/${location.banner_image_path}`" class="w-full h-full object-cover opacity-60" />
            <div class="absolute inset-0 bg-gradient-to-t from-surface-900 to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-full p-8 md:p-16">
                <div class="max-w-7xl mx-auto">
                    <h1 class="font-display font-black text-5xl md:text-6xl text-white mb-4 drop-shadow-md">{{ location.name }}</h1>
                    <p class="text-surface-200 text-lg flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        {{ location.address }}, {{ location.zip }} {{ location.city }}, {{ location.country }}
                    </p>
                </div>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Info Column -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-surface-200">
                        <h2 class="font-display font-bold text-2xl text-surface-900 mb-4">Über die Location</h2>
                        <p class="text-surface-600 leading-relaxed">{{ location.description || 'Keine Beschreibung verfügbar.' }}</p>
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-surface-200">
                        <h2 class="font-display font-bold text-2xl text-surface-900 mb-4">Karte & Anfahrt</h2>
                        <div class="h-48 bg-surface-200 rounded-xl overflow-hidden">
                            <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                                :src="`https://maps.google.com/maps?width=100%25&height=600&hl=de&q=${encodeURIComponent(location.address + ', ' + location.zip + ' ' + location.city)}&t=&z=14&ie=UTF8&iwloc=B&output=embed`"
                                class="w-full h-full grayscale contrast-125 opacity-80"></iframe>
                        </div>
                    </div>
                </div>

                <!-- Events Column -->
                <div class="lg:col-span-2">
                    <h2 class="font-display font-bold text-3xl text-surface-900 mb-8 border-b border-surface-200 pb-4">Kommende Events</h2>
                    <div class="space-y-6">
                        <div v-for="event in events" :key="event.id"
                            class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden hover:shadow-md transition-shadow group flex flex-col sm:flex-row">
                            <div class="w-full sm:w-48 h-48 sm:h-auto shrink-0 relative overflow-hidden bg-surface-200">
                                <img v-if="event.image_path" :src="`/storage/${event.image_path}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div v-if="event.category" class="absolute top-3 left-3 bg-brand-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                    {{ event.category.name }}
                                </div>
                            </div>
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-display font-bold text-xl text-surface-900 mb-2 group-hover:text-brand-600 transition-colors">
                                        <Link :href="route('event.show', event.slug)">{{ event.title }}</Link>
                                    </h3>
                                    <div class="text-sm text-surface-500 flex items-center gap-2 mb-1 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        {{ formatDate(event.start_date) }}
                                    </div>
                                    <p class="text-surface-600 text-sm line-clamp-2">{{ event.description }}</p>
                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <span v-if="event.min_price != null" class="font-bold text-surface-800">
                                        ab {{ formatPrice(event.min_price) }}
                                    </span>
                                    <span v-else class="text-surface-400 text-sm italic">Preis auf Anfrage</span>
                                    <Link :href="route('event.show', event.slug)"
                                        class="bg-brand-500 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-brand-600 transition-colors">
                                        Tickets →
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-if="events.length === 0" class="text-center py-12 text-surface-500 bg-white rounded-3xl border border-surface-200">
                            Aktuell keine Events in dieser Location geplant.
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
