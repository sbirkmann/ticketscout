<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    event: Object
});

const form = useForm({
    dates: [
        { start_date: '', end_date: '' }
    ]
});

function addDate() {
    form.dates.push({ start_date: '', end_date: '' });
}

function removeDate(index) {
    form.dates.splice(index, 1);
}

function submit() {
    // Only keep dates that have at least a start_date
    form.dates = form.dates.filter(d => d.start_date !== '');
    
    if (form.dates.length === 0) {
        alert('Bitte gib mindestens einen Termin an.');
        addDate();
        return;
    }

    form.post(route('vendor.events.batch.store', props.event.id), {
        onSuccess: () => {
            // It will redirect on success
        }
    });
}
</script>

<template>
    <Head :title="'Serientermine: ' + event.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('vendor.events.show', event.id)" class="text-surface-400 hover:text-surface-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Serientermine erstellen</h2>
                    <p class="text-sm text-surface-500">Vorlage: {{ event.title }}</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                    <div class="p-8">
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-surface-900 mb-2">Neue Termine anlegen</h3>
                            <p class="text-surface-500 text-sm">
                                Gib hier alle weiteren Daten ein, an denen dieses Event stattfinden soll. 
                                Für jedes Datum wird ein neues Event als Entwurf angelegt, welches die gleichen Tickets, Bilder und Einstellungen wie das Original hat.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="space-y-4">
                                <div v-for="(date, index) in form.dates" :key="index" class="flex flex-col sm:flex-row gap-4 items-start sm:items-end bg-surface-50 p-4 rounded-2xl border border-surface-200">
                                    <div class="flex-1 w-full">
                                        <label class="block text-sm font-medium text-surface-700 mb-1">Startdatum & Zeit <span class="text-red-500">*</span></label>
                                        <input v-model="date.start_date" type="datetime-local" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 bg-white" required>
                                    </div>
                                    <div class="flex-1 w-full">
                                        <label class="block text-sm font-medium text-surface-700 mb-1">Enddatum & Zeit (optional)</label>
                                        <input v-model="date.end_date" type="datetime-local" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 bg-white">
                                    </div>
                                    <div class="shrink-0 w-full sm:w-auto flex justify-end">
                                        <button type="button" @click="removeDate(index)" class="p-3 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition-colors" title="Entfernen">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="addDate" class="w-full border-2 border-dashed border-surface-300 rounded-2xl p-4 text-surface-500 font-medium hover:text-brand-600 hover:border-brand-300 hover:bg-brand-50 transition-colors flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Weiteres Datum hinzufügen
                            </button>

                            <div class="pt-6 border-t border-surface-100 flex justify-end gap-3">
                                <Link :href="route('vendor.events.show', event.id)" class="px-6 py-3 rounded-xl font-medium text-surface-600 hover:bg-surface-100 transition-colors">
                                    Abbrechen
                                </Link>
                                <button type="submit" :disabled="form.processing" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md flex items-center gap-2 disabled:opacity-50">
                                    <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    {{ form.processing ? 'Generiere...' : form.dates.length + ' Termine generieren' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
