<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    shift: Object,
    receipts: Array
});

function printZReport() {
    window.print();
}
</script>

<template>
    <Head :title="`Z-Bon: ${shift.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('vendor.pos-reports.index')" class="text-brand-600 hover:text-brand-800 font-bold text-sm mb-2 inline-flex items-center gap-1 print:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Zurück zur Übersicht
                    </Link>
                    <h2 class="font-display font-black text-2xl leading-tight text-surface-900 uppercase">
                        Z-Bon (Schicht-Bericht)
                    </h2>
                </div>
                <button @click="printZReport" class="bg-surface-900 hover:bg-surface-800 text-white px-6 py-2 rounded-xl font-bold transition-colors print:hidden flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Drucken / PDF
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200 print:shadow-none print:border-none">
                    <div class="p-8">
                        <div class="text-center mb-8 border-b border-surface-200 pb-8 print:border-b-2 print:border-black">
                            <h1 class="text-3xl font-black uppercase tracking-widest font-display mb-2">Z-Bon</h1>
                            <p class="text-surface-500 font-mono text-sm">Schicht #{{ shift.id }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-8 text-sm font-mono border-b border-surface-200 pb-8">
                            <div>
                                <p class="text-surface-500 uppercase tracking-wider text-[10px] font-bold">Terminal</p>
                                <p class="font-bold text-lg">{{ shift.terminal?.name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-surface-500 uppercase tracking-wider text-[10px] font-bold">Bediener</p>
                                <p class="font-bold text-lg">{{ shift.opened_by?.name || 'System' }}</p>
                            </div>
                            <div>
                                <p class="text-surface-500 uppercase tracking-wider text-[10px] font-bold">Eröffnet</p>
                                <p>{{ new Date(shift.opened_at).toLocaleString('de-DE') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-surface-500 uppercase tracking-wider text-[10px] font-bold">Geschlossen</p>
                                <p>{{ shift.closed_at ? new Date(shift.closed_at).toLocaleString('de-DE') : 'Noch offen' }}</p>
                            </div>
                        </div>

                        <div class="space-y-4 mb-8 border-b border-surface-200 pb-8">
                            <h3 class="font-bold uppercase tracking-widest text-xs text-surface-500 mb-4">Kassensturz (Bargeld)</h3>
                            <div class="flex justify-between text-lg">
                                <span>Startbestand (Wechselgeld):</span>
                                <span class="font-mono">{{ Number(shift.starting_cash).toFixed(2) }} €</span>
                            </div>
                            <!-- Summarize Einlagen/Entnahmen -->
                            <div class="flex justify-between text-lg">
                                <span>+ Bareinnahmen laut System:</span>
                                <span class="font-mono">
                                    {{ Number(receipts.filter(r => r.payment_method === 'cash').reduce((sum, r) => sum + parseFloat(r.total_gross), 0)).toFixed(2) }} €
                                </span>
                            </div>
                            <div class="flex justify-between text-lg font-bold pt-2 border-t border-surface-200">
                                <span>Soll-Bestand:</span>
                                <span class="font-mono">{{ shift.expected_cash ? Number(shift.expected_cash).toFixed(2) : '-' }} €</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold text-brand-600 pt-2">
                                <span>Ist-Bestand (Gezählt):</span>
                                <span class="font-mono">{{ shift.ending_cash ? Number(shift.ending_cash).toFixed(2) : '-' }} €</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold pt-2 border-t border-surface-200" :class="{'text-red-500': shift.difference < 0, 'text-green-500': shift.difference > 0}">
                                <span>Differenz:</span>
                                <span class="font-mono">{{ shift.closed_at ? Number(shift.difference).toFixed(2) : '-' }} €</span>
                            </div>
                        </div>

                        <div class="space-y-4 mb-8 border-b border-surface-200 pb-8">
                            <h3 class="font-bold uppercase tracking-widest text-xs text-surface-500 mb-4">Zahlungsmittel & Umsätze</h3>
                            
                            <div class="flex justify-between">
                                <span>Bargeld:</span>
                                <span class="font-mono">{{ Number(receipts.filter(r => r.payment_method === 'cash').reduce((sum, r) => sum + parseFloat(r.total_gross) + parseFloat(r.tip_amount || 0), 0)).toFixed(2) }} €</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Wallet (Guthaben):</span>
                                <span class="font-mono">{{ Number(receipts.filter(r => r.payment_method === 'wallet').reduce((sum, r) => sum + parseFloat(r.total_gross) + parseFloat(r.tip_amount || 0), 0)).toFixed(2) }} €</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Karte (Stripe/EC):</span>
                                <span class="font-mono">{{ Number(receipts.filter(r => r.payment_method === 'card').reduce((sum, r) => sum + parseFloat(r.total_gross) + parseFloat(r.tip_amount || 0), 0)).toFixed(2) }} €</span>
                            </div>
                            
                            <div class="flex justify-between text-brand-600 font-bold pt-2 border-t border-surface-200 mt-2">
                                <span>Davon Trinkgeld (Tip):</span>
                                <span class="font-mono">{{ Number(receipts.reduce((sum, r) => sum + parseFloat(r.tip_amount || 0), 0)).toFixed(2) }} €</span>
                            </div>

                            <div class="flex justify-between text-xl font-black pt-4 border-t border-surface-200 mt-2">
                                <span>Gesamteinnahmen (Inkl. Tip):</span>
                                <span class="font-mono">{{ Number(receipts.reduce((sum, r) => sum + parseFloat(r.total_gross) + parseFloat(r.tip_amount || 0), 0)).toFixed(2) }} €</span>
                            </div>
                        </div>
                        
                        <div class="text-center text-xs text-surface-400 font-mono">
                            Druckdatum: {{ new Date().toLocaleString('de-DE') }}<br>
                            Ticketsout24 Advanced POS
                        </div>

                    </div>
                </div>

                <!-- Einzelbelege (Nicht mitdrucken im Bon) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200 print:hidden">
                    <div class="p-8">
                        <h3 class="text-lg font-bold text-surface-900 mb-4">Erfasste Belege dieser Schicht ({{ receipts.length }})</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-surface-500 border-b border-surface-200">
                                    <tr>
                                        <th class="pb-3 font-medium">Beleg-Nr.</th>
                                        <th class="pb-3 font-medium">Zeit</th>
                                        <th class="pb-3 font-medium">Zahlart</th>
                                        <th class="pb-3 font-medium text-right">Summe</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-surface-100">
                                    <tr v-for="receipt in receipts" :key="receipt.id" class="hover:bg-surface-50 transition-colors">
                                        <td class="py-3 font-mono font-bold">#{{ receipt.receipt_number }}</td>
                                        <td class="py-3">{{ new Date(receipt.created_at).toLocaleTimeString('de-DE') }}</td>
                                        <td class="py-3 uppercase text-xs tracking-wider">{{ receipt.payment_method }}</td>
                                        <td class="py-3 text-right font-mono font-bold">{{ Number(receipt.total_gross).toFixed(2) }} €</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
