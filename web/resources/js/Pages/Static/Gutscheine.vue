<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const selectedAmount = ref(50);
const amounts = [25, 50, 100, 150, 200];
const message = ref('');
const loading = ref(false);

const checkout = async () => {
    loading.value = true;
    try {
        const response = await fetch(route('voucher.checkout'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({
                amount: selectedAmount.value,
                message: message.value
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            window.location.href = data.url;
        } else {
            alert('Ein Fehler ist aufgetreten.');
        }
    } catch (e) {
        console.error(e);
        alert('Ein Fehler ist aufgetreten.');
    }
    loading.value = false;
};
</script>

<template>
    <Head>
        <title>Gutscheine - Ticketsout24</title>
        <meta name="description" content="Verschenke unvergessliche Erlebnisse mit den Ticketsout24 Geschenkgutscheinen.">
    </Head>

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <main>
            <!-- Hero Section -->
            <div class="relative bg-surface-900 overflow-hidden">
                <div class="absolute inset-0">
                    <img src="/images/voucher_hero.png" class="w-full h-full object-cover opacity-50" alt="Premium Voucher">
                    <div class="absolute inset-0 bg-gradient-to-r from-surface-900 via-surface-900/80 to-transparent"></div>
                </div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32 flex flex-col items-start text-left">
                    <span class="text-brand-400 font-bold tracking-wider uppercase text-sm mb-4">Das perfekte Geschenk</span>
                    <h1 class="font-display font-black text-5xl md:text-7xl text-white mb-6 tracking-tight drop-shadow-md max-w-2xl">
                        Verschenke <span class="text-brand-500">Emotionen.</span>
                    </h1>
                    <p class="text-xl text-surface-200 max-w-xl mb-10 font-medium drop-shadow">
                        Mit einem Ticketsout24 Gutschein liegst du immer richtig. Einlösbar für tausende Konzerte, Festivals und Events.
                    </p>
                    <a href="#kaufen" class="bg-brand-500 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-brand-600 transition-colors shadow-glow flex items-center gap-2">
                        Jetzt Gutschein kaufen
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Content Section -->
            <div id="kaufen" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
                    
                    <!-- Left: Info -->
                    <div>
                        <h2 class="font-display font-bold text-3xl text-surface-900 mb-6">Wie funktioniert's?</h2>
                        <div class="space-y-8 mt-10">
                            <div class="flex gap-4">
                                <div class="bg-brand-100 text-brand-600 w-12 h-12 rounded-full flex items-center justify-center font-bold text-xl shrink-0">1</div>
                                <div>
                                    <h3 class="font-bold text-xl text-surface-900 mb-2">Wert auswählen</h3>
                                    <p class="text-surface-600">Wähle deinen Wunschbetrag aus oder trage einen individuellen Betrag ein.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="bg-brand-100 text-brand-600 w-12 h-12 rounded-full flex items-center justify-center font-bold text-xl shrink-0">2</div>
                                <div>
                                    <h3 class="font-bold text-xl text-surface-900 mb-2">Design & Grüße</h3>
                                    <p class="text-surface-600">Wähle ein Motiv und hinterlasse eine persönliche Nachricht für den Beschenkten.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="bg-brand-100 text-brand-600 w-12 h-12 rounded-full flex items-center justify-center font-bold text-xl shrink-0">3</div>
                                <div>
                                    <h3 class="font-bold text-xl text-surface-900 mb-2">Sofort erhalten</h3>
                                    <p class="text-surface-600">Zahle sicher und erhalte den Gutschein sofort per E-Mail zum Ausdrucken oder Weiterleiten.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Purchase Box -->
                    <div class="bg-white p-8 rounded-3xl shadow-glass border border-surface-200">
                        <h3 class="font-bold text-2xl text-surface-900 mb-6">Gutschein erstellen</h3>
                        
                        <label class="block text-sm font-medium text-surface-700 mb-3">Betrag wählen</label>
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <button 
                                v-for="amount in amounts" 
                                :key="amount"
                                @click="selectedAmount = amount"
                                :class="selectedAmount === amount ? 'bg-brand-500 text-white border-brand-500' : 'bg-surface-50 text-surface-700 border-surface-300 hover:border-brand-300'"
                                class="py-3 px-4 rounded-xl border text-lg font-bold transition-colors"
                            >
                                {{ amount }} €
                            </button>
                            <button class="py-3 px-4 rounded-xl border border-surface-300 bg-surface-50 text-surface-700 text-lg font-bold hover:border-brand-300 transition-colors">
                                Eigener Betrag
                            </button>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-surface-700 mb-2">Persönliche Nachricht (optional)</label>
                            <textarea v-model="message" rows="3" placeholder="Für wen ist dieser Gutschein und was möchtest du sagen?" class="w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200"></textarea>
                        </div>

                        <div class="border-t border-surface-100 pt-6 mb-6">
                            <div class="flex justify-between items-center text-xl font-bold text-surface-900">
                                <span>Gesamtbetrag</span>
                                <span>{{ selectedAmount }},00 €</span>
                            </div>
                        </div>

                        <button @click="checkout" :disabled="loading" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-md text-lg font-bold text-white bg-surface-900 hover:bg-surface-800 focus:outline-none transition-all disabled:opacity-50">
                            <span v-if="loading">Lädt...</span>
                            <span v-else>Jetzt kaufen</span>
                        </button>
                        <p class="text-xs text-center text-surface-500 mt-4">
                            Gutscheine sind 3 Jahre gültig und können für alle Events eingelöst werden.
                        </p>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>
