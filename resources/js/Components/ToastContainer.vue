<script setup>
import toast from "@/Stores/toast";
import ToastItem from "@/Components/ToastItem.vue";
</script>
<template>
    <TransitionGroup
        tag="div"
        enter-from-class="translate-y-full opacity-0"
        enter-active-class="duration-500"
        leave-active-class="duration-500"
        leave-to-class="translate-y-full opacity-0"
        class="fixed bottom-4 right-4 z-50 space-y-4 w-full max-w-xs">
        <ToastItem 
            v-for="(item,index) in toast.items" 
            :key="item.key" 
            :message="item.message"
            :variant="item.type"
            @remove="toast.remove(index)"
        />
    </TransitionGroup>
</template>