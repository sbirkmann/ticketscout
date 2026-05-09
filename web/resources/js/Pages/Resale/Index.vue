<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    listings: Object,
});

function formatMoney(v) {
    return parseFloat(v || 0).toFixed(2).replace('.', ',') + ' €';
}

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Ticket-Marktplatz | Fan-to-Fan Resale" />
    <Navbar />

    <main class="min-h-screen bg-surface-50 dark:bg-surface-950 py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h1 class="text-4xl font-display font-black text-surface-900 dark:text-white mb-3">Ticket-Marktplatz</h1>
                <p class="text-surface-500 dark:text-surface-400">Kaufe Tickets direkt von anderen Fans — sicher & offiziell.</p>
                <Link :href="route('resale.create')" class="mt-6 inline-block bg-brand-500 hover:bg-brand-600 text-white px-6 py-3 rounded-full font-bold shadow transition-colors">
                    🎟️ Eigenes Ticket anbieten
                </Link>
            </div>

            <div class="space-y-4">
                <div v-for="listing in listings.data" :key="listing.id"
                    class="bg-white dark:bg-surface-900 rounded-2xl shadow-sm border border-surface-200 dark:border-surface-800 p-6 flex items-center gap-6">
                    <div class="flex-1">
                        <h3 class="font-bold text-lg text-surface-900 dark:text-white">
                            {{ listing.ticket?.order?.event?.title ?? 'Event' }}
                        </h3>
                        <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                            📅 {{ formatDate(listing.ticket?.order?.event?.start_date) }}
                            · Anbieter: {{ listing.seller?.name }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-2xl font-black text-brand-600">{{ formatMoney(listing.asking_price) }}</div>
                        <Link :href="route('checkout.index', { event: listing.ticket.order.event.slug, listing_id: listing.id })"
                            class="mt-2 inline-block bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition-colors">
                            Kaufen
                        </Link>
                    </div>
                </div>

                <div v-if="listings.data.length === 0" class="text-center py-20 text-surface-400">
                    <div class="text-5xl mb-4">🎟️</div>
                    <p class="text-lg font-medium">Keine Tickets im Angebot.</p>
                    <p class="text-sm mt-2">Schau später nochmal rein!</p>
                </div>
            </div>
        </div>
    </main>

    <Footer />
</template>
