<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    artists: Array
});
</script>

<template>
    <Head title="Künstler & Acts | Ticketsout24" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <Navbar />

        <div class="bg-surface-900 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="font-display font-black text-4xl text-white mb-4">Künstler & Acts</h1>
                <p class="text-surface-400 max-w-2xl mx-auto text-lg">Entdecke Tourdaten und Tickets deiner Lieblingskünstler.</p>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
                <Link v-for="artist in artists" :key="artist.id" :href="route('artists.show', artist.slug)" class="group flex flex-col items-center text-center">
                    <div class="w-full aspect-square rounded-full overflow-hidden bg-surface-200 border-4 border-white shadow-md group-hover:border-brand-400 group-hover:shadow-xl transition-all duration-300 mb-4">
                        <img v-if="artist.image_path" :src="'/storage/' + artist.image_path" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div v-else class="w-full h-full flex items-center justify-center bg-surface-100 text-surface-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                    </div>
                    
                    <div v-if="artist.genre" class="text-xs font-bold text-brand-600 uppercase tracking-wider mb-1">
                        {{ artist.genre }}
                    </div>
                    <h2 class="text-lg font-bold text-surface-900 group-hover:text-brand-600 transition-colors">{{ artist.name }}</h2>
                </Link>
            </div>

            <div v-if="artists.length === 0" class="text-center py-20 bg-white rounded-3xl border border-surface-200">
                <p class="text-surface-500">Momentan sind keine Künstlerprofile verfügbar.</p>
            </div>

        </main>

        <Footer />
    </div>
</template>
