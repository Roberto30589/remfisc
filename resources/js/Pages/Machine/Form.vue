<script setup>
import AppMain from '@/Layouts/AppMain.vue';
import { useForm } from '@inertiajs/vue3'

import ButtonColor from '@/Components/ButtonColor.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';


const props = defineProps({
  machine: Object,
  types: Array,
})

const form = useForm({
  internal_id: props.machine?.internal_id ?? null,
  plate: props.machine?.plate ?? '',
  name: props.machine?.name ?? '',
  machine_type_id: props.machine?.machine_type_id ?? null,
  fuel_type: props.machine?.fuel_type ?? '',
  fuel_capacity: props.machine?.fuel_capacity ?? null,
})

const submit = () => {
  props.machine
    ? form.put(route('admin.machines.update', props.machine.id))
    : form.post(route('admin.machines.create'))
}
</script>
<template>
    <AppMain>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ props.machine ? 'Editar Maquinaria ' + props.machine.internal_id : 'Crear Maquinaria' }}
            </h2>
        </template>
        <div class="max-w-2xl mx-auto mt-6 p-4 bg-white shadow-md rounded">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="internal_id" value="ID Interno" />
                    <TextInput v-model="form.internal_id" type="number" id="internal_id" class="mt-1 block w-full" />
                    <InputError :message="form.errors.internal_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="plate" value="Placa" />
                    <TextInput v-model="form.plate" type="text" id="plate" class="mt-1 block w-full" />
                    <InputError :message="form.errors.plate" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="name" value="Nombre" />
                    <TextInput v-model="form.name" type="text" id="name" class="mt-1 block w-full" />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="machine_type_id" value="Tipo de Maquinaria" />
                    <select v-model="form.machine_type_id" id="machine_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option :value="null" disabled>Seleccione un tipo</option>
                        <option v-for="type in props.types" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <InputLabel for="fuel_type" value="Tipo de Combustible" />
                    <select v-model="form.fuel_type" id="fuel_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option :value="null" disabled>Seleccione un tipo de combustible</option>
                        <option value="Gasolina">Gasolina</option>
                        <option value="Diésel">Diésel</option>
                        <option value="Eléctrico">Eléctrico</option>
                        <option value="Híbrido">Híbrido</option>
                    </select>
                    <InputError :message="form.errors.fuel_type" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="fuel_capacity" value="Capacidad de Combustible" />
                    <TextInput v-model="form.fuel_capacity" type="number" id="fuel_capacity" class="mt-1 block w-full" />
                    <InputError :message="form.errors.fuel_capacity" class="mt-2" />
                </div>
                <div>
                    <ButtonColor type="submit" :color="props.machine ? 'blue' : 'green'">
                        {{ props.machine  ? 'Actualizar Maquinaria' : 'Crear Maquinaria' }}
                    </ButtonColor>
                </div>
            </form>
        </div>
    </AppMain>
</template>
