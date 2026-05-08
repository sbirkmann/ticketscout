<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import { defineProps } from 'vue';

const props = defineProps({
    category: Object,
    events: Array
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<template>
    <Head :title="`${category.name} Tickets - Ticketsout24`" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <div class="relative py-20 bg-surface-900 border-b-8 border-brand-500">
            <div class="absolute inset-0">
                <img v-if="category.image_path" :src="`/storage/${category.image_path}`" class="w-full h-full object-cover opacity-30" />
                <div class="absolute inset-0 bg-gradient-to-t from-surface-900 via-surface-900/60 to-transparent"></div>
            </div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="font-display font-black text-5xl md:text-6xl text-white mb-6 drop-shadow-md">{{ category.name }}</h1>
                <p class="text-xl text-surface-300 max-w-2xl mx-auto">{{ category.description }}</p>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            
            <div class="flex justify-between items-end mb-8 border-b border-surface-200 pb-4">
                <h2 class="font-display font-bold text-3xl text-surface-900">Aktuelle Top-Events</h2>
                <span class="text-surface-500 font-medium">{{ events.length }} Event(s) gefunden</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <div v-for="event in events" :key="event.id" class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-surface-200 flex flex-col">
                    <Link :href="route('event.show', event.slug)" class="block h-48 relative overflow-hidden bg-surface-200">
                        <img v-if="event.image_path" :src="`/storage/${event.image_path}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                    </Link>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-display font-bold text-xl text-surface-900 mb-2 group-hover:text-brand-600 transition-colors">
                                <Link :href="route('event.show', event.slug)">{{ event.title }}</Link>
                            </h3>
                            <div class="text-sm text-surface-500 flex items-center gap-2 mb-1 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ formatDate(event.start_date) }}
                            </div>
                            <div class="text-sm text-surface-500 flex items-center gap-2 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ event.location ? event.location.name : 'TBA' }}
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-surface-100 flex justify-between items-center">
                            <span v-if="event.min_price != null" class="text-brand-600 font-bold">
                                ab {{ parseFloat(event.min_price).toFixed(2).replace('.', ',') }} €
                            </span>
                            <span v-else class="text-surface-400 font-medium italic text-sm">Preis auf Anfrage</span>
                            <Link :href="route('event.show', event.slug)" class="bg-surface-900 text-white p-2 rounded-full hover:bg-surface-800 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" /></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="events.length === 0" class="text-center py-20 text-surface-500 text-lg bg-white rounded-3xl shadow-sm border border-surface-200 mt-8">
                In dieser Kategorie sind aktuell keine Events geplant.
            </div>

        </main>
    </div>
</template>
