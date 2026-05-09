<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SuperadminLayout from '@/Layouts/SuperadminLayout.vue';

const props = defineProps({
    cities: Object
});

const showModal = ref(false);
const editingCity = ref(null);

const form = useForm({
    name: '',
    image: null
});

function openCreateModal() {
    editingCity.value = null;
    form.reset();
    showModal.value = true;
}

function openEditModal(city) {
    editingCity.value = city;
    form.name = city.name;
    form.image = null;
    showModal.value = true;
}

function saveCity() {
    if (editingCity.value) {
        form.post(route('superadmin.cities.update', editingCity.value.id), {
            onSuccess: () => {
                showModal.value = false;
            },
            forceFormData: true,
            _method: 'put'
        });
    } else {
        form.post(route('superadmin.cities.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
            forceFormData: true
        });
    }
}

function deleteCity(id) {
    if (confirm('Diesen Ort wirklich löschen?')) {
        router.delete(route('superadmin.cities.destroy', id));
    }
}
</script>

<template>
    <Head title="Orte verwalten" />

    <SuperadminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-surface-800 leading-tight">Orte verwalten</h2>
                <button @click="openCreateModal" class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-2 px-4 rounded-xl text-sm transition-colors">
                    + Neuer Ort
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-surface-900">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="border-b p-3">Name</th>
                                    <th class="border-b p-3">Slug</th>
                                    <th class="border-b p-3">Bild</th>
                                    <th class="border-b p-3 text-right">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="city in cities.data" :key="city.id" class="hover:bg-surface-50">
                                    <td class="border-b p-3 font-medium">{{ city.name }}</td>
                                    <td class="border-b p-3 text-surface-500">{{ city.slug }}</td>
                                    <td class="border-b p-3">
                                        <img v-if="city.image_path" :src="'/storage/' + city.image_path" class="h-10 w-auto rounded" />
                                    </td>
                                    <td class="border-b p-3 text-right">
                                        <button @click="openEditModal(city)" class="text-blue-500 hover:underline mr-3 text-sm">Bearbeiten</button>
                                        <button @click="deleteCity(city.id)" class="text-red-500 hover:underline text-sm">Löschen</button>
                                    </td>
                                </tr>
                                <tr v-if="!cities.data.length">
                                    <td colspan="4" class="p-4 text-center text-surface-500">Keine Orte gefunden.</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        <div class="mt-4 flex gap-2" v-if="cities.links && cities.links.length > 3">
                            <template v-for="(link, k) in cities.links" :key="k">
                                <button
                                    v-if="link.url"
                                    @click="router.get(link.url)"
                                    class="px-3 py-1 rounded text-sm transition-colors"
                                    :class="link.active ? 'bg-brand-500 text-white font-bold' : 'bg-surface-100 hover:bg-surface-200'"
                                    v-html="link.label"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex justify-center items-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">{{ editingCity ? 'Ort bearbeiten' : 'Neuer Ort' }}</h3>
                <form @submit.prevent="saveCity">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-surface-700 mb-1">Name</label>
                        <input v-model="form.name" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-surface-700 mb-1">Bild</label>
                        <input @change="e => form.image = e.target.files[0]" type="file" class="w-full text-sm text-surface-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100" />
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-surface-100 hover:bg-surface-200 rounded-xl font-bold transition-colors">Abbrechen</button>
                        <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold transition-colors">Speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </SuperadminLayout>
</template>
