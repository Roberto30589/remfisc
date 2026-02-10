<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import { useForm } from '@inertiajs/vue3'

import ButtonColor from '@/Components/ButtonColor.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    project: {
        type: Object,
        default: null,
    },
})

const form = useForm({
    internal_id: props.project?.internal_id ?? '',
    name: props.project?.name ?? '',
    region: props.project?.region ?? '',
    comuna: props.project?.comuna ?? '',
    started_at: props.project?.started_at ?? '',
    planned_finish_at: props.project?.planned_finish_at ?? '',
    actual_finish_at: props.project?.actual_finish_at ?? '',
})

const submit = () => {
    if (props.project) {
        form.put(route('projects.update', props.project.id))
    } else {
        form.post(route('projects.create'))
    }
}
</script>

<template>
    <AppMain>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{
                    props.project
                        ? 'Editar Obra ' + props.project.internal_id
                        : 'Crear Obra'
                }}
            </h2>
        </template>

        <div class="max-w-2xl mx-auto mt-6 p-6 bg-white shadow-md rounded">
            <form @submit.prevent="submit" class="space-y-4">

                <!-- ID Interno -->
                <div>
                    <InputLabel for="internal_id" value="ID Interno" />
                    <TextInput
                        id="internal_id"
                        v-model="form.internal_id"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.internal_id" class="mt-2" />
                </div>

                <!-- Nombre -->
                <div>
                    <InputLabel for="name" value="Nombre de la Obra" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <!-- Región -->
                <div>
                    <InputLabel for="region" value="Región" />
                    <TextInput
                        id="region"
                        v-model="form.region"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.region" class="mt-2" />
                </div>

                <!-- Comuna -->
                <div>
                    <InputLabel for="comuna" value="Comuna" />
                    <TextInput
                        id="comuna"
                        v-model="form.comuna"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.comuna" class="mt-2" />
                </div>

                <!-- Fecha inicio -->
                <div>
                    <InputLabel for="started_at" value="Fecha de inicio" />
                    <TextInput
                        id="started_at"
                        v-model="form.started_at"
                        type="date"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.started_at" class="mt-2" />
                </div>

                <!-- Fecha término planificada -->
                <div>
                    <InputLabel
                        for="planned_finish_at"
                        value="Fecha término planificada"
                    />
                    <TextInput
                        id="planned_finish_at"
                        v-model="form.planned_finish_at"
                        type="date"
                        class="mt-1 block w-full"
                    />
                    <InputError
                        :message="form.errors.planned_finish_at"
                        class="mt-2"
                    />
                </div>

                <!-- Fecha término real -->
                <div>
                    <InputLabel
                        for="actual_finish_at"
                        value="Fecha término real"
                    />
                    <TextInput
                        id="actual_finish_at"
                        v-model="form.actual_finish_at"
                        type="date"
                        class="mt-1 block w-full"
                    />
                    <InputError
                        :message="form.errors.actual_finish_at"
                        class="mt-2"
                    />
                </div>

                <!-- Botón -->
                <div class="pt-4">
                    <ButtonColor
                        type="submit"
                        :color="props.project ? 'blue' : 'green'"
                        :disabled="form.processing"
                    >
                        {{
                            props.project
                                ? 'Actualizar Obra'
                                : 'Crear Obra'
                        }}
                    </ButtonColor>
                </div>
            </form>
        </div>
    </AppMain>
</template>
