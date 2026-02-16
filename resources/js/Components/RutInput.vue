<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRut } from '@/Composables/rut.js'; // Import the useRut function
const { formatRut, validateRut } = useRut();

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'isValidRut']);

const input = ref(null);
const internalValue = ref('');

// Manejar la pulsación de teclas (esta no cambia)
const handleKeydown = (event) => {
    const key = event.key;
    const isNumber = /^[0-9]$/.test(key);
    const isK = key.toLowerCase() === 'k';
    const isControlKey = ['Backspace', 'Tab', 'Delete', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(key);
    const isPaste = (event.ctrlKey || event.metaKey) && key === 'v';

    if (isNumber || isK || isControlKey || isPaste) {
        return true;
    } else {
        event.preventDefault();
        return false;
    }
};

// Actualizar el valor interno y emitir el evento después de que la entrada ha sido procesada
const handleInput = (event) => {
    const rawValue = event.target.value;
    const cleanValue = rawValue.replace(/[^0-9kK]/g, '');

    // Actualiza internalValue con el RUT formateado (para la visualización)
    internalValue.value = formatRut(cleanValue);
    
    // *** CAMBIO CLAVE AQUÍ: Emite el internalValue (formateado) en lugar de cleanValue ***
    emit('update:modelValue', internalValue.value); 
    
    // La validación se sigue haciendo con el RUT limpio
    emit('isValidRut', validateRut(cleanValue));
};

// Sincronizar modelValue externo con internalValue al montar o cambiar (esta no cambia)
onMounted(() => {
    internalValue.value = formatRut(props.modelValue);
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

// Observar cambios en modelValue para actualizar el input si se cambia desde el padre (esta no cambia)
watch(() => props.modelValue, (newValue) => {
    const newFormattedValue = formatRut(newValue);
    if (internalValue.value !== newFormattedValue) {
        internalValue.value = newFormattedValue;
    }
});

defineExpose({ focus: () => input.value.focus(), validate: validateRut });
</script>

<template>
    <input
        ref="input"
        class="text-black border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        :value="internalValue"
        @input="handleInput"
        @keydown="handleKeydown"
        type="text"
        placeholder="Ej: 12.345.678-9"
    />
</template>