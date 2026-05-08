<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    orders: Object,
    stats: Object,
});

function formatMoney(amount) {
    return parseFloat(amount || 0).toFixed(2).replace('.', ',') + ' €';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}
</script>

<template>
    <Head title="Bestellungen" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Bestellungen</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm">
                        <p class="text-sm font-medium text-surface-500 mb-1">Gesamtumsatz</p>
                        <p class="text-3xl font-display font-bold text-surface-900">{{ formatMoney(stats.revenue) }}</p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm">
                        <p class="text-sm font-medium text-surface-500 mb-1">Bezahlte Bestellungen</p>
                        <p class="text-3xl font-display font-bold text-surface-900">{{ stats.paid_orders }}</p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm">
                        <p class="text-sm font-medium text-surface-500 mb-1">Alle Bestellungen</p>
                        <p class="text-3xl font-display font-bold text-surface-900">{{ stats.total_orders }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                    <div class="p-6 border-b border-surface-200 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-surface-900">Letzte Bestellungen</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-50 border-b border-surface-200">
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Datum</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Bestellnummer</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Kunde</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Event</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider">Summe</th>
                                    <th class="py-3 px-6 text-xs font-bold text-surface-500 uppercase tracking-wider text-right">Aktion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in orders.data" :key="order.id" class="border-b border-surface-100 hover:bg-surface-50 transition-colors">
                                    <td class="py-4 px-6 text-sm text-surface-600">{{ formatDate(order.created_at) }}</td>
                                    <td class="py-4 px-6 text-sm font-medium text-surface-900">#{{ order.order_number }}</td>
                                    <td class="py-4 px-6 text-sm text-surface-900">
                                        {{ order.billing_name }}<br>
                                        <span class="text-xs text-surface-500">{{ order.billing_email }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-surface-600">
                                        {{ order.items[0]?.buyable?.event?.title || 'Mehrere Events' }}
                                    </td>
                                    <td class="py-4 px-6 text-sm">
                                        <span v-if="order.payment_status === 'paid'" class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-bold">Bezahlt</span>
                                        <span v-else-if="order.payment_status === 'open'" class="bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full text-xs font-bold">Offen</span>
                                        <span v-else class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-bold">Storniert</span>
                                    </td>
                                    <td class="py-4 px-6 text-sm font-bold text-surface-900">{{ formatMoney(order.total_gross) }}</td>
                                    <td class="py-4 px-6 text-sm text-right">
                                        <Link :href="route('vendor.orders.show', order.id)" class="text-brand-600 hover:text-brand-800 font-bold">
                                            Details &rarr;
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="orders.data.length === 0">
                                    <td colspan="7" class="py-8 text-center text-surface-500">
                                        Noch keine Bestellungen vorhanden.
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
