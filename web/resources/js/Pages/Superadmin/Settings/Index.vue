<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object
});

const form = useForm({
    settings: {
        banner_active: props.settings.banner_active || '0',
        banner_text: props.settings.banner_text || '',
        banner_color: props.settings.banner_color || 'brand-500'
    }
});

function save() {
    form.post(route('settings.store'), {
        preserveScroll: true
    });
}
</script>

<template>
    <Head title="Einstellungen - Superadmin" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <!-- Admin Header -->
        <div class="bg-surface-900 text-white px-6 py-4 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-4">
                <Link :href="route('superadmin.dashboard')" class="font-display font-black text-xl tracking-tighter">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </Link>
                <span class="text-surface-500">/</span>
                <span class="text-surface-300 font-medium">Einstellungen</span>
            </div>
            <Link :href="route('superadmin.dashboard')" class="text-surface-400 hover:text-white text-sm">
                &larr; Zurück zum Dashboard
            </Link>
        </div>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex justify-between items-center mb-8">
                <h1 class="font-display text-3xl font-black text-surface-900">Plattform Einstellungen</h1>
            </div>

            <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 text-green-700 p-4 rounded-xl border border-green-200">
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="save" class="bg-white rounded-3xl shadow-sm border border-surface-200 overflow-hidden">
                <div class="p-8">
                    <h2 class="text-xl font-bold text-surface-900 mb-6 border-b border-surface-100 pb-2">Globales Ankündigungs-Banner</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="flex items-center gap-3">
                                <input type="checkbox" v-model="form.settings.banner_active" true-value="1" false-value="0" class="rounded text-brand-500 focus:ring-brand-500 w-5 h-5" />
                                <span class="font-bold text-surface-900">Banner auf der Startseite & App aktivieren</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-surface-700 mb-1">Banner Text</label>
                            <input type="text" v-model="form.settings.banner_text" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400" placeholder="z.B. Sommer-Sale: 10% auf alle Tickets!" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-surface-700 mb-1">Hintergrundfarbe</label>
                            <select v-model="form.settings.banner_color" class="w-full rounded-xl border-surface-300 focus:ring-brand-400 focus:border-brand-400">
                                <option value="brand-500">Brand Primary (Orange)</option>
                                <option value="surface-900">Dark (Schwarz)</option>
                                <option value="blue-500">Info (Blau)</option>
                                <option value="red-500">Alert (Rot)</option>
                                <option value="green-500">Success (Grün)</option>
                            </select>
                        </div>
                        
                        <div class="pt-4 mt-6 border-t border-surface-100">
                            <p class="text-sm text-surface-500 mb-3">Vorschau:</p>
                            <div v-if="form.settings.banner_active === '1'" class="py-2 text-center text-sm font-bold text-white rounded shadow-sm" :class="`bg-${form.settings.banner_color}`">
                                {{ form.settings.banner_text || 'Beispiel Banner Text' }}
                            </div>
                            <div v-else class="py-2 text-center text-sm font-medium text-surface-400 bg-surface-100 rounded">
                                Banner ist deaktiviert
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-surface-50 px-8 py-4 border-t border-surface-100 flex justify-end">
                    <button type="submit" :disabled="form.processing" class="bg-surface-900 hover:bg-surface-800 text-white font-bold py-2 px-6 rounded-xl transition-colors disabled:opacity-50">
                        Einstellungen speichern
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
