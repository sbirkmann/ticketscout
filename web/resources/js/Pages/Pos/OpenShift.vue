<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    terminal: Object,
    events: Array
});

const form = useForm({
    event_id: props.events.length > 0 ? props.events[0].id : '',
    starting_cash: ''
});

function submit() {
    form.post(route('pos.shift.open'));
}
</script>

<template>
    <Head title="Kassenschicht eröffnen" />

    <div class="min-h-screen bg-surface-100 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden">
            <div class="bg-surface-900 p-8 text-center">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h1 class="text-2xl font-black text-white font-display uppercase tracking-wider">Kasse eröffnen</h1>
                <p class="text-surface-400 mt-2">{{ terminal.name }}</p>
            </div>

            <div class="p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-surface-700 mb-2">Für welches Event arbeitest du?</label>
                        <select v-model="form.event_id" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 py-3 bg-surface-50" required>
                            <option value="" disabled>Event auswählen...</option>
                            <option v-for="event in events" :key="event.id" :value="event.id">{{ event.title }} ({{ new Date(event.start_date).toLocaleDateString('de-DE') }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-surface-700 mb-2">Bargeld-Startbestand (Wechselgeld)</label>
                        <div class="relative">
                            <input v-model="form.starting_cash" type="number" step="0.01" min="0" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 py-3 pl-4 pr-12 text-xl font-mono" placeholder="0.00" required>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <span class="text-surface-500 font-bold">€</span>
                            </div>
                        </div>
                        <p class="text-xs text-surface-500 mt-2">Bitte zähle das Bargeld in der Kasse vor Beginn.</p>
                    </div>

                    <button type="submit" :disabled="form.processing || !form.event_id" class="w-full bg-brand-500 text-white font-black py-4 rounded-xl hover:bg-brand-600 transition-colors uppercase tracking-widest disabled:opacity-50 mt-4 shadow-lg shadow-brand-500/30">
                        Schicht starten
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
