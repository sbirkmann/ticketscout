<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    event: Object,
    seatingPlan: Object,
    ticketCategories: Array,
    mappings: Array
});

const elements = computed(() => props.seatingPlan?.layout_data?.elements || []);

// We keep a local mapping state
const currentMappings = ref({});

onMounted(() => {
    // Populate currentMappings from DB
    props.mappings.forEach(m => {
        currentMappings.value[m.element_id] = m.ticket_category_id;
    });
});

const form = useForm({
    mappings: []
});

function saveMappings() {
    // Convert local mapping state to array format
    form.mappings = Object.keys(currentMappings.value).map(elementId => ({
        element_id: elementId,
        ticket_category_id: currentMappings.value[elementId]
    }));
    
    form.put(route('vendor.events.seating.update', props.event.id));
}

// GUI Tools
const selectedCategory = ref(null);
const canvasRef = ref(null);

function selectElement(element) {
    if (!selectedCategory.value) return;
    
    // Toggle or Assign Category
    if (currentMappings.value[element.id] === selectedCategory.value) {
        // Remove if clicked again
        delete currentMappings.value[element.id];
    } else {
        // Assign
        currentMappings.value[element.id] = selectedCategory.value;
    }
}

function getCategoryColor(categoryId) {
    if (!categoryId) return null;
    const colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'];
    const index = props.ticketCategories.findIndex(c => c.id === categoryId);
    return index >= 0 ? colors[index % colors.length] : null;
}
</script>

<template>
    <Head title="Sitzplätze verknüpfen" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('vendor.events.edit', event.id)" class="text-surface-500 hover:text-brand-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">
                        Plätze zuweisen: {{ event.title }}
                    </h2>
                </div>
                <button @click="saveMappings" :disabled="form.processing" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2 rounded-xl font-bold shadow-sm transition-colors">
                    Zuordnungen speichern
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="ticketCategories.length === 0" class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl mb-6">
                    <strong>Hinweis:</strong> Du hast noch keine Ticket-Kategorien für dieses Event erstellt. Erstelle diese im Event-Formular, bevor du Plätze zuweist.
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    
                    <!-- Sidebar: Categories -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-surface-200">
                            <h3 class="font-bold text-surface-900 mb-4 border-b border-surface-100 pb-2">Kategorie wählen</h3>
                            <p class="text-xs text-surface-500 mb-4">Wähle eine Kategorie und klicke dann auf die Plätze im Plan, um sie zuzuweisen.</p>
                            
                            <div class="space-y-2">
                                <button 
                                    v-for="(cat, index) in ticketCategories" 
                                    :key="cat.id"
                                    @click="selectedCategory = selectedCategory === cat.id ? null : cat.id"
                                    class="w-full text-left px-4 py-3 rounded-xl border-2 transition-colors flex items-center justify-between"
                                    :class="[
                                        selectedCategory === cat.id ? 'border-brand-500 bg-brand-50' : 'border-surface-200 hover:border-surface-300'
                                    ]"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: getCategoryColor(cat.id) }"></div>
                                        <div>
                                            <div class="font-bold text-sm text-surface-900">{{ cat.name }}</div>
                                            <div class="text-xs text-surface-500">{{ cat.price }} €</div>
                                        </div>
                                    </div>
                                    <svg v-if="selectedCategory === cat.id" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                </button>

                                <button 
                                    @click="selectedCategory = 'remove'"
                                    class="w-full text-left px-4 py-3 rounded-xl border-2 transition-colors flex items-center gap-3 mt-4"
                                    :class="[
                                        selectedCategory === 'remove' ? 'border-red-500 bg-red-50 text-red-700' : 'border-surface-200 hover:border-surface-300 text-surface-700'
                                    ]"
                                >
                                    <div class="w-4 h-4 rounded-full bg-white border border-surface-400"></div>
                                    <span class="font-bold text-sm">Zuweisung entfernen</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Canvas -->
                    <div class="lg:col-span-3">
                        <div class="bg-white p-4 rounded-3xl shadow-sm border border-surface-200 overflow-auto flex justify-center h-[800px]">
                            <div 
                                ref="canvasRef" 
                                class="relative bg-white border shadow-md"
                                style="width: 800px; height: 1000px;"
                            >
                                <!-- Background Image Render -->
                                <img v-if="seatingPlan?.bg_image_path" :src="'/storage/' + seatingPlan.bg_image_path" class="absolute inset-0 w-full h-full object-contain opacity-40 pointer-events-none">
                                
                                <div 
                                    v-for="element in elements" 
                                    :key="element.id"
                                    class="absolute cursor-pointer select-none flex items-center justify-center font-bold text-xs transition-transform hover:scale-105"
                                    :class="{
                                        'rounded-full': element.type === 'seat',
                                        'rounded-lg border-2 border-dashed': element.type === 'area',
                                        'rounded-full border-4': element.type === 'table',
                                        'ring-2 ring-black ring-offset-1': selectedCategory === 'remove' && currentMappings[element.id]
                                    }"
                                    :style="{
                                        left: `${element.x}px`, 
                                        top: `${element.y}px`,
                                        width: element.type === 'seat' ? `${element.radius * 2}px` : `${element.width}px`,
                                        height: element.type === 'seat' ? `${element.radius * 2}px` : `${element.height}px`,
                                        backgroundColor: currentMappings[element.id] ? getCategoryColor(currentMappings[element.id]) : (element.type === 'seat' ? '#cbd5e1' : '#f8fafc40'),
                                        borderColor: currentMappings[element.id] ? getCategoryColor(currentMappings[element.id]) : '#94a3b8',
                                        color: currentMappings[element.id] && element.type === 'seat' ? '#fff' : '#475569'
                                    }"
                                    @click.stop="() => {
                                        if (selectedCategory === 'remove') {
                                            delete currentMappings[element.id];
                                        } else {
                                            selectElement(element);
                                        }
                                    }"
                                >
                                    <span class="pointer-events-none drop-shadow-md">
                                        {{ element.label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
