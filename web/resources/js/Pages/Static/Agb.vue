<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
</script>

<template>
    <Head>
        <title>AGB - Ticketsout24</title>
        <meta name="description" content="Allgemeine Geschäftsbedingungen der Ticketsout24 Plattform für den Kauf von Veranstaltungstickets.">
    </Head>

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <main class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
            <article class="prose prose-brand max-w-none bg-white p-10 rounded-3xl shadow-sm border border-surface-200">
                <h1 class="font-display text-4xl font-bold text-surface-900 mb-8">Allgemeine Geschäftsbedingungen (AGB)</h1>
                
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-surface-800 mb-4">1. Geltungsbereich</h2>
                    <p class="text-surface-600 leading-relaxed mb-4">
                        Diese Allgemeinen Geschäftsbedingungen gelten für alle Verträge über den Erwerb von Tickets zwischen Ticketsout24 (nachfolgend "Plattform" genannt) und dem Käufer. Ticketsout24 tritt hierbei überwiegend als Vermittler auf. Der eigentliche Veranstaltungsvertrag kommt direkt zwischen dem Käufer und dem jeweiligen Veranstalter (Händler) zustande.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-surface-800 mb-4">2. Vertragsschluss</h2>
                    <p class="text-surface-600 leading-relaxed mb-4">
                        Das Angebot für einen Vertragsabschluss geht vom Kunden aus, sobald er den Bestell-Button ("Zahlungspflichtig bestellen") anklickt. Mit der Zusendung der Bestellbestätigung per E-Mail nimmt Ticketsout24 das Angebot im Namen des Veranstalters an.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-surface-800 mb-4">3. Preise und Gebühren</h2>
                    <p class="text-surface-600 leading-relaxed mb-4">
                        Die auf der Plattform angegebenen Ticketpreise beinhalten die gesetzliche Mehrwertsteuer sowie gegebenenfalls anfallende Vorverkaufsgebühren. Zusatzleistungen (Add-ons) werden separat ausgewiesen. Zahlungen werden über unseren Zahlungsdienstleister (Stripe) abgewickelt.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-surface-800 mb-4">4. Rückgabe und Widerrufsrecht</h2>
                    <p class="text-surface-600 leading-relaxed mb-4">
                        Ein Widerrufsrecht für Verbraucher besteht nicht bei Verträgen zur Erbringung von Dienstleistungen im Zusammenhang mit Freizeitbetätigungen, wenn der Vertrag für die Erbringung einen spezifischen Termin oder Zeitraum vorsieht (§ 312g Abs. 2 Nr. 9 BGB). Das bedeutet, dass Tickets grundsätzlich von Rückgabe und Umtausch ausgeschlossen sind.
                    </p>
                </section>
                
                <p class="text-sm text-surface-400 mt-12 border-t border-surface-100 pt-4">Stand: Mai 2026</p>
            </article>
        </main>
    </div>
</template>
