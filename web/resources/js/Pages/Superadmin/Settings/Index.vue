<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Einstellungen - Superadmin" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <!-- Admin Header -->
        <div class="bg-surface-900 text-white px-6 py-4 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-4">
                <Link :href="route('superadmin.dashboard')" class="font-display font-black text-xl tracking-tighter">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </Link>
                <span class="text-surface-500">/</span>
                <span class="text-surface-300 font-medium">Einstellungen</span>
            </div>
            <Link :href="route('superadmin.dashboard')" class="text-surface-400 hover:text-white text-sm">
                &larr; Zurück zum Dashboard
            </Link>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex justify-between items-center mb-8">
                <h1 class="font-display text-3xl font-black text-surface-900">Plattform Einstellungen</h1>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden p-8">
                <p class="text-surface-600 mb-6">Globale Plattform-Einstellungen wie Gebühren, Zahlungsarten und Layouts können hier in Zukunft konfiguriert werden.</p>
                
                <div class="bg-surface-50 border-l-4 border-brand-500 p-4 rounded-r-xl">
                    <p class="text-surface-700"><strong>Hinweis:</strong> Dieses Feature befindet sich in der Entwicklung.</p>
                </div>
            </div>
        </main>
    </div>
</template>
