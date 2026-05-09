<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    promoCodes: Object
});

function destroy(promoCode) {
    if (confirm(`Gutscheincode "${promoCode.code}" wirklich löschen?`)) {
        router.delete(route('vendor.promo-codes.destroy', promoCode.id));
    }
}
</script>

<template>
    <Head title="Gutscheine & Rabatte" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Gutscheine & Rabatte</h2>
                <Link :href="route('vendor.promo-codes.create')" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2 rounded-xl font-medium transition-colors text-sm shadow-sm">
                    + Neuen Code erstellen
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-surface-50 border-b border-surface-200">
                            <tr>
                                <th class="px-6 py-4 text-sm font-bold text-surface-700">Code</th>
                                <th class="px-6 py-4 text-sm font-bold text-surface-700">Rabatt</th>
                                <th class="px-6 py-4 text-sm font-bold text-surface-700">Event-Bindung</th>
                                <th class="px-6 py-4 text-sm font-bold text-surface-700">Gültigkeit / Nutzung</th>
                                <th class="px-6 py-4 text-sm font-bold text-surface-700 text-right">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-100">
                            <tr v-for="code in promoCodes.data" :key="code.id" class="hover:bg-surface-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-surface-900 bg-surface-100 px-2 py-1 rounded inline-block font-mono text-sm tracking-wider">{{ code.code }}</div>
                                    <div class="mt-1">
                                        <span v-if="code.is_active" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">Aktiv</span>
                                        <span v-else class="text-xs bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded-full">Inaktiv</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-brand-600">
                                    <span v-if="code.type === 'percent'">{{ code.value }} %</span>
                                    <span v-else>{{ code.value }} €</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-surface-600">
                                    <span v-if="code.event">{{ code.event.title }}</span>
                                    <span v-else class="italic">Alle Events</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-surface-600">
                                    <div>Verwendet: {{ code.current_uses }} / {{ code.max_uses || '∞' }}</div>
                                    <div v-if="code.expires_at" class="text-xs text-surface-500 mt-1">Bis: {{ new Date(code.expires_at).toLocaleDateString() }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('vendor.promo-codes.edit', code.id)" class="text-xs text-surface-500 hover:text-brand-600 font-medium px-3 py-1 rounded-lg bg-surface-100 hover:bg-brand-50 transition-colors">Bearbeiten</Link>
                                        <button @click="destroy(code)" class="text-xs text-red-500 hover:text-red-700 font-medium px-3 py-1 rounded-lg bg-red-50 hover:bg-red-100 transition-colors">Löschen</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="promoCodes.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-surface-500">Du hast noch keine Rabattcodes angelegt.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex justify-center gap-2" v-if="promoCodes.last_page > 1">
                    <Link v-for="link in promoCodes.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                          class="px-4 py-2 rounded-xl text-sm font-bold"
                          :class="link.active ? 'bg-brand-500 text-white' : 'bg-white text-surface-600 hover:bg-surface-100 border border-surface-200'"></Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
