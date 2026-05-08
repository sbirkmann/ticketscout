<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    templates: Array
});

function deleteTemplate(id) {
    if (confirm('Dieses Template wirklich löschen?')) {
        router.delete(route('vendor.templates.destroy', id));
    }
}
</script>

<template>
    <Head title="Ticket Templates" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Ticket Templates</h2>
                <Link :href="route('vendor.templates.create')" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2 rounded-xl font-bold shadow-sm transition-colors">
                    Neues Template erstellen
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm">
                    <p class="text-surface-600 mb-6">Erstelle individuelle PDF-Ticketvorlagen für deine Events mit dem visuellen Editor.</p>

                    <div v-if="templates.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="template in templates" :key="template.id" class="border border-surface-200 rounded-2xl p-6 hover:shadow-md transition-shadow">
                            <h3 class="font-bold text-lg text-surface-900 mb-2">{{ template.name }}</h3>
                            <p class="text-sm text-surface-500 mb-4">Letzte Änderung: {{ new Date(template.updated_at).toLocaleDateString() }}</p>
                            
                            <div class="flex items-center gap-3">
                                <Link :href="route('vendor.templates.edit', template.id)" class="text-brand-600 hover:text-brand-800 font-bold text-sm bg-brand-50 px-4 py-2 rounded-lg flex-1 text-center">
                                    Bearbeiten
                                </Link>
                                <button @click="deleteTemplate(template.id)" class="text-red-600 hover:text-red-800 font-bold text-sm bg-red-50 px-4 py-2 rounded-lg">
                                    Löschen
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 bg-surface-50 rounded-2xl border border-dashed border-surface-200 text-surface-500">
                        Noch keine Ticket Templates vorhanden.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
