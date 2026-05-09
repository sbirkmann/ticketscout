<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    giftCards: Array
});

const isModalOpen = ref(false);

const form = useForm({
    code: '',
    type: 'fixed',
    value: '',
    max_uses: '',
    expires_at: ''
});

const submit = () => {
    form.post(route('admin.gift-cards.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const deleteCard = (id) => {
    if (confirm('Möchtest du diesen globalen Gutschein wirklich löschen?')) {
        useForm({}).delete(route('admin.gift-cards.destroy', id));
    }
};
</script>

<template>
    <Head title="Gutscheine (Global)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-display font-black text-2xl text-surface-900 leading-tight">
                    Plattform-Gutscheine
                </h2>
                <button @click="isModalOpen = true" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2 rounded-full font-bold shadow-md transition-all text-sm">
                    Neuer Gutschein
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200">
                    <div class="p-6 border-b border-surface-100 bg-surface-50 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-surface-900">Aktive Wertgutscheine</h3>
                            <p class="text-surface-500 text-sm">Diese Gutscheine können plattformweit für alle Events eingelöst werden. Die Kosten trägt die Plattform.</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-surface-600">
                            <thead class="bg-surface-50 border-b border-surface-200 text-xs uppercase font-bold text-surface-700">
                                <tr>
                                    <th class="px-6 py-4">Code</th>
                                    <th class="px-6 py-4">Typ & Wert</th>
                                    <th class="px-6 py-4">Nutzungen</th>
                                    <th class="px-6 py-4">Ablaufdatum</th>
                                    <th class="px-6 py-4 text-right">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="giftCards.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-surface-500">
                                        Keine globalen Gutscheine vorhanden.
                                    </td>
                                </tr>
                                <tr v-for="card in giftCards" :key="card.id" class="border-b border-surface-100 last:border-0 hover:bg-surface-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-mono font-bold bg-surface-200 px-2 py-1 rounded text-surface-900">{{ card.code }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-surface-900">
                                        {{ card.type === 'fixed' ? '€' + card.value : card.value + '%' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ card.current_uses }} / {{ card.max_uses || '∞' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ card.expires_at ? new Date(card.expires_at).toLocaleDateString('de-DE') : 'Nie' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="deleteCard(card.id)" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase">
                                            Löschen
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-surface-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-xl max-w-md w-full overflow-hidden">
                <div class="p-6 border-b border-surface-100 flex justify-between items-center bg-surface-50">
                    <h3 class="text-lg font-bold text-surface-900">Gutschein generieren</h3>
                    <button @click="isModalOpen = false" class="text-surface-400 hover:text-surface-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Code (optional)</label>
                        <input v-model="form.code" type="text" placeholder="Wird automatisch generiert, wenn leer" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Typ</label>
                            <select v-model="form.type" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20">
                                <option value="fixed">Fester Betrag (€)</option>
                                <option value="percent">Prozentual (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Wert</label>
                            <input v-model="form.value" type="number" step="0.01" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Max. Nutzungen</label>
                            <input v-model="form.max_uses" type="number" placeholder="Optional" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Ablaufdatum</label>
                            <input v-model="form.expires_at" type="date" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20">
                        </div>
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
