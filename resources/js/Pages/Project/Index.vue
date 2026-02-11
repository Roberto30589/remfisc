<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import ButtonColor from '@/Components/ButtonColor.vue'
import ButtonGroup from '@/Components/ButtonGroup.vue'
import { Head, router } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPen, faTrash } from '@fortawesome/free-solid-svg-icons'

import DataTable from 'datatables.net-vue3'
import DataTablesCore from 'datatables.net-dt'
import 'datatables.net-responsive-dt'
import { DataTableEs } from '@/Composables/datatableEs.js'

import { onMounted, ref } from 'vue'
import Swal from 'sweetalert2'

DataTable.use(DataTablesCore)

const columns = [
    { data: 'internal_id', title: 'Nº', width: '1%' },
    { data: 'name', title: 'Obra' },
    { data: 'region', title: 'Región' },
    { data: 'comuna', title: 'Comuna' },
    {
        data: null,
        render: '#action',
        title: 'Acción',
        width: '1%',
        className: 'ip-0',
        responsivePriority: 1, orderable: false
    }
]

const table = ref()
let dt = null
const showDeleted = ref(false)

const dt_options = {
    responsive: true,
    serverSide: true,
    processing: true,
    language: DataTableEs,
    ajax: {
        url: route('admin.projects.table'), 
        data(d) {
            d.show_deleted = showDeleted.value
        }
    }
}

onMounted(() => {
    dt = table.value.dt
})

const deleting = ref(false)

const deleteProject = (id, name) => {
    if (deleting.value) return

    Swal.fire({
        title: '¿Eliminar esta obra?',
        text: `Obra: ${name}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            deleting.value = true

            router.delete(route('admin.projects.destroy', id), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Obra eliminada',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    dt.ajax.reload(null, false)
                },
                onFinish: () => {
                    deleting.value = false
                }
            })
        }
    })
}
</script>

<template>
    <Head title="Obras" />

    <AppMain>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Obras
                </h2>

                <ButtonColor
                    color="green"
                    :href="route('admin.projects.add')"
                >
                    Crear obra
                </ButtonColor>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <DataTable
                        ref="table"
                        :columns="columns"
                        :options="dt_options"
                        class="cell-border compact"
                    >
                        <template #action="props">
                            <ButtonGroup>
                                <ButtonColor
                                    color="blue"
                                    :href="route('admin.projects.edit', props.rowData.id)"
                                >
                                    <FontAwesomeIcon :icon="faPen" class="size-4"/>
                                </ButtonColor>

                                <ButtonColor
                                    color="red"
                                    :disabled="props.rowData.deleted"
                                    @click="deleteProject(props.rowData.id, props.rowData.name)"
                                >
                                    <FontAwesomeIcon :icon="faTrash" class="size-4"/>
                                </ButtonColor>
                            </ButtonGroup>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AppMain>
</template>
