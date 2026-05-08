<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});

const form = useForm({});

function formatMoney(amount) {
    return parseFloat(amount || 0).toFixed(2).replace('.', ',') + ' €';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}

function resendTickets() {
    if(confirm('Möchtest du die Tickets an den Kunden erneut versenden?')) {
        form.post(route('vendor.orders.resend', props.order.id));
    }
}
</script>

<template>
    <Head :title="`Bestellung #${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('vendor.orders.index')" class="text-surface-400 hover:text-surface-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Bestellung #{{ order.order_number }}</h2>
                <span v-if="order.payment_status === 'paid'" class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold ml-2">Bezahlt</span>
                <span v-else-if="order.payment_status === 'open'" class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold ml-2">Offen</span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <div class="md:col-span-2 space-y-8">
                        <div class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                            <h3 class="font-bold text-lg text-surface-900 mb-6 border-b border-surface-100 pb-2">Bestellte Positionen</h3>
                            
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-xs font-bold text-surface-500 uppercase tracking-wider border-b border-surface-100">
                                        <th class="pb-3">Produkt</th>
                                        <th class="pb-3">Preis</th>
                                        <th class="pb-3">Anzahl</th>
                                        <th class="pb-3 text-right">Summe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in order.items" :key="item.id" class="border-b border-surface-100 last:border-0">
                                        <td class="py-4">
                                            <p class="font-bold text-surface-900">{{ item.name }}</p>
                                            <p class="text-xs text-surface-500">{{ item.buyable?.event?.title }}</p>
                                        </td>
                                        <td class="py-4 text-sm text-surface-700">{{ formatMoney(item.unit_price) }}</td>
                                        <td class="py-4 text-sm text-surface-700">{{ item.quantity }}x</td>
                                        <td class="py-4 text-sm font-bold text-surface-900 text-right">{{ formatMoney(item.total_gross) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                            <div class="flex justify-between items-center mb-6 border-b border-surface-100 pb-2">
                                <h3 class="font-bold text-lg text-surface-900">Ausgestellte Tickets</h3>
                                <button @click="resendTickets" :disabled="form.processing" class="text-brand-600 hover:text-brand-800 text-sm font-bold flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Erneut senden
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div v-for="ticket in order.tickets" :key="ticket.id" class="border border-surface-200 rounded-xl p-4 flex items-start gap-4">
                                    <div class="w-12 h-12 bg-surface-100 rounded-lg flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-surface-900 text-sm">{{ ticket.category?.name || 'Ticket' }}</p>
                                        <p class="text-xs text-surface-500 font-mono mt-1">{{ ticket.qr_code_hash }}</p>
                                        <span v-if="ticket.is_scanned" class="inline-block mt-2 text-[10px] uppercase font-bold tracking-wider bg-red-100 text-red-700 px-2 py-0.5 rounded">Gescannt</span>
                                        <span v-else class="inline-block mt-2 text-[10px] uppercase font-bold tracking-wider bg-green-100 text-green-700 px-2 py-0.5 rounded">Gültig</span>
                                    </div>
                                </div>
                                <div v-if="order.tickets.length === 0" class="col-span-2 text-center text-sm text-surface-500 py-4">
                                    Keine Tickets gefunden.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:col-span-1 space-y-8">
                        <div class="bg-surface-50 rounded-3xl p-6 border border-surface-200">
                            <h3 class="font-bold text-surface-900 mb-4 text-sm uppercase tracking-wider">Kunde</h3>
                            <div class="text-sm text-surface-700 space-y-1">
                                <p class="font-bold">{{ order.billing_name }}</p>
                                <p>{{ order.billing_address }}</p>
                                <p>{{ order.billing_zip }} {{ order.billing_city }}</p>
                                <p>{{ order.billing_country }}</p>
                                <p class="mt-4 pt-4 border-t border-surface-200 text-brand-600 font-medium">{{ order.billing_email }}</p>
                            </div>
                        </div>

                        <div class="bg-surface-50 rounded-3xl p-6 border border-surface-200">
                            <h3 class="font-bold text-surface-900 mb-4 text-sm uppercase tracking-wider">Details</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-surface-500">Bestelldatum</span>
                                    <span class="text-surface-900 font-medium">{{ formatDate(order.created_at) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-surface-500">Zahlungsart</span>
                                    <span class="text-surface-900 font-medium capitalize">{{ order.payment_method || 'Unbekannt' }}</span>
                                </div>
                                <div class="flex justify-between border-t border-surface-200 pt-3 font-bold">
                                    <span class="text-surface-900">Gesamtsumme</span>
                                    <span class="text-surface-900">{{ formatMoney(order.total_gross) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
