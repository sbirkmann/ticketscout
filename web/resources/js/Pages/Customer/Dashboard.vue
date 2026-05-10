<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import { defineProps } from 'vue';

const props = defineProps({
    orders: Array,
    recommendations: Array
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<template>
    <Head title="Mein Bereich - Ticketsout24" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="font-display text-4xl font-black text-surface-900 tracking-tight">Willkommen zurück!</h1>
                    <p class="text-surface-500 mt-2 text-lg">Hier findest du deine gekauften Tickets und neue Event-Empfehlungen.</p>
                </div>
                
                <!-- Loyalty Badge -->
                <div v-if="$page.props.auth.user" class="bg-gradient-to-r from-purple-100 to-indigo-100 border border-purple-200 rounded-2xl p-4 flex items-center gap-4 shadow-sm">
                    <div class="bg-white text-purple-600 p-3 rounded-xl shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-purple-800 uppercase tracking-wider mb-0.5">Ticketsout24 Loyalty</p>
                        <p class="font-black text-2xl text-purple-900 leading-none">{{ $page.props.auth.user.loyalty_points || 0 }} <span class="text-sm font-medium text-purple-700">Punkte</span></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                
                <!-- Left Column: Tickets / Orders -->
                <div class="lg:col-span-2 space-y-8">
                    <h2 class="font-display font-bold text-2xl text-surface-900 border-b border-surface-200 pb-4">Meine Tickets</h2>
                    
                    <div v-if="orders.length === 0" class="bg-white p-10 rounded-3xl shadow-sm border border-surface-200 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-surface-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        <h3 class="text-xl font-bold text-surface-900 mb-2">Noch keine Tickets</h3>
                        <p class="text-surface-500 mb-6">Du hast bisher noch keine Tickets auf Ticketsout24 gekauft.</p>
                        <Link :href="route('home')" class="inline-flex items-center gap-2 bg-brand-500 text-white px-6 py-3 rounded-full font-bold hover:bg-brand-600 transition-colors">
                            Events entdecken
                        </Link>
                    </div>

                    <div v-else class="space-y-6">
                        <div v-for="order in orders" :key="order.id" class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden hover:shadow-md transition-shadow">
                            <div class="flex flex-col sm:flex-row">
                                <!-- Event Image -->
                                <div class="w-full sm:w-48 h-48 sm:h-auto shrink-0 bg-surface-200 relative">
                                    <img v-if="order.event.image_path" :src="`/storage/${order.event.image_path}`" class="w-full h-full object-cover" alt="Event Image" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-surface-700 to-surface-900">
                                        <span class="text-surface-400 font-bold">Kein Bild</span>
                                    </div>
                                    <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-surface-900 shadow-sm">
                                        {{ order.tickets_count }} Ticket(s)
                                    </div>
                                </div>
                                
                                <!-- Order Info -->
                                <div class="p-6 flex-1 flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="font-display font-bold text-xl text-surface-900 line-clamp-2">
                                                <Link :href="route('event.show', order.event.slug)" class="hover:text-brand-600 transition-colors">{{ order.event.title }}</Link>
                                            </h3>
                                            <span class="text-sm font-bold text-surface-400 ml-4 shrink-0">{{ order.total_amount }} €</span>
                                        </div>
                                        <div class="text-sm text-surface-500 flex items-center gap-2 mb-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ formatDate(order.event.start_date) }}
                                        </div>
                                        <div class="text-sm text-surface-500 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            {{ order.event.location }}
                                        </div>
                                    </div>
                                    
                                    <div v-if="order.is_gift" class="mt-4 bg-pink-50 border border-pink-200 rounded-xl p-4">
                                        <div class="flex items-center gap-2 text-pink-600 font-bold mb-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                                            Geschenk für {{ order.gift_recipient_name || 'Freunde' }}
                                        </div>
                                        <p v-if="order.gift_message" class="text-sm text-pink-700 italic">"{{ order.gift_message }}"</p>
                                    </div>
                                    
                                    <div class="mt-6 flex flex-wrap gap-3">
                                        <a :href="route('customer.orders.tickets', order.id)" target="_blank" class="bg-brand-50 text-brand-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-brand-100 transition-colors inline-block text-center">
                                            Tickets (PDF)
                                        </a>
                                        <a :href="route('customer.orders.invoice', order.id)" target="_blank" class="bg-surface-50 text-surface-600 px-4 py-2 rounded-xl text-sm font-bold border border-surface-200 hover:bg-surface-100 transition-colors inline-block text-center">
                                            Rechnung (PDF)
                                        </a>
                                    </div>

                                    <!-- Wallet System -->
                                    <div v-if="order.event.enable_wallet" class="mt-6 pt-6 border-t border-surface-100">
                                        <h4 class="font-bold text-surface-900 mb-3 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500" viewBox="0 0 20 20" fill="currentColor">
                                              <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                              <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                                            </svg>
                                            Ticket Guthaben (Cashless)
                                        </h4>
                                        <div class="space-y-3">
                                            <div v-for="ticket in order.tickets" :key="ticket.id" class="flex items-center justify-between bg-surface-50 p-3 rounded-xl border border-surface-200">
                                                <div>
                                                    <span class="block font-bold text-sm text-surface-900">{{ ticket.ticket_category?.name || 'Ticket' }} #{{ ticket.code.substring(0,8) }}</span>
                                                    <span class="text-xs text-surface-500">
                                                        <span v-if="ticket.attendee_name">{{ ticket.attendee_name }}</span>
                                                        <span v-else>Aktuelles Guthaben: {{ Number(ticket.wallet_balance || 0).toFixed(2) }} €</span>
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span v-if="ticket.attendee_name" class="font-bold text-surface-900 bg-white px-2 py-1 rounded shadow-sm border border-surface-100">{{ Number(ticket.wallet_balance || 0).toFixed(2) }} €</span>
                                                    <Link :href="route('customer.wallet.show', ticket.id)" class="bg-brand-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-brand-600 transition-colors">
                                                        Aufladen / Verlauf
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Recommendations -->
                <div class="space-y-8">
                    <h2 class="font-display font-bold text-2xl text-surface-900 border-b border-surface-200 pb-4">Das könnte dir gefallen</h2>
                    
                    <div class="space-y-6">
                        <div v-for="rec in recommendations" :key="rec.id" class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden hover:shadow-md transition-shadow group">
                            <Link :href="route('event.show', rec.slug)" class="block">
                                <div class="h-40 w-full relative overflow-hidden bg-surface-200">
                                    <img v-if="rec.image_path" :src="`/storage/${rec.image_path}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                    <div v-else class="w-full h-full bg-gradient-to-br from-surface-700 to-surface-900 flex items-center justify-center">
                                        <span class="text-surface-400 font-bold">Event</span>
                                    </div>
                                    <div v-if="rec.category" class="absolute top-3 right-3 bg-brand-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                        {{ rec.category.name }}
                                    </div>
                                </div>
                                <div class="p-5">
                                    <h3 class="font-display font-bold text-lg text-surface-900 line-clamp-2 mb-2 group-hover:text-brand-600 transition-colors">{{ rec.title }}</h3>
                                    <div class="text-sm text-surface-500 mb-1 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        {{ formatDate(rec.start_date) }}
                                    </div>
                                    <div class="text-sm text-surface-500 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        {{ rec.location ? rec.location.name : 'TBA' }}
                                    </div>
                                </div>
                            </Link>
                        </div>
                        
                        <div v-if="recommendations.length === 0" class="text-surface-500 text-center py-8">
                            Aktuell keine neuen Events verfügbar.
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>
