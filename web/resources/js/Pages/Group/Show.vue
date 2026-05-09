<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    reservation: Object,
    shareUrl: String,
});

const copied = ref(false);

function copyLink() {
    navigator.clipboard.writeText(props.shareUrl);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2500);
}

const paidCount = (props.reservation.participants ?? []).filter(p => p.paid_at).length;
const remaining = props.reservation.total_tickets - paidCount;

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('de-DE', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatMoney(v) {
    return parseFloat(v || 0).toFixed(2).replace('.', ',') + ' €';
}
</script>

<template>
    <Head :title="`Gruppen-Reservierung – ${reservation.event?.title}`" />
    <Navbar />

    <main class="min-h-screen bg-surface-50 dark:bg-surface-950 py-16">
        <div class="max-w-2xl mx-auto px-4 space-y-6">

            <!-- Event Card -->
            <div class="bg-white dark:bg-surface-900 rounded-3xl shadow-sm border border-surface-200 dark:border-surface-800 p-8">
                <span class="text-xs font-bold text-brand-500 uppercase tracking-wider">Gruppen-Reservierung</span>
                <h1 class="text-2xl font-display font-black text-surface-900 dark:text-white mt-1">{{ reservation.event?.title }}</h1>
                <p class="text-surface-500 dark:text-surface-400 mt-1 text-sm">
                    📅 {{ formatDate(reservation.event?.start_date) }}
                    <span v-if="reservation.event?.location"> · 📍 {{ reservation.event.location.name }}</span>
                </p>

                <!-- Progress -->
                <div class="mt-6">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-surface-600 dark:text-surface-400">Bezahlt: <strong>{{ paidCount }}</strong> / {{ reservation.total_tickets }}</span>
                        <span class="text-surface-600 dark:text-surface-400">Noch offen: <strong>{{ remaining }}</strong></span>
                    </div>
                    <div class="w-full bg-surface-100 dark:bg-surface-800 rounded-full h-3">
                        <div class="bg-brand-500 h-3 rounded-full transition-all duration-500"
                            :style="{ width: (paidCount / reservation.total_tickets * 100) + '%' }"></div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                    <div class="bg-surface-50 dark:bg-surface-800 rounded-xl p-4">
                        <div class="text-xs text-surface-500">Kategorie</div>
                        <div class="font-bold text-surface-900 dark:text-white">{{ reservation.ticket_category }}</div>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-800 rounded-xl p-4">
                        <div class="text-xs text-surface-500">Preis/Ticket</div>
                        <div class="font-bold text-brand-600 dark:text-brand-400">{{ formatMoney(reservation.price_per_ticket) }}</div>
                    </div>
                </div>
            </div>

            <!-- Share Link -->
            <div class="bg-white dark:bg-surface-900 rounded-3xl shadow-sm border border-surface-200 dark:border-surface-800 p-6">
                <h2 class="font-bold text-surface-900 dark:text-white mb-3">🔗 Freunde einladen</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Teile diesen Link mit deiner Gruppe. Jeder kann darüber seinen Ticket-Anteil bezahlen.</p>

                <div class="flex gap-2">
                    <input readonly :value="shareUrl"
                        class="flex-1 bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl px-4 py-2.5 text-sm text-surface-700 dark:text-surface-300 font-mono">
                    <button @click="copyLink"
                        class="px-4 py-2.5 rounded-xl font-bold text-sm transition-colors"
                        :class="copied ? 'bg-green-500 text-white' : 'bg-brand-500 hover:bg-brand-600 text-white'">
                        {{ copied ? '✓ Kopiert!' : 'Kopieren' }}
                    </button>
                </div>
            </div>


            <!-- Participation Action -->
            <div v-if="remaining > 0 && reservation.status === 'open'" class="text-center">
                <Link :href="route('checkout.index', { event: reservation.event.slug, group_token: reservation.share_token })"
                    class="inline-block w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl shadow-glow transition-all text-lg">
                    💰 Meinen Anteil bezahlen ({{ formatMoney(reservation.price_per_ticket) }})
                </Link>
                <p class="text-xs text-surface-500 mt-3">Du erhältst dein Ticket sofort nach der Zahlung.</p>
            </div>

            <!-- Status Badge -->
            <div class="text-center pb-10">
                <span v-if="reservation.status === 'complete'" class="bg-green-100 text-green-700 px-4 py-2 rounded-full font-bold text-sm">
                    ✅ Gruppe vollständig – alle Tickets bezahlt!
                </span>
                <span v-else-if="reservation.status === 'expired'" class="bg-red-100 text-red-700 px-4 py-2 rounded-full font-bold text-sm">
                    ⏱️ Reservierung abgelaufen
                </span>
                <span v-else class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded-full font-bold text-sm">
                    ⏳ Warte auf {{ remaining }} weitere Zahlung{{ remaining !== 1 ? 'en' : '' }}
                </span>
            </div>

        </div>
    </main>

    <Footer />
</template>
