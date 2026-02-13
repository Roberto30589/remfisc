<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import { useForm, usePage } from '@inertiajs/vue3'

import ButtonColor from '@/Components/ButtonColor.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import SelectInput from '@/Components/SelectInput.vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { computed } from 'vue'

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
    total_km: props.dailyReport?.total_km ?? 0,
    initial_hm: props.dailyReport?.initial_hm ?? null,
    final_hm: props.dailyReport?.final_hm ?? null,
    total_hm: props.dailyReport?.total_hm ?? 0,
    work_description: props.dailyReport?.work_description ?? '',
    fuel_quantity: props.dailyReport?.fuel_quantity ?? null,
    fuel_observation: props.dailyReport?.fuel_observation ?? '',
    finished_at: props.dailyReport?.finished_at ?? null,
})

const totalKm = computed(() => {
    //para calcular la diferencia final_km y initial_km deben ser mayores a 0
    return form.final_km>0 && form.initial_km>0 ? form.final_km - form.initial_km : null;
})

const totalHm = computed(() => {
    return form.final_hm>0 && form.initial_hm>0 ? form.final_hm - form.initial_hm : null;
})

const submit = () => {
    props.dailyReport
        ? form.put(route('daily-reports.update', props.dailyReport.id))
        : form.post(route('daily-reports.create'))
}

const finishReport = () => {
    // para terminar el reporte se asigna la fecha actual a finished_at como timestamp 'YYYY-MM-DD HH:MM:SS'
    form.finished_at = new Date().toISOString().slice(0,19).replace('T', ' ');
    submit();
}
</script>

<template>
    <AppMain>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 text-center">
                {{ props.dailyReport ? 'Editar Reporte Diario' : 'Crear Reporte Diario' }}
            </h2>
        </template>

        <div class="max-w-3xl mx-auto mt-6 p-4 bg-white shadow-md rounded">
            <div class="grid grid-cols-2 gap-2">
                <ApplicationLogo class="mx-auto mb-4 w-auto h-20" />
                <div class="text-center text-green-600 mb-6 font-bold text-lg self-center">
                    {{ props.dailyReport ? 'Nº' + props.dailyReport.id : 'Sin Guardar' }}
                </div>
            </div>
            <h2 class="text-center text-gray-800 font-bold text-2xl mb-6">
                REPORTE DIARIO DE MAQUINARIA
            </h2>
            <form @submit.prevent="submit" class="flex flex-col sm:grid sm:grid-cols-2 gap-4">
                <div>
                    <InputLabel value="Usuario" />
                    <TextInput v-model="user.name" class="w-full bg-gray-100 cursor-not-allowed" disabled />
                </div>
                <!-- Obra -->
                <div>
                    <InputLabel value="Obra" />
                    <SelectInput v-model="form.project_id" class="w-full">
                        <option :value="null" disabled>Seleccione un Obra</option>
                        <option v-for="p in props.projects" :key="p.id" :value="p.id">
                            {{ p.name }}
                        </option>
                    </SelectInput>
                    <InputError :message="form.errors.project_id" />
                </div>

                <!-- Máquina -->
                <div>
                    <InputLabel value="Maquinaria" />
                    <SelectInput v-model="form.machine_id" class="w-full">
                        <option :value="null" disabled>Seleccione una maquinaria</option>
                        <option v-for="m in props.machines" :key="m.id" :value="m.id">
                            {{ m.plate }} - {{ m.name }}
                        </option>
                    </SelectInput>
                    <InputError :message="form.errors.machine_id" />
                </div>

                <!-- Fecha -->
                <div>
                    <InputLabel value="Fecha" />
                    <TextInput type="date" v-model="form.date"/>
                    <InputError :message="form.errors.date" />
                </div>

                <!-- KM -->
                <div class="grid grid-cols-3 gap-4 border rounded">
                    <div class="col-span-3 text-center text-gray-800 text-sm font-bold">
                        INDICAR KILOMETRAJE
                    </div>
                    <div>
                        <InputLabel value="Inicial" />
                        <TextInput type="number" v-model="form.initial_km" class="w-full"/>
                    </div>
                    <div>
                        <InputLabel value="Final" />
                        <TextInput type="number" v-model="form.final_km"  class="w-full"/>
                    </div>
                    <div>
                        <InputLabel value="Total" />
                        <TextInput type="number" v-model="totalKm" class="w-full bg-gray-100" readonly />

                    </div>
                </div>

                <!-- HM -->
                <div class="grid grid-cols-3 gap-4 border rounded">
                    <div class="col-span-3 text-center text-gray-800 text-sm font-bold">
                        INDICAR HOROMETRO
                    </div>
                    <div>
                        <InputLabel value="Inicial" />
                        <TextInput type="number" v-model="form.initial_hm" class="w-full"/>
                    </div>
                    <div>
                        <InputLabel value="Final" />
                        <TextInput type="number" v-model="form.final_hm" class="w-full"/>
                    </div>
                    <div>
                        <InputLabel value="Total" />
                        <TextInput type="number" v-model="totalHm" class="w-full bg-gray-100" :class="totalHm<0 ?? 'text-danger-600'" readonly />
                    </div>
                </div>

                <!-- Trabajo -->
                <div class="col-span-2">
                    <InputLabel value="DESCRIPCION DE LOS TRABAJOS REALIZADOS" />
                    <textarea v-model="form.work_description" class="w-full border-stone-300 focus:border-stone-500 focus:ring-stone-500 rounded-md shadow-sm bg-white"></textarea>
                </div>
                <hr class="col-span-2">
                <!-- Combustible -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold mb-2 mt-0">Combustible</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <InputLabel value="Cantidad (L)" />
                            <TextInput type="number" v-model="form.fuel_quantity" class="w-full"/>
                        </div>

                        <div class="col-span-2">
                            <InputLabel value="Observación" />
                            <TextInput v-model="form.fuel_observation" class="w-full"/>
                        </div>
                    </div>
                </div>

                <div class="col-span-2 flex justify-between mt-4">
                    <ButtonColor type="submit" color="green">
                        {{ props.dailyReport ? 'Actualizar Reporte' : 'Crear Reporte' }}
                    </ButtonColor>
                    
                    <ButtonColor type="button" color="blue" v-if="props.dailyReport" @click="finishReport">
                        Terminar reporte
                    </ButtonColor>
                </div>

            </form>
        </div>
    </AppMain>
</template>
