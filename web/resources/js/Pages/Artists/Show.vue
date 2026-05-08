<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    artist: Object,
    events: Array,
});

function formatDate(d) {
    return new Date(d).toLocaleDateString('de-DE', {
        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
    });
}

function formatTime(d) {
    return new Date(d).toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head :title="`${artist.name} – Künstler | Ticketsout24`">
        <meta name="description" :content="`Alle Infos und Tickets zu ${artist.name} auf Ticketsout24.`" />
    </Head>

    <div class="min-h-screen bg-surface-50 font-sans">
        <Navbar />

        <!-- Hero -->
        <div class="relative h-[55vh] min-h-80 bg-surface-900 overflow-hidden">
            <img v-if="artist.header_image_path" :src="`/storage/${artist.header_image_path}`" class="w-full h-full object-cover opacity-50" />
            <img v-else-if="artist.image_path" :src="`/storage/${artist.image_path}`" class="w-full h-full object-cover opacity-30 scale-110 blur-sm" />
            <div class="absolute inset-0 bg-gradient-to-t from-surface-900 via-surface-900/40 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 flex items-end gap-8">
                <!-- Avatar -->
                <div class="w-28 h-28 rounded-3xl overflow-hidden border-4 border-white shadow-xl shrink-0 bg-surface-800">
                    <img v-if="artist.image_path" :src="`/storage/${artist.image_path}`" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-4xl text-surface-400">🎵</div>
                </div>
                <div>
                    <div v-if="artist.genre" class="inline-block bg-brand-500 text-white text-xs font-bold px-3 py-1 rounded-full mb-3">{{ artist.genre }}</div>
                    <h1 class="font-display font-black text-5xl md:text-6xl text-white drop-shadow-lg">{{ artist.name }}</h1>
                </div>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

                <!-- Left: Bio & Links -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                        <h2 class="font-display font-bold text-xl text-surface-900 mb-4">Über den Künstler</h2>
                        <p class="text-surface-600 leading-relaxed">{{ artist.bio || 'Keine Biografie vorhanden.' }}</p>
                    </div>

                    <!-- Social Links -->
                    <div v-if="artist.instagram || artist.spotify || artist.youtube || artist.website" class="bg-white rounded-3xl p-6 shadow-sm border border-surface-200">
                        <h3 class="font-bold text-sm text-surface-500 uppercase tracking-wider mb-4">Links</h3>
                        <div class="space-y-3">
                            <a v-if="artist.instagram" :href="artist.instagram" target="_blank" class="flex items-center gap-3 text-sm text-surface-700 hover:text-brand-600 transition-colors font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07..."/></svg>
                                Instagram
                            </a>
                            <a v-if="artist.spotify" :href="artist.spotify" target="_blank" class="flex items-center gap-3 text-sm text-surface-700 hover:text-green-600 transition-colors font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#1DB954"/><path fill="white" d="M8.5 15.5a.5.5 0 01-.5-.5v-6a.5.5 0 011 0v6a.5.5 0 01-.5.5z"/></svg>
                                Spotify
                            </a>
                            <a v-if="artist.youtube" :href="artist.youtube" target="_blank" class="flex items-center gap-3 text-sm text-surface-700 hover:text-red-600 transition-colors font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                YouTube
                            </a>
                            <a v-if="artist.website" :href="artist.website" target="_blank" class="flex items-center gap-3 text-sm text-surface-700 hover:text-brand-600 transition-colors font-medium">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                Website
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right: Upcoming Events -->
                <div class="lg:col-span-2">
                    <h2 class="font-display font-bold text-3xl text-surface-900 mb-8">Kommende Events</h2>

                    <div class="space-y-4">
                        <div v-for="event in events" :key="event.id" class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow group flex">
                            <div class="w-32 shrink-0 relative overflow-hidden bg-surface-200">
                                <img v-if="event.image_path" :src="`/storage/${event.image_path}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div v-if="event.category" class="absolute top-2 left-2 bg-brand-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ event.category.name }}</div>
                            </div>
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-display font-bold text-xl text-surface-900 mb-1 group-hover:text-brand-600 transition-colors">{{ event.title }}</h3>
                                    <p class="text-sm text-surface-500 mb-1">📅 {{ formatDate(event.start_date) }}, {{ formatTime(event.start_date) }} Uhr</p>
                                    <p v-if="event.location" class="text-sm text-surface-500">📍 {{ event.location.name }}, {{ event.location.city }}</p>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <Link :href="route('event.show', event.slug)" class="bg-brand-500 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-brand-600 transition-colors">
                                        Tickets →
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-if="events.length === 0" class="text-center py-16 text-surface-500 bg-white rounded-3xl border border-surface-200">
                            Aktuell keine Events geplant.
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
