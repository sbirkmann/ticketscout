<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    options: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Suchen...'
    }
});

const emit = defineEmits(['update:modelValue']);

const searchQuery = ref('');
const isOpen = ref(false);

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const lowerQuery = searchQuery.value.toLowerCase();
    return props.options.filter(opt => opt.name.toLowerCase().includes(lowerQuery));
});

const selectedOptions = computed(() => {
    return props.options.filter(opt => props.modelValue.includes(opt.id));
});

const toggleSelect = (id) => {
    const newValue = [...props.modelValue];
    const index = newValue.indexOf(id);
    if (index > -1) {
        newValue.splice(index, 1);
    } else {
        newValue.push(id);
    }
    emit('update:modelValue', newValue);
    // Keep focus
};

const removeOption = (id) => {
    const newValue = props.modelValue.filter(val => val !== id);
    emit('update:modelValue', newValue);
};

// Close dropdown when clicking outside
import { onMounted, onUnmounted } from 'vue';
const container = ref(null);
const handleClickOutside = (e) => {
    if (container.value && !container.value.contains(e.target)) {
        isOpen.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

</script>

<template>
    <div class="relative w-full" ref="container">
        <!-- Badges Area -->
        <div 
            class="min-h-[42px] border border-surface-300 rounded-xl p-2 flex flex-wrap gap-2 cursor-pointer bg-white"
            @click="isOpen = !isOpen"
            :class="{'ring-2 ring-brand-500 border-brand-500': isOpen}"
        >
            <div v-if="selectedOptions.length === 0" class="text-surface-400 p-1 px-2 text-sm">
                {{ placeholder }}
            </div>
            
            <div 
                v-for="opt in selectedOptions" 
                :key="opt.id"
                class="bg-brand-50 text-brand-700 border border-brand-200 rounded-lg px-2 py-1 text-sm flex items-center gap-1 font-medium"
            >
                {{ opt.name }}
                <button @click.stop="removeOption(opt.id)" class="text-brand-400 hover:text-brand-900 focus:outline-none">
                    &times;
                </button>
            </div>
        </div>

        <!-- Dropdown Area -->
        <div 
            v-show="isOpen" 
            class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-lg border border-surface-200 overflow-hidden"
        >
            <div class="p-2 border-b border-surface-100">
                <input 
                    v-model="searchQuery" 
                    type="text" 
                    class="w-full text-sm border-surface-200 rounded-lg focus:ring-brand-500 focus:border-brand-500" 
                    placeholder="Tippen zum Suchen..."
                    @click.stop
                >
            </div>
            <ul class="max-h-60 overflow-y-auto p-1">
                <li 
                    v-for="opt in filteredOptions" 
                    :key="opt.id"
                    @click="toggleSelect(opt.id)"
                    class="px-3 py-2 text-sm rounded-lg cursor-pointer flex justify-between items-center transition-colors"
                    :class="props.modelValue.includes(opt.id) ? 'bg-brand-50 text-brand-700 font-bold' : 'text-surface-700 hover:bg-surface-50'"
                >
                    {{ opt.name }}
                    <svg v-if="props.modelValue.includes(opt.id)" class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </li>
                <li v-if="filteredOptions.length === 0" class="p-3 text-sm text-surface-500 text-center">
                    Keine Ergebnisse
                </li>
            </ul>
        </div>
    </div>
</template>
