<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import EventCard from '@/Components/EventCard.vue';

defineProps({
    favorites: Object
});
</script>

<template>
    <Head title="Meine Favoriten | Ticketsout24" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white flex flex-col">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
            <h1 class="font-display font-black text-3xl md:text-4xl text-surface-900 mb-2">Meine Favoriten</h1>
            <p class="text-surface-600 mb-10 text-lg">Hier findest du alle Events, die du dir gemerkt hast.</p>

            <div v-if="favorites.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <EventCard v-for="event in favorites.data" :key="event.id" :event="event" />
            </div>

            <div v-else class="text-center py-20 bg-white rounded-3xl border border-surface-200 shadow-sm">
                <div class="w-16 h-16 bg-surface-100 rounded-full flex items-center justify-center mx-auto mb-4 text-surface-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-surface-900 mb-2">Noch keine Favoriten</h3>
                <p class="text-surface-500 mb-6">Du hast dir noch keine Events gemerkt.</p>
                <Link :href="route('home')" class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-3 px-6 rounded-xl transition-colors inline-block">
                    Events entdecken
                </Link>
            </div>

            <!-- Pagination -->
            <div class="mt-10 flex justify-center gap-2" v-if="favorites.last_page > 1">
                <Link v-for="link in favorites.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                      class="px-4 py-2 rounded-xl text-sm font-bold transition-colors"
                      :class="link.active ? 'bg-brand-500 text-white' : 'bg-white text-surface-600 hover:bg-surface-100 border border-surface-200'"></Link>
            </div>
        </main>

        <Footer />
    </div>
</template>
