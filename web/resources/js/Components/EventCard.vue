<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    event: Object
});

const page = usePage();
const isFavorited = ref(props.event.is_favorited || false);
const isLoading = ref(false);

const toggleFavorite = async (e) => {
    e.preventDefault(); // Prevent navigating to event
    if (!page.props.auth.user) {
        window.location.href = route('login');
        return;
    }
    
    isLoading.value = true;
    try {
        const response = await axios.post(route('events.favorite', props.event.id));
        isFavorited.value = response.data.is_favorite;
    } catch (error) {
        console.error("Error toggling favorite", error);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Link :href="route('event.show', event.slug)" class="group bg-white dark:bg-surface-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-glass border border-surface-200 dark:border-surface-700 hover:border-brand-300 transition-all duration-300 flex flex-col relative block">
        <!-- Favorite Button -->
        <button @click="toggleFavorite" :disabled="isLoading" class="absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-white/80 backdrop-blur shadow flex items-center justify-center transition-transform hover:scale-110 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-colors duration-300" :class="isFavorited ? 'text-red-500 fill-current' : 'text-surface-400'" viewBox="0 0 24 24" stroke="currentColor" :stroke-width="isFavorited ? 0 : 2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
        </button>

        <div class="relative h-48 overflow-hidden bg-surface-200 z-10">
            <img v-if="event.image_path" :src="`/storage/${event.image_path}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="event.title" />
            <div v-else class="w-full h-full flex items-center justify-center text-surface-400">
                Kein Bild
            </div>
            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-md px-3 py-1 rounded-md text-xs font-bold text-surface-900 shadow-sm flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-brand-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
                {{ new Date(event.start_date).toLocaleDateString('de-DE', { day: '2-digit', month: 'short', year: 'numeric' }) }}
            </div>
        </div>
        <div class="p-5 flex-1 flex flex-col z-10">
            <h3 class="font-bold text-lg text-surface-900 dark:text-white mb-2 leading-tight group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors line-clamp-2">{{ event.title }}</h3>
            <p class="text-surface-500 dark:text-surface-400 text-sm mb-4 flex-1 line-clamp-2">{{ event.description || 'Sichere dir jetzt deine Tickets.' }}</p>
            
            <div class="flex items-center text-surface-600 dark:text-surface-400 text-xs font-medium mt-auto pt-4 border-t border-surface-100 dark:border-surface-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span v-if="event.location">{{ event.location.city }}</span>
                <span v-else>Ort noch unbekannt</span>
            </div>
        </div>
    </Link>
</template>
