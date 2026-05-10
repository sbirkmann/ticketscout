<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    login_code: '',
    pin: ''
});

function submit() {
    form.post(route('pos.login.post'));
}
</script>

<template>
    <Head title="POS Login" />

    <div class="min-h-screen bg-surface-900 flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-surface-800 p-8 rounded-3xl shadow-2xl border border-surface-700">
            <div class="text-center mb-8">
                <div class="font-display font-black text-3xl tracking-tighter text-white mb-1">
                    TICKETSOUT<span class="text-brand-500">24</span>
                </div>
                <div class="text-surface-400 font-bold uppercase tracking-widest text-sm">
                    Point of Sale
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-surface-300 mb-2">Terminal Login-Code</label>
                    <input v-model="form.login_code" type="text" class="w-full bg-surface-900 border-surface-700 text-white rounded-xl focus:border-brand-500 focus:ring-brand-500 uppercase text-center tracking-widest text-xl font-mono py-3" placeholder="XXXXXX" required>
                    <div v-if="form.errors.login_code" class="text-red-400 text-xs mt-2 text-center">{{ form.errors.login_code }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-surface-300 mb-2">PIN</label>
                    <input v-model="form.pin" type="password" class="w-full bg-surface-900 border-surface-700 text-white rounded-xl focus:border-brand-500 focus:ring-brand-500 text-center tracking-widest text-3xl font-mono py-3" placeholder="****" required>
                    <div v-if="form.errors.pin" class="text-red-400 text-xs mt-2 text-center">{{ form.errors.pin }}</div>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-4 rounded-xl transition-colors disabled:opacity-50 text-lg shadow-lg">
                    Anmelden
                </button>
            </form>
        </div>
    </div>
</template>
