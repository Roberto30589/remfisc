<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import ButtonColor from '@/Components/ButtonColor.vue'
import ButtonGroup from '@/Components/ButtonGroup.vue'
import { Head, router } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faClipboardList, faFile, faPen, faTrash } from '@fortawesome/free-solid-svg-icons'
import DataTable from 'datatables.net-vue3'
import DataTablesCore from 'datatables.net-dt'
import 'datatables.net-responsive-dt'
import { DataTableEs } from '@/Composables/datatableEs.js'
import Swal from 'sweetalert2'
import { onMounted, ref } from 'vue'
import { usePermission } from '@/Composables/permission'

const { hasRole, hasPermission } = usePermission()

// Registrar DataTables
DataTable.use(DataTablesCore)

// Columnas de la tabla
const columns = [
    { data: 'id', title: 'Nº', width: '1%' },
    { 
        data: 'date', 
        render: (data, type, row) => row.date ? new Date(row.date).toLocaleDateString('es-CL').replace(/\-/g, '/') : '-', 
        title: 'Fecha' 
    },
    { data: 'user.name', title: 'Usuario', defaultContent: '-' },
    { data: 'project.name', title: 'Obra', defaultContent: '-' },
    { data: 'machine.plate', title: 'Patente', defaultContent: '-' },
    { data: 'machine.internal_id', title: 'ID Interno', defaultContent: '-' },
    { data: 'machine.brand', title: 'Marca', defaultContent: '-' },
    { data: 'machine.model', title: 'Modelo', defaultContent: '-' },
    {
        data: null,
        render: '#action',
        title: 'Acción',
        width: '1%',
        className: 'ip-0',
        responsivePriority: 1,
        orderable: false
    }
]

// Ref de la tabla y opciones
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

// Eliminación de reporte
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

            router.delete(route('daily-reports.destroy', { daily_report: id }), {
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
        <!-- Header -->
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                    <font-awesome-icon :icon="faClipboardList"/>
                    Listado de Reportes diarios
                </h2>

                <ButtonColor
                    color="green"
                    :href="route('daily-reports.create')"
                >
                    Crear reporte
                </ButtonColor>
            </div>
        </template>

        <!-- Tabla -->
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
                        <!-- Render fecha seguro -->
                        <template #fecha="props">
                            {{ props.rowData.date ? new Date(props.rowData.date).toLocaleDateString('es-CL').replace(/\-/g, '/') : '-' }}
                        </template>

                        <!-- Render botones de acción -->
                        <template #action="props">
                            <ButtonGroup>
                                <!-- Editar si no finalizado y tiene permiso -->
                                <ButtonColor
                                    v-if="props.rowData.finished_at === null && hasPermission('daily_reports.edit') && (props.rowData.user_id === $page.props.auth.user.id || hasRole('Administrador'))"
                                    color="blue"
                                    :href="route('daily-reports.edit', { id: props.rowData.id })"
                                >
                                    <FontAwesomeIcon :icon="faPen" class="size-4" />
                                </ButtonColor>

                                <!-- Ver PDF -->
                                <ButtonColor
                                    v-else
                                    color="teal"
                                    target="_blank"
                                    :href="route('daily-reports.show', {id: props.rowData.id})"
                                >
                                    <FontAwesomeIcon :icon="faFile" class="size-4" />
                                </ButtonColor>

                                <!-- Eliminar -->
                                <ButtonColor
                                    v-if="hasPermission('daily_reports.delete')"
                                    type="button"
                                    color="red"
                                    title="Eliminar"
                                    :disabled="deleting"
                                    class="disabled:opacity-40 disabled:cursor-not-allowed"
                                    @click.stop="deleteReport(props.rowData.id)"
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