<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    as: String,
    active: {
        type: Boolean,
        default: false
    }
});

const classes = computed(() => {
    return props.active
        ? 'inline-flex w-52 h-10 items-center px-1 border-l-4 border-[#00be00] text-sm text-white bg-[#005856] hover:border-gray-300 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
        : 'inline-flex w-52 h-10 items-center px-1 border-l-4 border-transparent text-sm text-white hover:bg-[#005856] hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
});

</script>

<template>
    <div>
        <button v-if="as == 'button'" type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-200 hover:text-white hover:bg-emerald-800 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
            <slot />
        </button>

        <a v-else-if="as =='a'" :href="href" class="block px-4 py-2 text-sm leading-5 text-gray-200 hover:bg-emerald-800 hover:text-white focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
            <slot />
        </a>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>
