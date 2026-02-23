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
    lastReport: Object,
    maintenanceTypes: {
        type: Array,
        default: () => []
    },
})

const user = usePage().props.auth.user

//Crear mapa optimizado para no usar find() en cada celda
const maintenanceTypeMap = computed(() => {
    return Object.fromEntries(
        props.maintenanceTypes.map(t => [t.id, t])
    )
})

// Normalizar mantenciones cuando se edita
const buildMaintenances = () => {

    // Si estamos editando
    if (props.dailyReport?.maintenances?.length) {

        return props.maintenanceTypes.map(type => {
            const existing = props.dailyReport.maintenances
                .find(m => m.maintenance_type_id === type.id)

            return {
                maintenance_type_id: type.id,
                quantity: existing?.quantity ?? null,
                observation: existing?.observation ?? '',
            }
        })
    }

    // Si estamos creando, partimos con mantenciones vacías
    return props.maintenanceTypes.map(type => ({
        maintenance_type_id: type.id,
        quantity: null,
        observation: '',
    }))
}

const form = useForm({
    project_id: props.dailyReport?.project_id ?? props.lastReport?.project_id ?? null,
    machine_id: props.dailyReport?.machine_id ?? props.lastReport?.machine_id ?? null,
    date: props.dailyReport?.date ?? new Date().toISOString().slice(0,10),
    initial_km: props.dailyReport?.initial_km ?? props.lastReport?.final_km ?? '',
    final_km: props.dailyReport?.final_km ?? '',
    initial_hm: props.dailyReport?.initial_hm ?? props.lastReport?.final_hm ?? '',
    final_hm: props.dailyReport?.final_hm ?? '',
    work_description: props.dailyReport?.work_description ?? '',
    finished_at: props.dailyReport?.finished_at ?? null,
    is_finished: false,

    maintenances: buildMaintenances()
})

const totalKm = computed(() => {
    return form.final_km > 0 && form.initial_km > 0
        ? form.final_km - form.initial_km
        : ''
})

const totalHm = computed(() => {
    return form.final_hm > 0 && form.initial_hm > 0
        ? form.final_hm - form.initial_hm
        : ''
})

const submit = () => {
    props.dailyReport
        ? form.put(route('daily-reports.update', props.dailyReport.id))
        : form.post(route('daily-reports.store'))
}

const finishReport = () => {
    form.is_finished = true
    submit()
}
</script>

<template>
<AppMain>
    <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800 text-center">
            {{ props.dailyReport ? 'Editar Reporte Diario' : 'Crear Reporte Diario' }}
        </h2>
    </template>

    <div class="max-w-4xl mx-auto mt-6 p-6 bg-white shadow-md rounded">

        <!-- Logo y Número -->
        <div class="grid grid-cols-2 gap-2 mb-4">
            <ApplicationLogo class="mx-auto w-auto h-20" />
            <div class="text-center text-green-600 font-bold text-lg self-center">
                {{ props.dailyReport ? 'Nº ' + props.dailyReport.id : 'Sin Guardar' }}
            </div>
        </div>

        <h2 class="text-center text-gray-800 font-bold text-2xl mb-6">
            REPORTE DIARIO DE MAQUINARIA
        </h2>

        <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">

            <!-- Usuario -->
            <div>
                <InputLabel value="Usuario" />
                <TextInput v-model="user.name" class="w-full bg-gray-100" disabled />
            </div>

            <!-- Obra -->
            <div>
                <InputLabel value="Obra" />
                <SelectInput v-model="form.project_id" class="w-full">
                    <option :value="null" disabled>Seleccione una Obra</option>
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
                        {{ m.plate }} - {{ m.internal_id }}
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
                        <TextInput type="number" v-model="totalHm" class="w-full bg-gray-100" :class="totalHm<0 ? 'text-danger-600' : ''" readonly />
                    </div>
                </div>

            <!-- Trabajo -->
            <div class="col-span-2">
                <InputLabel value="DESCRIPCION DE LOS TRABAJOS REALIZADOS" />
                <textarea
                    v-model="form.work_description"
                    class="w-full min-h-32 border-stone-300 focus:border-stone-500 focus:ring-stone-500 rounded-md shadow-sm">
                </textarea>
            </div>

            <!-- DETALLE MANTENCIONES -->
            <div class="col-span-2 mt-6">

                <h3 class="text-lg font-semibold mb-2">
                    DETALLE A LLENAR PARA TODAS LAS MAQUINAS
                </h3>

                <div class="overflow-x-auto">

                    <table class="w-full border border-gray-300 text-sm table-fixed">

                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">Tipo de Mantención</th>
                                <th class="border px-3 py-2 text-left">Cantidad</th>
                                <th class="border px-3 py-2 text-left">Observación</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr
                            v-for="(maintenance,index) in form.maintenances"
                            :key="maintenance.maintenance_type_id"
                            :class="[
                                index%2 ? 'bg-gray-50' : '',
                                maintenance.quantity || maintenance.observation
                                ? 'bg-blue-50'
                                : ''
                            ]"
                            >

                                <!-- NOMBRE + UNIDAD -->
                                <td class="border px-3 py-2 font-medium align-middle">

                                    {{ maintenanceTypeMap[maintenance.maintenance_type_id]?.name }}

                                    <span
                                    v-if="maintenanceTypeMap[maintenance.maintenance_type_id]?.unit"
                                    class="text-xs text-gray-500">

                                        ({{ maintenanceTypeMap[maintenance.maintenance_type_id]?.unit }})

                                    </span>

                                </td>


                                <!-- CANTIDAD -->
                                <td class="border px-3 py-2">

                                    <TextInput
                                    v-if="maintenanceTypeMap[maintenance.maintenance_type_id]?.requires_quantity"
                                    type="number"
                                    step="0.01"
                                    v-model.number="maintenance.quantity"
                                    class="w-full"
                                    />

                                    <span v-else class="text-gray-400 italic">
                                        No aplica
                                    </span>

                                </td>


                                <!-- OBSERVACION -->
                                <td class="border px-3 py-2">

                                    <TextInput
                                    v-model="maintenance.observation"
                                    class="w-full"
                                    placeholder="Detalle opcional"
                                    />

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

            <!-- Botones -->
            <div class="col-span-2 flex justify-between mt-6">
                <ButtonColor type="submit" color="green" :disabled="form.processing">
                    {{ props.dailyReport ? 'Actualizar Reporte' : 'Crear Reporte' }}
                </ButtonColor>

                <ButtonColor
                    color="blue"
                    :disabled="!props.dailyReport || form.is_finished || form.processing"
                    @click="finishReport">
                    Terminar Reporte
                </ButtonColor>
            </div>

        </form>
    </div>
</AppMain>
</template>