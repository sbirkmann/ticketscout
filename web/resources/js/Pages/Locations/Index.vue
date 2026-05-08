<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import { defineProps } from 'vue';

const props = defineProps({
    locations: Array
});
</script>

<template>
    <Head title="Locations - Ticketsout24" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <!-- Map Header -->
        <div class="w-full h-96 relative bg-surface-200">
            <!-- Google Maps iframe placeholder centering on Germany/Berlin -->
            <iframe 
                width="100%" 
                height="100%" 
                frameborder="0" 
                scrolling="no" 
                marginheight="0" 
                marginwidth="0" 
                src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=de&amp;q=Berlin+(Ticketsout24)&amp;t=&amp;z=11&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"
                class="absolute inset-0 grayscale contrast-125 opacity-80"
            ></iframe>
            <div class="absolute inset-0 bg-surface-900/30 flex items-center justify-center">
                <h1 class="font-display text-5xl font-black text-white drop-shadow-md">Locations entdecken</h1>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <Link v-for="loc in locations" :key="loc.id" :href="route('locations.show', loc.slug)" class="group block bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-surface-200">
                    <div class="h-48 relative overflow-hidden bg-surface-200">
                        <img v-if="loc.banner_image_path" :src="`/storage/${loc.banner_image_path}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-surface-700 to-surface-900">
                            <span class="text-surface-400 font-bold">Location</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="font-display font-bold text-xl text-surface-900 mb-2 group-hover:text-brand-600 transition-colors">{{ loc.name }}</h3>
                        <p class="text-surface-500 text-sm flex items-center gap-2 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ loc.city }}, {{ loc.country }}
                        </p>
                        <p class="text-surface-600 text-sm line-clamp-2">{{ loc.description }}</p>
                    </div>
                </Link>
            </div>

            <div v-if="locations.length === 0" class="text-center py-20 text-surface-500 text-lg">
                Keine Locations gefunden.
            </div>

        </main>
    </div>
</template>
