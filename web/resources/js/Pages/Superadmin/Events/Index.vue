<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    events: Object
});

function destroy(event) {
    if (confirm(`Event "${event.title}" wirklich löschen?`)) {
        router.delete(route('superadmin.events.destroy', event.id));
    }
}
</script>

<template>
    <Head title="Events verwalten - Superadmin" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <!-- Admin Header -->
        <div class="bg-surface-900 text-white px-6 py-4 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-4">
                <Link :href="route('superadmin.dashboard')" class="font-display font-black text-xl tracking-tighter">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </Link>
                <span class="text-surface-500">/</span>
                <span class="text-surface-300 font-medium">Events</span>
            </div>
            <Link :href="route('superadmin.dashboard')" class="text-surface-400 hover:text-white text-sm">
                &larr; Zurück zum Dashboard
            </Link>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex justify-between items-center mb-8">
                <h1 class="font-display text-3xl font-black text-surface-900">Alle Events</h1>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-surface-50 border-b border-surface-200">
                        <tr>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Titel</th>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Location</th>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Vendor</th>
                            <th class="text-center px-6 py-4 text-sm font-bold text-surface-700">Status</th>
                            <th class="text-right px-6 py-4 text-sm font-bold text-surface-700">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100">
                        <tr v-for="evt in events.data" :key="evt.id" class="hover:bg-surface-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-surface-900">{{ evt.title }}</div>
                                <div class="text-xs text-surface-500">{{ new Date(evt.start_date).toLocaleString() }}</div>
                            </td>
                            <td class="px-6 py-4 text-surface-600 text-sm">{{ evt.location?.name || 'Keine' }}</td>
                            <td class="px-6 py-4 text-surface-600 text-sm">{{ evt.vendor?.name || 'Keiner' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="evt.status === 'published'" class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded">Veröffentlicht</span>
                                <span v-else class="bg-surface-100 text-surface-700 text-xs font-bold px-2 py-1 rounded">{{ evt.status }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('event.show', evt.slug)" target="_blank" class="text-xs text-surface-500 hover:text-brand-600 font-medium px-3 py-1 rounded-lg bg-surface-100 hover:bg-brand-50 transition-colors">
                                        Ansehen
                                    </Link>
                                    <button @click="destroy(evt)" class="text-xs text-red-500 hover:text-red-700 font-medium px-3 py-1 rounded-lg bg-red-50 hover:bg-red-100 transition-colors">
                                        Löschen
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="events.data.length === 0">
                            <td colspan="5" class="text-center text-surface-500 py-12">Noch keine Events vorhanden.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex justify-center gap-2" v-if="events.last_page > 1">
                <Link v-for="link in events.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                      class="px-4 py-2 rounded-xl text-sm font-bold"
                      :class="link.active ? 'bg-brand-500 text-white' : 'bg-white text-surface-600 hover:bg-surface-100 border border-surface-200'"></Link>
            </div>
        </main>
    </div>
</template>
