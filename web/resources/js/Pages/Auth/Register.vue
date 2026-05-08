<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Registrieren - Ticketsout24" />

    <div class="min-h-screen bg-surface-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <Link :href="route('home')" class="flex items-center justify-center gap-2 mb-6">
                <img src="/images/logo.png" alt="Ticketsout24 Logo" class="h-12 w-auto" />
                <div class="font-display font-black text-3xl tracking-tighter text-surface-900">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </div>
            </Link>
            <h2 class="text-center text-3xl font-display font-bold text-surface-900 mb-2">
                Konto erstellen
            </h2>
            <p class="text-center text-sm text-surface-600">
                Werde Teil der Community und sichere dir deine Tickets.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-glass sm:rounded-3xl sm:px-10 border border-surface-200">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-surface-700">Dein Name</label>
                        <div class="mt-1">
                            <input id="name" v-model="form.name" type="text" autocomplete="name" required class="appearance-none block w-full px-3 py-3 border border-surface-300 rounded-xl shadow-sm placeholder-surface-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-all" />
                        </div>
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>

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
                            <input id="password" v-model="form.password" type="password" autocomplete="new-password" required class="appearance-none block w-full px-3 py-3 border border-surface-300 rounded-xl shadow-sm placeholder-surface-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-all" />
                        </div>
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-surface-700">Passwort bestätigen</label>
                        <div class="mt-1">
                            <input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" required class="appearance-none block w-full px-3 py-3 border border-surface-300 rounded-xl shadow-sm placeholder-surface-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-all" />
                        </div>
                    </div>

                    <div>
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-glow text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 disabled:opacity-50 transition-all">
                            Jetzt registrieren
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center space-y-2">
                    <p class="text-sm text-surface-600">
                        Du hast bereits ein Konto? 
                        <Link :href="route('login')" class="font-medium text-brand-600 hover:text-brand-500">Hier anmelden</Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
