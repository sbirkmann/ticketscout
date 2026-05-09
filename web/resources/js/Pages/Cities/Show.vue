<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import EventCard from '@/Components/EventCard.vue';

defineProps({
    city: Object,
    locations: Array,
    events: Array
});
</script>

<template>
    <Head :title="`Events in ${city.name} | Ticketsout24`" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <!-- Hero Section -->
        <div class="relative bg-surface-900 overflow-hidden min-h-[300px] flex items-center justify-center">
            <div class="absolute inset-0">
                <img v-if="city.image_path" :src="'/storage/' + city.image_path" class="w-full h-full object-cover opacity-50" />
                <div v-else class="w-full h-full bg-gradient-to-r from-brand-900 to-surface-900"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-surface-900 via-surface-900/60 to-transparent"></div>
            </div>
            
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center w-full mt-16">
                <h1 class="text-4xl md:text-6xl font-display font-black text-white tracking-tight mb-4 drop-shadow-lg">
                    {{ city.name }}
                </h1>
                <p class="text-xl text-surface-200 font-medium">Finde die besten Events und Locations in deiner Stadt.</p>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 -mt-10 relative z-20">
            
            <!-- Events in City -->
            <div class="mb-16">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-display font-black text-surface-900 tracking-tight">Anstehende Events</h2>
                </div>

                <div v-if="events.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <EventCard v-for="event in events" :key="event.id" :event="event" />
                </div>
                <div v-else class="bg-white rounded-3xl p-12 text-center border border-surface-200 shadow-sm">
                    <div class="w-16 h-16 bg-brand-50 text-brand-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-surface-900 mb-2">Aktuell keine Events in {{ city.name }}</h3>
                    <p class="text-surface-500">Komm bald wieder, wir aktualisieren unser Angebot täglich!</p>
                </div>
            </div>
            
            <!-- Locations in City -->
            <div>
                <h2 class="text-3xl font-display font-black text-surface-900 tracking-tight mb-8">Locations in {{ city.name }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link v-for="loc in locations" :key="loc.id" :href="route('locations.show', loc.slug)" class="group block bg-white rounded-3xl overflow-hidden border border-surface-200 shadow-sm hover:shadow-glow transition-all">
                        <div class="aspect-video bg-surface-200 relative">
                            <img v-if="loc.banner_image_path" :src="'/storage/' + loc.banner_image_path" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                        </div>
                        <div class="p-6">
                            <h3 class="font-bold text-xl text-surface-900 group-hover:text-brand-600 transition-colors">{{ loc.name }}</h3>
                            <p class="text-surface-500 text-sm mt-1">{{ loc.address }}</p>
                        </div>
                    </Link>
                </div>
            </div>

        </main>
        
        <Footer />
    </div>
</template>
