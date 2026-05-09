<script setup>
import { Head, Link } from '@inertiajs/vue3';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    events: Array
});

// Minimal mockup for an interactive map without pulling heavy dependencies like Leaflet
// We will render an SVG map of Germany or a custom bounding box, and plot the events
const activeEvent = ref(null);

function selectEvent(event) {
    activeEvent.value = event;
}

function closePopup() {
    activeEvent.value = null;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Interaktive Event-Karte" />

    <FrontendLayout>
        <div class="bg-surface-50 min-h-screen pt-24 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-8 text-center max-w-2xl mx-auto">
                    <h1 class="text-4xl font-display font-black text-surface-900 mb-4">Event-Karte</h1>
                    <p class="text-lg text-surface-600">Finde Events in deiner Umgebung auf unserer interaktiven Karte.</p>
                </div>

                <div class="bg-white rounded-3xl shadow-xl border border-surface-200 overflow-hidden relative" style="height: 600px;">
                    
                    <!-- MOCK MAP BACKGROUND (CSS Pattern) -->
                    <div class="absolute inset-0 bg-surface-100" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;"></div>
                    
                    <!-- Map Content Area -->
                    <div class="absolute inset-0 z-10 overflow-hidden">
                        
                        <!-- Event Pins -->
                        <div v-for="(event, idx) in events" :key="event.id" 
                             class="absolute transform -translate-x-1/2 -translate-y-1/2 cursor-pointer transition-transform hover:scale-125 z-20"
                             :style="{ top: `${20 + (idx * 15 % 60)}%`, left: `${20 + (idx * 25 % 60)}%` }"
                             @click="selectEvent(event)">
                             
                            <div class="relative">
                                <div class="w-8 h-8 bg-brand-500 rounded-full flex items-center justify-center text-white shadow-lg border-2 border-white animate-bounce-slow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <div class="absolute w-8 h-8 bg-brand-500 rounded-full inset-0 animate-ping opacity-20"></div>
                            </div>
                        </div>

                    </div>

                    <!-- Overlay Popup -->
                    <div v-if="activeEvent" class="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-30 w-full max-w-sm px-4 animate-in slide-in-from-bottom-4 fade-in duration-300">
                        <div class="bg-white rounded-2xl shadow-2xl border border-surface-200 p-6 relative">
                            <button @click="closePopup" class="absolute top-4 right-4 text-surface-400 hover:text-surface-600 bg-surface-100 rounded-full p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                            
                            <span class="bg-brand-100 text-brand-600 text-xs font-bold px-2 py-1 rounded-md mb-3 inline-block uppercase tracking-wider">{{ activeEvent.category }}</span>
                            <h3 class="font-display font-bold text-xl text-surface-900 mb-2 leading-tight">{{ activeEvent.title }}</h3>
                            
                            <div class="space-y-2 mt-4 mb-6">
                                <div class="flex items-start gap-3 text-surface-600 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span>{{ formatDate(activeEvent.start_date) }}</span>
                                </div>
                                <div class="flex items-start gap-3 text-surface-600 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span>
                                        <strong class="block text-surface-900">{{ activeEvent.location.name }}</strong>
                                        {{ activeEvent.location.city }}
                                    </span>
                                </div>
                            </div>
                            
                            <Link :href="route('events.show', activeEvent.slug)" class="block w-full py-3 bg-surface-900 hover:bg-black text-white text-center rounded-xl font-bold transition-colors">
                                Zum Event
                            </Link>
                        </div>
                    </div>

                    <!-- Overlay UI Elements -->
                    <div class="absolute top-6 left-6 z-20 flex gap-2">
                        <Link :href="route('home')" class="bg-white/90 backdrop-blur px-4 py-2 rounded-xl text-sm font-bold text-surface-700 shadow-sm border border-surface-200 hover:bg-white transition-colors">
                            &larr; Zurück
                        </Link>
                    </div>
                    <div class="absolute top-6 right-6 z-20 flex gap-2">
                        <span class="bg-brand-500 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md">
                            {{ events.length }} Events gefunden
                        </span>
                    </div>

                </div>

            </div>
        </div>
    </FrontendLayout>
</template>

<style scoped>
.animate-bounce-slow {
    animation: bounce 2s infinite;
}
@keyframes bounce {
    0%, 100% { transform: translateY(-10%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
    50% { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); }
}
</style>
