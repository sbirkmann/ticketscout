<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
    stats: Array
});

// Auto-refresh the page every 30 seconds to keep stats live
let refreshInterval;
onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['stats'] });
    }, 30000);
});

onUnmounted(() => {
    clearInterval(refreshInterval);
});
</script>

<template>
    <Head title="Check-in Tracker" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-display font-black text-2xl text-surface-900 leading-tight">
                    Echtzeit Check-in Tracker
                </h2>
                <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Live Update
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-surface-200">
                    <div class="p-8 border-b border-surface-100">
                        <h3 class="text-xl font-bold text-surface-900 mb-2">Heutige & Anstehende Events</h3>
                        <p class="text-surface-600">Verfolge live, wie viele deiner Gäste bereits eingecheckt wurden. Die Ansicht aktualisiert sich automatisch alle 30 Sekunden.</p>
                    </div>

                    <div class="p-8">
                        <div v-if="stats.length > 0" class="space-y-8">
                            <div v-for="(stat, index) in stats" :key="index" class="bg-surface-50 border border-surface-200 rounded-2xl p-6">
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <h4 class="font-bold text-lg text-surface-900">{{ stat.event.title }}</h4>
                                        <p class="text-sm text-surface-500">{{ new Date(stat.event.start_date).toLocaleString('de-DE') }} Uhr</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-display font-black text-3xl" :class="stat.percentage >= 90 ? 'text-green-600' : 'text-brand-600'">
                                            {{ stat.percentage }}%
                                        </span>
                                        <p class="text-xs font-bold text-surface-500 uppercase tracking-wide">Eingecheckt</p>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="w-full bg-surface-200 rounded-full h-4 mb-4 overflow-hidden">
                                    <div class="h-4 rounded-full transition-all duration-1000 ease-in-out" 
                                         :class="stat.percentage >= 90 ? 'bg-green-500' : 'bg-brand-500'"
                                         :style="`width: ${stat.percentage}%`"></div>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <p class="font-medium text-surface-900"><span class="font-bold">{{ stat.scanned }}</span> Gäste drin</p>
                                    <p class="font-medium text-surface-500"><span class="font-bold">{{ stat.total - stat.scanned }}</span> fehlen noch</p>
                                    <p class="font-medium text-surface-500"><span class="font-bold">{{ stat.total }}</span> Gesamt Tickets</p>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-surface-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            <h3 class="text-xl font-bold text-surface-900 mb-2">Keine Events in diesem Zeitraum</h3>
                            <p class="text-surface-500 max-w-md mx-auto">Es gibt keine Events von gestern bis in 7 Tagen, für die ein Einlass getrackt werden könnte.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
