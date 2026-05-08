<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    location: Object,
    seatingPlans: Array
});

function deletePlan(id) {
    if (confirm('Soll dieser Saalplan wirklich gelöscht werden?')) {
        router.delete(route('vendor.locations.seating-plans.destroy', [props.location.id, id]));
    }
}
</script>

<template>
    <Head title="Saalpläne" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('superadmin.locations.index')" class="text-surface-500 hover:text-brand-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">
                        Saalpläne: {{ location.name }}
                    </h2>
                </div>
                <Link :href="route('vendor.locations.seating-plans.create', location.id)" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2 rounded-xl font-bold shadow-sm transition-colors">
                    Neuer Saalplan
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        
                        <div v-for="plan in seatingPlans" :key="plan.id" class="border border-surface-200 rounded-2xl p-6 hover:shadow-md transition-shadow relative overflow-hidden group">
                            
                            <div v-if="plan.bg_image_path" class="absolute inset-0 z-0 opacity-10">
                                <img :src="'/storage/' + plan.bg_image_path" class="w-full h-full object-cover">
                            </div>
                            
                            <div class="relative z-10">
                                <h3 class="font-bold text-xl text-surface-900 mb-1">{{ plan.name }}</h3>
                                <p class="text-sm text-surface-500 mb-6">Erstellt: {{ new Date(plan.created_at).toLocaleDateString() }}</p>
                                
                                <div class="flex gap-3">
                                    <Link :href="route('vendor.locations.seating-plans.edit', [location.id, plan.id])" class="flex-1 bg-brand-50 hover:bg-brand-100 text-brand-700 text-center py-2 rounded-lg font-bold text-sm transition-colors">
                                        Bearbeiten
                                    </Link>
                                    <button @click="deletePlan(plan.id)" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg font-bold text-sm transition-colors">
                                        Löschen
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="seatingPlans.length === 0" class="col-span-full py-12 text-center text-surface-500 bg-surface-50 rounded-2xl border border-dashed border-surface-200">
                            Noch kein Saalplan für diese Location vorhanden.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
