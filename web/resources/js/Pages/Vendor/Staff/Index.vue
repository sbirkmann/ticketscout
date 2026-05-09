<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    staff: Array
});

const isModalOpen = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('vendor.staff.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const deleteStaff = (id) => {
    if (confirm('Möchtest du diesen Scanner-Account wirklich löschen?')) {
        useForm({}).delete(route('vendor.staff.destroy', id));
    }
};
</script>

<template>
    <Head title="Staff Accounts" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-display font-black text-2xl text-surface-900 leading-tight">
                    Team & Scanner
                </h2>
                <button @click="isModalOpen = true" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2 rounded-full font-bold shadow-md transition-all text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Scanner Account anlegen
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-surface-200">
                    <div class="p-8 border-b border-surface-100 bg-surface-50">
                        <h3 class="text-xl font-bold text-surface-900 mb-2">Deine Scanner-Accounts</h3>
                        <p class="text-surface-600">Lege Accounts für dein Team an. Diese Accounts können sich einloggen, sehen aber <strong>ausschließlich</strong> das Ticket-Scanner Modul und haben keinen Zugriff auf Verkäufe oder Events.</p>
                    </div>

                    <div class="p-8">
                        <div v-if="staff.length === 0" class="text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-surface-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <h3 class="text-lg font-bold text-surface-900 mb-2">Noch keine Teammitglieder</h3>
                            <p class="text-surface-500">Du hast noch keine separaten Scanner-Accounts angelegt.</p>
                        </div>
                        
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="user in staff" :key="user.id" class="border border-surface-200 rounded-2xl p-6 flex flex-col justify-between bg-white hover:border-brand-300 transition-colors">
                                <div>
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="w-12 h-12 bg-surface-100 rounded-full flex items-center justify-center text-surface-500 font-bold text-xl">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="bg-brand-100 text-brand-800 text-xs font-bold px-2 py-1 rounded">Scanner</span>
                                    </div>
                                    <h4 class="font-bold text-surface-900 text-lg">{{ user.name }}</h4>
                                    <p class="text-surface-500 text-sm mb-4">{{ user.email }}</p>
                                </div>
                                <button @click="deleteStaff(user.id)" class="text-red-500 hover:text-red-700 text-sm font-bold flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Löschen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-surface-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-xl max-w-md w-full overflow-hidden">
                <div class="p-6 border-b border-surface-100 flex justify-between items-center bg-surface-50">
                    <h3 class="text-lg font-bold text-surface-900">Neuen Scanner anlegen</h3>
                    <button @click="isModalOpen = false" class="text-surface-400 hover:text-surface-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Name</label>
                        <input v-model="form.name" type="text" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20" required>
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">E-Mail</label>
                        <input v-model="form.email" type="email" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20" required>
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Passwort</label>
                        <input v-model="form.password" type="password" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20" required>
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Passwort bestätigen</label>
                        <input v-model="form.password_confirmation" type="password" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20" required>
                    </div>
                    
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-surface-600 font-medium hover:bg-surface-100 rounded-xl transition-colors">Abbrechen</button>
                        <button type="submit" :disabled="form.processing" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2 rounded-xl font-bold transition-colors disabled:opacity-50">
                            Speichern
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
