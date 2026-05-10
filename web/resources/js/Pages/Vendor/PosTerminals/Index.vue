<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    terminals: Array
});

const form = useForm({
    name: '',
    pin: '',
    terminal_type: 'web',
    printer_type: '',
    printer_ip: ''
});

function createTerminal() {
    form.post(route('vendor.pos-terminals.store'), {
        onSuccess: () => form.reset(),
    });
}

function deleteTerminal(id) {
    if (confirm('Bist du sicher, dass du diesen POS-Zugang löschen möchtest?')) {
        router.delete(route('vendor.pos-terminals.destroy', id));
    }
}
</script>

<template>
    <Head title="POS-Kassen (Cashless)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">POS-Kassen (Cashless)</h2>
                    <p class="text-surface-500 text-sm mt-1">Verwalte die Zugänge für dein Vor-Ort Personal zum Abbuchen von Guthaben.</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <div v-if="$page.props.flash.success" class="bg-green-50 text-green-700 p-4 rounded-xl font-medium border border-green-200">
                    {{ $page.props.flash.success }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-3xl border border-surface-200 shadow-sm sticky top-8">
                            <h3 class="font-bold text-surface-900 text-lg mb-4">Neue Kasse anlegen</h3>
                            <form @submit.prevent="createTerminal" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Name / Bezeichnung (z.B. Bar 1)</label>
                                    <input v-model="form.name" type="text" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500" required>
                                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">PIN (4-10 Zeichen)</label>
                                    <input v-model="form.pin" type="text" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500" required minlength="4" maxlength="10">
                                    <div v-if="form.errors.pin" class="text-red-500 text-xs mt-1">{{ form.errors.pin }}</div>
                                    <p class="text-xs text-surface-500 mt-1">Die PIN benötigt dein Personal zur Anmeldung am Endgerät.</p>
                                </div>
                                <div class="pt-4 border-t border-surface-200">
                                    <h4 class="font-bold text-sm text-surface-900 mb-3">Hardware & Integration</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-surface-700 mb-1">Zahlungs-Terminal Typ</label>
                                            <select v-model="form.terminal_type" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
                                                <option value="web">Nur Web (Bar / Ticket-Guthaben)</option>
                                                <option value="stripe_terminal">Stripe Terminal (Kreditkarte/NFC)</option>
                                                <option value="zvt">Externes EC-Gerät (ZVT)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-surface-700 mb-1">Drucker Typ (Optional)</label>
                                            <select v-model="form.printer_type" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
                                                <option value="">Kein Drucker</option>
                                                <option value="browser">System-Druckdialog (Browser)</option>
                                                <option value="network">Netzwerk ESC/POS Drucker</option>
                                            </select>
                                        </div>
                                        <div v-if="form.printer_type === 'network'">
                                            <label class="block text-sm font-medium text-surface-700 mb-1">Drucker IP-Adresse</label>
                                            <input v-model="form.printer_ip" type="text" placeholder="192.168.178.50" class="w-full rounded-xl border-surface-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" :disabled="form.processing" class="w-full bg-brand-500 text-white font-bold py-2.5 rounded-xl hover:bg-brand-600 transition-colors disabled:opacity-50">
                                    Erstellen
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- List -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-3xl p-6 text-blue-800">
                            <h4 class="font-bold flex items-center gap-2 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                Info zur Nutzung
                            </h4>
                            <p class="text-sm">
                                Dein Personal muss auf dem Smartphone oder Tablet die Seite <strong>{{ $page.props.app_url }}/pos</strong> öffnen.
                                Dort geben sie den <strong>Login-Code</strong> und die <strong>PIN</strong> ein.
                            </p>
                        </div>

                        <div v-if="terminals.length === 0" class="text-center p-12 bg-white rounded-3xl border border-surface-200">
                            <p class="text-surface-500">Noch keine POS-Kassen angelegt.</p>
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="terminal in terminals" :key="terminal.id" class="bg-white p-5 rounded-2xl border border-surface-200 shadow-sm flex flex-col relative group">
                                <button @click="deleteTerminal(terminal.id)" class="absolute top-3 right-3 text-surface-400 hover:text-red-500 transition-colors" title="Löschen">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                      <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <h4 class="font-bold text-surface-900 text-lg pr-8">{{ terminal.name }}</h4>
                                
                                <div class="mt-2 text-xs text-surface-500 flex gap-2 flex-wrap">
                                    <span class="bg-surface-100 px-2 py-1 rounded">Typ: {{ terminal.terminal_type === 'stripe_terminal' ? 'Stripe NFC' : (terminal.terminal_type === 'zvt' ? 'ZVT' : 'Web') }}</span>
                                    <span v-if="terminal.printer_type" class="bg-surface-100 px-2 py-1 rounded">Drucker: {{ terminal.printer_type }}</span>
                                </div>

                                <div class="mt-4 bg-surface-100 p-3 rounded-xl flex justify-between items-center">
                                    <span class="text-sm text-surface-500">Login-Code:</span>
                                    <span class="font-mono font-bold text-xl tracking-wider text-brand-600">{{ terminal.login_code }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
