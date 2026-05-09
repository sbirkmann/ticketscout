<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue'; // Optional if exists

const props = defineProps({
    activities: Object
});

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleString('de-DE');
};

const getActionColor = (description) => {
    if (description.includes('created')) return 'text-green-600 bg-green-100';
    if (description.includes('updated')) return 'text-blue-600 bg-blue-100';
    if (description.includes('deleted')) return 'text-red-600 bg-red-100';
    return 'text-surface-600 bg-surface-100';
};
</script>

<template>
    <Head title="Audit Log | Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-display font-black text-2xl text-surface-900 leading-tight">
                System Audit-Log
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200">
                    <div class="p-6 border-b border-surface-100 bg-surface-50">
                        <h3 class="text-lg font-bold text-surface-900">Aktivitätenprotokoll</h3>
                        <p class="text-surface-500 text-sm">Übersicht aller wichtigen Systemänderungen und Benutzeraktionen.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-surface-600">
                            <thead class="bg-surface-50 border-b border-surface-200 text-xs uppercase font-bold text-surface-700">
                                <tr>
                                    <th class="px-6 py-4">Zeitpunkt</th>
                                    <th class="px-6 py-4">Aktion</th>
                                    <th class="px-6 py-4">Subjekt</th>
                                    <th class="px-6 py-4">Verursacher (Causer)</th>
                                    <th class="px-6 py-4">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!activities.data || activities.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-surface-500">
                                        Noch keine Aktivitäten aufgezeichnet.
                                    </td>
                                </tr>
                                <tr v-for="activity in activities.data" :key="activity.id" class="border-b border-surface-100 last:border-0 hover:bg-surface-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ formatDate(activity.created_at) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded text-xs font-bold uppercase" :class="getActionColor(activity.description)">
                                            {{ activity.description }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs">
                                        {{ activity.subject_type ? activity.subject_type.split('\\').pop() : '-' }} #{{ activity.subject_id || '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="activity.causer" class="font-bold text-surface-900">
                                            {{ activity.causer.name }} <span class="text-xs font-normal text-surface-500">#{{ activity.causer_id }}</span>
                                        </div>
                                        <span v-else class="text-surface-400 italic">System</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <pre class="text-xs bg-surface-100 p-2 rounded max-h-20 overflow-y-auto max-w-xs">{{ JSON.stringify(activity.properties, null, 2) }}</pre>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
