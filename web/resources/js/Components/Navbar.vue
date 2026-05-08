<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const canLogin = computed(() => page.props.canLogin ?? true);
const canRegister = computed(() => page.props.canRegister ?? true);

const dropdownOpen = ref(false);

function logout() {
    router.post(route('logout'));
}

function toggleDropdown() {
    dropdownOpen.value = !dropdownOpen.value;
}

// Close on outside click
function closeDropdown() {
    dropdownOpen.value = false;
}

// First name for display
const firstName = computed(() => user.value?.name?.split(' ')[0] ?? 'Konto');
</script>

<template>
    <nav class="bg-white border-b border-surface-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-8">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <div class="font-display font-black text-2xl tracking-tighter text-surface-900">
                            TICKETSOUT<span class="text-brand-500">24</span>
                        </div>
                    </Link>
                    <div class="hidden lg:flex items-center gap-6 ml-4 font-medium text-surface-600">
                        <Link :href="route('locations.index')" class="hover:text-brand-600 transition-colors">Locations</Link>
                        <Link :href="route('artists.index')" class="hover:text-brand-600 transition-colors">Künstler</Link>
                    </div>
                    
                    <!-- Main Desktop Search -->
                    <div class="hidden md:block relative w-80 ml-4">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" class="block w-full pl-10 pr-3 py-2.5 border border-surface-300 rounded-full leading-5 bg-surface-50 placeholder-surface-500 focus:outline-none focus:bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 sm:text-sm transition-all shadow-inner" placeholder="Suche...">
                    </div>
                </div>
                
                <div v-if="canLogin" class="flex items-center gap-4">
                    <!-- Logged in: Name + Dropdown -->
                    <div v-if="user" class="relative">
                        <button @click="toggleDropdown" @blur.capture="() => setTimeout(closeDropdown, 150)" class="flex items-center gap-2 bg-surface-100 hover:bg-surface-200 px-4 py-2.5 rounded-full transition-colors border border-surface-200 focus:outline-none focus:ring-2 focus:ring-brand-400">
                            <!-- Avatar circle -->
                            <div class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                {{ firstName.charAt(0).toUpperCase() }}
                            </div>
                            <span class="font-semibold text-surface-800 text-sm hidden sm:block">{{ firstName }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-surface-500 transition-transform" :class="dropdownOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="dropdownOpen" class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-lg border border-surface-200 py-2 overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-surface-100">
                                <p class="text-xs text-surface-500 font-medium">Angemeldet als</p>
                                <p class="text-sm font-bold text-surface-900 truncate">{{ user.name }}</p>
                                <p class="text-xs text-surface-500 truncate">{{ user.email }}</p>
                            </div>
                            <Link :href="route('dashboard')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-surface-50 hover:text-brand-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                Mein Dashboard
                            </Link>
                            <Link :href="route('profile.edit')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-surface-50 hover:text-brand-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Profil & Einstellungen
                            </Link>

                            <template v-if="$page.props.auth?.roles?.includes('superadmin')">
                                <div class="px-4 py-2 mt-1 bg-brand-50 border-y border-brand-100">
                                    <p class="text-xs font-bold text-brand-700 uppercase tracking-wider">Superadmin Bereich</p>
                                </div>
                                <Link :href="route('superadmin.dashboard')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    Platform Dashboard
                                </Link>
                                <Link :href="route('superadmin.locations.index')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    Locations & Freigaben
                                </Link>
                                <Link :href="route('superadmin.events.index')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Events bearbeiten
                                </Link>
                                <Link :href="route('superadmin.orders.index')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Bestellungen & Zahlungen
                                </Link>
                                <Link :href="route('superadmin.settings.index')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Einstellungen
                                </Link>
                            </template>
                            
                            <template v-if="$page.props.auth?.roles?.includes('vendor')">
                                <div class="px-4 py-2 mt-1 bg-surface-50 border-y border-surface-100">
                                    <p class="text-xs font-bold text-surface-500 uppercase tracking-wider">Händler Bereich</p>
                                </div>
                                <Link :href="route('vendor.events.index')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-surface-50 hover:text-brand-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Meine Events
                                </Link>
                                <Link :href="route('vendor.orders.index')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-surface-50 hover:text-brand-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    Bestellungen
                                </Link>
                                <Link :href="route('vendor.templates.index')" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-surface-700 hover:bg-surface-50 hover:text-brand-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Ticket-Design
                                </Link>
                            </template>
                            <div class="border-t border-surface-100 mt-1 pt-1">
                                <button @click="logout" class="flex items-center gap-3 w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Abmelden
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Not logged in -->
                    <template v-else>
                        <Link :href="route('login')" class="text-surface-700 font-medium hover:text-brand-600 transition-colors">
                            Anmelden
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="bg-brand-500 text-white px-5 py-2.5 rounded-full font-bold hover:bg-brand-600 transition-colors shadow-sm hidden sm:inline-block">
                            Registrieren
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>
