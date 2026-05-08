<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    order_id: Number
});
</script>

<template>
    <Head title="Vielen Dank für deine Bestellung" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white flex flex-col">
        <Navbar />

        <main class="flex-grow flex items-center justify-center py-20 px-4">
            <div class="bg-white rounded-3xl shadow-glass border border-surface-200 p-12 max-w-2xl w-full text-center">
                <div class="w-24 h-24 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-8 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <h1 class="font-display font-black text-4xl text-surface-900 mb-4">Vielen Dank für deine Bestellung!</h1>
                <p class="text-xl text-surface-600 mb-8 leading-relaxed">
                    Deine Zahlung war erfolgreich. Die Tickets wurden dir per E-Mail zugesendet.
                    <span v-if="order_id" class="block mt-2 font-mono text-sm text-surface-400">Bestellnummer: #{{ order_id }}</span>
                </p>
                
                <div class="space-x-4">
                    <Link :href="route('home')" class="inline-block bg-surface-900 hover:bg-black text-white px-8 py-3 rounded-full font-bold transition-all shadow-md">
                        Zurück zur Startseite
                    </Link>
                    <Link :href="route('profile.edit')" class="inline-block bg-brand-50 hover:bg-brand-100 text-brand-600 px-8 py-3 rounded-full font-bold transition-all">
                        Zu meinen Tickets
                    </Link>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
