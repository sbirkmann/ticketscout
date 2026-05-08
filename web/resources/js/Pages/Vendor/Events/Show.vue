<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    event: Object
});

const activeTab = ref('tickets'); // 'tickets', 'addons'

// --- Ticket Form ---
const ticketForm = useForm({
    name: '',
    price: '',
    quantity: '',
    is_default: false,
    requires_attendee_name: false,
});
const editingTicket = ref(null);

function saveTicket() {
    if (editingTicket.value) {
        ticketForm.put(route('vendor.events.ticket-categories.update', [props.event.id, editingTicket.value.id]), {
            onSuccess: () => { resetTicketForm(); }
        });
    } else {
        ticketForm.post(route('vendor.events.ticket-categories.store', props.event.id), {
            onSuccess: () => { resetTicketForm(); }
        });
    }
}

function editTicket(cat) {
    editingTicket.value = cat;
    ticketForm.name = cat.name;
    ticketForm.price = cat.price;
    ticketForm.quantity = cat.quantity;
    ticketForm.is_default = cat.is_default;
    ticketForm.requires_attendee_name = cat.requires_attendee_name;
}

function deleteTicket(id) {
    if (confirm('Wirklich löschen?')) {
        router.delete(route('vendor.events.ticket-categories.destroy', [props.event.id, id]));
    }
}

function toggleTicketActive(id) {
    router.patch(route('vendor.events.ticket-categories.toggle-active', [props.event.id, id]));
}

function resetTicketForm() {
    editingTicket.value = null;
    ticketForm.reset();
}

// --- Addon Form ---
const addonForm = useForm({
    name: '',
    description: '',
    price: '',
    quantity: '',
});
const editingAddon = ref(null);

function saveAddon() {
    if (editingAddon.value) {
        addonForm.put(route('vendor.events.addons.update', [props.event.id, editingAddon.value.id]), {
            onSuccess: () => { resetAddonForm(); }
        });
    } else {
        addonForm.post(route('vendor.events.addons.store', props.event.id), {
            onSuccess: () => { resetAddonForm(); }
        });
    }
}

function editAddon(addon) {
    editingAddon.value = addon;
    addonForm.name = addon.name;
    addonForm.description = addon.description;
    addonForm.price = addon.price;
    addonForm.quantity = addon.quantity;
}

function deleteAddon(id) {
    if (confirm('Wirklich löschen?')) {
        router.delete(route('vendor.events.addons.destroy', [props.event.id, id]));
    }
}

function resetAddonForm() {
    editingAddon.value = null;
    addonForm.reset();
}
</script>

<template>
    <Head :title="'Verwaltung: ' + event.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('vendor.events.index')" class="text-surface-400 hover:text-surface-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">{{ event.title }}</h2>
                        <p class="text-sm text-surface-500">{{ new Date(event.start_date).toLocaleString() }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('vendor.events.duplicate', event.id)" method="post" as="button" class="bg-surface-100 hover:bg-surface-200 text-surface-700 px-5 py-2 rounded-xl font-medium transition-colors text-sm">
                        Als neuen Termin duplizieren (Serie)
                    </Link>
                    <Link :href="route('vendor.events.edit', event.id)" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2 rounded-xl font-medium transition-colors text-sm shadow-sm">
                        Event bearbeiten
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-2xl border border-surface-200 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-sm text-surface-500 font-medium">Status</p>
                            <p class="text-lg font-bold" :class="event.status === 'published' ? 'text-green-600' : 'text-surface-600'">
                                {{ event.status === 'published' ? 'Aktiv (Online)' : 'Entwurf' }}
                            </p>
                        </div>
                        <Link :href="route('event.show', event.slug)" target="_blank" class="text-brand-600 bg-brand-50 p-2 rounded-lg hover:bg-brand-100" title="Im Shop ansehen">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" /><path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" /></svg>
                        </Link>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-surface-200 shadow-sm">
                        <p class="text-sm text-surface-500 font-medium">Ticket-Kategorien</p>
                        <p class="text-2xl font-bold text-surface-900">{{ event.ticket_categories ? event.ticket_categories.length : 0 }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-surface-200 shadow-sm">
                        <p class="text-sm text-surface-500 font-medium">Add-ons (Extras)</p>
                        <p class="text-2xl font-bold text-surface-900">{{ event.addons ? event.addons.length : 0 }}</p>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
                    <div class="flex border-b border-surface-200">
                        <button @click="activeTab = 'tickets'" :class="activeTab === 'tickets' ? 'border-b-2 border-brand-500 text-brand-600 font-bold' : 'text-surface-500 hover:text-surface-700 font-medium'" class="flex-1 py-4 text-center transition-colors">
                            Ticket-Kategorien
                        </button>
                        <button @click="activeTab = 'addons'" :class="activeTab === 'addons' ? 'border-b-2 border-brand-500 text-brand-600 font-bold' : 'text-surface-500 hover:text-surface-700 font-medium'" class="flex-1 py-4 text-center transition-colors">
                            Upgrades & Add-ons
                        </button>
                    </div>

                    <div class="p-8">
                        <!-- TICKETS TAB -->
                        <div v-if="activeTab === 'tickets'">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                                <div class="lg:col-span-2">
                                    <h3 class="text-lg font-bold text-surface-900 mb-4">Aktuelle Kategorien</h3>
                                    <div v-if="event.ticket_categories && event.ticket_categories.length" class="space-y-4">
                                        <div v-for="cat in event.ticket_categories" :key="cat.id" class="p-4 border border-surface-200 rounded-2xl flex items-center justify-between">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-bold" :class="cat.is_active ? 'text-surface-900' : 'text-surface-400 line-through'">{{ cat.name }}</h4>
                                                    <span v-if="!cat.is_active" class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full font-bold">Gesperrt</span>
                                                    <span v-if="cat.is_default" class="bg-brand-100 text-brand-600 text-xs px-2 py-0.5 rounded-full font-bold">Standard</span>
                                                    <span v-if="cat.requires_attendee_name" class="bg-blue-100 text-blue-600 text-xs px-2 py-0.5 rounded-full font-bold">Personalisiert</span>
                                                </div>
                                                <div class="text-sm text-surface-500 mt-1">
                                                    {{ parseFloat(cat.price).toFixed(2).replace('.', ',') }} € • {{ cat.quantity || 'Unbegrenzt' }} verfügbar
                                                </div>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <button @click="toggleTicketActive(cat.id)" class="text-xs font-bold px-3 py-1.5 rounded-lg transition-colors" :class="cat.is_active ? 'bg-orange-100 text-orange-600 hover:bg-orange-200' : 'bg-green-100 text-green-600 hover:bg-green-200'">
                                                    {{ cat.is_active ? 'Sperren' : 'Freigeben' }}
                                                </button>
                                                <button @click="editTicket(cat)" class="text-surface-400 hover:text-brand-600 p-2">✎</button>
                                                <button @click="deleteTicket(cat.id)" class="text-surface-400 hover:text-red-600 p-2">✕</button>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="text-surface-500 italic bg-surface-50 p-6 rounded-2xl text-center border border-surface-100">Noch keine Tickets angelegt.</p>
                                </div>
                                
                                <div class="lg:col-span-1">
                                    <div class="bg-surface-50 p-6 rounded-2xl border border-surface-200 sticky top-6">
                                        <h3 class="text-lg font-bold text-surface-900 mb-4">{{ editingTicket ? 'Kategorie bearbeiten' : 'Neue Kategorie' }}</h3>
                                        <form @submit.prevent="saveTicket" class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-surface-700 mb-1">Name</label>
                                                <input v-model="ticketForm.name" type="text" class="w-full rounded-xl border-surface-300 text-sm focus:ring-brand-500" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-surface-700 mb-1">Preis (€)</label>
                                                <input v-model="ticketForm.price" type="number" step="0.01" class="w-full rounded-xl border-surface-300 text-sm focus:ring-brand-500" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-surface-700 mb-1">Anzahl (optional)</label>
                                                <input v-model="ticketForm.quantity" type="number" class="w-full rounded-xl border-surface-300 text-sm focus:ring-brand-500">
                                            </div>
                                            <div class="space-y-2 pt-2">
                                                <label class="flex items-center gap-2">
                                                    <input v-model="ticketForm.is_default" type="checkbox" class="text-brand-500 focus:ring-brand-500 rounded border-surface-300">
                                                    <span class="text-sm text-surface-700">Ist Standard-Kategorie</span>
                                                </label>
                                                <label class="flex items-center gap-2">
                                                    <input v-model="ticketForm.requires_attendee_name" type="checkbox" class="text-brand-500 focus:ring-brand-500 rounded border-surface-300">
                                                    <span class="text-sm text-surface-700 font-bold text-brand-700">Personalisierte Tickets (Gästenamen)</span>
                                                </label>
                                            </div>
                                            <div class="pt-4 flex gap-2">
                                                <button v-if="editingTicket" type="button" @click="resetTicketForm" class="flex-1 bg-surface-200 hover:bg-surface-300 text-surface-800 py-2 rounded-xl text-sm font-bold transition-colors">Abbrechen</button>
                                                <button type="submit" :disabled="ticketForm.processing" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white py-2 rounded-xl text-sm font-bold transition-colors shadow-sm">Speichern</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ADDONS TAB -->
                        <div v-if="activeTab === 'addons'">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                                <div class="lg:col-span-2">
                                    <h3 class="text-lg font-bold text-surface-900 mb-4">Aktuelle Add-ons (Upsells)</h3>
                                    <div v-if="event.addons && event.addons.length" class="space-y-4">
                                        <div v-for="addon in event.addons" :key="addon.id" class="p-4 border border-surface-200 rounded-2xl flex items-center justify-between">
                                            <div>
                                                <h4 class="font-bold text-surface-900">{{ addon.name }}</h4>
                                                <div class="text-sm text-surface-500 mt-1">
                                                    + {{ parseFloat(addon.price).toFixed(2).replace('.', ',') }} € • {{ addon.quantity || 'Unbegrenzt' }} verfügbar
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <button @click="editAddon(addon)" class="text-surface-400 hover:text-brand-600 p-2">✎</button>
                                                <button @click="deleteAddon(addon.id)" class="text-surface-400 hover:text-red-600 p-2">✕</button>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="text-surface-500 italic bg-surface-50 p-6 rounded-2xl text-center border border-surface-100">Noch keine Add-ons (Upsells) angelegt.</p>
                                </div>
                                
                                <div class="lg:col-span-1">
                                    <div class="bg-surface-50 p-6 rounded-2xl border border-surface-200 sticky top-6">
                                        <h3 class="text-lg font-bold text-surface-900 mb-4">{{ editingAddon ? 'Add-on bearbeiten' : 'Neues Add-on' }}</h3>
                                        <form @submit.prevent="saveAddon" class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-surface-700 mb-1">Name (z.B. VIP Upgrade)</label>
                                                <input v-model="addonForm.name" type="text" class="w-full rounded-xl border-surface-300 text-sm focus:ring-brand-500" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-surface-700 mb-1">Beschreibung</label>
                                                <textarea v-model="addonForm.description" rows="2" class="w-full rounded-xl border-surface-300 text-sm focus:ring-brand-500"></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-surface-700 mb-1">Aufpreis (€)</label>
                                                <input v-model="addonForm.price" type="number" step="0.01" class="w-full rounded-xl border-surface-300 text-sm focus:ring-brand-500" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-surface-700 mb-1">Anzahl (optional)</label>
                                                <input v-model="addonForm.quantity" type="number" class="w-full rounded-xl border-surface-300 text-sm focus:ring-brand-500">
                                            </div>
                                            <div class="pt-4 flex gap-2">
                                                <button v-if="editingAddon" type="button" @click="resetAddonForm" class="flex-1 bg-surface-200 hover:bg-surface-300 text-surface-800 py-2 rounded-xl text-sm font-bold transition-colors">Abbrechen</button>
                                                <button type="submit" :disabled="addonForm.processing" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white py-2 rounded-xl text-sm font-bold transition-colors shadow-sm">Speichern</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
