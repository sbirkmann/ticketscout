<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    template: Object
});

const form = useForm({
    name: props.template?.name || 'Standard Ticket',
    html_content: props.template?.html_content || '',
    css_content: props.template?.css_content || '',
    layout_data: props.template?.layout_data ? JSON.parse(props.template.layout_data) : {
        elements: [
            { id: 'event_name', type: 'text', text: '{{ event_name }}', x: 20, y: 40, fontSize: 24, fontWeight: 'bold', color: '#000000' },
            { id: 'customer_name', type: 'text', text: '{{ customer_name }}', x: 20, y: 100, fontSize: 18, fontWeight: 'normal', color: '#333333' },
            { id: 'category_name', type: 'text', text: '{{ category_name }}', x: 20, y: 140, fontSize: 16, fontWeight: 'normal', color: '#666666' },
            { id: 'qr_code', type: 'qr', x: 300, y: 40, width: 150, height: 150 }
        ]
    }
});

const canvasRef = ref(null);
const draggingElement = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const selectedElement = ref(null);

function startDrag(e, element) {
    draggingElement.value = element;
    selectedElement.value = element;
    
    // Get mouse position relative to element
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
    
    // Simple boundary check
    newX = Math.max(0, Math.min(newX, canvasRect.width - 50));
    newY = Math.max(0, Math.min(newY, canvasRect.height - 20));
    
    draggingElement.value.x = newX;
    draggingElement.value.y = newY;
}

function stopDrag() {
    draggingElement.value = null;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
    generateHtml();
}

function generateHtml() {
    let html = `<div style="position: relative; width: 100%; height: 297mm; background: #fff;">`;
    let css = `
        body { margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .ticket-element { position: absolute; }
    `;
    
    form.layout_data.elements.forEach((el, index) => {
        let style = `left: ${el.x}px; top: ${el.y}px; `;
        if (el.type === 'text') {
            style += `font-size: ${el.fontSize}px; font-weight: ${el.fontWeight}; color: ${el.color};`;
            html += `<div class="ticket-element" style="${style}">${el.text}</div>`;
        } else if (el.type === 'qr') {
            style += `width: ${el.width}px; height: ${el.height}px;`;
            html += `<div class="ticket-element" style="${style}">{{ qr_code }}</div>`;
        }
    });
    
    html += `</div>`;
    
    form.html_content = html;
    form.css_content = css;
}

function save() {
    generateHtml();
    form.layout_data = JSON.stringify(form.layout_data); // Serialize for submission
    
    if (props.template?.id) {
        form.put(route('vendor.templates.update', props.template.id), {
            onSuccess: () => { form.layout_data = JSON.parse(form.layout_data); }
        });
    } else {
        form.post(route('vendor.templates.store'), {
            onSuccess: () => { form.layout_data = JSON.parse(form.layout_data); }
        });
    }
}
</script>

<template>
    <Head title="Ticket Template Builder" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl text-surface-900 leading-tight font-display">
                    {{ template ? 'Ticket Template bearbeiten' : 'Neues Ticket Template' }}
                </h2>
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
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-surface-200">
                            <label class="block text-sm font-bold text-surface-900 mb-2">Template Name</label>
                            <input v-model="form.name" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500">
                        </div>

                        <div v-if="selectedElement" class="bg-white rounded-3xl p-6 shadow-sm border border-surface-200 space-y-4">
                            <h3 class="font-bold text-surface-900 border-b border-surface-100 pb-2">Element anpassen</h3>
                            
                            <template v-if="selectedElement.type === 'text'">
                                <div>
                                    <label class="block text-xs font-medium text-surface-500 mb-1">Schriftgröße (px)</label>
                                    <input v-model.number="selectedElement.fontSize" @change="generateHtml" type="number" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-surface-500 mb-1">Schriftstärke</label>
                                    <select v-model="selectedElement.fontWeight" @change="generateHtml" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                        <option value="normal">Normal</option>
                                        <option value="bold">Fett</option>
                                        <option value="900">Black</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-surface-500 mb-1">Farbe</label>
                                    <input v-model="selectedElement.color" @change="generateHtml" type="color" class="w-full h-8 rounded-lg border-surface-300 cursor-pointer">
                                </div>
                            </template>
                            
                            <template v-if="selectedElement.type === 'qr'">
                                <div>
                                    <label class="block text-xs font-medium text-surface-500 mb-1">Größe (px)</label>
                                    <input v-model.number="selectedElement.width" @change="generateHtml; selectedElement.height = selectedElement.width;" type="number" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                </div>
                            </template>
                            
                            <button @click="selectedElement = null" class="w-full bg-surface-100 hover:bg-surface-200 text-surface-700 py-2 rounded-lg text-sm font-bold mt-4 transition-colors">
                                Fertig
                            </button>
                        </div>
                        <div v-else class="bg-surface-50 rounded-3xl p-6 text-center text-sm text-surface-500 border border-surface-200">
                            Klicke auf ein Element im Ticket, um es anzupassen. Du kannst die Elemente frei verschieben.
                        </div>
                    </div>
                    
                    <!-- Canvas -->
                    <div class="lg:col-span-3">
                        <div class="bg-white p-8 rounded-3xl shadow-sm border border-surface-200 overflow-x-auto flex justify-center">
                            <!-- A4 representation approx (width 595px roughly matching A4 proportions) -->
                            <div 
                                ref="canvasRef" 
                                class="relative bg-white border shadow-md"
                                style="width: 595px; height: 842px; background-image: radial-gradient(#e5e7eb 1px, transparent 1px); background-size: 20px 20px;"
                                @mousedown.self="selectedElement = null"
                            >
                                <div 
                                    v-for="(element, index) in form.layout_data.elements" 
                                    :key="element.id"
                                    class="absolute cursor-move select-none border border-transparent hover:border-brand-300 hover:bg-brand-50/50 p-1 rounded transition-colors"
                                    :class="{'ring-2 ring-brand-500 border-transparent': selectedElement === element}"
                                    :style="{
                                        left: `${element.x}px`, 
                                        top: `${element.y}px`,
                                        fontSize: element.type === 'text' ? `${element.fontSize}px` : undefined,
                                        fontWeight: element.type === 'text' ? element.fontWeight : undefined,
                                        color: element.type === 'text' ? element.color : undefined,
                                        width: element.type === 'qr' ? `${element.width}px` : undefined,
                                        height: element.type === 'qr' ? `${element.height}px` : undefined,
                                        backgroundColor: element.type === 'qr' ? '#f3f4f6' : 'transparent',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center'
                                    }"
                                    @mousedown="startDrag($event, element)"
                                >
                                    <template v-if="element.type === 'text'">
                                        {{ element.text }}
                                    </template>
                                    <template v-else-if="element.type === 'qr'">
                                        <div class="text-surface-400 text-xs font-bold text-center flex flex-col items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                            QR CODE
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
