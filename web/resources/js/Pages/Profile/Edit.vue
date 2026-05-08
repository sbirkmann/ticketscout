<script setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});
</script>

<template>
    <Head title="Mein Profil – Ticketsout24" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            <div class="mb-10">
                <h1 class="font-display text-4xl font-black text-surface-900 tracking-tight">Mein Profil</h1>
                <p class="text-surface-500 mt-2">Verwalte deine persönlichen Daten, Passwort und Kontoeinstellungen.</p>
            </div>

            <div class="space-y-8">

                <!-- Profile Info -->
                <div class="bg-white rounded-3xl shadow-sm border border-surface-200 p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Password -->
                <div class="bg-white rounded-3xl shadow-sm border border-surface-200 p-8">
                    <UpdatePasswordForm />
                </div>

                <!-- Delete Account -->
                <div class="bg-white rounded-3xl shadow-sm border border-red-100 p-8">
                    <DeleteUserForm />
                </div>

            </div>
        </main>

        <Footer />
    </div>
</template>
