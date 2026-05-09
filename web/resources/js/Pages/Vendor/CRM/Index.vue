<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    events: Array
});

const form = useForm({
    event_id: '',
    subject: '',
    message: ''
});

function send() {
    form.post(route('vendor.crm.send'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('subject', 'message');
        }
    });
}
</script>

<template>
    <Head title="Kunden-Kommunikation (CRM)" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-display font-black text-2xl text-surface-900 leading-tight">
                Kunden-Kommunikation
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div v-if="$page.props.flash?.success" class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-200">
                    {{ $page.props.flash.success }}
                </div>
                
                <div v-if="$page.props.flash?.error" class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200">
                    {{ $page.props.flash.error }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-surface-200">
                    <div class="p-8">
                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-surface-900 mb-2">Nachricht an Ticketkäufer senden</h3>
                            <p class="text-surface-600">Sende wichtige Updates (z.B. Wetterwarnungen, Einlassänderungen) direkt an alle Gäste deines Events.</p>
                        </div>

                        <form @submit.prevent="send" class="space-y-6 max-w-3xl">
                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-1">Event auswählen *</label>
                                <select v-model="form.event_id" required class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400">
                                    <option value="" disabled>Bitte wählen...</option>
                                    <option v-for="event in events" :key="event.id" :value="event.id">
                                        {{ event.title }} ({{ new Date(event.start_date).toLocaleDateString('de-DE') }}) - {{ event.orders_count }} Bestellungen
                                    </option>
                                </select>
                                <p v-if="form.errors.event_id" class="text-red-500 text-xs mt-1">{{ form.errors.event_id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-1">Betreff *</label>
                                <input v-model="form.subject" type="text" required class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" placeholder="Wichtiges Update zu deinem Event" />
                                <p v-if="form.errors.subject" class="text-red-500 text-xs mt-1">{{ form.errors.subject }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-surface-700 mb-1">Nachricht *</label>
                                <textarea v-model="form.message" rows="6" required class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" placeholder="Liebe Gäste, ..."></textarea>
                                <p class="text-xs text-surface-500 mt-2">Die Anrede und der Link zum Event werden automatisch hinzugefügt.</p>
                                <p v-if="form.errors.message" class="text-red-500 text-xs mt-1">{{ form.errors.message }}</p>
                            </div>

                            <div class="pt-4 border-t border-surface-100 flex items-center justify-between">
                                <p class="text-sm text-surface-500">Diese Funktion sendet E-Mails an alle zahlenden Kunden dieses Events.</p>
                                <button type="submit" :disabled="form.processing" class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-3 px-6 rounded-xl transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2">
                                    <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Jetzt senden</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
