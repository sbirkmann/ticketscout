<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { Html5Qrcode } from 'html5-qrcode';

const isScanning = ref(false);
const scanResult = ref(null);
const scanMessage = ref('');
const isSuccess = ref(false);
const html5QrCode = ref(null);

const startScanner = async () => {
    isScanning.value = true;
    scanResult.value = null;
    scanMessage.value = '';

    html5QrCode.value = new Html5Qrcode("reader");
    
    try {
        await html5QrCode.value.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            async (decodedText, decodedResult) => {
                // Stop scanning while processing
                await stopScanner();
                
                try {
                    // Send hash to backend
                    const response = await axios.get(route('scanner.validate', decodedText));
                    scanResult.value = response.data.ticket;
                    scanMessage.value = response.data.message;
                    isSuccess.value = response.data.valid;
                    
                    if (isSuccess.value) {
                        // Play success sound
                        playBeep(800, 200, 'sine');
                    } else {
                        // Play error sound
                        playBeep(200, 500, 'sawtooth');
                    }
                } catch (error) {
                    isSuccess.value = false;
                    scanMessage.value = error.response?.data?.message || 'Unbekannter Fehler beim Scannen.';
                    playBeep(200, 500, 'sawtooth');
                }
            },
            (errorMessage) => {
                // Parse errors can be ignored
            }
        );
    } catch (err) {
        console.error("Camera access failed", err);
        isScanning.value = false;
        scanMessage.value = 'Kamera konnte nicht gestartet werden. Bitte Berechtigungen prüfen.';
    }
};

const stopScanner = async () => {
    if (html5QrCode.value && html5QrCode.value.isScanning) {
        await html5QrCode.value.stop();
        html5QrCode.value.clear();
        isScanning.value = false;
    }
};

const playBeep = (frequency, duration, type) => {
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioCtx.createOscillator();
    const gainNode = audioCtx.createGain();

    oscillator.type = type;
    oscillator.frequency.value = frequency;
    oscillator.connect(gainNode);
    gainNode.connect(audioCtx.destination);
    
    oscillator.start();
    setTimeout(() => {
        oscillator.stop();
        audioCtx.close();
    }, duration);
};

onUnmounted(() => {
    stopScanner();
});
</script>

<template>
    <Head title="Ticket Scanner" />

    <div class="min-h-screen bg-gray-900 text-white font-sans selection:bg-brand-500 selection:text-white">
        <!-- Minimal Navbar -->
        <nav class="bg-gray-800 border-b border-gray-700 sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                        <div class="font-display font-black text-xl tracking-tight text-white">
                            SCANNER
                        </div>
                    </div>
                    <div>
                        <a :href="route('dashboard')" class="text-sm text-gray-400 hover:text-white transition-colors">Beenden</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="p-4 flex flex-col items-center">
            
            <!-- Result Alert -->
            <div v-if="scanMessage" class="w-full max-w-md mb-6 rounded-2xl p-6 text-center shadow-lg transform transition-all duration-300"
                :class="isSuccess ? 'bg-green-600' : 'bg-red-600'">
                <div class="flex justify-center mb-2">
                    <svg v-if="isSuccess" xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">{{ isSuccess ? 'GÜLTIG' : 'UNGÜLTIG' }}</h3>
                <p class="text-white/90 text-sm md:text-base">{{ scanMessage }}</p>

                <!-- Ticket Details if valid -->
                <div v-if="isSuccess && scanResult" class="mt-4 bg-white/20 p-4 rounded-xl text-left">
                    <div class="font-bold text-lg mb-1">{{ scanResult.order?.event?.title }}</div>
                    <div class="text-sm opacity-90">Typ: {{ scanResult.category_name || 'Standard' }}</div>
                    <div class="text-sm opacity-90">Ticket-ID: #{{ scanResult.id }}</div>
                    <div v-if="scanResult.attendee_name" class="text-sm opacity-90 font-bold mt-2">Gast: {{ scanResult.attendee_name }}</div>
                </div>

                <button @click="startScanner" class="mt-6 bg-white text-black font-bold py-3 px-8 rounded-full shadow-md hover:bg-gray-100 transition-colors w-full">
                    Nächster Scan
                </button>
            </div>

            <!-- Scanner View -->
            <div v-show="!scanMessage" class="w-full max-w-md relative">
                <div id="reader" class="rounded-3xl overflow-hidden shadow-2xl bg-black w-full border-4 border-gray-800"></div>
                
                <div v-if="!isScanning" class="absolute inset-0 flex items-center justify-center bg-gray-900/80 rounded-3xl">
                    <button @click="startScanner" class="bg-brand-500 hover:bg-brand-600 text-white font-bold text-lg px-8 py-4 rounded-full shadow-[0_0_20px_rgba(249,115,22,0.5)] transition-all transform hover:scale-105 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Scanner starten
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* html5-qrcode overrides to make it look native/clean */
#reader {
    width: 100%;
}
#reader video {
    object-fit: cover !important;
}
#reader__dashboard_section_csr {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 10px;
}
#reader__dashboard_section_swaplink {
    display: none !important;
}
</style>
