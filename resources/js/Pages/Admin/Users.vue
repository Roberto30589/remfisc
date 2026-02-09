<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Usuarios" />

    <AppMain>
        <!-- HEADER -->
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Usuarios
                </h2>

                <!-- Botón crear usuario -->
                <Link
                    :href="route('admin.users.add')"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Crear usuario
                </Link>
            </div>
        </template>

        <!-- CONTENIDO -->
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Aquí va la tabla de usuarios
                    </div>
                </div>
            </div>
        </div>
    </AppMain>
</template>
