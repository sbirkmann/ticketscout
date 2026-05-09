<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    company_name: '',
    company_address: '',
    business_registration: null,
});

const submit = () => {
    form.post(route('vendor.register'), {
        forceFormData: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Händler Registrierung - Ticketsout24" />

    <div class="min-h-screen bg-surface-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <Link :href="route('home')" class="mb-6 block">
                <div class="font-display font-black text-3xl tracking-tighter text-surface-900">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </div>
            </Link>
            <h2 class="text-center text-3xl font-display font-bold text-surface-900 mb-2">
                Verkaufe deine Tickets bei uns
            </h2>
            <p class="text-center text-sm text-surface-600">
                Registriere dich als Veranstalter. Wir prüfen deine Unterlagen und schalten deinen Zugang frei.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl">
            <div class="bg-white py-8 px-4 shadow-glass sm:rounded-3xl sm:px-10 border border-surface-200">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <h3 class="font-bold text-lg border-b border-surface-100 pb-2 text-surface-900">Persönliche Daten</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-surface-700">Dein Name</label>
                            <input v-model="form.name" type="text" required class="mt-1 w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200">
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-surface-700">E-Mail Adresse</label>
                            <input v-model="form.email" type="email" required class="mt-1 w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200">
                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-surface-700">Passwort</label>
                            <input v-model="form.password" type="password" required class="mt-1 w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200">
                            <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-surface-700">Passwort bestätigen</label>
                            <input v-model="form.password_confirmation" type="password" required class="mt-1 w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200">
                        </div>
                    </div>

                    <h3 class="font-bold text-lg border-b border-surface-100 pb-2 pt-4 text-surface-900">Firmendaten</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-surface-700">Firmenname</label>
                        <input v-model="form.company_name" type="text" required class="mt-1 w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200">
                        <div v-if="form.errors.company_name" class="text-red-500 text-xs mt-1">{{ form.errors.company_name }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-surface-700">Vollständige Anschrift</label>
                        <textarea v-model="form.company_address" rows="3" required class="mt-1 w-full rounded-xl border-surface-300 shadow-sm focus:border-brand-500 focus:ring focus:ring-brand-200"></textarea>
                        <div v-if="form.errors.company_address" class="text-red-500 text-xs mt-1">{{ form.errors.company_address }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-surface-700">Gewerbeanmeldung / HRB Auszug (PDF/Bild)</label>
                        <input @input="form.business_registration = $event.target.files[0]" type="file" required class="mt-1 w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                        <div v-if="form.errors.business_registration" class="text-red-500 text-xs mt-1">{{ form.errors.business_registration }}</div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-glow text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 disabled:opacity-50">
                            Als Veranstalter bewerben
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <Link :href="route('login')" class="text-sm font-medium text-brand-600 hover:text-brand-500">
                        Du hast bereits einen Account? Hier anmelden.
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
