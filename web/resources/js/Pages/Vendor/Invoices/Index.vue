<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    customerInvoices: Array,
    platformInvoices: Array,
    events: Array,
    filters: Object
});

const filterEvent = ref(props.filters?.event_id || '');
const filterSearch = ref(props.filters?.search || '');
const filterDateFrom = ref(props.filters?.date_from || '');
const filterDateTo = ref(props.filters?.date_to || '');

function applyFilter() {
    router.get(route('vendor.invoices.index'), {
        event_id: filterEvent.value,
        search: filterSearch.value,
        date_from: filterDateFrom.value,
        date_to: filterDateTo.value
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    filterEvent.value = '';
    filterSearch.value = '';
    filterDateFrom.value = '';
    filterDateTo.value = '';
    applyFilter();
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('de-DE');
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(amount);
}
</script>

<template>
    <Head title="Rechnungen" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Rechnungen</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Platform Invoices (Rechnungen an dich) -->
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm">
                    <h3 class="text-xl font-bold font-display text-surface-900 mb-6">Rechnungen von Ticketsout24</h3>
                    <p class="text-surface-600 mb-6">Hier findest du die Abrechnungen für die genutzten Plattform-Gebühren.</p>

                    <div v-if="platformInvoices.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-surface-50 text-surface-500 font-bold uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4 rounded-l-xl">Datum</th>
                                    <th class="px-6 py-4">Rechnungsnummer</th>
                                    <th class="px-6 py-4">Betrag</th>
                                    <th class="px-6 py-4 rounded-r-xl">Aktion</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-100">
                                <tr v-for="invoice in platformInvoices" :key="invoice.id" class="hover:bg-surface-50 transition-colors">
                                    <td class="px-6 py-4">{{ formatDate(invoice.created_at) }}</td>
                                    <td class="px-6 py-4 font-mono text-brand-600 font-bold">{{ invoice.invoice_number }}</td>
                                    <td class="px-6 py-4 font-medium">{{ formatCurrency(invoice.gross) }}</td>
                                    <td class="px-6 py-4">
                                        <a :href="route('vendor.invoices.download', invoice.id)" target="_blank" class="text-brand-500 hover:text-brand-700 font-bold text-sm bg-brand-50 px-3 py-1.5 rounded-lg">PDF Herunterladen</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-10 bg-surface-50 rounded-2xl border border-dashed border-surface-200 text-surface-500">
                        Noch keine Plattform-Rechnungen vorhanden.
                    </div>
                </div>

                <!-- Customer Invoices (Rechnungen an Kunden) -->
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <div>
                            <h3 class="text-xl font-bold font-display text-surface-900">Ausgestellte Kundenrechnungen</h3>
                            <p class="text-surface-600">Rechnungen, die in deinem Namen an Ticket-Käufer ausgestellt wurden.</p>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="bg-surface-50 p-4 rounded-xl border border-surface-200 mb-6 flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-bold text-surface-600 uppercase tracking-wider mb-1">Suche</label>
                            <input v-model="filterSearch" @keyup.enter="applyFilter" type="text" placeholder="Kunde, Bestell- oder Rechnungsnr." class="w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200 text-sm">
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-bold text-surface-600 uppercase tracking-wider mb-1">Event</label>
                            <select v-model="filterEvent" @change="applyFilter" class="w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200 text-sm">
                                <option value="">Alle Events</option>
                                <option v-for="event in events" :key="event.id" :value="event.id">{{ event.title }}</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[130px]">
                            <label class="block text-xs font-bold text-surface-600 uppercase tracking-wider mb-1">Von Datum</label>
                            <input v-model="filterDateFrom" @change="applyFilter" type="date" class="w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200 text-sm">
                        </div>
                        <div class="flex-1 min-w-[130px]">
                            <label class="block text-xs font-bold text-surface-600 uppercase tracking-wider mb-1">Bis Datum</label>
                            <input v-model="filterDateTo" @change="applyFilter" type="date" class="w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200 text-sm">
                        </div>
                        <div>
                            <button @click="resetFilters" class="px-4 py-2 bg-surface-200 hover:bg-surface-300 text-surface-700 rounded-xl font-bold text-sm transition-colors shadow-sm">
                                Reset
                            </button>
                        </div>
                        <div>
                            <button @click="applyFilter" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold text-sm transition-colors shadow-sm">
                                Filtern
                            </button>
                        </div>
                    </div>

                    <div v-if="customerInvoices.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-surface-50 text-surface-500 font-bold uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4 rounded-l-xl">Datum</th>
                                    <th class="px-6 py-4">Event</th>
                                    <th class="px-6 py-4">Kunde</th>
                                    <th class="px-6 py-4">Rechnungsnummer</th>
                                    <th class="px-6 py-4">Betrag</th>
                                    <th class="px-6 py-4 rounded-r-xl">Aktion</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-100">
                                <tr v-for="invoice in customerInvoices" :key="invoice.id" class="hover:bg-surface-50 transition-colors">
                                    <td class="px-6 py-4">{{ formatDate(invoice.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-surface-900 truncate max-w-[200px]" :title="invoice.order?.event?.title">
                                            {{ invoice.order?.event?.title || 'Unbekanntes Event' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-surface-600">{{ invoice.order?.billing_first_name }} {{ invoice.order?.billing_last_name }}</td>
                                    <td class="px-6 py-4 font-mono text-brand-600 font-bold">{{ invoice.invoice_number }}</td>
                                    <td class="px-6 py-4 font-medium">{{ formatCurrency(invoice.gross) }}</td>
                                    <td class="px-6 py-4">
                                        <a :href="route('vendor.invoices.download', invoice.id)" target="_blank" class="text-brand-500 hover:text-brand-700 font-bold text-sm bg-brand-50 px-3 py-1.5 rounded-lg">PDF</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-10 bg-surface-50 rounded-2xl border border-dashed border-surface-200 text-surface-500">
                        Noch keine Kundenrechnungen ausgestellt.
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
