<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

    const props = defineProps({
        pendingEvents: Array,
        pendingLocations: Array,
        stats: Object,
    });
    
    const form = useForm({});
    
    const approveEvent = (id) => {
        form.post(route('superadmin.events.approve', id));
    };
    
    const approveLocation = (id) => {
        form.post(route('superadmin.locations.approve', id));
    };
    
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(value);
    };
    </script>
    
    <template>
        <Head title="Admin Dashboard" />
    
        <AuthenticatedLayout>
            <template #header>
                <h2 class="text-2xl font-bold font-display text-surface-900">Admin Dashboard</h2>
            </template>
    
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                    
                    <!-- Global Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-surface-500 mb-1">Gesamtumsatz Plattform</p>
                                <p class="text-3xl font-bold font-display text-surface-900">{{ formatCurrency(stats?.total_revenue || 0) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        </div>
                        <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-surface-500 mb-1">Einnahmen (Plattformgebühr)</p>
                                <p class="text-3xl font-bold font-display text-brand-600">{{ formatCurrency(stats?.total_platform_fees || 0) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-brand-100 rounded-full flex items-center justify-center text-brand-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            </div>
                        </div>
                        <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-surface-500 mb-1">Verkaufte Tickets</p>
                                <p class="text-3xl font-bold font-display text-surface-900">{{ stats?.total_tickets_sold || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            </div>
                        </div>
                        <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-surface-500 mb-1">Bestellungen</p>
                                <p class="text-3xl font-bold font-display text-surface-900">{{ stats?.total_orders || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                        </div>
                        <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-surface-500 mb-1">Aktive Events</p>
                                <p class="text-3xl font-bold font-display text-surface-900">{{ stats?.active_events || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-orange-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <div class="bg-white rounded-3xl p-6 border border-surface-200 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-surface-500 mb-1">Aktive Veranstalter</p>
                                <p class="text-3xl font-bold font-display text-surface-900">{{ stats?.active_vendors || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center text-teal-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pending Events -->
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm">
                    <h3 class="text-xl font-bold font-display text-surface-900 mb-6">Freizugebende Events</h3>
                    
                    <div v-if="pendingEvents.length === 0" class="text-surface-500 py-8 text-center bg-surface-50 rounded-2xl border border-surface-100">
                        Keine Events zur Freigabe ausstehend.
                    </div>
                    
                    <div v-else class="space-y-4">
                        <div v-for="event in pendingEvents" :key="event.id" class="flex items-center justify-between p-4 border border-surface-200 rounded-2xl hover:bg-surface-50 transition-colors">
                            <div>
                                <h4 class="font-bold text-surface-900">{{ event.title }}</h4>
                                <p class="text-sm text-surface-500">
                                    Händler: {{ event.vendor?.name || 'Unbekannt' }} &bull; Datum: {{ new Date(event.start_date).toLocaleDateString() }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="route('event.show', event.slug)" target="_blank" class="px-4 py-2 text-sm font-medium text-surface-600 bg-surface-100 rounded-xl hover:bg-surface-200">
                                    Ansehen
                                </Link>
                                <button @click="approveEvent(event.id)" class="px-4 py-2 text-sm font-bold text-white bg-green-500 rounded-xl hover:bg-green-600 shadow-sm">
                                    Freigeben
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Locations -->
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm">
                    <h3 class="text-xl font-bold font-display text-surface-900 mb-6">Freizugebende Locations</h3>
                    
                    <div v-if="pendingLocations.length === 0" class="text-surface-500 py-8 text-center bg-surface-50 rounded-2xl border border-surface-100">
                        Keine Locations zur Freigabe ausstehend.
                    </div>
                    
                    <div v-else class="space-y-4">
                        <div v-for="loc in pendingLocations" :key="loc.id" class="flex items-center justify-between p-4 border border-surface-200 rounded-2xl hover:bg-surface-50 transition-colors">
                            <div>
                                <h4 class="font-bold text-surface-900">{{ loc.name }}</h4>
                                <p class="text-sm text-surface-500">
                                    {{ loc.address }}, {{ loc.zip }} {{ loc.city }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button @click="approveLocation(loc.id)" class="px-4 py-2 text-sm font-bold text-white bg-green-500 rounded-xl hover:bg-green-600 shadow-sm">
                                    Freigeben
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
