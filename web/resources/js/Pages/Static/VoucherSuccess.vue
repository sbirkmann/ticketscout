<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    voucherCode: String,
    amount: [String, Number],
    email: String,
    alreadyProcessed: Boolean
});
</script>

<template>
    <Head>
        <title>Gutschein erfolgreich gekauft - Ticketsout24</title>
    </Head>

    <div class="min-h-screen bg-surface-50 font-sans">
        <Navbar />

        <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            
            <div v-if="alreadyProcessed" class="bg-white rounded-3xl p-10 shadow-sm border border-surface-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-brand-500 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <h1 class="font-display font-black text-3xl text-surface-900 mb-4">Gutschein bereits ausgestellt</h1>
                <p class="text-surface-600 mb-8 text-lg">
                    Dieser Gutschein wurde bereits erfolgreich verarbeitet und an deine E-Mail-Adresse gesendet.
                </p>
                <Link :href="route('home')" class="bg-surface-900 hover:bg-black text-white px-8 py-3 rounded-full font-bold transition-all shadow-md">
                    Zurück zur Startseite
                </Link>
            </div>

            <div v-else class="bg-white rounded-3xl p-10 shadow-sm border border-surface-200">
                <div class="bg-green-100 text-green-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                
                <h1 class="font-display font-black text-4xl text-surface-900 mb-4">Vielen Dank für deinen Kauf!</h1>
                <p class="text-surface-600 mb-8 text-lg">
                    Dein Gutschein im Wert von <strong class="text-surface-900">{{ amount }},00 €</strong> wurde erfolgreich erstellt. Wir haben dir eine Bestätigung an <strong>{{ email }}</strong> gesendet.
                </p>

                <div class="bg-surface-50 border border-brand-200 rounded-2xl p-8 mb-8 inline-block shadow-sm">
                    <p class="text-sm font-bold text-brand-600 uppercase tracking-wider mb-2">Dein Gutscheincode</p>
                    <p class="font-mono text-3xl font-black text-surface-900 tracking-widest">{{ voucherCode }}</p>
                </div>

                <div>
                    <Link :href="route('home')" class="bg-brand-500 hover:bg-brand-600 text-white px-8 py-3 rounded-full font-bold transition-all shadow-md inline-block">
                        Jetzt Events entdecken
                    </Link>
                </div>
            </div>

        </main>
    </div>
</template>
