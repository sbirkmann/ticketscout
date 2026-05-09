<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    tickets: Array,
});

const form = useForm({
    ticket_id: '',
    asking_price: '',
});

function submit() {
    form.post(route('resale.store'));
}

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('de-DE', { day: '2-digit', month: 'long', year: 'numeric' });
}
</script>

<template>
    <Head title="Ticket anbieten" />
    <Navbar />

    <main class="min-h-screen bg-surface-50 dark:bg-surface-950 py-16">
        <div class="max-w-xl mx-auto px-4">
            <div class="bg-white dark:bg-surface-900 rounded-3xl shadow-sm border border-surface-200 dark:border-surface-800 p-8">
                <h1 class="text-2xl font-display font-black text-surface-900 dark:text-white mb-2">Ticket anbieten</h1>
                <p class="text-surface-500 dark:text-surface-400 text-sm mb-6">Verkaufe dein Ticket sicher an andere Fans. Du bekommst den Betrag abzüglich der Plattform-Gebühr ausgezahlt.</p>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Dein Ticket</label>
                        <select v-model="form.ticket_id" class="w-full rounded-xl border-surface-300 dark:border-surface-700 dark:bg-surface-800 dark:text-white focus:ring-brand-500 focus:border-brand-500" required>
                            <option value="">Ticket wählen...</option>
                            <option v-for="ticket in tickets" :key="ticket.id" :value="ticket.id">
                                {{ ticket.event?.title ?? 'Event' }} · {{ formatDate(ticket.event?.start_date) }} · {{ ticket.code }}
                            </option>
                        </select>
                        <div v-if="form.errors.ticket_id" class="text-red-500 text-xs mt-1">{{ form.errors.ticket_id }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Verkaufspreis (€)</label>
                        <input type="number" step="0.01" min="1" v-model="form.asking_price"
                            class="w-full rounded-xl border-surface-300 dark:border-surface-700 dark:bg-surface-800 dark:text-white focus:ring-brand-500 focus:border-brand-500"
                            placeholder="z.B. 49.00" required>
                        <p class="text-xs text-surface-400 mt-1">Hinweis: 5% Plattform-Gebühr wird vom Verkaufspreis abgezogen.</p>
                    </div>

                    <div class="bg-brand-50 dark:bg-brand-900/20 rounded-xl p-4 text-sm text-brand-700 dark:text-brand-300">
                        🛡️ Sicher: Das Ticket wird nach dem Verkauf automatisch übertragen. Betrug ist nicht möglich.
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-brand-500 hover:bg-brand-600 text-white py-3 rounded-xl font-bold text-lg transition-colors shadow-sm">
                        Ticket zum Verkauf anbieten
                    </button>
                </form>

                <div v-if="tickets.length === 0" class="text-center py-8 text-surface-400">
                    <p>Du hast keine gültigen Tickets, die du anbieten könntest.</p>
                </div>
            </div>
        </div>
    </main>

    <Footer />
</template>
