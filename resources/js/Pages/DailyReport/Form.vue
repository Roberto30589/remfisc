<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import { useForm, usePage } from '@inertiajs/vue3'

import ButtonColor from '@/Components/ButtonColor.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    dailyReport: Object,
    projects: Array,
    machines: Array,
})

const user = usePage().props.auth.user

const form = useForm({
    project_id: props.dailyReport?.project_id ?? null,
    machine_id: props.dailyReport?.machine_id ?? null,
    date: props.dailyReport?.date ?? new Date().toISOString().slice(0,10),
    initial_km: props.dailyReport?.initial_km ?? null,
    final_km: props.dailyReport?.final_km ?? null,
    initial_hm: props.dailyReport?.initial_hm ?? null,
    final_hm: props.dailyReport?.final_hm ?? null,
    work_description: props.dailyReport?.work_description ?? '',
    fuel_quantity: props.dailyReport?.fuel_quantity ?? null,
    fuel_observation: props.dailyReport?.fuel_observation ?? '',
})

const submit = () => {
    props.dailyReport
        ? form.put(route('daily-reports.update', props.dailyReport.id))
        : form.post(route('daily-reports.create'))
}
</script>

<template>
    <AppMain>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ props.dailyReport ? 'Editar Reporte Diario' : 'Crear Reporte Diario' }}
            </h2>
        </template>

        <div class="max-w-3xl mx-auto mt-6 p-4 bg-white shadow-md rounded">
            <form @submit.prevent="submit" class="space-y-4">

                <!-- Obra -->
                <div>
                    <InputLabel value="Obra" />
                    <select v-model="form.project_id" class="input">
                        <option :value="null" disabled>Seleccione un Obra</option>
                        <option v-for="p in props.projects" :key="p.id" :value="p.id">
                            {{ p.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.project_id" />
                </div>

                <!-- Máquina -->
                <div>
                    <InputLabel value="Maquinaria" />
                    <select v-model="form.machine_id" class="input">
                        <option :value="null" disabled>Seleccione una maquinaria</option>
                        <option v-for="m in props.machines" :key="m.id" :value="m.id">
                            {{ m.plate }} - {{ m.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.machine_id" />
                </div>

                <!-- Fecha -->
                <div>
                    <InputLabel value="Fecha" />
                    <TextInput type="date" v-model="form.date" class="w-full" />
                    <InputError :message="form.errors.date" />
                </div>

                <!-- KM -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="KM Inicial" />
                        <TextInput type="number" v-model="form.initial_km" />
                    </div>
                    <div>
                        <InputLabel value="KM Final" />
                        <TextInput type="number" v-model="form.final_km" />
                    </div>
                </div>

                <!-- HM -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="HM Inicial" />
                        <TextInput type="number" v-model="form.initial_hm" />
                    </div>
                    <div>
                        <InputLabel value="HM Final" />
                        <TextInput type="number" v-model="form.final_hm" />
                    </div>
                </div>

                <!-- Trabajo -->
                <div>
                    <InputLabel value="Descripción del trabajo" />
                    <textarea v-model="form.work_description" class="input"></textarea>
                </div>

                <!-- Combustible -->
                <div>
                    <InputLabel value="Cantidad combustible (L)" />
                    <TextInput type="number" v-model="form.fuel_quantity" />
                </div>

                <div>
                    <InputLabel value="Observación combustible" />
                    <textarea v-model="form.fuel_observation" class="input"></textarea>
                </div>

                <div>
                    <ButtonColor type="submit" color="green">
                        {{ props.dailyReport ? 'Actualizar Reporte' : 'Crear Reporte' }}
                    </ButtonColor>
                </div>

            </form>
        </div>
    </AppMain>
</template>
