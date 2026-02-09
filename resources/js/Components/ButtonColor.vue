<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'href', // Tipo de botón por defecto
    },
    href: String,
    target: {
        type: String,
        default: '_self', // Target por defecto
    },
    color: {
        type: String,
        default: 'gray', // Color por defecto
        validator: (value) => ['gray', 'red', 'green', 'yellow', 'teal', 'blue', 'indigo', 'purple', 'pink'].includes(value), // Validador de colores
    },
    padding: {
        type: String,
        default: 'px-2 py-1', // Padding por defecto
    },
    disabled: {
        type: Boolean,
        default: false, // Estado deshabilitado por defecto
    },
});

const colorClasses = computed(() => {
    // Definir un objeto con clases para cada color
    const colorMap = {
        gray: 'bg-gray-800 hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:ring-gray-300 disabled:opacity-50 disabled:cursor-not-allowed',
        red: 'bg-red-600 hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:ring-red-300 disabled:opacity-50 disabled:cursor-not-allowed',
        green: 'bg-green-600 hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:ring-green-300 disabled:opacity-50 disabled:cursor-not-allowed',
        yellow: 'bg-yellow-500 hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-400 focus:ring-yellow-300 disabled:opacity-50 disabled:cursor-not-allowed',
        teal: 'bg-teal-600 hover:bg-teal-500 focus:bg-teal-500 active:bg-teal-700 focus:ring-teal-300 disabled:opacity-50 disabled:cursor-not-allowed',
        blue: 'bg-blue-600 hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed',
        indigo: 'bg-indigo-600 hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:ring-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed',
        purple: 'bg-purple-600 hover:bg-purple-500 focus:bg-purple-500 active:bg-purple-700 focus:ring-purple-300 disabled:opacity-50 disabled:cursor-not-allowed',
        pink: 'bg-pink-600 hover:bg-pink-500 focus:bg-pink-500 active:bg-pink-700 focus:ring-pink-300 disabled:opacity-50 disabled:cursor-not-allowed',
        lightblue: 'bg-lightblue-600 hover:bg-lightblue-500 focus:bg-lightblue-500 active:bg-lightblue-700 focus:ring-lightblue-300 disabled:opacity-50 disabled:cursor-not-allowed',
    };

    // Devolver las clases correspondientes al color
    return colorMap[props.color] || colorMap.gray; // fallback a gray si el color no existe
});
</script>

<template>
    <a v-if="type === 'href'" :href="href" type="href" :target="target" :class="['inline-flex items-center border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest disabled:opacity-100 transition ease-in-out duration-150', colorClasses, padding]">
        <slot />
    </a>
    <button v-else :type="type" :disabled="disabled" :class="['inline-flex items-center border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest disabled:opacity-100 transition ease-in-out duration-150', colorClasses, padding]">
        <slot />
    </button>
</template>