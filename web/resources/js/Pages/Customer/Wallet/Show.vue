<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    ticket: Object,
    transactions: Array,
});

const topupForm = useForm({
    amount: 10,
});

function submitTopup() {
    topupForm.post(route('wallet.topup', props.ticket.id));
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}
</script>

<template>
    <Head :title="'Guthaben: Ticket #' + ticket.code.substring(0, 8)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('dashboard')" class="text-surface-400 hover:text-surface-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Ticket Guthaben</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Balance Card -->
                <div class="bg-gradient-to-br from-brand-500 to-brand-700 rounded-3xl shadow-lg p-8 text-white flex flex-col md:flex-row items-center justify-between gap-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-8 -mr-8 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                    
                    <div class="relative z-10 text-center md:text-left">
                        <p class="text-brand-100 font-bold mb-1 uppercase tracking-wider text-sm">Aktuelles Guthaben</p>
                        <h3 class="text-5xl font-display font-black">{{ Number(ticket.wallet_balance || 0).toFixed(2) }} €</h3>
                        <p class="mt-2 text-brand-100 text-sm">Für: {{ ticket.event.title }} ({{ ticket.ticket_category?.name || 'Standard' }})</p>
                    </div>

                    <div class="relative z-10 w-full md:w-auto bg-white/10 backdrop-blur-sm p-6 rounded-2xl border border-white/20">
                        <h4 class="font-bold text-white mb-4 text-center">Guthaben aufladen</h4>
                        <form @submit.prevent="submitTopup" class="flex flex-col gap-3">
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="topupForm.amount = 10" :class="topupForm.amount === 10 ? 'bg-white text-brand-700' : 'bg-white/20 hover:bg-white/30 text-white'" class="py-2 rounded-xl font-bold transition-colors">10 €</button>
                                <button type="button" @click="topupForm.amount = 20" :class="topupForm.amount === 20 ? 'bg-white text-brand-700' : 'bg-white/20 hover:bg-white/30 text-white'" class="py-2 rounded-xl font-bold transition-colors">20 €</button>
                                <button type="button" @click="topupForm.amount = 50" :class="topupForm.amount === 50 ? 'bg-white text-brand-700' : 'bg-white/20 hover:bg-white/30 text-white'" class="py-2 rounded-xl font-bold transition-colors">50 €</button>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="relative flex-1">
                                    <input v-model.number="topupForm.amount" type="number" min="5" max="500" class="w-full bg-white/20 border-white/30 text-white placeholder-white/50 rounded-xl focus:ring-white focus:border-white pl-4 pr-8 py-2" placeholder="Anderer Betrag">
                                    <span class="absolute right-3 top-2.5 text-white/70 font-bold">€</span>
                                </div>
                                <button type="submit" :disabled="topupForm.processing" class="bg-white text-brand-600 px-6 py-2 rounded-xl font-bold hover:bg-brand-50 transition-colors disabled:opacity-50 whitespace-nowrap shadow-sm">
                                    Aufladen
                                </button>
                            </div>
                            <p v-if="topupForm.errors.amount" class="text-red-300 text-xs text-center mt-1">{{ topupForm.errors.amount }}</p>
                        </form>
                    </div>
                </div>

                <div v-if="$page.props.flash.success" class="bg-green-50 text-green-700 p-4 rounded-xl font-medium border border-green-200">
                    {{ $page.props.flash.success }}
                </div>
                
                <div v-if="$page.props.flash.error" class="bg-red-50 text-red-700 p-4 rounded-xl font-medium border border-red-200">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Transaction History -->
                <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                    <div class="p-6 border-b border-surface-200">
                        <h3 class="font-bold text-lg text-surface-900">Verlauf</h3>
                    </div>
                    
                    <div v-if="transactions.length === 0" class="p-8 text-center text-surface-500">
                        Es gibt noch keine Buchungen auf diesem Ticket. Lade jetzt Guthaben auf, um vor Ort damit bezahlen zu können.
                    </div>

                    <ul v-else class="divide-y divide-surface-100">
                        <li v-for="tx in transactions" :key="tx.id" class="p-4 hover:bg-surface-50 transition-colors flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div :class="tx.type === 'topup' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                                    <svg v-if="tx.type === 'topup'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-surface-900">
                                        {{ tx.type === 'topup' ? 'Aufladung' : (tx.type === 'refund' ? 'Auszahlung' : 'Zahlung am Stand') }}
                                    </p>
                                    <p class="text-xs text-surface-500">{{ formatDate(tx.created_at) }} &bull; {{ tx.description || 'Systembuchung' }}</p>
                                </div>
                            </div>
                            <div :class="tx.type === 'topup' ? 'text-green-600' : 'text-red-600'" class="font-bold whitespace-nowrap">
                                {{ tx.type === 'topup' ? '+' : '-' }} {{ Number(tx.amount).toFixed(2) }} €
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div class="text-center text-surface-500 text-sm">
                    Restguthaben kann nach Beendigung der Veranstaltung ausgezahlt werden.
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
