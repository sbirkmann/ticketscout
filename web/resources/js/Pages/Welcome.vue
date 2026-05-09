<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import EventCard from '@/Components/EventCard.vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    events: Array,
    categories: Array,
    locations: Array
});
</script>

<template>
    <Head title="Ticketsout24 - Erlebe die besten Events" />

    <div class="min-h-screen bg-surface-50 dark:bg-surface-950 font-sans selection:bg-brand-500 selection:text-white transition-colors duration-200">
        <!-- Navigation -->
        <Navbar />

        <!-- Hero Banner Carousel -->
        <div class="relative bg-surface-900 overflow-hidden">
            <div class="absolute inset-0">
                <img src="/images/hero.png" class="w-full h-full object-cover opacity-40 blur-sm" alt="Concert background">
                <div class="absolute inset-0 bg-gradient-to-t from-surface-900 via-surface-900/60 to-transparent"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32 flex flex-col items-center text-center">
                <h1 class="font-display font-black text-5xl md:text-7xl text-white mb-6 tracking-tight drop-shadow-md">
                    Momente, die bleiben.
                </h1>
                <p class="text-xl md:text-2xl text-surface-200 max-w-3xl mb-10 font-medium drop-shadow">
                    Finde und buche Tickets für die besten Konzerte, Festivals und Events in deiner Nähe.
                </p>
                <!-- Mobile Search in Hero -->
                <div class="md:hidden w-full max-w-md relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-6 w-6 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" class="block w-full pl-12 pr-4 py-4 border-none rounded-full bg-white shadow-xl text-lg placeholder-surface-500 focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Suche Event...">
                </div>
            </div>
        </div>

        <!-- Categories Horizontal List (Eventim Style) -->
        <div class="bg-white dark:bg-surface-900 border-b border-surface-200 dark:border-surface-800 py-6 sticky top-20 z-40 shadow-sm hidden md:block transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-8 overflow-x-auto pb-2 scrollbar-hide">
                    <Link href="#top-events" class="whitespace-nowrap font-bold text-surface-900 dark:text-white border-b-2 border-brand-500 pb-1">Top Events</Link>
                    <Link v-for="cat in categories" :key="cat.id" :href="'/category/' + cat.slug" class="whitespace-nowrap font-medium text-surface-600 dark:text-surface-300 hover:text-brand-500 dark:hover:text-brand-400 transition-colors pb-1">
                        {{ cat.name }}
                    </Link>
                    <Link href="#locations" class="whitespace-nowrap font-medium text-surface-600 dark:text-surface-300 hover:text-brand-500 dark:hover:text-brand-400 transition-colors pb-1">Locations</Link>
                </div>
            </div>
        </div>

        <!-- Top Events Grid -->
        <div id="top-events" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-display font-bold text-3xl text-surface-900 dark:text-white mb-8 flex items-center gap-3 transition-colors duration-200">
                <span class="bg-brand-500 text-white p-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </span>
                Aktuelle Top Events
            </h2>

            <div v-if="events && events.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Event Card -->
                <EventCard v-for="event in events" :key="event.id" :event="event" />
            </div>
            <div v-else class="text-center py-16 bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 transition-colors duration-200">
                <p class="text-surface-500 dark:text-surface-400 text-lg">Aktuell keine Events verfügbar. Schau später wieder vorbei!</p>
            </div>
        </div>

        <!-- Featured Locations -->
        <div id="locations" class="bg-surface-100 dark:bg-surface-900 py-16 border-t border-surface-200 dark:border-surface-800 transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-end mb-8">
                    <h2 class="font-display font-bold text-3xl text-surface-900 dark:text-white flex items-center gap-3 transition-colors duration-200">
                        <span class="bg-surface-900 text-white p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </span>
                        Angesagte Locations
                    </h2>
                    <Link href="/locations" class="text-brand-600 font-bold hover:text-brand-700 hidden sm:block">Alle ansehen &rarr;</Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link v-for="location in locations" :key="location.id" :href="'/location/' + location.slug" class="group relative h-64 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                        <img :src="`/storage/${location.banner_image_path}`" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" :alt="location.name">
                        <div class="absolute inset-0 bg-gradient-to-t from-surface-900 via-surface-900/40 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-6">
                            <h3 class="font-display font-bold text-2xl text-white mb-1 group-hover:text-brand-400 transition-colors">{{ location.name }}</h3>
                            <p class="text-surface-200 text-sm flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ location.city }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-surface-900 text-surface-400 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-12 text-sm">
                <div class="col-span-1 md:col-span-2">
                    <div class="font-display font-black text-2xl text-white mb-4 flex items-center gap-3">
                        TICKETSOUT<span class="text-brand-500">24</span>
                    </div>
                    <p class="mb-6 max-w-sm text-surface-500">Dein Premium-Partner für unvergessliche Live-Erlebnisse. Entdecke Konzerte, Festivals, Sport-Events und mehr.</p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Entdecken</h4>
                    <ul class="space-y-3">
                        <li><a href="#top-events" class="hover:text-white transition-colors">Alle Events</a></li>
                        <li><a href="#locations" class="hover:text-white transition-colors">Locations</a></li>
                        <li><Link :href="route('gutscheine')" class="hover:text-white transition-colors">Gutscheine</Link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Service & Rechtliches</h4>
                    <ul class="space-y-3">
                        <li><Link :href="route('hilfe')" class="hover:text-white transition-colors">Hilfe & Kontakt</Link></li>
                        <li><Link :href="route('agb')" class="hover:text-white transition-colors">AGB</Link></li>
                        <li><Link :href="route('datenschutz')" class="hover:text-white transition-colors">Datenschutz</Link></li>
                        <li><Link :href="route('impressum')" class="hover:text-white transition-colors">Impressum</Link></li>
                    </ul>
                </div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-surface-800 text-center text-surface-600">
                &copy; {{ new Date().getFullYear() }} Ticketsout24. Alle Rechte vorbehalten.
            </div>
        </footer>
    </div>
</template>
