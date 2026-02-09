<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import ButtonColor from '@/Components/ButtonColor.vue';
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
                <ButtonColor 
                    color="green" 
                    :href="route('admin.users.add')"
                >
                    Crear usuario
                </ButtonColor>
            </div>
        </template>

        <!-- CONTENIDO -->
        <div class="py-4">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table>
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RUT</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Aquí irán las filas de las maquinarias -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppMain>
</template>
