<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    payouts: Object,
    vendors: Array
});

const isCreating = ref(false);

const form = useForm({
    vendor_id: '',
    amount: '',
    status: 'pending',
    reference: ''
});

function submitPayout() {
    form.post(route('superadmin.payouts.store'), {
        onSuccess: () => {
            isCreating.value = false;
            form.reset();
        }
    });
}

const updateForm = useForm({
    status: '',
    reference: ''
});

function markAsPaid(payout) {
    if(confirm('Möchtest du diese Auszahlung wirklich als Bezahlt markieren?')) {
        updateForm.status = 'paid';
        updateForm.reference = payout.reference;
        updateForm.put(route('superadmin.payouts.update', payout.id));
    }
}

function formatMoney(amount) {
    return parseFloat(amount || 0).toFixed(2).replace('.', ',') + ' €';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('de-DE', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    });
}
</script>

<template>
    <Head title="Auszahlungen (Payouts)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Auszahlungen</h2>
                <button @click="isCreating = true" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Neue Auszahlung
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Create Form Modal -->
                <div v-if="isCreating" class="bg-white rounded-3xl p-6 shadow-sm border border-surface-200 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg text-surface-900">Auszahlung erfassen</h3>
                        <button @click="isCreating = false" class="text-surface-400 hover:text-surface-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    </div>

                    <form @submit.prevent="submitPayout" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1">Veranstalter (Vendor)</label>
                                <select v-model="form.vendor_id" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" required>
                                    <option value="">Bitte wählen...</option>
                                    <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">{{ vendor.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1">Betrag (€)</label>
                                <input type="number" step="0.01" min="0.01" v-model="form.amount" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1">Referenz (optional)</label>
                                <input type="text" v-model="form.reference" placeholder="z.B. Rechnungsnummer" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1">Status</label>
                                <select v-model="form.status" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" required>
                                    <option value="pending">Ausstehend (Pending)</option>
                                    <option value="paid">Bezahlt (Paid)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" :disabled="form.processing" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2 rounded-xl font-bold transition-colors">
                                Speichern
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-50 border-b border-surface-200">
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Datum</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Vendor</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Betrag</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Referenz</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider text-right">Aktion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payout in payouts.data" :key="payout.id" class="border-b border-surface-100 hover:bg-surface-50">
                                    <td class="py-4 px-6 text-sm text-surface-600">{{ formatDate(payout.created_at) }}</td>
                                    <td class="py-4 px-6 text-sm font-bold text-surface-900">{{ payout.vendor?.name }}</td>
                                    <td class="py-4 px-6 text-sm font-bold text-surface-900">{{ formatMoney(payout.amount) }}</td>
                                    <td class="py-4 px-6 text-sm text-surface-500">{{ payout.reference || '-' }}</td>
                                    <td class="py-4 px-6 text-sm">
                                        <span v-if="payout.status === 'paid'" class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-bold">
                                            Bezahlt ({{ formatDate(payout.paid_at) }})
                                        </span>
                                        <span v-else class="bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full text-xs font-bold">
                                            Ausstehend
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-right">
                                        <button v-if="payout.status === 'pending'" @click="markAsPaid(payout)" class="text-brand-600 hover:text-brand-800 font-bold">
                                            Als Bezahlt markieren
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="payouts.data.length === 0">
                                    <td colspan="6" class="py-8 text-center text-surface-500">
                                        Keine Auszahlungen vorhanden.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
