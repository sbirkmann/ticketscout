<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import axios from 'axios';

const props = defineProps({
    event: Object,
    locations: Array,
    categories: Array,
    artists: Array,
});

const form = useForm({
    _method: 'put',
    title: props.event.title,
    event_category_id: props.event.event_category_id,
    location_id: props.event.location_id || '',
    new_location_name: '',
    new_location_address: '',
    new_location_zip: '',
    new_location_city: '',
    new_location_country: 'DE',
    description: props.event.description || '',
    start_date: props.event.start_date.slice(0, 16),
    end_date: props.event.end_date ? props.event.end_date.slice(0, 16) : '',
    status: props.event.status,
    show_remaining_tickets: props.event.show_remaining_tickets,
    image: null,
    tags: props.event.tags ? props.event.tags.join(', ') : '',
    artist_ids: props.event.artists ? props.event.artists.map(a => a.id) : [],
    ticket_template_id: props.event.ticket_template_id || '',
    seating_plan_id: props.event.seating_plan_id || '',
    enable_wallet: props.event.enable_wallet ? true : false
});

const isNewLocation = ref(false);

// Previews
const previewImage = ref(props.event.image_path ? '/storage/' + props.event.image_path : null);
const previewHero = ref(props.event.hero_background_path ? '/storage/' + props.event.hero_background_path : null);
const previewGallery = ref(props.event.gallery_images ? props.event.gallery_images.map(p => '/storage/' + p) : []);

const handleImageUpload = (event, field) => {
    const file = event.target.files[0];
    if (file) {
        form[field] = file;
        if (field === 'image') previewImage.value = URL.createObjectURL(file);
        if (field === 'hero_background') previewHero.value = URL.createObjectURL(file);
    }
};

const handleGalleryUpload = (event) => {
    const files = Array.from(event.target.files);
    form.gallery_images = files;
    previewGallery.value = files.map(file => URL.createObjectURL(file));
};

const submit = () => {
    form.post(route('vendor.events.update', props.event.id));
};

const destroy = () => {
    if (confirm('Bist du sicher, dass du dieses Event löschen möchtest?')) {
        form.delete(route('vendor.events.destroy', props.event.id));
    }
};

const generatingAi = ref(false);

const generateDescription = () => {
    if (!form.title) {
        alert('Bitte gib zuerst einen Titel ein.');
        return;
    }
    generatingAi.value = true;
    axios.post(route('vendor.events.ai-description'), {
        title: form.title,
        tags: form.tags
    }).then(res => {
        form.description = res.data.description;
    }).catch(err => {
        alert('Fehler bei der KI-Generierung.');
    }).finally(() => {
        generatingAi.value = false;
    });
};
</script>

<template>
    <Head :title="event.title + ' bearbeiten'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('vendor.events.index')" class="text-surface-400 hover:text-surface-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Event bearbeiten</h2>
                </div>
                <button @click="destroy" class="text-red-500 hover:text-red-700 font-bold text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Löschen
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm border border-surface-200 sm:rounded-2xl overflow-hidden">
                    <form @submit.prevent="submit" class="p-8 space-y-8" enctype="multipart/form-data">
                        
                        <!-- Basic Info -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Grundinformationen</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Event Titel *</label>
                                    <input v-model="form.title" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" required>
                                    <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Kategorie *</label>
                                    <select v-model="form.event_category_id" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" required>
                                        <option value="" disabled>Bitte wählen...</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                    <div v-if="form.errors.event_category_id" class="text-red-500 text-xs mt-1">{{ form.errors.event_category_id }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Template -->
                        <div class="bg-surface-50 p-6 rounded-2xl border border-surface-200">
                            <h4 class="font-bold text-surface-900 mb-4">Ticket Design</h4>
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1">Ticket Template</label>
                                <select v-model="form.ticket_template_id" class="w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200">
                                    <option value="">-- Standard System Template --</option>
                                    <option v-for="t in $page.props.templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                                </select>
                                <p class="text-xs text-surface-500 mt-1">Wähle das PDF-Design, das Kunden nach dem Kauf erhalten.</p>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Datum & Uhrzeit</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Start *</label>
                                    <input v-model="form.start_date" type="datetime-local" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" required>
                                    <div v-if="form.errors.start_date" class="text-red-500 text-xs mt-1">{{ form.errors.start_date }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Ende (optional)</label>
                                    <input v-model="form.end_date" type="datetime-local" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500">
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Veranstaltungsort</h3>
                            
                            <div class="mb-4">
                                <label class="flex items-center gap-2 mb-2">
                                    <input type="radio" :value="false" v-model="isNewLocation" class="text-brand-500 focus:ring-brand-500">
                                    <span class="text-sm font-medium">Bestehende Location wählen</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" :value="true" v-model="isNewLocation" class="text-brand-500 focus:ring-brand-500">
                                    <span class="text-sm font-medium">Neue Location anlegen</span>
                                </label>
                            </div>

                            <div v-if="!isNewLocation">
                                <select v-model="form.location_id" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" :required="!isNewLocation">
                                    <option value="" disabled>Bitte wählen...</option>
                                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }} ({{ loc.city }})</option>
                                </select>
                                <div v-if="form.errors.location_id" class="text-red-500 text-xs mt-1">{{ form.errors.location_id }}</div>
                                
                                <div v-if="form.location_id" class="mt-4">
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Saalplan (optional)</label>
                                    <select v-model="form.seating_plan_id" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500">
                                        <option value="">-- Kein Saalplan --</option>
                                        <option v-for="plan in locations.find(l => l.id === form.location_id)?.seating_plans || []" :key="plan.id" :value="plan.id">
                                            {{ plan.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="isNewLocation" class="bg-surface-50 p-4 rounded-xl border border-surface-200 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Name der Location *</label>
                                    <input v-model="form.new_location_name" type="text" class="w-full rounded-xl border-surface-300" :required="isNewLocation">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Straße & Nr *</label>
                                    <input v-model="form.new_location_address" type="text" class="w-full rounded-xl border-surface-300" :required="isNewLocation">
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-1">
                                        <label class="block text-sm font-medium text-surface-700 mb-1">PLZ *</label>
                                        <input v-model="form.new_location_zip" type="text" class="w-full rounded-xl border-surface-300" :required="isNewLocation">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-surface-700 mb-1">Stadt *</label>
                                        <input v-model="form.new_location_city" type="text" class="w-full rounded-xl border-surface-300" :required="isNewLocation">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Tags -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Beschreibung & Tags</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Künstler / Line-Up</label>
                                    <MultiSelect v-model="form.artist_ids" :options="artists" placeholder="Künstler auswählen..." />
                                </div>
                                <div>
                                    <div class="flex justify-between items-end mb-1">
                                        <label class="block text-sm font-medium text-surface-700">Beschreibung</label>
                                        <button type="button" @click="generateDescription" :disabled="generatingAi" class="text-xs flex items-center gap-1 bg-purple-100 text-purple-700 hover:bg-purple-200 px-3 py-1.5 rounded-lg font-bold transition-colors disabled:opacity-50">
                                            <span v-if="generatingAi">Generiert...</span>
                                            <span v-else>✨ Mit KI generieren</span>
                                        </button>
                                    </div>
                                    <textarea v-model="form.description" rows="5" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Beschreibe dein Event..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Tags (kommagetrennt)</label>
                                    <input v-model="form.tags" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500" placeholder="z.B. Open Air, 80s, Rock, Foodtrucks">
                                </div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Bilder</h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-2">Event-Vorschaubild (Quadratisch)</label>
                                    <div class="flex items-start gap-4">
                                        <div v-if="previewImage" class="w-32 h-32 rounded-2xl overflow-hidden border border-surface-200 shadow-sm flex-shrink-0">
                                            <img :src="previewImage" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-grow">
                                            <input type="file" @change="e => handleImageUpload(e, 'image')" accept="image/*" class="w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                                            <p class="text-xs text-surface-500 mt-2">Wenn du hier ein neues Bild auswählst, wird das alte ersetzt.</p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-2">Hero-Hintergrundbild (16:9, optional)</label>
                                    <div class="flex items-start gap-4">
                                        <div v-if="previewHero" class="w-48 h-24 rounded-2xl overflow-hidden border border-surface-200 shadow-sm flex-shrink-0">
                                            <img :src="previewHero" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-grow">
                                            <input type="file" @change="e => handleImageUpload(e, 'hero_background')" accept="image/*" class="w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                                            <p class="text-xs text-surface-500 mt-2">Wenn du hier ein neues Bild auswählst, wird das alte ersetzt.</p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-2">Bildergalerie (mehrere möglich, optional)</label>
                                    <div class="space-y-3">
                                        <input type="file" multiple @change="handleGalleryUpload" accept="image/*" class="w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                                        <p class="text-xs text-surface-500">Wenn du hier Bilder auswählst, wird die bestehende Galerie überschrieben.</p>
                                        <div v-if="previewGallery.length" class="flex flex-wrap gap-2">
                                            <div v-for="(url, i) in previewGallery" :key="i" class="w-20 h-20 rounded-xl overflow-hidden border border-surface-200 shadow-sm">
                                                <img :src="url" class="w-full h-full object-cover">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Einstellungen</h3>
                            <div class="space-y-4">
                                <label class="flex items-center gap-3 p-4 rounded-xl border border-surface-200 hover:bg-surface-50 cursor-pointer transition-colors">
                                    <input type="checkbox" v-model="form.show_remaining_tickets" class="rounded border-surface-300 text-brand-500 shadow-sm focus:ring-brand-500">
                                    <div>
                                        <span class="block font-bold text-surface-900">Restplätze im Frontend anzeigen</span>
                                        <span class="text-sm text-surface-500">Zeigt an, wie viele Tickets pro Kategorie noch verfügbar sind (erzeugt Dringlichkeit/FOMO).</span>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-4 rounded-xl border border-surface-200 hover:bg-surface-50 cursor-pointer transition-colors">
                                    <input type="checkbox" v-model="form.enable_wallet" class="rounded border-surface-300 text-brand-500 shadow-sm focus:ring-brand-500">
                                    <div>
                                        <span class="block font-bold text-surface-900">Cashless Payment (Guthaben-System) aktivieren</span>
                                        <span class="text-sm text-surface-500">Erlaubt Kunden, Guthaben auf ihre Tickets zu laden, um vor Ort damit zu bezahlen (POS).</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Status & Submit -->
                        <div class="pt-6 border-t border-surface-200 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <label class="block text-sm font-medium text-surface-700">Status:</label>
                                <select v-model="form.status" class="rounded-xl border-surface-300 focus:ring-brand-500 focus:border-brand-500 text-sm py-1.5">
                                    <option value="draft">Entwurf</option>
                                    <option value="published">Veröffentlicht</option>
                                </select>
                            </div>
                            
                            <div class="flex gap-3">
                                <Link :href="route('vendor.events.show', event.id)" class="px-6 py-2.5 rounded-full font-medium text-surface-600 hover:bg-surface-100 transition-colors">
                                    Abbrechen
                                </Link>
                                <button type="submit" :disabled="form.processing" class="bg-brand-500 hover:bg-brand-600 text-white px-8 py-2.5 rounded-full font-bold transition-all disabled:opacity-50">
                                    Änderungen speichern
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
