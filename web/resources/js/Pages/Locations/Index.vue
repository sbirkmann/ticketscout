<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { onMounted, ref } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const props = defineProps({
    locations: Array
});

const mapContainer = ref(null);

onMounted(() => {
    // Fix leaflet marker issue in vite/webpack
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
        iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    });

    if (mapContainer.value && props.locations.length > 0) {
        // Initialize map centered roughly on Germany if no specific bounds
        const map = L.map(mapContainer.value).setView([51.165691, 10.451526], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const bounds = L.latLngBounds();
        let hasValidCoords = false;

        props.locations.forEach(location => {
            if (location.lat && location.lng) {
                hasValidCoords = true;
                const marker = L.marker([location.lat, location.lng]).addTo(map);
                
                const popupContent = `
                    <div style="text-align: center;">
                        <strong style="display: block; font-size: 16px; margin-bottom: 5px;">${location.name}</strong>
                        <p style="margin: 0 0 10px 0; font-size: 13px; color: #666;">${location.city}</p>
                        <a href="/locations/${location.slug}" style="display: inline-block; background: #6366f1; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 12px;">Events ansehen</a>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                bounds.extend([location.lat, location.lng]);
            }
        });

        if (hasValidCoords) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }
});
</script>

<template>
    <Head title="Locations | Ticketsout24" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <Navbar />

        <div class="bg-surface-900 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="font-display font-black text-4xl text-white mb-4">Locations & Venues</h1>
                <p class="text-surface-400 max-w-2xl mx-auto text-lg">Entdecke die besten Veranstaltungsorte und Hallen für deine nächsten Events auf unserer interaktiven Karte.</p>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            <!-- Map Container -->
            <div class="bg-white p-2 rounded-3xl shadow-sm border border-surface-200 mb-12">
                <div ref="mapContainer" class="w-full h-[400px] rounded-2xl z-0"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <Link v-for="location in locations" :key="location.id" :href="route('locations.show', location.slug)" class="group bg-white rounded-3xl overflow-hidden shadow-sm border border-surface-200 hover:shadow-lg transition-all duration-300 flex flex-col">
                    <div class="h-48 bg-surface-200 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-t from-surface-900/80 to-transparent z-10"></div>
                        <img v-if="location.image_path" :src="'/storage/' + location.image_path" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div v-else class="w-full h-full flex items-center justify-center bg-surface-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <div class="absolute bottom-4 left-4 z-20">
                            <h2 class="text-xl font-bold text-white mb-1 group-hover:text-brand-400 transition-colors">{{ location.name }}</h2>
                            <div class="text-surface-300 text-sm flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ location.city }}
                            </div>
                        </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <p class="text-surface-600 text-sm mb-4 line-clamp-3">
                            {{ location.description || 'Erlebe unvergessliche Events in dieser Location. Klicke hier für weitere Informationen und kommende Veranstaltungen.' }}
                        </p>
                        <div class="mt-auto flex items-center text-brand-600 font-bold text-sm group-hover:translate-x-2 transition-transform">
                            Location Details & Events <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-if="locations.length === 0" class="text-center py-20 bg-white rounded-3xl border border-surface-200">
                <p class="text-surface-500">Momentan sind keine Locations verfügbar.</p>
            </div>

        </main>

        <Footer />
    </div>
</template>
