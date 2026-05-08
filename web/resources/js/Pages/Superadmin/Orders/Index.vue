<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    orders: Object
});
</script>

<template>
    <Head title="Bestellungen - Superadmin" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <!-- Admin Header -->
        <div class="bg-surface-900 text-white px-6 py-4 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-4">
                <Link :href="route('superadmin.dashboard')" class="font-display font-black text-xl tracking-tighter">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </Link>
                <span class="text-surface-500">/</span>
                <span class="text-surface-300 font-medium">Bestellungen</span>
            </div>
            <Link :href="route('superadmin.dashboard')" class="text-surface-400 hover:text-white text-sm">
                &larr; Zurück zum Dashboard
            </Link>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex justify-between items-center mb-8">
                <h1 class="font-display text-3xl font-black text-surface-900">Alle Bestellungen</h1>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-surface-50 border-b border-surface-200">
                        <tr>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Bestell-Nr.</th>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Kunde</th>
                            <th class="text-left px-6 py-4 text-sm font-bold text-surface-700">Event</th>
                            <th class="text-right px-6 py-4 text-sm font-bold text-surface-700">Betrag</th>
                            <th class="text-center px-6 py-4 text-sm font-bold text-surface-700">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-surface-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-surface-900">{{ order.order_number }}</div>
                                <div class="text-xs text-surface-500">{{ new Date(order.created_at).toLocaleString() }}</div>
                            </td>
                            <td class="px-6 py-4 text-surface-600 text-sm">
                                <div>{{ order.customer_name || (order.user ? order.user.name : 'Gast') }}</div>
                                <div class="text-xs text-surface-500">{{ order.customer_email || (order.user ? order.user.email : '') }}</div>
                            </td>
                            <td class="px-6 py-4 text-surface-600 text-sm">{{ order.event?.title || 'Unbekannt' }}</td>
                            <td class="px-6 py-4 text-right font-medium text-surface-900">€ {{ order.total_amount.toFixed(2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="order.status === 'paid'" class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded">Bezahlt</span>
                                <span v-else-if="order.status === 'pending'" class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-1 rounded">Ausstehend</span>
                                <span v-else class="bg-surface-100 text-surface-700 text-xs font-bold px-2 py-1 rounded">{{ order.status }}</span>
                            </td>
                        </tr>
                        <tr v-if="orders.data.length === 0">
                            <td colspan="5" class="text-center text-surface-500 py-12">Noch keine Bestellungen vorhanden.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex justify-center gap-2" v-if="orders.last_page > 1">
                <Link v-for="link in orders.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                      class="px-4 py-2 rounded-xl text-sm font-bold"
                      :class="link.active ? 'bg-brand-500 text-white' : 'bg-white text-surface-600 hover:bg-surface-100 border border-surface-200'"></Link>
            </div>
        </main>
    </div>
</template>
