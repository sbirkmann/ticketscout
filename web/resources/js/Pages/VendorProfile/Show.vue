<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    vendor: Object,
    events: Array
});

function formatDate(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<template>
    <Head :title="`${vendor.vendor_settings?.company_name || vendor.name} | Ticketsout24`" />

    <div class="min-h-screen bg-surface-50 font-sans">
        <Navbar />

        <!-- Vendor Hero -->
        <div class="relative h-[40vh] min-h-64 bg-surface-900 overflow-hidden flex items-center justify-center">
            <div class="absolute inset-0 bg-gradient-to-t from-surface-900 via-surface-900/80 to-surface-800"></div>
            <div class="relative z-10 text-center max-w-4xl mx-auto px-4 mt-8">
                <div v-if="vendor.vendor_settings?.invoice_logo_path" class="w-32 h-32 mx-auto rounded-full border-4 border-surface-900 bg-white shadow-xl overflow-hidden mb-6 flex items-center justify-center">
                    <img :src="'/storage/' + vendor.vendor_settings.invoice_logo_path" class="max-w-[80%] max-h-[80%] object-contain" />
                </div>
                <div v-else class="w-32 h-32 mx-auto rounded-full border-4 border-surface-900 bg-brand-500 text-white flex items-center justify-center text-4xl font-bold shadow-xl mb-6">
                    {{ (vendor.vendor_settings?.company_name || vendor.name).charAt(0).toUpperCase() }}
                </div>
                
                <h1 class="font-display font-black text-4xl md:text-5xl text-white drop-shadow-lg leading-tight mb-2">
                    {{ vendor.vendor_settings?.company_name || vendor.name }}
                </h1>
                <p class="text-surface-300 text-lg">Offizieller Ticket-Shop</p>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                
                <!-- Left: Info & Contact -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-surface-200">
                        <h3 class="font-bold text-xl text-surface-900 mb-6">Kontakt & Info</h3>
                        
                        <div class="space-y-4">
                            <div v-if="vendor.vendor_settings?.public_email" class="flex gap-3 text-surface-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-surface-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                <a :href="`mailto:${vendor.vendor_settings.public_email}`" class="hover:text-brand-600 transition-colors break-all">{{ vendor.vendor_settings.public_email }}</a>
                            </div>
                            
                            <div v-if="vendor.vendor_settings?.public_phone" class="flex gap-3 text-surface-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-surface-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                <a :href="`tel:${vendor.vendor_settings.public_phone}`" class="hover:text-brand-600 transition-colors">{{ vendor.vendor_settings.public_phone }}</a>
                            </div>
                            
                            <div v-if="vendor.vendor_settings?.company_address" class="flex gap-3 text-surface-600 pt-4 border-t border-surface-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-surface-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                <div>
                                    <div class="font-medium text-surface-900">{{ vendor.vendor_settings.company_name }}</div>
                                    <div class="whitespace-pre-wrap">{{ vendor.vendor_settings.company_address }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Upcoming Events -->
                <div class="lg:col-span-2">
                    <h2 class="font-display font-bold text-3xl text-surface-900 mb-6">Alle Events von {{ vendor.vendor_settings?.company_name || vendor.name }}</h2>
                    
                    <div v-if="events.length > 0" class="space-y-4">
                        <Link v-for="event in events" :key="event.id" :href="route('event.show', event.slug)" class="group flex flex-col sm:flex-row gap-6 bg-white p-5 rounded-3xl border border-surface-200 hover:border-brand-300 hover:shadow-md transition-all">
                            <div class="flex flex-col items-center justify-center w-20 h-20 bg-surface-50 border border-surface-200 rounded-2xl shrink-0 group-hover:bg-brand-50 group-hover:border-brand-200 transition-colors">
                                <div class="text-xs text-brand-600 font-bold uppercase">{{ new Date(event.start_date).toLocaleDateString('de-DE', { month: 'short' }) }}</div>
                                <div class="text-2xl font-black text-surface-900 leading-none mt-0.5">{{ new Date(event.start_date).getDate() }}</div>
                            </div>
                            
                            <div class="flex-1 py-1">
                                <div class="text-surface-500 font-medium text-xs mb-1">{{ formatDate(event.start_date) }}</div>
                                <h3 class="text-xl font-bold text-surface-900 mb-1 group-hover:text-brand-600 transition-colors">{{ event.title }}</h3>
                                <div class="flex items-center gap-1.5 text-sm text-surface-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-surface-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                                    {{ event.location?.name }}, {{ event.location?.city }}
                                </div>
                            </div>
                            
                            <div class="sm:self-center w-full sm:w-auto">
                                <div class="bg-brand-50 text-brand-600 group-hover:bg-brand-600 group-hover:text-white text-center font-bold px-6 py-3 rounded-xl transition-colors w-full">
                                    Tickets ab {{ new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(event.min_price) }}
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-center py-16 bg-white rounded-3xl border border-surface-200">
                        <p class="text-surface-500 text-lg">Aktuell sind keine kommenden Events geplant.</p>
                    </div>
                </div>

            </div>
        </main>

        <Footer />
    </div>
</template>
