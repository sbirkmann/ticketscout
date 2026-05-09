e <script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object
});

const form = useForm({
    tax_rate: props.settings.tax_rate || 19.00,
    invoice_prefix: props.settings.invoice_prefix || 'RE-',
    invoice_footer_text: props.settings.invoice_footer_text || '',
    disable_invoicing: props.settings.disable_invoicing == 1,
    company_name: props.settings.company_name || '',
    company_address: props.settings.company_address || '',
    tax_number: props.settings.tax_number || '',
    vat_id: props.settings.vat_id || '',
    iban: props.settings.iban || '',
    bic: props.settings.bic || '',
    bank_name: props.settings.bank_name || '',
    email_template: props.settings.email_template || 'Vielen Dank für deine Bestellung! Anbei findest du deine Tickets.',
    sender_name: props.settings.sender_name || '',
    custom_domain: props.settings.custom_domain || '',
});

const submit = () => {
    form.put(route('vendor.settings.update'));
};
</script>

<template>
    <Head title="Einstellungen & Onboarding" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">Einstellungen & Onboarding</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Stripe Setup Status -->
                <div class="bg-white rounded-3xl p-8 border border-surface-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h3 class="text-xl font-bold font-display text-surface-900 mb-2">Zahlungsabwicklung (Stripe)</h3>
                        <p class="text-surface-600">Verbinde dein Stripe-Konto, um Auszahlungen für deine Ticketverkäufe zu erhalten.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a v-if="!settings.stripe_account_id" :href="route('stripe.connect')" class="inline-flex whitespace-nowrap bg-brand-500 hover:bg-brand-600 text-white px-6 py-3 rounded-full font-bold shadow-md transition-all items-center justify-center">
                            Jetzt mit Stripe verbinden
                        </a>
                        <div v-else class="inline-flex items-center gap-2 text-green-600 font-bold bg-green-50 px-4 py-2 rounded-xl whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Stripe verbunden
                        </div>
                    </div>
                </div>

                <!-- Settings Form -->
                <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
                    <form @submit.prevent="submit" class="p-8 space-y-8">
                        
                        <!-- Company Info -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Unternehmensdaten</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Firmenname / Veranstalter *</label>
                                    <input v-model="form.company_name" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Anschrift (Straße, PLZ, Ort) *</label>
                                    <textarea v-model="form.company_address" rows="3" class="w-full rounded-xl border-surface-300 focus:ring-brand-500" required></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-surface-700 mb-1">Steuernummer</label>
                                        <input v-model="form.tax_number" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-surface-700 mb-1">USt-IdNr. (VAT ID)</label>
                                        <input v-model="form.vat_id" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Details -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Bankverbindung (für Rechnungen)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">IBAN</label>
                                    <input v-model="form.iban" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">BIC</label>
                                    <input v-model="form.bic" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Bank Name</label>
                                    <input v-model="form.bank_name" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500">
                                </div>
                            </div>
                        </div>

                        <!-- Taxes & Invoices -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Steuern & Rechnungen</h3>
                            <div class="space-y-4">
                                <div class="flex items-center gap-4">
                                    <label class="block text-sm font-medium text-surface-700">Standard Steuersatz (%) *</label>
                                    <input v-model="form.tax_rate" type="number" step="0.01" class="w-32 rounded-xl border-surface-300 focus:ring-brand-500" required>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2">
                                        <input v-model="form.disable_invoicing" type="checkbox" class="text-brand-500 rounded border-surface-300 focus:ring-brand-500">
                                        <span class="text-sm font-bold text-surface-900">Automatische Rechnungserstellung deaktivieren</span>
                                    </label>
                                </div>

                                <div v-if="!form.disable_invoicing" class="bg-surface-50 p-4 rounded-2xl border border-surface-200 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-surface-700 mb-1">Rechnungs-Präfix</label>
                                        <input v-model="form.invoice_prefix" type="text" class="w-full max-w-xs rounded-xl border-surface-300 focus:ring-brand-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-surface-700 mb-1">Individueller Fußzeilen-Text (auf PDF-Rechnungen)</label>
                                        <textarea v-model="form.invoice_footer_text" rows="3" class="w-full rounded-xl border-surface-300 focus:ring-brand-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket & E-Mail Settings -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">Tickets & E-Mail Versand</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Absendername (E-Mails)</label>
                                    <input v-model="form.sender_name" type="text" class="w-full max-w-sm rounded-xl border-surface-300 focus:ring-brand-500" placeholder="z.B. Dein Event Team">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Standard E-Mail Text (beim Ticket-Versand)</label>
                                    <textarea v-model="form.email_template" rows="5" class="w-full rounded-xl border-surface-300 focus:ring-brand-500"></textarea>
                                    <p class="text-xs text-surface-500 mt-1">Dieser Text wird in der E-Mail angezeigt, wenn ein Kunde Tickets kauft.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Whitelabel & Domains -->
                        <div>
                            <h3 class="text-lg font-bold text-surface-900 border-b border-surface-200 pb-2 mb-4">White-Label & Domain</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-surface-700 mb-1">Eigene Domain (Custom Domain)</label>
                                    <input v-model="form.custom_domain" type="text" class="w-full max-w-sm rounded-xl border-surface-300 focus:ring-brand-500" placeholder="z.B. tickets.meine-band.de">
                                    <p class="text-xs text-surface-500 mt-1">Leite deine eigene Domain (CNAME/A-Record) auf unsere Plattform, um deinen eigenen gebrandeten Ticket-Shop zu betreiben.</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" :disabled="form.processing" class="bg-surface-900 hover:bg-black text-white px-8 py-3 rounded-full font-bold transition-all disabled:opacity-50 shadow-md">
                                Einstellungen speichern
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
