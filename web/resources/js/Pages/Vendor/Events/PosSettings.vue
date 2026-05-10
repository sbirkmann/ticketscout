<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    event: Object,
    vendorArticles: Array,
    eventArticles: Array
});

// Build a list of all vendor articles and mark them if they are selected for this event
const formArticles = ref([]);

onMounted(() => {
    formArticles.value = props.vendorArticles.map(article => {
        const existing = props.eventArticles.find(ea => ea.pos_article_id === article.id);
        return {
            pos_article_id: article.id,
            name: article.name,
            default_price: article.default_price,
            category: article.category?.name,
            is_available: existing ? existing.is_available : false,
            override_price: existing?.override_price || ''
        };
    });
});

const form = useForm({
    pos_receipt_header_override: props.event.pos_receipt_header_override || '',
    pos_receipt_footer_override: props.event.pos_receipt_footer_override || '',
    articles: []
});

function save() {
    // Filter out articles that are not available to keep the DB clean, 
    // or just send all and only save the checked ones
    form.articles = formArticles.value.filter(a => a.is_available).map(a => ({
        pos_article_id: a.pos_article_id,
        is_available: true,
        override_price: a.override_price !== '' ? a.override_price : null
    }));

    form.put(route('vendor.events.pos.update', props.event.id), {
        preserveScroll: true
    });
}
</script>

<template>
    <Head :title="`POS Setup: ${event.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('vendor.events.show', event.id)" class="text-brand-600 hover:text-brand-800 font-bold text-sm mb-2 inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Zurück zum Event
                    </Link>
                    <h2 class="font-display font-black text-2xl leading-tight text-surface-900 uppercase">
                        POS Kassen-Setup
                    </h2>
                    <p class="text-surface-500 font-bold">{{ event.title }}</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                
                <div v-if="!$page.props.auth.user.vendor_settings?.has_advanced_pos" class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-3xl mb-8 font-bold">
                    Das erweiterte Kassensystem (Advanced POS) ist für Ihren Account noch nicht freigeschaltet. Bitte kontaktieren Sie den Support.
                </div>

                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200">
                    <div class="p-8">
                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-surface-900 mb-2">Verfügbare Artikel für dieses Event</h3>
                            <p class="text-surface-500 text-sm">Wählen Sie, welche Ihrer angelegten POS-Artikel auf diesem Event verkauft werden sollen. Optional können Sie einen abweichenden Preis speziell für dieses Event definieren.</p>
                        </div>

                        <form @submit.prevent="save">
                            <div class="overflow-x-auto mb-8">
                                <table class="w-full text-left text-sm">
                                    <thead class="text-surface-500 border-b border-surface-200">
                                        <tr>
                                            <th class="pb-3 w-12">Aktiv</th>
                                            <th class="pb-3 font-medium">Artikel</th>
                                            <th class="pb-3 font-medium">Kategorie</th>
                                            <th class="pb-3 font-medium text-right">Standard-Preis</th>
                                            <th class="pb-3 font-medium text-right">Event-Preis (optional)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-surface-100">
                                        <tr v-for="article in formArticles" :key="article.pos_article_id" :class="{'bg-surface-50': article.is_available}">
                                            <td class="py-4">
                                                <input type="checkbox" v-model="article.is_available" class="rounded border-surface-300 text-brand-500 focus:ring-brand-500 w-5 h-5">
                                            </td>
                                            <td class="py-4 font-bold text-surface-900">
                                                {{ article.name }}
                                            </td>
                                            <td class="py-4 text-surface-500">
                                                {{ article.category || '-' }}
                                            </td>
                                            <td class="py-4 text-right font-mono text-surface-500">
                                                {{ Number(article.default_price).toFixed(2) }} €
                                            </td>
                                            <td class="py-4 text-right">
                                                <input type="number" step="0.01" min="0" v-model="article.override_price" :disabled="!article.is_available" class="w-32 rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 text-right disabled:opacity-50 disabled:bg-surface-100" placeholder="-" />
                                                <span class="ml-2 text-surface-500">€</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mb-8 pt-8 border-t border-surface-200">
                                <h3 class="text-xl font-bold text-surface-900 mb-4">Bon-Druck anpassen (Nur für dieses Event)</h3>
                                <p class="text-surface-500 text-sm mb-6">Standardmäßig werden die Bon-Texte aus deinen globalen <Link :href="route('vendor.settings.index')" class="text-brand-600 hover:underline">Vendor-Einstellungen</Link> übernommen. Hier kannst du sie speziell für dieses Event überschreiben (z.B. wenn das Event von einer fremden Location durchgeführt wird).</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-surface-50 p-6 rounded-3xl border border-surface-200">
                                    <div>
                                        <label class="block text-sm font-medium text-surface-700 mb-1">Kassenbon Kopfzeile (Override)</label>
                                        <textarea v-model="form.pos_receipt_header_override" rows="3" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 font-mono text-sm" placeholder="Bleibt leer = Globaler Standard wird genutzt"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-surface-700 mb-1">Kassenbon Fußzeile (Override)</label>
                                        <textarea v-model="form.pos_receipt_footer_override" rows="3" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 font-mono text-sm" placeholder="Bleibt leer = Globaler Standard wird genutzt"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-6 border-t border-surface-200">
                                <button type="submit" :disabled="form.processing" class="bg-brand-500 text-white font-bold py-3 px-8 rounded-xl hover:bg-brand-600 transition-colors shadow-lg shadow-brand-500/30">
                                    Einstellungen speichern
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
