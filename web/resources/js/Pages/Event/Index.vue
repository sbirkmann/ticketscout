<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import EventCard from '@/Components/EventCard.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    events: Object,
    filters: Object,
    categories: Array
});

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');
const sort = ref(props.filters.sort || 'date_asc');

const updateFilters = debounce(() => {
    router.get(route('events.index'), {
        search: search.value,
        category: category.value,
        date_from: date_from.value,
        date_to: date_to.value,
        sort: sort.value
    }, { preserveState: true, replace: true, preserveScroll: true });
}, 300);

watch([search, category, date_from, date_to, sort], updateFilters);

function clearFilters() {
    search.value = '';
    category.value = '';
    date_from.value = '';
    date_to.value = '';
    sort.value = 'date_asc';
}
</script>

<template>
    <Head title="Events durchsuchen | Ticketsout24" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white flex flex-col">
        <Navbar />

        <div class="bg-surface-900 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl font-display font-black text-white tracking-tight mb-4">Events entdecken</h1>
                <p class="text-lg text-surface-400 max-w-2xl mx-auto">Finde die besten Konzerte, Partys und Erlebnisse in deiner Umgebung. Nutze die Filter, um genau das zu finden, wonach du suchst.</p>
            </div>
        </div>

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
            <div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Sidebar Filters -->
                <aside class="w-full lg:w-80 shrink-0">
                    <div class="bg-white rounded-3xl shadow-sm border border-surface-200 p-6 sticky top-28">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="font-bold text-lg text-surface-900">Filter</h2>
                            <button @click="clearFilters" class="text-xs font-medium text-brand-600 hover:text-brand-700">Zurücksetzen</button>
                        </div>
                        
                        <div class="space-y-6">
                            <!-- Search -->
                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-2">Suche</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </div>
                                    <input type="text" v-model="search" class="w-full pl-9 rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" placeholder="Suchbegriff..." />
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-2">Kategorie</label>
                                <select v-model="category" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400">
                                    <option value="">Alle Kategorien</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            
                            <!-- Date Range -->
                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-2">Zeitraum</label>
                                <div class="space-y-2">
                                    <input type="date" v-model="date_from" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400 text-sm" />
                                    <input type="date" v-model="date_to" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400 text-sm" />
                                </div>
                            </div>
                            
                            <!-- Sorting -->
                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-2">Sortierung</label>
                                <select v-model="sort" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400">
                                    <option value="date_asc">Datum (früheste zuerst)</option>
                                    <option value="date_desc">Datum (späteste zuerst)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Results -->
                <div class="flex-grow">
                    <div class="mb-6">
                        <p class="text-surface-600 font-medium">{{ events.total }} {{ events.total === 1 ? 'Event' : 'Events' }} gefunden</p>
                    </div>

                    <div v-if="events.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <EventCard v-for="event in events.data" :key="event.id" :event="event" />
                    </div>
                    
                    <div v-else class="bg-white rounded-3xl p-12 text-center border border-surface-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-surface-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <h3 class="text-xl font-bold text-surface-900 mb-2">Keine Events gefunden</h3>
                        <p class="text-surface-500 mb-6">Versuche deine Suche oder die Filter anzupassen.</p>
                        <button @click="clearFilters" class="text-brand-600 font-bold hover:text-brand-700">Filter zurücksetzen</button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="events.last_page > 1" class="mt-12 flex justify-center gap-2">
                        <Link v-for="link in events.links" :key="link.label" :href="link.url || '#'" 
                              class="px-4 py-2 rounded-xl font-medium transition-colors border"
                              :class="{
                                  'bg-brand-500 text-white border-brand-500': link.active,
                                  'bg-white text-surface-700 border-surface-200 hover:bg-surface-50': !link.active && link.url,
                                  'bg-surface-50 text-surface-400 border-surface-200 cursor-not-allowed': !link.url
                              }"
                              v-html="link.label" />
                    </div>
                </div>
                
            </div>
        </main>
        <Footer />
    </div>
</template>
