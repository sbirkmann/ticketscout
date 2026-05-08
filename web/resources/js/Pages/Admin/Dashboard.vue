<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    pendingEvents: Array,
    pendingLocations: Array,
});

const form = useForm({});

const approveEvent = (id) => {
    form.post(route('superadmin.events.approve', id));
};

const approveLocation = (id) => {
    form.post(route('superadmin.locations.approve', id));
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold font-display text-surface-900">Admin Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Pending Events -->
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm">
                    <h3 class="text-xl font-bold font-display text-surface-900 mb-6">Freizugebende Events</h3>
                    
                    <div v-if="pendingEvents.length === 0" class="text-surface-500 py-8 text-center bg-surface-50 rounded-2xl border border-surface-100">
                        Keine Events zur Freigabe ausstehend.
                    </div>
                    
                    <div v-else class="space-y-4">
                        <div v-for="event in pendingEvents" :key="event.id" class="flex items-center justify-between p-4 border border-surface-200 rounded-2xl hover:bg-surface-50 transition-colors">
                            <div>
                                <h4 class="font-bold text-surface-900">{{ event.title }}</h4>
                                <p class="text-sm text-surface-500">
                                    Händler: {{ event.vendor?.name || 'Unbekannt' }} &bull; Datum: {{ new Date(event.start_date).toLocaleDateString() }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="route('event.show', event.slug)" target="_blank" class="px-4 py-2 text-sm font-medium text-surface-600 bg-surface-100 rounded-xl hover:bg-surface-200">
                                    Ansehen
                                </Link>
                                <button @click="approveEvent(event.id)" class="px-4 py-2 text-sm font-bold text-white bg-green-500 rounded-xl hover:bg-green-600 shadow-sm">
                                    Freigeben
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Locations -->
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm">
                    <h3 class="text-xl font-bold font-display text-surface-900 mb-6">Freizugebende Locations</h3>
                    
                    <div v-if="pendingLocations.length === 0" class="text-surface-500 py-8 text-center bg-surface-50 rounded-2xl border border-surface-100">
                        Keine Locations zur Freigabe ausstehend.
                    </div>
                    
                    <div v-else class="space-y-4">
                        <div v-for="loc in pendingLocations" :key="loc.id" class="flex items-center justify-between p-4 border border-surface-200 rounded-2xl hover:bg-surface-50 transition-colors">
                            <div>
                                <h4 class="font-bold text-surface-900">{{ loc.name }}</h4>
                                <p class="text-sm text-surface-500">
                                    {{ loc.address }}, {{ loc.zip }} {{ loc.city }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button @click="approveLocation(loc.id)" class="px-4 py-2 text-sm font-bold text-white bg-green-500 rounded-xl hover:bg-green-600 shadow-sm">
                                    Freigeben
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
