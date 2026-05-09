<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import SeatSelector from '@/Components/SeatSelector.vue';

const props = defineProps({
    seatingPlan: Object,
    event: Object,
    ticketCategories: Array,
});

const selectedSeats = ref([]);

function onSeatsSelected(seats) {
    selectedSeats.value = seats;
}

function formatMoney(v) {
    return parseFloat(v || 0).toFixed(2).replace('.', ',') + ' €';
}

const total = () => selectedSeats.value.reduce((s, seat) => s + parseFloat(seat.price_override ?? 0), 0);
</script>

<template>
    <Head :title="`Saalplan – ${event?.title ?? 'Demo'}`" />
    <Navbar />

    <main class="min-h-screen bg-surface-50 dark:bg-surface-950 py-12">
        <div class="max-w-4xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8 text-center">
                <span class="text-brand-500 font-bold text-sm uppercase tracking-wider">Interaktiver Saalplan</span>
                <h1 class="text-3xl font-display font-black text-surface-900 dark:text-white mt-1">
                    {{ event?.title ?? seatingPlan.name }}
                </h1>
                <p class="text-surface-500 dark:text-surface-400 mt-2 text-sm">
                    Klicke auf einen freien Platz, um ihn auszuwählen. Mehrfachauswahl möglich.
                </p>
            </div>

            <!-- Seat Selector -->
            <div class="bg-white dark:bg-surface-900 rounded-3xl shadow-sm border border-surface-200 dark:border-surface-800 p-6 md:p-10">
                <SeatSelector
                    :seating-plan="seatingPlan"
                    :ticket-categories="ticketCategories"
                    @seats-selected="onSeatsSelected"
                />
            </div>

            <!-- Checkout Panel -->
            <div v-if="selectedSeats.length > 0"
                class="mt-6 bg-brand-500 text-white rounded-3xl shadow-lg p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <div class="font-bold text-lg">{{ selectedSeats.length }} Platz{{ selectedSeats.length > 1 ? 'e' : '' }} ausgewählt</div>
                    <div class="text-brand-100 text-sm mt-1">
                        {{ selectedSeats.map(s => s.label).join(', ') }}
                    </div>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <div class="text-3xl font-black">{{ formatMoney(total()) }}</div>
                    <a :href="`/checkout/${event?.slug ?? ''}`"
                        class="bg-white text-brand-600 font-black px-6 py-3 rounded-xl hover:bg-brand-50 transition-colors shadow-sm">
                        Weiter zum Checkout →
                    </a>
                </div>
            </div>

        </div>
    </main>

    <Footer />
</template>
