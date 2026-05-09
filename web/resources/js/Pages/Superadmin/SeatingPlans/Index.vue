<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    seatingPlans: Array,
    locations: Array, // Need to pass locations for the dropdown
});

const showCreateModal = ref(false);
const editingPlan = ref(null);

const form = useForm({
    name: '',
    location_id: '',
    bg_image: null,
});

function openModal(plan = null) {
    editingPlan.value = plan;
    if (plan) {
        form.name = plan.name;
        form.location_id = plan.location_id;
        form.bg_image = null;
    } else {
        form.reset();
        form.bg_image = null;
    }
    showCreateModal.value = true;
}

function submit() {
    if (editingPlan.value) {
        form.post(route('superadmin.seating-plans.update', editingPlan.value.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('superadmin.seating-plans.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            }
        });
    }
}

function destroy(plan) {
    if (confirm(`Saalplan "${plan.name}" wirklich löschen?`)) {
        router.delete(route('superadmin.seating-plans.destroy', plan.id));
    }
}

function handleImageUpload(e) {
    form.bg_image = e.target.files[0];
}
</script>

<template>
    <Head title="Saalpläne verwalten - Superadmin" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <!-- Admin Header -->
        <div class="bg-surface-900 text-white px-6 py-4 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-4">
                <Link :href="route('superadmin.dashboard')" class="font-display font-black text-xl tracking-tighter">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </Link>
                <span class="text-surface-500">/</span>
                <span class="text-surface-300 font-medium">Saalpläne</span>
            </div>
            <Link :href="route('superadmin.dashboard')" class="text-surface-400 hover:text-white text-sm">
                &larr; Zurück zum Dashboard
            </Link>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex justify-between items-center mb-8">
                <h1 class="font-display text-3xl font-black text-surface-900">Saalpläne verwalten</h1>
                <button @click="openModal()" class="bg-brand-500 text-white px-6 py-3 rounded-xl font-bold hover:bg-brand-600 transition-colors flex items-center gap-2 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                    Neuer Saalplan
                </button>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-surface-50 border-b border-surface-200">
                        <tr>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Name</th>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Location</th>
                            <th class="text-right px-6 py-4 text-sm font-bold text-surface-700">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100">
                        <tr v-for="plan in seatingPlans" :key="plan.id" class="hover:bg-surface-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-surface-900">{{ plan.name }}</div>
                            </td>
                            <td class="px-6 py-4 text-surface-600 text-sm">{{ plan.location?.name || 'Keine' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="openModal(plan)" class="text-xs text-blue-500 hover:text-blue-700 font-medium px-3 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors">
                                        Bearbeiten
                                    </button>
                                    <button @click="destroy(plan)" class="text-xs text-red-500 hover:text-red-700 font-medium px-3 py-1 rounded-lg bg-red-50 hover:bg-red-100 transition-colors">
                                        Löschen
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="seatingPlans.length === 0">
                            <td colspan="3" class="text-center text-surface-500 py-12">Noch keine Saalpläne angelegt.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8">
                <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">{{ editingPlan ? 'Saalplan bearbeiten' : 'Neuer Saalplan' }}</h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Name *</label>
                        <input v-model="form.name" type="text" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-200" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Location *</label>
                        <select v-model="form.location_id" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-200" required>
                            <option value="">Bitte wählen...</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Hintergrundbild</label>
                        <input type="file" @change="handleImageUpload" accept="image/*" class="w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100" />
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-surface-100">
                        <button type="submit" :disabled="form.processing" class="flex-1 bg-brand-500 text-white py-3 rounded-xl font-bold hover:bg-brand-600 disabled:opacity-50 transition-colors">
                            Speichern
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
