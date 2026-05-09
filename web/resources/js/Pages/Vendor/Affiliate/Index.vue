<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    links: Array,
    events: Array
});

const isModalOpen = ref(false);

const form = useForm({
    name: '',
    event_id: '',
    code: ''
});

const submit = () => {
    form.post(route('vendor.affiliate-links.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const deleteLink = (id) => {
    if (confirm('Tracking-Link wirklich löschen? Historische Daten bleiben erhalten.')) {
        useForm({}).delete(route('vendor.affiliate-links.destroy', id));
    }
};

const copyToClipboard = (code, eventSlug) => {
    // Generate URL based on whether it's linked to an event or the homepage
    let url = window.location.origin;
    if (eventSlug) {
        url += `/event/${eventSlug}?ref=${code}`;
    } else {
        url += `/?ref=${code}`;
    }
    
    navigator.clipboard.writeText(url).then(() => {
        alert('Link in Zwischenablage kopiert!');
    });
};
</script>

<template>
    <Head title="Affiliate & Tracking Links | Vendor" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-display font-black text-2xl text-surface-900 leading-tight">
                    Tracking & Affiliate Links
                </h2>
                <button @click="isModalOpen = true" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2 rounded-full font-bold shadow-md transition-all text-sm">
                    Neuen Link erstellen
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200 mb-8">
                    <div class="p-6 border-b border-surface-100 bg-surface-50 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-surface-900">Aktive Kampagnen</h3>
                            <p class="text-surface-500 text-sm">Erstelle Tracking-Links für Social Media Ads, Partner oder Influencer, um zu sehen, welche Kanäle am besten konvertieren.</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-surface-600">
                            <thead class="bg-surface-50 border-b border-surface-200 text-xs uppercase font-bold text-surface-700">
                                <tr>
                                    <th class="px-6 py-4">Name / Kampagne</th>
                                    <th class="px-6 py-4">Ziel</th>
                                    <th class="px-6 py-4">Klicks</th>
                                    <th class="px-6 py-4">Ticketverkäufe</th>
                                    <th class="px-6 py-4 text-right">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="links.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-surface-500">
                                        Du hast noch keine Tracking-Links erstellt.
                                    </td>
                                </tr>
                                <tr v-for="link in links" :key="link.id" class="border-b border-surface-100 last:border-0 hover:bg-surface-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-surface-900">{{ link.name }}</div>
                                        <div class="text-xs text-surface-400 mt-1">ref={{ link.code }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="link.event" class="text-xs bg-brand-50 text-brand-700 font-bold px-2 py-1 rounded">
                                            {{ link.event.title }}
                                        </span>
                                        <span v-else class="text-xs bg-surface-100 text-surface-600 font-bold px-2 py-1 rounded">
                                            Homepage (Global)
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-surface-900">
                                        {{ link.clicks }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold">
                                            {{ link.orders_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="copyToClipboard(link.code, link.event ? link.event.slug : null)" class="text-brand-600 hover:text-brand-800 font-bold text-xs uppercase mr-4">
                                            Kopieren
                                        </button>
                                        <button @click="deleteLink(link.id)" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase">
                                            Löschen
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-surface-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-xl max-w-md w-full overflow-hidden">
                <div class="p-6 border-b border-surface-100 flex justify-between items-center bg-surface-50">
                    <h3 class="text-lg font-bold text-surface-900">Neuer Tracking-Link</h3>
                    <button @click="isModalOpen = false" class="text-surface-400 hover:text-surface-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Kampagnen-Name *</label>
                        <input v-model="form.name" type="text" placeholder="z.B. Instagram Story Sommer" required class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Ziel-Event (Optional)</label>
                        <select v-model="form.event_id" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20">
                            <option value="">Startseite (Alle Events)</option>
                            <option v-for="event in events" :key="event.id" :value="event.id">
                                {{ event.title }}
                            </option>
                        </select>
                        <p class="text-xs text-surface-500 mt-1">Wenn leer, leitet der Link auf die Startseite des Ticketshops.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Eigener REF-Code (Optional)</label>
                        <input v-model="form.code" type="text" placeholder="z.B. insta24" class="w-full border-surface-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-500/20">
                        <p class="text-xs text-surface-500 mt-1">Wird automatisch generiert, falls leer gelassen.</p>
                    </div>
                    
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-surface-600 font-medium hover:bg-surface-100 rounded-xl transition-colors">Abbrechen</button>
                        <button type="submit" :disabled="form.processing" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2 rounded-xl font-bold transition-colors disabled:opacity-50">
                            Link erstellen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
