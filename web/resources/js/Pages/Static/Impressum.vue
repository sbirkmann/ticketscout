<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
</script>

<template>
    <Head>
        <title>Impressum - Ticketsout24</title>
        <meta name="description" content="Impressum und rechtliche Angaben der Ticketsout24 Plattform.">
    </Head>

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <main class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
            <article class="prose prose-brand max-w-none bg-white p-10 rounded-3xl shadow-sm border border-surface-200">
                <h1 class="font-display text-4xl font-bold text-surface-900 mb-8">Impressum</h1>
                
                <section class="mb-8 text-surface-700 leading-relaxed">
                    <h2 class="text-xl font-bold text-surface-900 mb-2">Angaben gemäß § 5 TMG</h2>
                    <p>
                        Ticketsout24 GmbH<br>
                        Musterstraße 123<br>
                        10115 Berlin<br>
                        Deutschland
                    </p>

                    <h2 class="text-xl font-bold text-surface-900 mt-6 mb-2">Vertreten durch</h2>
                    <p>Max Mustermann (Geschäftsführer)</p>

                    <h2 class="text-xl font-bold text-surface-900 mt-6 mb-2">Kontakt</h2>
                    <p>
                        Telefon: +49 (0) 30 123456-0<br>
                        E-Mail: info@ticketsout24.de
                    </p>

                    <h2 class="text-xl font-bold text-surface-900 mt-6 mb-2">Registereintrag</h2>
                    <p>
                        Eintragung im Handelsregister.<br>
                        Registergericht: Amtsgericht Berlin (Charlottenburg)<br>
                        Registernummer: HRB 123456 B
                    </p>

                    <h2 class="text-xl font-bold text-surface-900 mt-6 mb-2">Umsatzsteuer-ID</h2>
                    <p>Umsatzsteuer-Identifikationsnummer gemäß § 27 a Umsatzsteuergesetz:<br>DE123456789</p>

                    <h2 class="text-xl font-bold text-surface-900 mt-6 mb-2">Streitschlichtung</h2>
                    <p>
                        Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: 
                        <a href="https://ec.europa.eu/consumers/odr" target="_blank" class="text-brand-600 hover:underline">https://ec.europa.eu/consumers/odr</a>.<br>
                        Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.
                    </p>
                </section>
            </article>
        </main>
    </div>
</template>
