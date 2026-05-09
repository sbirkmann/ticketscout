<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Anmelden - Ticketsout24" />

    <div class="min-h-screen bg-surface-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <Link :href="route('home')" class="mb-6 block">
                <div class="font-display font-black text-3xl tracking-tighter text-surface-900">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </div>
            </Link>
            <h2 class="text-center text-3xl font-display font-bold text-surface-900 mb-2">
                Willkommen zurück
            </h2>
            <p class="text-center text-sm text-surface-600">
                Melde dich an, um deine Tickets und Bestellungen zu verwalten.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-glass sm:rounded-3xl sm:px-10 border border-surface-200">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-surface-700">E-Mail Adresse</label>
                        <div class="mt-1">
                            <input id="email" v-model="form.email" type="email" autocomplete="email" required class="appearance-none block w-full px-3 py-3 border border-surface-300 rounded-xl shadow-sm placeholder-surface-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-all" />
                        </div>
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-surface-700">Passwort</label>
                        <div class="mt-1">
                            <input id="password" v-model="form.password" type="password" autocomplete="current-password" required class="appearance-none block w-full px-3 py-3 border border-surface-300 rounded-xl shadow-sm placeholder-surface-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-all" />
                        </div>
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" v-model="form.remember" type="checkbox" class="h-4 w-4 text-brand-500 focus:ring-brand-500 border-surface-300 rounded" />
                            <label for="remember" class="ml-2 block text-sm text-surface-900">
                                Angemeldet bleiben
                            </label>
                        </div>

                        <div class="text-sm">
                            <Link v-if="route().has('password.request')" :href="route('password.request')" class="font-medium text-brand-600 hover:text-brand-500">
                                Passwort vergessen?
                            </Link>
                        </div>
                    </div>

                    <div>
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-glow text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 disabled:opacity-50 transition-all">
                            Anmelden
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center space-y-2">
                    <p class="text-sm text-surface-600">
                        Noch kein Konto? 
                        <Link :href="route('register')" class="font-medium text-brand-600 hover:text-brand-500">Hier registrieren</Link>
                    </p>
                    <p class="text-sm text-surface-600">
                        Veranstalter? 
                        <Link :href="route('vendor.register')" class="font-medium text-brand-600 hover:text-brand-500">Händler-Account erstellen</Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
