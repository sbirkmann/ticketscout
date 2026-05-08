<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
</script>

<template>
    <div>
        <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
            <nav class="bg-white border-b border-surface-200">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="flex items-center gap-2">
                                    <div class="font-display font-black text-2xl tracking-tighter text-surface-900">
                                        TICKETSOUT<span class="text-brand-500">24</span>
                                    </div>
                                    <span class="bg-surface-900 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md ml-1">Veranstalter</span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('vendor.dashboard')" :active="route().current('vendor.dashboard')">
                                    Dashboard
                                </NavLink>
                                <NavLink :href="route('vendor.events.index')" :active="route().current('vendor.events.*')">
                                    Events
                                </NavLink>
                                <NavLink v-if="$page.props.auth?.roles?.includes('vendor')" :href="route('vendor.orders.index')" :active="route().current('vendor.orders.*')">
                                    Bestellungen
                                </NavLink>
                                <NavLink v-if="$page.props.auth?.roles?.includes('vendor')" :href="route('vendor.invoices.index')" :active="route().current('vendor.invoices.index')">
                                    Rechnungen
                                </NavLink>
                                <NavLink v-if="$page.props.auth?.roles?.includes('vendor')" :href="route('vendor.templates.index')" :active="route().current('vendor.templates.*')">
                                    Ticket-Design
                                </NavLink>
                                <NavLink v-if="$page.props.auth?.roles?.includes('vendor')" :href="route('vendor.settings.index')" :active="route().current('vendor.settings.*')">
                                    Einstellungen
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-bold leading-4 text-surface-600 transition duration-150 ease-in-out hover:text-surface-900 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profil bearbeiten
                                        </DropdownLink>
                                        <DropdownLink :href="route('home')">
                                            Zur Startseite
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Abmelden
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-surface-400 transition duration-150 ease-in-out hover:bg-surface-100 hover:text-surface-500 focus:bg-surface-100 focus:text-surface-500 focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('vendor.dashboard')" :active="route().current('vendor.dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('vendor.events.index')" :active="route().current('vendor.events.*')">
                            Events
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.auth?.roles?.includes('vendor')" :href="route('vendor.orders.index')" :active="route().current('vendor.orders.*')">
                            Bestellungen
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.auth?.roles?.includes('vendor')" :href="route('vendor.invoices.index')" :active="route().current('vendor.invoices.index')">
                            Rechnungen
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.auth?.roles?.includes('vendor')" :href="route('vendor.templates.index')" :active="route().current('vendor.templates.*')">
                            Ticket-Design
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.auth?.roles?.includes('vendor')" :href="route('vendor.settings.index')" :active="route().current('vendor.settings.*')">
                            Einstellungen
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-surface-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-surface-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-surface-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Abmelden
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow-sm border-b border-surface-200" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
