<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    events: Object
});

const rejectForm = useForm({
    reason: ''
});

const isRejectModalOpen = ref(false);
const selectedEvent = ref(null);

const approveEvent = (id) => {
    if (confirm('Bist du sicher, dass du dieses Event für den Marktplatz freigeben möchtest?')) {
        useForm({}).post(route('event-moderation.approve', id));
    }
};

const openRejectModal = (event) => {
    selectedEvent.value = event;
    isRejectModalOpen.value = true;
};

const closeRejectModal = () => {
    isRejectModalOpen.value = false;
    selectedEvent.value = null;
    rejectForm.reset();
};

const submitReject = () => {
    if (selectedEvent.value) {
        rejectForm.post(route('event-moderation.reject', selectedEvent.value.id), {
            onSuccess: () => {
                closeRejectModal();
            }
        });
    }
};
</script>

<template>
    <Head title="Event-Freigaben | Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-display font-black text-2xl text-surface-900 leading-tight">
                Ausstehende Event-Freigaben
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200">
                    <div class="p-6 border-b border-surface-100 bg-surface-50">
                        <h3 class="text-lg font-bold text-surface-900">Events in Prüfung</h3>
                        <p class="text-surface-500 text-sm">Diese Events wurden von Vendoren zur Veröffentlichung eingereicht und warten auf die Freigabe.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-surface-600">
                            <thead class="bg-surface-50 border-b border-surface-200 text-xs uppercase font-bold text-surface-700">
                                <tr>
                                    <th class="px-6 py-4">Event</th>
                                    <th class="px-6 py-4">Vendor</th>
                                    <th class="px-6 py-4">Datum & Ort</th>
                                    <th class="px-6 py-4 text-right">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!events.data || events.data.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-surface-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <p class="font-bold text-lg text-surface-600">Alles erledigt!</p>
                                            <p class="text-surface-500">Es gibt derzeit keine neuen Events, die auf eine Freigabe warten.</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="event in events.data" :key="event.id" class="border-b border-surface-100 last:border-0 hover:bg-surface-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-xl bg-surface-200 overflow-hidden shrink-0">
                                                <img v-if="event.image_path" :src="`/storage/${event.image_path}`" class="w-full h-full object-cover" />
                                            </div>
                                            <div>
                                                <div class="font-bold text-surface-900">{{ event.title }}</div>
                                                <Link :href="route('event.show', event.slug)" target="_blank" class="text-xs text-brand-600 hover:text-brand-800 font-bold">Vorschau ansehen ↗</Link>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-surface-900">{{ event.vendor?.name }}</div>
                                        <div class="text-xs text-surface-500">{{ event.vendor?.email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-surface-900">{{ new Date(event.start_date).toLocaleDateString('de-DE') }}</div>
                                        <div class="text-xs text-surface-500">{{ event.location?.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openRejectModal(event)" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase px-3 py-2 rounded-lg hover:bg-red-50 transition-colors mr-2">
                                            Ablehnen
                                        </button>
                                        <button @click="approveEvent(event.id)" class="bg-green-100 text-green-700 hover:bg-green-200 hover:text-green-800 font-bold text-xs uppercase px-4 py-2 rounded-lg shadow-sm transition-colors">
                                            Freigeben
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="isRejectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-surface-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-xl max-w-lg w-full overflow-hidden">
                <div class="p-6 border-b border-surface-100 bg-surface-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-red-600 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Event ablehnen
                    </h3>
                    <button @click="closeRejectModal" class="text-surface-400 hover:text-surface-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submitReject" class="p-6 space-y-4">
                    <p class="text-sm text-surface-600">Bitte gib einen Grund an, warum das Event <strong>{{ selectedEvent?.title }}</strong> abgelehnt wird. Dieser Grund wird dem Veranstalter mitgeteilt.</p>
                    
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Begründung *</label>
                        <textarea v-model="rejectForm.reason" rows="4" required placeholder="z.B. Das hochgeladene Bild hat nicht die nötige Auflösung oder die Beschreibung ist unvollständig..." class="w-full border-surface-300 rounded-xl focus:border-red-500 focus:ring focus:ring-red-500/20"></textarea>
                    </div>
                    
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="closeRejectModal" class="px-4 py-2 text-surface-600 font-medium hover:bg-surface-100 rounded-xl transition-colors">Abbrechen</button>
                        <button type="submit" :disabled="rejectForm.processing" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-xl font-bold transition-colors disabled:opacity-50">
                            Event ablehnen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
