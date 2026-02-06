<script setup>
import { watch, onMounted, ref } from 'vue';

const props = defineProps({
    expansion: {
        type: Boolean,
        default: false,
    },
});

const container = ref(null);
const panel = ref(null);

onMounted(() => {
    if (props.expansion) {
        container.value.style.height = 'auto';
        container.value.style.overflow = 'visible';
    } else {
        container.value.style.height = '0px';
        container.value.style.overflow = 'hidden';
    }
});

watch(() => props.expansion, (value) => {
    if (value) {
        container.value.style.height = panel.value.scrollHeight + 'px';
        container.value.style.overflow = 'visible';
        container.value.addEventListener('transitionend', function(e) {
            if(container.value.style.height === (panel.value.scrollHeight + 'px')){
                container.value.style.height = "auto";
                container.value.style.overflow = 'visible';
            }
            container.value.removeEventListener('transitionend', arguments.callee);
        });
    } else {
        requestAnimationFrame(function() {
            container.value.style.height = panel.value.scrollHeight + 'px';
            container.value.style.transition = container.value.style.transition;
            container.value.style.transition = '';
            requestAnimationFrame(function() {
                container.value.style.height = 0 + 'px';
                container.value.style.overflow = 'hidden';
            });
        });
    }
});
</script>

<template>
    <div class="expansionpanel" ref="container">
        <div ref="panel">
            <slot/>
        </div>
    </div>
</template>
<style scoped>
    .expansionpanel {
        transition: height 0.3s ease;
    }
</style>