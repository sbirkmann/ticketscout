<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    event: Object,
});

const form = useForm({
    total_tickets: 2,
    ticket_category: '',
    price_per_ticket: '',
});

function submit() {
    form.post(route('group.store', props.event.slug));
}
</script>

<template>
    <Head :title="`Gruppen-Reservierung – ${event.title}`" />
    <Navbar />

    <main class="min-h-screen bg-surface-50 dark:bg-surface-950 py-16">
        <div class="max-w-xl mx-auto px-4">
            <div class="bg-white dark:bg-surface-900 rounded-3xl shadow-sm border border-surface-200 dark:border-surface-800 p-8">
                <div class="mb-6">
                    <span class="text-brand-500 font-bold text-sm">Gruppen-Kauf</span>
                    <h1 class="text-2xl font-display font-black text-surface-900 dark:text-white mt-1">{{ event.title }}</h1>
                    <p class="text-surface-500 dark:text-surface-400 text-sm mt-2">Reserviere Tickets für deine Gruppe. Deine Freunde zahlen ihren Anteil über einen Sharing-Link.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Anzahl Tickets</label>
                        <input type="number" min="2" max="20" v-model="form.total_tickets"
                            class="w-full rounded-xl border-surface-300 dark:border-surface-700 dark:bg-surface-800 dark:text-white focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Ticket-Kategorie</label>
                        <select v-model="form.ticket_category"
                            class="w-full rounded-xl border-surface-300 dark:border-surface-700 dark:bg-surface-800 dark:text-white focus:ring-brand-500 focus:border-brand-500" required>
                            <option value="">Kategorie wählen...</option>
                            <option v-for="cat in event.ticket_categories" :key="cat.id" :value="cat.name">
                                {{ cat.name }} ({{ cat.price }} €)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Preis pro Ticket (€)</label>
                        <input type="number" step="0.01" min="0" v-model="form.price_per_ticket"
                            class="w-full rounded-xl border-surface-300 dark:border-surface-700 dark:bg-surface-800 dark:text-white focus:ring-brand-500 focus:border-brand-500"
                            placeholder="z.B. 49.00" required>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 text-sm text-blue-700 dark:text-blue-300">
                        🔗 Nach dem Erstellen erhältst du einen Link, den du an deine Freunde schicken kannst. Jeder zahlt einzeln.
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-brand-500 hover:bg-brand-600 text-white py-3 rounded-xl font-bold text-lg transition-colors shadow-sm">
                        Gruppenlink erstellen
                    </button>
                </form>
            </div>
        </div>
    </main>

    <Footer />
</template>
