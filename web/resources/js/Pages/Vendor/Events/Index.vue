<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    events: Array
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

const selectedEvents = ref([]);
const bulkAction = ref('');

const toggleAll = (event) => {
    if (event.target.checked) {
        selectedEvents.value = props.events.map(e => e.id);
    } else {
        selectedEvents.value = [];
    }
};

const executeBulkAction = () => {
    if (!bulkAction.value || selectedEvents.value.length === 0) return;
    
    if (confirm(`Möchtest du wirklich ${selectedEvents.value.length} Events ${bulkAction.value === 'delete' ? 'löschen' : 'aktualisieren'}?`)) {
        router.post(route('vendor.events.bulk'), {
            action: bulkAction.value,
            event_ids: selectedEvents.value
        }, {
            onSuccess: () => {
                selectedEvents.value = [];
                bulkAction.value = '';
            }
        });
    }
};
</script>

<template>
    <Head title="Meine Events" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center flex-wrap gap-4">
                <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Meine Events</h2>
                
                <div class="flex items-center gap-4">
                    <div v-if="selectedEvents.length > 0" class="flex items-center gap-2 bg-surface-100 px-4 py-2 rounded-full">
                        <span class="text-sm font-bold text-surface-600">{{ selectedEvents.length }} markiert</span>
                        <select v-model="bulkAction" class="text-sm border-surface-300 rounded-lg focus:ring-brand-400 py-1 pl-3 pr-8">
                            <option value="">Aktion wählen...</option>
                            <option value="publish">Veröffentlichen</option>
                            <option value="draft">Als Entwurf markieren</option>
                            <option value="delete">Löschen</option>
                        </select>
                        <button @click="executeBulkAction" class="bg-surface-800 text-white px-3 py-1 rounded-lg text-sm font-bold hover:bg-black transition-colors">Ausführen</button>
                    </div>

                    <Link :href="route('vendor.events.create')" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-full font-medium transition-all shadow-md flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Event anlegen
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="events.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-12 text-center border border-surface-200">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-50 text-brand-500 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-display font-semibold text-surface-900 mb-2">Noch keine Events</h3>
                    <p class="text-surface-500 max-w-md mx-auto mb-6">Du hast noch keine Events erstellt. Beginne jetzt und lege dein erstes Event an.</p>
                    <Link :href="route('vendor.events.create')" class="inline-flex text-brand-600 hover:text-brand-700 font-bold">
                        Erstes Event erstellen &rarr;
                    </Link>
                </div>

                <div v-else>
                    <div class="mb-4 flex items-center gap-2">
                        <input type="checkbox" @change="toggleAll" :checked="selectedEvents.length === events.length && events.length > 0" class="rounded border-surface-300 text-brand-500 focus:ring-brand-500 w-5 h-5 cursor-pointer">
                        <span class="text-sm font-medium text-surface-600">Alle auswählen</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="event in events" :key="event.id" class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden hover:shadow-md transition-all duration-300 group flex flex-col relative" :class="{'ring-2 ring-brand-500': selectedEvents.includes(event.id)}">
                            <div class="absolute top-4 left-4 z-10 bg-white/80 backdrop-blur rounded p-1 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity" :class="{'opacity-100': selectedEvents.includes(event.id)}">
                                <input type="checkbox" :value="event.id" v-model="selectedEvents" class="rounded border-surface-300 text-brand-500 focus:ring-brand-500 w-5 h-5 cursor-pointer">
                            </div>
                            
                            <div class="h-48 bg-surface-200 relative overflow-hidden">
                                <img v-if="event.image_path" :src="`/storage/${event.image_path}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            <div v-else class="w-full h-full flex items-center justify-center text-surface-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm" :class="event.status === 'published' ? 'text-green-600' : 'text-surface-500'">
                                {{ event.status === 'published' ? 'Aktiv' : 'Entwurf' }}
                            </div>
                        </div>
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="text-xs font-bold text-brand-600 mb-1" v-if="event.category">{{ event.category.name }}</div>
                            <h3 class="text-xl font-display font-bold text-surface-900 mb-2 group-hover:text-brand-600 transition-colors">
                                <Link :href="route('vendor.events.show', event.id)">{{ event.title }}</Link>
                            </h3>
                            <p class="text-sm text-surface-500 mb-4 flex-1 line-clamp-2">{{ event.description || 'Keine Beschreibung.' }}</p>
                            
                            <div class="flex items-center justify-between mt-auto pt-4 border-t border-surface-100">
                                <div class="text-sm font-medium text-surface-600 flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ formatDate(event.start_date) }}
                                </div>
                                <div class="flex gap-2">
                                    <Link v-if="event.seating_plan_id" :href="route('vendor.events.seating', event.id)" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors">
                                        Saalplan
                                    </Link>
                                    <Link :href="route('vendor.events.show', event.id)" class="text-brand-600 hover:text-brand-800 font-bold text-sm bg-brand-50 px-3 py-1.5 rounded-lg transition-colors">
                                        Verwalten →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
