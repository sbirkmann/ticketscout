<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    logs: Array,
    stats: Object
});
</script>

<template>
    <Head title="System Health - Superadmin" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <!-- Admin Header -->
        <div class="bg-surface-900 text-white px-6 py-4 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-4">
                <Link :href="route('superadmin.dashboard')" class="font-display font-black text-xl tracking-tighter">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </Link>
                <span class="text-surface-500">/</span>
                <span class="text-surface-300 font-medium">System Health</span>
            </div>
            <Link :href="route('superadmin.dashboard')" class="text-surface-400 hover:text-white text-sm">
                &larr; Zurück zum Dashboard
            </Link>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex justify-between items-center mb-8">
                <h1 class="font-display text-3xl font-black text-surface-900">System Health & Logs</h1>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Stats overview -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden p-6">
                        <h2 class="text-lg font-bold text-surface-900 mb-4 border-b border-surface-100 pb-2">Datenbank Statistiken</h2>
                        <dl class="space-y-4">
                            <div class="flex justify-between">
                                <dt class="text-surface-600 text-sm">User & Vendors</dt>
                                <dd class="font-bold text-surface-900">{{ stats.total_users }} (davon {{ stats.total_vendors }} V)</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-surface-600 text-sm">Events</dt>
                                <dd class="font-bold text-surface-900">{{ stats.total_events }}</dd>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-surface-100">
                                <dt class="text-surface-600 text-sm">Bestellungen (Gesamt)</dt>
                                <dd class="font-bold text-surface-900">{{ stats.total_orders }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-surface-600 text-sm">Bestellungen (Bezahlt)</dt>
                                <dd class="font-bold text-green-600">{{ stats.orders_paid }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-surface-600 text-sm">Bestellungen (Fehlgeschlagen)</dt>
                                <dd class="font-bold text-red-600">{{ stats.orders_failed }}</dd>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-surface-100">
                                <dt class="text-surface-600 text-sm">Datenbank Größe (SQLite)</dt>
                                <dd class="font-mono text-surface-900 bg-surface-100 px-2 rounded">{{ stats.db_size }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Log output -->
                <div class="lg:col-span-2">
                    <div class="bg-surface-900 rounded-3xl shadow-sm border border-surface-800 overflow-hidden flex flex-col h-[600px]">
                        <div class="p-4 border-b border-surface-800 flex justify-between items-center bg-surface-800">
                            <h2 class="text-sm font-bold text-surface-300 uppercase tracking-wider">laravel.log (Letzte 100 Zeilen)</h2>
                            <div class="flex gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            </div>
                        </div>
                        <div class="p-4 overflow-y-auto font-mono text-xs text-surface-300 leading-relaxed bg-[#1e1e1e] flex-grow">
                            <div v-if="logs.length > 0">
                                <div v-for="(log, index) in logs" :key="index" class="mb-1 pb-1 border-b border-surface-800/50 break-all"
                                     :class="{
                                         'text-red-400 font-bold': log.includes('ERROR') || log.includes('Exception'),
                                         'text-yellow-400': log.includes('WARNING'),
                                         'text-blue-300': log.includes('INFO')
                                     }">
                                    {{ log }}
                                </div>
                            </div>
                            <div v-else class="text-surface-500 italic">Keine Logs gefunden oder Log-Datei leer.</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
