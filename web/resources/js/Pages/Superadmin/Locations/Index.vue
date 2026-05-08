<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    locations: Array
});

const showCreateModal = ref(false);

const form = useForm({
    name: '',
    address: '',
    city: '',
    zip: '',
    country: 'Deutschland',
    description: '',
    is_global: false,
});

function submit() {
    form.post(route('superadmin.locations.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
}

function toggleGlobal(location) {
    router.patch(route('superadmin.locations.toggle-global', location.id));
}

function destroy(location) {
    if (confirm(`Location "${location.name}" wirklich löschen?`)) {
        router.delete(route('superadmin.locations.destroy', location.id));
    }
}
</script>

<template>
    <Head title="Locations verwalten - Superadmin" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <!-- Admin Header -->
        <div class="bg-surface-900 text-white px-6 py-4 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-4">
                <Link :href="route('superadmin.dashboard')" class="font-display font-black text-xl tracking-tighter">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </Link>
                <span class="text-surface-500">/</span>
                <span class="text-surface-300 font-medium">Locations</span>
            </div>
            <Link :href="route('superadmin.dashboard')" class="text-surface-400 hover:text-white text-sm">
                &larr; Zurück zum Dashboard
            </Link>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex justify-between items-center mb-8">
                <h1 class="font-display text-3xl font-black text-surface-900">Locations verwalten</h1>
                <button @click="showCreateModal = true" class="bg-brand-500 text-white px-6 py-3 rounded-xl font-bold hover:bg-brand-600 transition-colors flex items-center gap-2 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                    Neue Location
                </button>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-surface-50 border-b border-surface-200">
                        <tr>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Name</th>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Adresse</th>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Stadt</th>
                            <th class="text-center px-6 py-4 text-sm font-bold text-surface-700">Auf Homepage (Global)</th>
                            <th class="text-right px-6 py-4 text-sm font-bold text-surface-700">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100">
                        <tr v-for="loc in locations" :key="loc.id" class="hover:bg-surface-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-surface-900">{{ loc.name }}</div>
                            </td>
                            <td class="px-6 py-4 text-surface-600 text-sm">{{ loc.address }}</td>
                            <td class="px-6 py-4 text-surface-600 text-sm">{{ loc.zip }} {{ loc.city }}</td>
                            <td class="px-6 py-4 text-center">
                                <button @click="toggleGlobal(loc)" :class="loc.is_global ? 'bg-brand-500 text-white' : 'bg-surface-200 text-surface-500'" class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors focus:outline-none" :title="loc.is_global ? 'Global — auf Homepage sichtbar' : 'Nicht global'">
                                    <span :class="loc.is_global ? 'translate-x-7' : 'translate-x-1'" class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('locations.show', loc.slug)" target="_blank" class="text-xs text-surface-500 hover:text-brand-600 font-medium px-3 py-1 rounded-lg bg-surface-100 hover:bg-brand-50 transition-colors">
                                        Ansehen
                                    </Link>
                                    <button @click="destroy(loc)" class="text-xs text-red-500 hover:text-red-700 font-medium px-3 py-1 rounded-lg bg-red-50 hover:bg-red-100 transition-colors">
                                        Löschen
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="locations.length === 0">
                            <td colspan="5" class="text-center text-surface-500 py-12">Noch keine Locations angelegt.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8">
                <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">Neue Location</h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Name *</label>
                        <input v-model="form.name" type="text" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-200" required />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Adresse *</label>
                        <input v-model="form.address" type="text" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-200" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">PLZ *</label>
                            <input v-model="form.zip" type="text" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-200" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Stadt *</label>
                            <input v-model="form.city" type="text" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-200" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Land</label>
                        <input v-model="form.country" type="text" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-200" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Beschreibung</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-200"></textarea>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <input id="is_global" v-model="form.is_global" type="checkbox" class="rounded border-surface-300 text-brand-500 focus:ring-brand-200" />
                        <label for="is_global" class="text-sm font-medium text-surface-700">Auf der Homepage anzeigen (global)</label>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-surface-100">
                        <button type="submit" :disabled="form.processing" class="flex-1 bg-brand-500 text-white py-3 rounded-xl font-bold hover:bg-brand-600 disabled:opacity-50 transition-colors">
                            Erstellen
                        </button>
                        <button type="button" @click="showCreateModal = false" class="flex-1 bg-surface-100 text-surface-700 py-3 rounded-xl font-bold hover:bg-surface-200 transition-colors">
                            Abbrechen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
