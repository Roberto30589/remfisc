<script setup>
import AppMain from '@/Layouts/AppMain.vue';
import { Head } from '@inertiajs/vue3';
import UserForm from './Form.vue';
</script>

<template>
    <Head title="Crear Usuario" />

    <AppMain>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Crear Usuario
            </h2>
        </template>

        <UserForm
            :submitRoute="route('admin.users.create')"
            method="post"
        />
    </AppMain>
</template>
