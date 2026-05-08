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
        format: 'A4',
        elements: [
            { id: 'event_name_' + Date.now(), type: 'text', text: '{{ event_name }}', x: 20, y: 40, fontSize: 24, fontWeight: 'bold', color: '#000000', fontFamily: 'Arial', zIndex: 10 },
            { id: 'customer_name_' + Date.now(), type: 'text', text: '{{ customer_name }}', x: 20, y: 100, fontSize: 18, fontWeight: 'normal', color: '#333333', fontFamily: 'Arial', zIndex: 10 },
            { id: 'category_name_' + Date.now(), type: 'text', text: '{{ category_name }}', x: 20, y: 140, fontSize: 16, fontWeight: 'normal', color: '#666666', fontFamily: 'Arial', zIndex: 10 },
            { id: 'qr_code_' + Date.now(), type: 'qr', x: 300, y: 40, width: 150, height: 150, zIndex: 10 }
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

function addField(placeholder, textLabel) {
    form.layout_data.elements.push({
        id: 'field_' + Date.now(),
        type: 'text',
        text: placeholder,
        x: 50,
        y: 200,
        fontSize: 16,
        fontWeight: 'normal',
        color: '#000000',
        fontFamily: 'Arial',
        zIndex: 10
    });
    generateHtml();
}

function addImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (event) => {
        form.layout_data.elements.push({
            id: 'img_' + Date.now(),
            type: 'image',
            src: event.target.result,
            x: 50,
            y: 50,
            width: 200,
            height: 200,
            zIndex: 1
        });
        generateHtml();
    };
    reader.readAsDataURL(file);
}

function removeElement(element) {
    form.layout_data.elements = form.layout_data.elements.filter(el => el.id !== element.id);
    if (selectedElement.value?.id === element.id) selectedElement.value = null;
    generateHtml();
}

function changeZIndex(element, delta) {
    element.zIndex = (element.zIndex || 10) + delta;
    generateHtml();
}

function generateHtml() {
    let dimensions = 'width: 210mm; height: 297mm;'; // A4 default
    if (form.layout_data.format === 'A5') dimensions = 'width: 148mm; height: 210mm;';
    if (form.layout_data.format === 'Ticket') dimensions = 'width: 210mm; height: 99mm;';

    let html = `<div style="position: relative; ${dimensions} background: #fff;">`;
    let css = `
        @page { size: ${form.layout_data.format === 'Ticket' ? '210mm 99mm' : form.layout_data.format}; margin: 0; }
        body { margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .ticket-element { position: absolute; }
    `;
    
    form.layout_data.elements.forEach((el, index) => {
        let style = `left: ${el.x}px; top: ${el.y}px; z-index: ${el.zIndex || 10}; `;
        if (el.type === 'text') {
            style += `font-size: ${el.fontSize}px; font-weight: ${el.fontWeight}; color: ${el.color}; font-family: ${el.fontFamily || 'Arial'};`;
            html += `<div class="ticket-element" style="${style}">${el.text}</div>`;
        } else if (el.type === 'qr') {
            style += `width: ${el.width}px; height: ${el.height}px;`;
            html += `<div class="ticket-element" style="${style}">{{ qr_code }}</div>`;
        } else if (el.type === 'image') {
            style += `width: ${el.width}px; height: ${el.height}px; background-image: url('${el.src}'); background-size: cover; background-position: center;`;
            html += `<div class="ticket-element" style="${style}"></div>`;
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
                            <input v-model="form.name" type="text" class="w-full rounded-xl border-surface-300 focus:ring-brand-500 mb-4">
                            
                            <label class="block text-sm font-bold text-surface-900 mb-2">Format</label>
                            <select v-model="form.layout_data.format" @change="generateHtml" class="w-full rounded-xl border-surface-300 focus:ring-brand-500">
                                <option value="A4">DIN A4 (Hochformat)</option>
                                <option value="A5">DIN A5 (Hochformat)</option>
                                <option value="Ticket">Ticket (210x99mm)</option>
                            </select>
                        </div>

                        <div v-if="selectedElement" class="bg-white rounded-3xl p-6 shadow-sm border border-surface-200 space-y-4">
                            <h3 class="font-bold text-surface-900 border-b border-surface-100 pb-2">Element anpassen</h3>
                            
                            <template v-if="selectedElement.type === 'text'">
                                <div>
                                    <label class="block text-xs font-medium text-surface-500 mb-1">Inhalt / Freitext</label>
                                    <input v-model="selectedElement.text" @change="generateHtml" type="text" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-surface-500 mb-1">Schriftgröße (px)</label>
                                        <input v-model.number="selectedElement.fontSize" @change="generateHtml" type="number" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-surface-500 mb-1">Farbe</label>
                                        <input v-model="selectedElement.color" @change="generateHtml" type="color" class="w-full h-8 rounded-lg border-surface-300 cursor-pointer">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-surface-500 mb-1">Schriftart</label>
                                        <select v-model="selectedElement.fontFamily" @change="generateHtml" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                            <option value="Arial">Arial</option>
                                            <option value="Helvetica">Helvetica</option>
                                            <option value="'Times New Roman'">Times New Roman</option>
                                            <option value="'Courier New'">Courier New</option>
                                            <option value="Verdana">Verdana</option>
                                            <option value="Georgia">Georgia</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-surface-500 mb-1">Stärke</label>
                                        <select v-model="selectedElement.fontWeight" @change="generateHtml" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                            <option value="normal">Normal</option>
                                            <option value="bold">Fett</option>
                                            <option value="900">Black</option>
                                        </select>
                                    </div>
                                </div>
                            </template>
                            
                            <template v-if="selectedElement.type === 'qr' || selectedElement.type === 'image'">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-surface-500 mb-1">Breite (px)</label>
                                        <input v-model.number="selectedElement.width" @change="generateHtml" type="number" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-surface-500 mb-1">Höhe (px)</label>
                                        <input v-model.number="selectedElement.height" @change="generateHtml" type="number" class="w-full rounded-lg border-surface-300 py-1 text-sm">
                                    </div>
                                </div>
                            </template>

                            <div class="border-t border-surface-100 pt-3">
                                <label class="block text-xs font-medium text-surface-500 mb-2">Ebenen-Anordnung (Z-Index: {{ selectedElement.zIndex || 10 }})</label>
                                <div class="flex gap-2">
                                    <button @click="changeZIndex(selectedElement, -1)" class="flex-1 bg-surface-100 hover:bg-surface-200 py-1 rounded text-sm text-surface-700">Nach unten</button>
                                    <button @click="changeZIndex(selectedElement, 1)" class="flex-1 bg-surface-100 hover:bg-surface-200 py-1 rounded text-sm text-surface-700">Nach oben</button>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 mt-4 pt-4 border-t border-surface-100">
                                <button @click="removeElement(selectedElement)" class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-2 rounded-lg text-sm font-bold transition-colors">
                                    Löschen
                                </button>
                                <button @click="selectedElement = null" class="w-full bg-brand-500 hover:bg-brand-600 text-white py-2 rounded-lg text-sm font-bold transition-colors">
                                    Fertig
                                </button>
                            </div>
                        </div>
                        <div v-else class="bg-surface-50 rounded-3xl p-6 text-sm text-surface-600 border border-surface-200 space-y-4">
                            <p class="text-center font-bold text-surface-900 mb-4">Element hinzufügen</p>
                            <div class="space-y-2">
                                <button @click="addField('{{ seat_info }}', 'Sitzplatz Info')" class="w-full bg-white border border-surface-200 hover:border-brand-300 py-2 rounded-xl text-sm font-medium transition-colors text-left px-4 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg> Sitzplatz Info
                                </button>
                                <button @click="addField('{{ event_date }}', 'Datum')" class="w-full bg-white border border-surface-200 hover:border-brand-300 py-2 rounded-xl text-sm font-medium transition-colors text-left px-4 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> Event Datum
                                </button>
                                <button @click="addField('{{ vendor_name }}', 'Veranstalter')" class="w-full bg-white border border-surface-200 hover:border-brand-300 py-2 rounded-xl text-sm font-medium transition-colors text-left px-4 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg> Veranstalter
                                </button>
                                <button @click="addField('Neuer Text', 'Freitext')" class="w-full bg-white border border-surface-200 hover:border-brand-300 py-2 rounded-xl text-sm font-medium transition-colors text-left px-4 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg> Eigener Text
                                </button>
                                <div class="relative w-full">
                                    <input type="file" @change="addImage" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <button class="w-full bg-white border border-surface-200 hover:border-brand-300 py-2 rounded-xl text-sm font-medium transition-colors text-left px-4 flex items-center gap-2 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> Bild hochladen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Canvas -->
                    <div class="lg:col-span-3">
                        <div class="bg-white p-8 rounded-3xl shadow-sm border border-surface-200 overflow-x-auto flex justify-center bg-surface-100">
                            <div 
                                ref="canvasRef" 
                                class="relative bg-white border shadow-md transition-all duration-300"
                                :style="{
                                    width: form.layout_data.format === 'A5' ? '420px' : '595px',
                                    height: form.layout_data.format === 'A4' ? '842px' : (form.layout_data.format === 'A5' ? '595px' : '280px'),
                                    backgroundImage: 'radial-gradient(#e5e7eb 1px, transparent 1px)',
                                    backgroundSize: '20px 20px'
                                }"
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
                                        zIndex: element.zIndex || 10,
                                        fontSize: element.type === 'text' ? `${element.fontSize}px` : undefined,
                                        fontWeight: element.type === 'text' ? element.fontWeight : undefined,
                                        color: element.type === 'text' ? element.color : undefined,
                                        fontFamily: element.type === 'text' ? (element.fontFamily || 'Arial') : undefined,
                                        width: (element.type === 'qr' || element.type === 'image') ? `${element.width}px` : undefined,
                                        height: (element.type === 'qr' || element.type === 'image') ? `${element.height}px` : undefined,
                                        backgroundImage: element.type === 'image' ? `url(${element.src})` : undefined,
                                        backgroundSize: 'cover',
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
