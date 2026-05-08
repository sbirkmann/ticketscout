<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
</script>

<template>
    <Head>
        <title>Hilfe & Kontakt - Ticketsout24</title>
        <meta name="description" content="Support, FAQs und Kontaktmöglichkeiten für Käufer und Veranstalter bei Ticketsout24.">
    </Head>

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <main class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
            <article class="prose prose-brand max-w-none bg-white p-10 rounded-3xl shadow-sm border border-surface-200 text-center">
                <h1 class="font-display text-4xl font-bold text-surface-900 mb-4">Hilfe & Kontakt</h1>
                <p class="text-lg text-surface-600 mb-8">Wie können wir dir helfen?</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left mt-12">
                    <div class="p-6 border border-surface-200 rounded-2xl bg-surface-50">
                        <h3 class="font-bold text-xl mb-3 text-surface-900">Fragen zur Ticketbestellung?</h3>
                        <p class="text-surface-600 mb-4">Wenn du Fragen zu einer bestehenden Bestellung hast, wende dich bitte direkt an den Veranstalter (die Kontaktdaten findest du auf deiner Bestellbestätigung).</p>
                    </div>
                    <div class="p-6 border border-surface-200 rounded-2xl bg-surface-50">
                        <h3 class="font-bold text-xl mb-3 text-surface-900">Technischer Support</h3>
                        <p class="text-surface-600 mb-4">Für technische Probleme mit der Plattform schreibe uns eine E-Mail an:</p>
                        <a href="mailto:support@ticketsout24.de" class="text-brand-600 font-bold hover:underline">support@ticketsout24.de</a>
                    </div>
                </div>
            </article>
        </main>
    </div>
</template>
