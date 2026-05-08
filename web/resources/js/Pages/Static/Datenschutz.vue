<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
</script>

<template>
    <Head>
        <title>Datenschutzerklärung - Ticketsout24</title>
        <meta name="description" content="Datenschutzerklärung und Informationen zum Umgang mit Ihren Daten bei Ticketsout24.">
    </Head>

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <main class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
            <article class="prose prose-brand max-w-none bg-white p-10 rounded-3xl shadow-sm border border-surface-200">
                <h1 class="font-display text-4xl font-bold text-surface-900 mb-8">Datenschutzerklärung</h1>
                
                <section class="mb-8 text-surface-700 leading-relaxed">
                    <p>
                        Wir nehmen den Schutz Ihrer persönlichen Daten sehr ernst. Wir behandeln Ihre personenbezogenen Daten vertraulich und entsprechend der gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerklärung.
                    </p>
                    <p class="mt-4">
                        Die Nutzung unserer Website ist in der Regel ohne Angabe personenbezogener Daten möglich. Soweit auf unseren Seiten personenbezogene Daten (beispielsweise Name, Anschrift oder E-Mail-Adressen für den Ticketkauf) erhoben werden, erfolgt dies, soweit möglich, stets auf freiwilliger Basis. Diese Daten werden ohne Ihre ausdrückliche Zustimmung nicht an Dritte (außer an den jeweiligen Veranstalter zur Vertragserfüllung) weitergegeben.
                    </p>
                </section>
            </article>
        </main>
    </div>
</template>
