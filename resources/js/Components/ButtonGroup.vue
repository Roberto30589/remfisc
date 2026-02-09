<template>
    <div>
        <slot />
    </div>
</template>
<style scoped>
    div{
        display:flex;
        flex-wrap: nowrap;
    }
    div *:not(:last-child) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    div *:not(:first-child) {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
</style>