<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    cities: Array
});
</script>

<template>
    <Head title="Orte & Städte | Ticketsout24" />

    <div class="min-h-screen bg-surface-50 font-sans selection:bg-brand-500 selection:text-white">
        <Navbar />

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-display font-black text-surface-900 tracking-tight mb-8">
                Alle Städte entdecken
            </h1>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <Link v-for="city in cities" :key="city.id" :href="route('cities.show', city.slug)" class="group block relative overflow-hidden rounded-3xl aspect-[4/3] bg-surface-200">
                    <img v-if="city.image_path" :src="'/storage/' + city.image_path" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    <div v-else class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-brand-500 to-brand-700">
                        <span class="text-white opacity-50 font-bold text-xl">{{ city.name }}</span>
                    </div>
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    
                    <div class="absolute bottom-0 left-0 w-full p-5">
                        <h3 class="text-white font-bold text-2xl group-hover:text-brand-300 transition-colors">{{ city.name }}</h3>
                    </div>
                </Link>
            </div>
            
            <div v-if="!cities.length" class="text-center py-20">
                <p class="text-surface-500 text-lg">Keine Städte gefunden.</p>
            </div>
        </main>
        
        <Footer />
    </div>
</template>
