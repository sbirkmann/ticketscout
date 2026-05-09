<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    events: Array
});

const form = useForm({
    code: '',
    type: 'percent',
    value: '',
    event_id: '',
    max_uses: '',
    expires_at: '',
});

const submit = () => {
    form.post(route('vendor.promo-codes.store'));
};

const generateCode = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 8; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.code = 'PROMO-' + result;
};
</script>

<template>
    <Head title="Neuer Rabattcode" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('vendor.promo-codes.index')" class="text-surface-400 hover:text-surface-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Neuen Rabattcode anlegen</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm border border-surface-200 sm:rounded-2xl overflow-hidden">
                    <form @submit.prevent="submit" class="p-8 space-y-8">
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1">Gutscheincode *</label>
                                <div class="flex gap-2">
                                    <input v-model="form.code" type="text" class="flex-1 rounded-xl border-surface-300 uppercase focus:ring-brand-500 focus:border-brand-500 font-mono tracking-wider" required placeholder="z.B. SOMMER24">
                                    <button type="button" @click="generateCode" class="bg-surface-100 hover:bg-surface-200 text-surface-700 px-4 py-2 rounded-xl text-sm font-bold transition-colors">Generieren</button>
                                </div>
                                <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Rabatt-Typ *</label>
                                    <select v-model="form.type" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" required>
                                        <option value="percent">Prozentual (%)</option>
                                        <option value="fixed">Fester Betrag (€)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Wert *</label>
                                    <input v-model="form.value" type="number" step="0.01" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" required placeholder="z.B. 10">
                                    <div v-if="form.errors.value" class="text-red-500 text-xs mt-1">{{ form.errors.value }}</div>
                                </div>
                            </div>

                            <hr class="border-surface-200">

                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1">Auf bestimmtes Event beschränken (Optional)</label>
                                <select v-model="form.event_id" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500">
                                    <option value="">-- Gültig für ALLE meine Events --</option>
                                    <option v-for="event in events" :key="event.id" :value="event.id">{{ event.title }}</option>
                                </select>
                                <div class="text-xs text-surface-500 mt-1">Wenn kein Event gewählt ist, kann der Code bei all deinen Events eingelöst werden.</div>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Maximale Einlösungen (Optional)</label>
                                    <input v-model="form.max_uses" type="number" min="1" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" placeholder="z.B. 50">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Gültig bis (Optional)</label>
                                    <input v-model="form.expires_at" type="datetime-local" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500">
                                </div>
                            </div>

                        </div>

                        <div class="pt-6 border-t border-surface-200 flex items-center justify-end gap-3">
                            <Link :href="route('vendor.promo-codes.index')" class="px-6 py-2.5 rounded-full font-medium text-surface-600 hover:bg-surface-100 transition-colors">
                                Abbrechen
                            </Link>
                            <button type="submit" :disabled="form.processing" class="bg-brand-500 hover:bg-brand-600 text-white px-8 py-2.5 rounded-full font-bold transition-all disabled:opacity-50 shadow-sm">
                                Rabattcode speichern
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
