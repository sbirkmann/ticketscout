<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    shifts: Object
});
</script>

<template>
    <Head title="POS Berichte (Z-Bons)" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-display font-black text-2xl leading-tight text-surface-900 uppercase">
                POS Kassenberichte & Z-Bons
            </h2>
            <p class="text-surface-500 text-sm mt-1">Übersicht aller Schichten und Kassenabschlüsse.</p>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200">
                    <div class="p-8">
                        <div v-if="!shifts.data || shifts.data.length === 0" class="text-center py-12 text-surface-500">
                            Noch keine Kassenabschlüsse vorhanden.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-surface-500 border-b border-surface-200">
                                    <tr>
                                        <th class="pb-3 font-medium">Zeitraum</th>
                                        <th class="pb-3 font-medium">Terminal</th>
                                        <th class="pb-3 font-medium">Mitarbeiter</th>
                                        <th class="pb-3 font-medium text-right">Startgeld</th>
                                        <th class="pb-3 font-medium text-right">Endgeld (Soll)</th>
                                        <th class="pb-3 font-medium text-right">Differenz</th>
                                        <th class="pb-3 font-medium text-center">Status</th>
                                        <th class="pb-3 font-medium text-right">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-surface-100">
                                    <tr v-for="shift in shifts.data" :key="shift.id" class="hover:bg-surface-50 transition-colors">
                                        <td class="py-4">
                                            <div class="font-bold text-surface-900">{{ new Date(shift.opened_at).toLocaleDateString('de-DE') }}</div>
                                            <div class="text-[10px] text-surface-400">
                                                {{ new Date(shift.opened_at).toLocaleTimeString('de-DE', {hour: '2-digit', minute:'2-digit'}) }} - 
                                                {{ shift.closed_at ? new Date(shift.closed_at).toLocaleTimeString('de-DE', {hour: '2-digit', minute:'2-digit'}) : 'Offen' }}
                                            </div>
                                        </td>
                                        <td class="py-4 font-medium">{{ shift.terminal?.name || 'Unbekannt' }}</td>
                                        <td class="py-4 text-surface-600">{{ shift.opened_by?.name || 'System' }}</td>
                                        <td class="py-4 text-right font-mono">{{ Number(shift.starting_cash).toFixed(2) }} €</td>
                                        <td class="py-4 text-right font-mono">{{ shift.expected_cash ? Number(shift.expected_cash).toFixed(2) + ' €' : '-' }}</td>
                                        <td class="py-4 text-right font-mono font-bold" :class="{'text-red-500': shift.difference < 0, 'text-green-500': shift.difference > 0, 'text-surface-500': shift.difference === 0 || !shift.closed_at}">
                                            {{ shift.closed_at ? Number(shift.difference).toFixed(2) + ' €' : '-' }}
                                        </td>
                                        <td class="py-4 text-center">
                                            <span v-if="shift.status === 'open'" class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-700 rounded-lg">Geöffnet</span>
                                            <span v-else class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider bg-surface-200 text-surface-700 rounded-lg">Abgeschlossen</span>
                                        </td>
                                        <td class="py-4 text-right">
                                            <Link :href="route('vendor.pos-reports.show', shift.id)" class="text-brand-600 hover:text-brand-900 font-bold text-xs">
                                                Details / Z-Bon
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <!-- Pagination can be added here if needed using shifts.links -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
