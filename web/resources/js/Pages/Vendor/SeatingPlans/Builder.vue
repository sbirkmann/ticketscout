<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    location: Object,
    seatingPlan: Object
});

const form = useForm({
    name: props.seatingPlan?.name || '',
    bg_image: null,
    layout_data: props.seatingPlan?.layout_data || { elements: [] }
});

const canvasRef = ref(null);
const selectedElement = ref(null);
const draggingElement = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const nextId = ref(1);

onMounted(() => {
    if (form.layout_data.elements.length > 0) {
        // Find max ID to prevent collisions
        const maxId = Math.max(...form.layout_data.elements.map(e => parseInt(e.id.replace(/\D/g, '')) || 0));
        nextId.value = maxId + 1;
    }
});

function addSeat() {
    const newElement = {
        id: `S${nextId.value++}`,
        type: 'seat',
        x: 50,
        y: 50,
        label: `Platz ${nextId.value - 1}`,
        color: '#14b8a6', // brand color
        radius: 15
    };
    form.layout_data.elements.push(newElement);
    selectedElement.value = newElement;
}

function addArea() {
    const newElement = {
        id: `A${nextId.value++}`,
        type: 'area',
        x: 100,
        y: 100,
        width: 150,
        height: 100,
        label: 'Stehplatz',
        capacity: 100,
        color: '#f59e0b' // yellow/orange
    };
    form.layout_data.elements.push(newElement);
    selectedElement.value = newElement;
}

function addTable() {
    const newElement = {
        id: `T${nextId.value++}`,
        type: 'table',
        x: 200,
        y: 200,
        width: 120,
        height: 120,
        label: 'Tisch',
        min_booking: 2,
        color: '#e5e7eb',
        seats: [] // child seats could be added here or just placed over it visually
    };
    form.layout_data.elements.push(newElement);
    selectedElement.value = newElement;
}

function deleteSelected() {
    if (!selectedElement.value) return;
    form.layout_data.elements = form.layout_data.elements.filter(e => e.id !== selectedElement.value.id);
    selectedElement.value = null;
}

function startDrag(e, element) {
    draggingElement.value = element;
    selectedElement.value = element;
    
    const rect = e.target.getBoundingClientRect();
    dragOffset.value = {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
    
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
}

function onDrag(e) {
    if (!draggingElement.value || !canvasRef.value) return;
    
    const canvasRect = canvasRef.value.getBoundingClientRect();
    let newX = e.clientX - canvasRect.left - dragOffset.value.x;
    let newY = e.clientY - canvasRect.top - dragOffset.value.y;
    
    // Snap to grid (10px)
    newX = Math.round(newX / 10) * 10;
    newY = Math.round(newY / 10) * 10;
    
    newX = Math.max(0, Math.min(newX, canvasRect.width - (draggingElement.value.width || draggingElement.value.radius * 2 || 30)));
    newY = Math.max(0, Math.min(newY, canvasRect.height - (draggingElement.value.height || draggingElement.value.radius * 2 || 30)));
    
    draggingElement.value.x = newX;
    draggingElement.value.y = newY;
}

function stopDrag() {
    draggingElement.value = null;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
}

function handleImageUpload(e) {
    form.bg_image = e.target.files[0];
}

function save() {
    if (props.seatingPlan?.id) {
        form.post(route('vendor.locations.seating-plans.update', [props.location.id, props.seatingPlan.id]), {
            _method: 'put' // Fake PUT because of file upload
        });
    } else {
        form.post(route('vendor.locations.seating-plans.store', props.location.id));
    }
}
</script>

<template>
    <Head title="Saalplan Builder" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('vendor.locations.seating-plans.index', location.id)" class="text-surface-500 hover:text-brand-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">
                        {{ seatingPlan ? 'Saalplan bearbeiten' : 'Neuer Saalplan' }}
                    </h2>
                </div>
                <button @click="save" :disabled="form.processing" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2 rounded-xl font-bold shadow-sm transition-colors">
                    Speichern
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    
                    <!-- Sidebar settings -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- General Settings -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-surface-200">
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-surface-900 mb-2">Name des Saalplans</label>
                                <input v-model="form.name" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500" placeholder="z.B. Hauptsaal Standard">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-surface-900 mb-2">Hintergrundbild</label>
                                <input @change="handleImageUpload" type="file" accept="image/*" class="w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                            </div>
                        </div>

                        <!-- Toolbox -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-surface-200">
                            <h3 class="font-bold text-surface-900 border-b border-surface-100 pb-2 mb-4">Werkzeuge</h3>
                            <div class="space-y-2">
                                <button @click="addSeat" class="w-full text-left px-4 py-2 hover:bg-surface-50 rounded-xl transition-colors flex items-center gap-3 border border-surface-200">
                                    <div class="w-4 h-4 rounded-full bg-brand-500"></div>
                                    <span>Sitzplatz hinzufügen</span>
                                </button>
                                <button @click="addArea" class="w-full text-left px-4 py-2 hover:bg-surface-50 rounded-xl transition-colors flex items-center gap-3 border border-surface-200">
                                    <div class="w-5 h-4 bg-orange-400 rounded"></div>
                                    <span>Stehplatz-Bereich</span>
                                </button>
                                <button @click="addTable" class="w-full text-left px-4 py-2 hover:bg-surface-50 rounded-xl transition-colors flex items-center gap-3 border border-surface-200">
                                    <div class="w-5 h-5 border-2 border-surface-400 rounded-full"></div>
                                    <span>Tisch (Gruppe)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Element Settings -->
                        <div v-if="selectedElement" class="bg-white rounded-3xl p-6 shadow-sm border border-surface-200 space-y-4">
                            <div class="flex justify-between items-center border-b border-surface-100 pb-2">
                                <h3 class="font-bold text-surface-900">Element anpassen</h3>
                                <button @click="deleteSelected" class="text-red-500 hover:text-red-700 text-sm font-bold">Löschen</button>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-surface-500 mb-1">ID (Eindeutig)</label>
                                <input v-model="selectedElement.id" type="text" class="w-full rounded-lg border-surface-300 py-1 text-sm bg-surface-50">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-surface-500 mb-1">Beschriftung</label>
                                <input v-model="selectedElement.label" type="text" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-surface-500 mb-1">Farbe</label>
                                <input v-model="selectedElement.color" type="color" class="w-full h-8 rounded-lg border-surface-300 cursor-pointer">
                            </div>
                            
                            <template v-if="selectedElement.type === 'area'">
                                <div>
                                    <label class="block text-xs font-medium text-surface-500 mb-1">Kapazität</label>
                                    <input v-model.number="selectedElement.capacity" type="number" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                </div>
                            </template>

                            <template v-if="selectedElement.type === 'table'">
                                <div>
                                    <label class="block text-xs font-medium text-surface-500 mb-1">Mindestbuchung</label>
                                    <input v-model.number="selectedElement.min_booking" type="number" min="1" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                    <p class="text-[10px] text-surface-500 mt-1">Zwingt Kunden z.B. min. 2 Plätze zu buchen.</p>
                                </div>
                            </template>
                            
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <div>
                                    <label class="block text-xs text-surface-500">Breite / Radius</label>
                                    <input v-if="selectedElement.type === 'seat'" v-model.number="selectedElement.radius" type="number" class="w-full rounded border-surface-300 p-1 text-xs">
                                    <input v-else v-model.number="selectedElement.width" type="number" class="w-full rounded border-surface-300 p-1 text-xs">
                                </div>
                                <div v-if="selectedElement.type !== 'seat'">
                                    <label class="block text-xs text-surface-500">Höhe</label>
                                    <input v-model.number="selectedElement.height" type="number" class="w-full rounded border-surface-300 p-1 text-xs">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Canvas -->
                    <div class="lg:col-span-3">
                        <div class="bg-white p-4 rounded-3xl shadow-sm border border-surface-200 overflow-auto flex justify-center h-[800px]">
                            <div 
                                ref="canvasRef" 
                                class="relative bg-white border shadow-md"
                                style="width: 800px; height: 1000px; background-image: radial-gradient(#e5e7eb 1px, transparent 1px); background-size: 20px 20px; background-position: center;"
                                @mousedown.self="selectedElement = null"
                            >
                                <!-- Background Image Render -->
                                <img v-if="seatingPlan?.bg_image_path && !form.bg_image" :src="'/storage/' + seatingPlan.bg_image_path" class="absolute inset-0 w-full h-full object-contain opacity-40 pointer-events-none">
                                
                                <div 
                                    v-for="(element, index) in form.layout_data.elements" 
                                    :key="element.id"
                                    class="absolute cursor-move select-none flex items-center justify-center font-bold text-xs"
                                    :class="{
                                        'ring-2 ring-blue-500 ring-offset-2': selectedElement === element,
                                        'rounded-full': element.type === 'seat',
                                        'rounded-lg border-2 border-dashed': element.type === 'area',
                                        'rounded-full border-4': element.type === 'table'
                                    }"
                                    :style="{
                                        left: `${element.x}px`, 
                                        top: `${element.y}px`,
                                        width: element.type === 'seat' ? `${element.radius * 2}px` : `${element.width}px`,
                                        height: element.type === 'seat' ? `${element.radius * 2}px` : `${element.height}px`,
                                        backgroundColor: element.type === 'seat' ? element.color : `${element.color}40`,
                                        borderColor: element.type !== 'seat' ? element.color : 'transparent',
                                        color: element.type === 'seat' ? '#fff' : element.color
                                    }"
                                    @mousedown.stop="startDrag($event, element)"
                                >
                                    <span class="pointer-events-none drop-shadow-md">
                                        {{ element.label }}
                                        <span v-if="element.type === 'area'" class="block text-[10px] opacity-75">({{ element.capacity }})</span>
                                        <span v-if="element.type === 'table'" class="block text-[10px] opacity-75">Min: {{ element.min_booking }}</span>
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
