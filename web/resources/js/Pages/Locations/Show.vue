<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    location: Object,
    events: Array
});

function formatDate(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<template>
    <Head :title="`${location.name} | Ticketsout24`" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <Navbar />

        <!-- Location Hero -->
        <div class="relative h-[40vh] min-h-64 bg-surface-900 overflow-hidden">
            <img v-if="location.image_path" :src="'/storage/' + location.image_path" class="w-full h-full object-cover opacity-50" />
            <div class="absolute inset-0 bg-gradient-to-t from-surface-900 via-surface-900/50 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
                <h1 class="font-display font-black text-4xl md:text-5xl text-white drop-shadow-lg leading-tight">{{ location.name }}</h1>
                <div class="flex items-center gap-2 mt-3 text-surface-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span class="text-lg">{{ location.city }}, {{ location.country }}</span>
                </div>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                
                <!-- Left: Info -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                        <h3 class="font-bold text-xl text-surface-900 mb-4">Über die Location</h3>
                        <p class="text-surface-600 mb-6">{{ location.description || 'Diese Location bietet regelmäßig spannende Events und Veranstaltungen.' }}</p>
                        
                        <div class="space-y-4 pt-4 border-t border-surface-100">
                            <div>
                                <div class="text-xs font-bold text-surface-500 uppercase">Adresse</div>
                                <div class="text-surface-900">{{ location.address }}</div>
                                <div class="text-surface-900">{{ location.zip }} {{ location.city }}</div>
                            </div>
                        </div>
                        
                        <div class="h-48 mt-6 rounded-2xl overflow-hidden border border-surface-200">
                            <iframe
                                width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                                :src="`https://maps.google.com/maps?width=100%25&height=400&hl=de&q=${encodeURIComponent(location.address + ', ' + location.zip + ' ' + location.city)}&t=&z=14&ie=UTF8&iwloc=B&output=embed`"
                                class="w-full h-full grayscale contrast-125 opacity-80"
                            ></iframe>
                        </div>
                    </div>
                </div>

                <!-- Right: Events -->
                <div class="lg:col-span-2">
                    <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">Kommende Events in {{ location.name }}</h2>
                    
                    <div v-if="events.length > 0" class="space-y-4">
                        <Link v-for="event in events" :key="event.id" :href="route('event.show', event.slug)" class="group flex flex-col sm:flex-row gap-6 bg-white p-4 rounded-3xl border border-surface-200 hover:border-brand-300 hover:shadow-md transition-all">
                            <div class="w-full sm:w-48 h-32 rounded-2xl overflow-hidden bg-surface-100 shrink-0">
                                <img v-if="event.image_path" :src="'/storage/' + event.image_path" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <div class="flex-1 py-2 pr-2">
                                <div class="text-brand-600 font-bold text-xs mb-1">{{ event.category?.name || 'Event' }}</div>
                                <h3 class="text-lg font-bold text-surface-900 mb-1 group-hover:text-brand-600 transition-colors">{{ event.title }}</h3>
                                <div class="flex items-center gap-4 text-sm text-surface-500 mt-2">
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        {{ formatDate(event.start_date) }}
                                    </span>
                                </div>
                            </div>
                            <div class="sm:self-center">
                                <div class="hidden sm:flex items-center justify-center w-10 h-10 rounded-full bg-surface-50 group-hover:bg-brand-50 group-hover:text-brand-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-center py-12 bg-white rounded-3xl border border-surface-200">
                        <p class="text-surface-500">Aktuell sind keine Events für diese Location geplant.</p>
                    </div>
                </div>

            </div>
        </main>

        <Footer />
    </div>
</template>
