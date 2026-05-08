<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    artist: Object,
    events: Array
});

function formatDate(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<template>
    <Head :title="`${artist.name} Tickets & Tour | Ticketsout24`" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <Navbar />

        <!-- Artist Hero -->
        <div class="relative h-[50vh] min-h-72 bg-surface-900 overflow-hidden">
            <img v-if="artist.image_path" :src="'/storage/' + artist.image_path" class="w-full h-full object-cover opacity-50" />
            <div class="absolute inset-0 bg-gradient-to-t from-surface-900 via-surface-900/50 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10 flex items-end gap-6">
                <!-- Avatar Profile (optional, could be same as hero or separate if we had an avatar) -->
                <div class="w-32 h-32 rounded-full border-4 border-surface-900 bg-surface-200 shadow-xl overflow-hidden shrink-0 hidden md:block">
                    <img v-if="artist.image_path" :src="'/storage/' + artist.image_path" class="w-full h-full object-cover" />
                </div>
                
                <div class="mb-2">
                    <div v-if="artist.genre" class="inline-block bg-brand-500 text-white text-xs font-bold px-3 py-1 rounded-full mb-3 uppercase tracking-wider">
                        {{ artist.genre }}
                    </div>
                    <h1 class="font-display font-black text-4xl md:text-6xl text-white drop-shadow-lg leading-tight">{{ artist.name }}</h1>
                </div>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                
                <!-- Left: Info -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                        <h3 class="font-bold text-xl text-surface-900 mb-4">Über {{ artist.name }}</h3>
                        <p class="text-surface-600 whitespace-pre-wrap">{{ artist.bio || 'Aktuell keine Biografie hinterlegt.' }}</p>
                    </div>
                </div>

                <!-- Right: Tour Dates -->
                <div class="lg:col-span-2">
                    <h2 class="font-display font-bold text-3xl text-surface-900 mb-6">Tour & Events</h2>
                    
                    <div v-if="events.length > 0" class="space-y-4">
                        <Link v-for="event in events" :key="event.id" :href="route('event.show', event.slug)" class="group flex flex-col sm:flex-row gap-6 bg-white p-5 rounded-3xl border border-surface-200 hover:border-brand-300 hover:shadow-md transition-all">
                            <div class="flex flex-col items-center justify-center w-20 h-20 bg-surface-50 border border-surface-200 rounded-2xl shrink-0 group-hover:bg-brand-50 group-hover:border-brand-200 transition-colors">
                                <div class="text-xs text-brand-600 font-bold uppercase">{{ new Date(event.start_date).toLocaleDateString('de-DE', { month: 'short' }) }}</div>
                                <div class="text-2xl font-black text-surface-900 leading-none mt-0.5">{{ new Date(event.start_date).getDate() }}</div>
                            </div>
                            
                            <div class="flex-1 py-1">
                                <div class="text-surface-500 font-medium text-xs mb-1">{{ formatDate(event.start_date) }}</div>
                                <h3 class="text-xl font-bold text-surface-900 mb-1 group-hover:text-brand-600 transition-colors">{{ event.title }}</h3>
                                <div class="flex items-center gap-1.5 text-sm text-surface-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-surface-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                                    {{ event.location?.name }}, {{ event.location?.city }}
                                </div>
                            </div>
                            
                            <div class="sm:self-center w-full sm:w-auto">
                                <div class="bg-brand-50 text-brand-600 group-hover:bg-brand-600 group-hover:text-white text-center font-bold px-6 py-3 rounded-xl transition-colors w-full">
                                    Tickets
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-center py-16 bg-white rounded-3xl border border-surface-200">
                        <p class="text-surface-500 text-lg">Aktuell sind keine kommenden Termine für {{ artist.name }} geplant.</p>
                    </div>
                </div>

            </div>
        </main>

        <Footer />
    </div>
</template>
