<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import ButtonColor from '@/Components/ButtonColor.vue'
import ButtonGroup from '@/Components/ButtonGroup.vue'
import { Head, router } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faEye, faTrash } from '@fortawesome/free-solid-svg-icons'
import DataTable from 'datatables.net-vue3'
import DataTablesCore from 'datatables.net-dt'
import 'datatables.net-responsive-dt'
import { DataTableEs } from '@/Composables/datatableEs.js'
import Swal from 'sweetalert2'
import { onMounted, ref } from 'vue'

DataTable.use(DataTablesCore)

const columns = [
    { data: 'id', title: 'Nº', width: '1%' },
    { data: 'date', render: '#fecha', title: 'Fecha' },
    { data: 'user.name', title: 'Usuario' },
    { data: 'project.name', title: 'Proyecto' },
    { data: 'machine.plate', title: 'Patente' },
    { data: 'machine.name', title: 'Maquinaria' },
    {
        data: null,
        render: '#action',
        title: 'Acción',
        width: '1%',
        className: 'ip-0',
        responsivePriority: 1, orderable: false
    }
]

let dt
const table = ref()

const dt_options = {
    responsive: true,
    serverSide: true,
    language: DataTableEs,
}

onMounted(() => {
    dt = table.value.dt
})

const deleting = ref(false)

const deleteReport = (id) => {
    if (deleting.value) return

    Swal.fire({
        title: '¿Eliminar reporte diario?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            deleting.value = true

            router.delete(route('daily-reports.destroy', { dailyReport: id }), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reporte eliminado',
                        showConfirmButton: false,
                        timer: 1500
                    })

                    dt.ajax.reload(null, false)
                },
                onFinish: () => {
                    deleting.value = false
                },
                onError: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el reporte'
                    })
                }
            })
        }
    })
}
</script>

<template>
    <Head title="Reportes diarios" />

    <AppMain>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">
                    Reportes diarios
                </h2>

                <!-- OJO: esta ruta EXISTE -->
                <ButtonColor
                    color="green"
                    :href="route('daily-reports.add')"
                >
                    Crear reporte
                </ButtonColor>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-4">

                    <DataTable
                        :ajax="route('daily-reports.table')"
                        :columns="columns"
                        :options="dt_options"
                        ref="table"
                        class="cell-border compact"
                    >
                        <template #fecha="props">
                            {{ new Date(props.rowData.date).toLocaleDateString('es-CL').replace(/\-/g, '/') }}
                        </template>
                        <template #action="props">
                            <ButtonGroup>

                                <!-- Ver -->
                                <ButtonColor
                                    color="blue"
                                    :href="route('daily-reports.show', { id: props.rowData.id })"
                                    title="Ver reporte"
                                >
                                    <FontAwesomeIcon :icon="faEye" class="size-4" />
                                </ButtonColor>

                                <!-- Eliminar -->
                                <ButtonColor
                                    type="button"
                                    color="red"
                                    title="Eliminar"
                                    :disabled="deleting"
                                    class="disabled:opacity-40 disabled:cursor-not-allowed"
                                    @mousedown.stop="deleteReport(props.rowData.id)"
                                >
                                    <FontAwesomeIcon :icon="faTrash" class="size-4" />
                                </ButtonColor>

                            </ButtonGroup>
                        </template>
                    </DataTable>

                </div>
            </div>
        </div>
    </AppMain>
</template>
