<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

import { computed } from 'vue';

const props = defineProps({
    stripeConnected: Boolean,
    recentEvents: Array,
    recentOrders: Array,
    stats: Object,
    chartData: Array,
});

function formatMoney(amount) {
    return parseFloat(amount || 0).toFixed(2).replace('.', ',') + ' €';
}

const maxRevenue = computed(() => {
    if (!props.chartData || props.chartData.length === 0) return 100;
    const max = Math.max(...props.chartData.map(d => d.revenue));
    return max > 0 ? max : 100; // default 100 to prevent 0 division
});
</script>

<template>
    <Head title="Händler Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Händler Übersicht</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Alert if Stripe is missing -->
                <div v-if="!stripeConnected" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-2xl shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-bold text-yellow-800">Auszahlungen noch nicht aktiviert</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Um Auszahlungen aus Ticketverkäufen zu erhalten, musst du dein Stripe Connect-Konto einrichten.</p>
                            </div>
                            <div class="mt-4">
                                <Link href="#" class="text-sm font-bold text-yellow-800 hover:text-yellow-900 bg-yellow-100 px-4 py-2 rounded-lg">Jetzt einrichten &rarr;</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm">
                        <p class="text-sm font-medium text-surface-500 mb-1">Gesamtumsatz</p>
                        <p class="text-3xl font-display font-bold text-surface-900">{{ formatMoney(stats.revenue) }}</p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm">
                        <p class="text-sm font-medium text-surface-500 mb-1">Bestellungen</p>
                        <p class="text-3xl font-display font-bold text-surface-900">{{ stats.orders }}</p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm">
                        <p class="text-sm font-medium text-surface-500 mb-1">Aktive Events</p>
                        <p class="text-3xl font-display font-bold text-surface-900">{{ stats.events }}</p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm relative overflow-hidden">
                        <div class="absolute right-0 top-0 mt-2 mr-2">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                        </div>
                        <p class="text-sm font-medium text-surface-500 mb-1">Live Check-ins</p>
                        <div class="flex items-end gap-2">
                            <p class="text-3xl font-display font-bold text-surface-900">{{ stats.checkins }}</p>
                            <p class="text-xs text-surface-400 pb-1">/ {{ stats.tickets }} Tickets</p>
                        </div>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm">
                    <h3 class="font-display font-bold text-xl mb-6">Umsatz der letzten 30 Tage</h3>
                    <div class="h-64 flex items-end gap-1 md:gap-2">
                        <div v-for="(day, index) in chartData" :key="index" class="flex-1 flex flex-col items-center group relative">
                            <!-- Tooltip -->
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-12 bg-surface-900 text-white text-xs py-1 px-2 rounded pointer-events-none transition-opacity whitespace-nowrap z-10">
                                {{ day.date }}: {{ formatMoney(day.revenue) }}
                            </div>
                            <!-- Bar -->
                            <div class="w-full bg-brand-100 hover:bg-brand-500 rounded-t-sm transition-all duration-300 relative"
                                 :style="`height: ${Math.max((day.revenue / maxRevenue) * 100, 2)}%`">
                            </div>
                            <!-- Label (show every 5th day on mobile, or just hide on very small screens) -->
                            <div class="text-[10px] text-surface-400 mt-2 rotate-45 md:rotate-0 origin-left hidden md:block" v-if="index % 3 === 0">
                                {{ day.date }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-surface-900 rounded-3xl p-8 text-white shadow-md relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-10">
                        <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" /></svg>
                    </div>
                    <h3 class="font-display font-bold text-2xl mb-6 relative z-10">Aktionen</h3>
                    <div class="flex flex-wrap gap-4 relative z-10">
                        <Link :href="route('vendor.events.create')" class="bg-brand-500 hover:bg-brand-400 text-white px-6 py-3 rounded-xl font-bold transition-colors shadow-sm">
                            + Neues Event anlegen
                        </Link>
                        <Link href="#" class="bg-surface-800 hover:bg-surface-700 text-white px-6 py-3 rounded-xl font-bold transition-colors">
                            ⚙️ Rechnungen & Steuern konfigurieren
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Recent Events -->
                    <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-surface-100 flex justify-between items-center bg-surface-50">
                            <h3 class="font-bold text-surface-900">Letzte Events</h3>
                            <Link :href="route('vendor.events.index')" class="text-sm text-brand-600 font-bold hover:underline">Alle ansehen</Link>
                        </div>
                        <div class="p-6">
                            <ul v-if="recentEvents.length > 0" class="space-y-4">
                                <li v-for="event in recentEvents" :key="event.id" class="flex justify-between items-center">
                                    <div>
                                        <Link :href="route('vendor.events.show', event.id)" class="font-bold text-surface-900 hover:text-brand-600">{{ event.title }}</Link>
                                        <p class="text-xs text-surface-500">{{ new Date(event.start_date).toLocaleDateString() }}</p>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-1 rounded bg-surface-100" :class="event.status === 'published' ? 'text-green-600 bg-green-50' : 'text-surface-500'">
                                        {{ event.status }}
                                    </span>
                                </li>
                            </ul>
                            <p v-else class="text-surface-500 text-sm">Noch keine Events vorhanden.</p>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-surface-100 flex justify-between items-center bg-surface-50">
                            <h3 class="font-bold text-surface-900">Letzte Bestellungen</h3>
                            <Link href="#" class="text-sm text-brand-600 font-bold hover:underline">Alle ansehen</Link>
                        </div>
                        <div class="p-6">
                            <ul v-if="recentOrders.length > 0" class="space-y-4">
                                <li v-for="order in recentOrders" :key="order.id" class="flex justify-between items-center border-b border-surface-50 pb-2 last:border-0 last:pb-0">
                                    <div>
                                        <p class="font-bold text-surface-900">{{ order.billing_first_name }} {{ order.billing_last_name }}</p>
                                        <p class="text-xs text-surface-500" v-if="order.event">{{ order.event.title }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-brand-600">{{ formatMoney(order.total_amount) }}</p>
                                        <p class="text-xs text-surface-500">{{ new Date(order.created_at).toLocaleDateString() }}</p>
                                    </div>
                                </li>
                            </ul>
                            <p v-else class="text-surface-500 text-sm">Noch keine Bestellungen vorhanden.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
