<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
    articles: Array,
});

const categoryForm = useForm({
    name: '',
    color: '#e2e8f0',
    sort_order: 0
});

const articleForm = useForm({
    id: null,
    category_id: '',
    name: '',
    sku: '',
    default_price: '',
    tax_rate: 19.00,
    is_active: true
});

const isEditingArticle = ref(false);

function submitCategory() {
    categoryForm.post(route('vendor.pos-article-categories.store'), {
        preserveScroll: true,
        onSuccess: () => categoryForm.reset(),
    });
}

function deleteCategory(id) {
    if (confirm('Wirklich löschen? Alle zugeordneten Artikel verlieren ihre Kategorie.')) {
        router.delete(route('vendor.pos-article-categories.destroy', id), { preserveScroll: true });
    }
}

function submitArticle() {
    if (isEditingArticle.value) {
        articleForm.put(route('vendor.pos-articles.update', articleForm.id), {
            preserveScroll: true,
            onSuccess: () => resetArticleForm(),
        });
    } else {
        articleForm.post(route('vendor.pos-articles.store'), {
            preserveScroll: true,
            onSuccess: () => resetArticleForm(),
        });
    }
}

function editArticle(article) {
    isEditingArticle.value = true;
    articleForm.id = article.id;
    articleForm.category_id = article.category_id || '';
    articleForm.name = article.name;
    articleForm.sku = article.sku;
    articleForm.default_price = article.default_price;
    articleForm.tax_rate = article.tax_rate;
    articleForm.is_active = !!article.is_active;
}

function resetArticleForm() {
    isEditingArticle.value = false;
    articleForm.reset();
    articleForm.id = null;
}

function deleteArticle(id) {
    if (confirm('Artikel wirklich löschen?')) {
        router.delete(route('vendor.pos-articles.destroy', id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="POS Artikel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-display font-black text-2xl leading-tight text-surface-900 uppercase">
                POS Artikel & Kategorien
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 flex flex-col md:flex-row gap-8">
                
                <!-- KATEGORIEN -->
                <div class="w-full md:w-1/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200 mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-surface-900 mb-4">Neue Kategorie</h3>
                            <form @submit.prevent="submitCategory" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-surface-700">Name</label>
                                    <input v-model="categoryForm.name" type="text" class="mt-1 block w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" required>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-1">
                                        <label class="block text-sm font-bold text-surface-700">Farbe</label>
                                        <input v-model="categoryForm.color" type="color" class="mt-1 block w-full h-10 rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm cursor-pointer p-1">
                                    </div>
                                    <div class="w-24">
                                        <label class="block text-sm font-bold text-surface-700">Sortierung</label>
                                        <input v-model="categoryForm.sort_order" type="number" class="mt-1 block w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm">
                                    </div>
                                </div>
                                <button type="submit" :disabled="categoryForm.processing" class="w-full bg-surface-900 text-white font-bold py-2 px-4 rounded-xl hover:bg-surface-800 transition-colors">
                                    Kategorie hinzufügen
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-surface-900 mb-4">Kategorien</h3>
                            <ul v-if="categories.length > 0" class="space-y-2">
                                <li v-for="cat in categories" :key="cat.id" class="flex items-center justify-between p-3 bg-surface-50 rounded-xl border border-surface-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: cat.color }"></div>
                                        <span class="font-bold text-surface-800">{{ cat.name }}</span>
                                    </div>
                                    <button @click="deleteCategory(cat.id)" class="text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </li>
                            </ul>
                            <div v-else class="text-sm text-surface-500 text-center py-4">Keine Kategorien vorhanden.</div>
                        </div>
                    </div>
                </div>

                <!-- ARTIKEL -->
                <div class="w-full md:w-2/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200 mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-surface-900 mb-4">{{ isEditingArticle ? 'Artikel bearbeiten' : 'Neuer Artikel' }}</h3>
                            <form @submit.prevent="submitArticle" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-surface-700">Name</label>
                                        <input v-model="articleForm.name" type="text" class="mt-1 block w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-surface-700">Kategorie (optional)</label>
                                        <select v-model="articleForm.category_id" class="mt-1 block w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm">
                                            <option value="">-- Keine Kategorie --</option>
                                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-surface-700">Standard-Preis (€)</label>
                                        <input v-model="articleForm.default_price" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-surface-700">Steuersatz (%)</label>
                                        <select v-model="articleForm.tax_rate" class="mt-1 block w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" required>
                                            <option value="19.00">19% (Regulär)</option>
                                            <option value="7.00">7% (Ermäßigt)</option>
                                            <option value="0.00">0% (Steuerfrei)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-surface-700">SKU / Kurzcode</label>
                                        <input v-model="articleForm.sku" type="text" class="mt-1 block w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm uppercase">
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 pt-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" v-model="articleForm.is_active" class="rounded border-surface-300 text-brand-500 focus:ring-brand-500 shadow-sm">
                                        <span class="ml-2 text-sm text-surface-600 font-bold">Aktiv für Kasse</span>
                                    </label>
                                    
                                    <div class="flex-1"></div>
                                    
                                    <button v-if="isEditingArticle" type="button" @click="resetArticleForm" class="text-surface-500 hover:text-surface-700 font-bold text-sm">
                                        Abbrechen
                                    </button>
                                    <button type="submit" :disabled="articleForm.processing" class="bg-brand-500 text-white font-bold py-2 px-6 rounded-xl hover:bg-brand-600 transition-colors">
                                        {{ isEditingArticle ? 'Speichern' : 'Hinzufügen' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-surface-200">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-surface-900 mb-4">Artikelübersicht</h3>
                            
                            <div v-if="articles.length > 0" class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="text-surface-500 border-b border-surface-200">
                                        <tr>
                                            <th class="pb-3 font-medium">Name</th>
                                            <th class="pb-3 font-medium">Kategorie</th>
                                            <th class="pb-3 font-medium text-right">Preis</th>
                                            <th class="pb-3 font-medium text-right">Steuer</th>
                                            <th class="pb-3 font-medium text-center">Status</th>
                                            <th class="pb-3 font-medium text-right">Aktionen</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-surface-100">
                                        <tr v-for="article in articles" :key="article.id" class="hover:bg-surface-50 transition-colors">
                                            <td class="py-3 font-bold text-surface-900">
                                                {{ article.name }}
                                                <div v-if="article.sku" class="text-[10px] text-surface-400 font-mono">{{ article.sku }}</div>
                                            </td>
                                            <td class="py-3">
                                                <span v-if="article.category" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium" :style="{ backgroundColor: article.category.color + '20', color: article.category.color !== '#ffffff' ? article.category.color : '#000' }">
                                                    {{ article.category.name }}
                                                </span>
                                                <span v-else class="text-surface-400 text-xs">-</span>
                                            </td>
                                            <td class="py-3 text-right font-mono font-medium">{{ Number(article.default_price).toFixed(2) }} €</td>
                                            <td class="py-3 text-right text-surface-500">{{ Number(article.tax_rate) }}%</td>
                                            <td class="py-3 text-center">
                                                <span v-if="article.is_active" class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                                                <span v-else class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                            </td>
                                            <td class="py-3 text-right">
                                                <button @click="editArticle(article)" class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold text-xs">Bearbeiten</button>
                                                <button @click="deleteArticle(article.id)" class="text-red-600 hover:text-red-900 font-bold text-xs">Löschen</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-sm text-surface-500 text-center py-8">Noch keine Artikel angelegt.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
