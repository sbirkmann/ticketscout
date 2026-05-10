<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    event: Object
});

const activeTab = ref('tickets'); // 'tickets', 'addons', 'waitlist'

// --- Ticket Form ---
const ticketForm = useForm({
    name: '',
    price: '',
    quantity: '',
    is_default: false,
    requires_attendee_name: false,
    use_dynamic_pricing: false,
    dynamic_pricing_threshold_percent: '',
    dynamic_pricing_increase_amount: '',
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
    ticketForm.quantity = cat.capacity || cat.quantity; // it's mapped to capacity in db
    ticketForm.is_default = cat.is_default;
    ticketForm.requires_attendee_name = cat.requires_attendee_name;
    ticketForm.use_dynamic_pricing = cat.use_dynamic_pricing;
    ticketForm.dynamic_pricing_threshold_percent = cat.dynamic_pricing_threshold_percent;
    ticketForm.dynamic_pricing_increase_amount = cat.dynamic_pricing_increase_amount;
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
    ticket_categories: [], // Array of ticket category IDs
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
    addonForm.ticket_categories = addon.ticket_categories ? addon.ticket_categories.map(c => c.id) : [];
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
                    <Link v-if="event.enable_wallet && $page.props.auth.user.vendor_settings?.has_advanced_pos" :href="route('vendor.events.pos.show', event.id)" class="bg-surface-800 hover:bg-surface-900 text-white px-4 py-2 rounded-xl font-medium transition-colors text-sm shadow-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Kasse (POS) Setup
                    </Link>
                    <Link :href="route('vendor.events.duplicate', event.id)" method="post" as="button" class="bg-surface-100 hover:bg-surface-200 text-surface-700 px-4 py-2 rounded-xl font-medium transition-colors text-sm">
                        Duplizieren
                    </Link>
                    <Link :href="route('vendor.events.batch.create', event.id)" class="bg-brand-100 hover:bg-brand-200 text-brand-700 px-4 py-2 rounded-xl font-medium transition-colors text-sm">
                        Serientermine erstellen
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
                        <button @click="activeTab = 'waitlist'" :class="activeTab === 'waitlist' ? 'border-b-2 border-brand-500 text-brand-600 font-bold' : 'text-surface-500 hover:text-surface-700 font-medium'" class="flex-1 py-4 text-center transition-colors">
                            Warteliste <span v-if="event.waitlists?.length" class="ml-1 bg-surface-200 text-surface-700 px-2 py-0.5 rounded-full text-xs">{{ event.waitlists.length }}</span>
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
                                                    <span v-if="cat.use_dynamic_pricing" class="bg-purple-100 text-purple-600 text-xs px-2 py-0.5 rounded-full font-bold" title="Preis steigt bei Knappheit">Dynamisch</span>
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
                                                <div class="border-t border-surface-200 mt-4 pt-4">
                                                    <label class="flex items-center gap-2">
                                                        <input v-model="ticketForm.use_dynamic_pricing" type="checkbox" class="text-purple-500 focus:ring-purple-500 rounded border-surface-300">
                                                        <span class="text-sm text-purple-700 font-bold">Dynamisches Pricing aktivieren</span>
                                                    </label>
                                                    <div v-if="ticketForm.use_dynamic_pricing" class="mt-3 p-3 bg-purple-50 rounded-xl space-y-3">
                                                        <div>
                                                            <label class="block text-xs font-medium text-surface-700 mb-1">Wenn nur noch (%) Tickets übrig sind:</label>
                                                            <input v-model="ticketForm.dynamic_pricing_threshold_percent" type="number" min="1" max="99" class="w-full rounded-lg border-purple-200 text-sm focus:ring-purple-500 focus:border-purple-500 p-1.5" placeholder="z.B. 20">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-surface-700 mb-1">Steigt der Preis um (€):</label>
                                                            <input v-model="ticketForm.dynamic_pricing_increase_amount" type="number" step="0.01" min="0" class="w-full rounded-lg border-purple-200 text-sm focus:ring-purple-500 focus:border-purple-500 p-1.5" placeholder="z.B. 5.00">
                                                        </div>
                                                    </div>
                                                </div>
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
                                            <div v-if="event.ticket_categories && event.ticket_categories.length" class="space-y-2 pt-2 border-t border-surface-200">
                                                <label class="block text-sm font-medium text-surface-700 mb-2">An Ticket-Kategorie binden (optional)</label>
                                                <p class="text-xs text-surface-500 mb-2">Wenn keine Kategorie gewählt ist, kann jeder das Add-on kaufen. Andernfalls nur bei Wahl der markierten Kategorien.</p>
                                                <div v-for="cat in event.ticket_categories" :key="cat.id" class="flex items-center gap-2">
                                                    <input type="checkbox" :id="'addon-cat-' + cat.id" :value="cat.id" v-model="addonForm.ticket_categories" class="rounded border-surface-300 text-brand-500 focus:ring-brand-500">
                                                    <label :for="'addon-cat-' + cat.id" class="text-sm text-surface-700 cursor-pointer">{{ cat.name }}</label>
                                                </div>
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

                        <!-- WAITLIST TAB -->
                        <div v-if="activeTab === 'waitlist'">
                            <div class="max-w-4xl mx-auto">
                                <h3 class="text-lg font-bold text-surface-900 mb-4">Warteliste</h3>
                                <p class="text-surface-600 text-sm mb-6">Hier siehst du alle Personen, die sich für die Warteliste eingetragen haben, weil das Event (oder eine Kategorie) ausverkauft war.</p>

                                <div v-if="event.waitlists && event.waitlists.length" class="bg-white border border-surface-200 rounded-2xl overflow-hidden shadow-sm">
                                    <table class="w-full text-left text-sm whitespace-nowrap">
                                        <thead class="bg-surface-50 text-surface-500 border-b border-surface-200">
                                            <tr>
                                                <th class="px-6 py-4 font-bold">Datum</th>
                                                <th class="px-6 py-4 font-bold">Name</th>
                                                <th class="px-6 py-4 font-bold">E-Mail</th>
                                                <th class="px-6 py-4 font-bold">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-surface-100">
                                            <tr v-for="entry in event.waitlists" :key="entry.id" class="hover:bg-surface-50 transition-colors">
                                                <td class="px-6 py-4 text-surface-500">{{ new Date(entry.created_at).toLocaleString('de-DE') }}</td>
                                                <td class="px-6 py-4 font-bold text-surface-900">{{ entry.name }}</td>
                                                <td class="px-6 py-4 text-surface-600"><a :href="'mailto:' + entry.email" class="text-brand-600 hover:underline">{{ entry.email }}</a></td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium"
                                                            :class="{
                                                                'bg-yellow-100 text-yellow-800': entry.status === 'pending',
                                                                'bg-blue-100 text-blue-800': entry.status === 'notified',
                                                                'bg-green-100 text-green-800': entry.status === 'purchased'
                                                            }">
                                                            {{ entry.status === 'pending' ? 'Wartend' : (entry.status === 'notified' ? 'Benachrichtigt' : 'Gekauft') }}
                                                        </span>
                                                        <button v-if="entry.status === 'pending'" @click="router.post(route('vendor.events.waitlist.notify', [event.id, entry.id]))" class="text-brand-600 hover:text-brand-700 text-xs font-bold transition-colors">
                                                            Benachrichtigen
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-else class="text-surface-500 italic bg-surface-50 p-6 rounded-2xl text-center border border-surface-100">Die Warteliste ist leer.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
