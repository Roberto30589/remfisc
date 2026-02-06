<script setup>
import { onMounted, computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faInfoCircle, faExclamationTriangle, faBug, faCheckCircle } from '@fortawesome/free-solid-svg-icons';


const props = defineProps({
  message: {
    type: String,
    required: true,
  },
  variant: {
    type: String,
    default: 'success', // Variante por defecto
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value),
  },
});

// Configuración de variantes
const variantStyles = {
  success: {
    type: 'success',
    icon: faCheckCircle,
  },
  error: {
    type: 'error',
    icon: faBug,
  },
  warning: {
    type: 'warning',
    icon: faExclamationTriangle,
  },
  info: {
    type: 'info',
    icon: faInfoCircle,
  },
};
// Estilo dinámico basado en la variante
const style = computed(() => variantStyles[props.variant]);

const emit = defineEmits(['remove']);

onMounted(()=>{
    setTimeout(()=>emit('remove'),5000)
})
</script>

<template>
    <div :class="`flex items-center w-full max-w-xs p-1 bg-white rounded-lg shadow ${style.type}`" role="alert">
        <font-awesome-icon :icon="style.icon" size="2x" class="icon mx-1 my-2"/>
        <div class="ms-3 text-md font-normal">{{props.message}}</div>
        <button
            @click="emit('remove')"
            type="button" class="ms-auto p-1.5 h-8 w-8 button" data-dismiss-target="#toast-default" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
</template>
<style scoped>    
    .success{
        background-color:#d1e7dd;
        color:#0f5132;
    }
    .success .icon{
        color:#0f5132;
    }
    
    .success .button{
        color:#0f5132;
    }
    .success .button:hover{
        color:#1b925b;
    }

    .error{
        background-color:#f8d7da;
        color:#842029;
    }
    .error .icon{
        color:#842029;
    }
    
    .error .button{
        color:#842029;
    }
    .error .button:hover{
        color:#d03240;
    }

    
    .warning{
        background-color:#fff3cd;
        color:#664d03;
    }
    .warning .icon{
        color:#664d03;
    }
    
    .warning .button{
        color:#664d03;
    }
    .warning .button:hover{
        color:#b28605;
    }

    
    .info{
        background-color:#cfe2ff;
        color:#084298;
    }
    .info .icon{
        color:#084298;
    }
    
    .info .button{
        color:#084298;
    }
    .info .button:hover{
        color:#0c63e4;
    }
</style>
