<script setup>
import AppMain from '@/Layouts/AppMain.vue';
import ButtonColor from '@/Components/ButtonColor.vue';
import { Head } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPen,faTrash } from '@fortawesome/free-solid-svg-icons'
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import {DataTableEs} from '@/Composables/datatableEs.js';
import 'datatables.net-responsive-dt';
import {onMounted, ref} from "vue";
import ButtonGroup from '@/Components/ButtonGroup.vue';
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2';


DataTable.use(DataTablesCore);
const columns = [
    { data: 'internal_id', title: 'Nº',width:'1%'},
    { data: 'plate', title: 'Patente' },
    { data: 'type.name', title: 'Tipo' },
    { data: 'fuel_type', title: 'Tipo de Combustible'},
    { data: null,render: '#action', title: 'Acción',width:'1%', className: 'ip-0',responsivePriority: 1}
];
let dt;
const table = ref();
const dt_options = {
  responsive: true,
  select: true,
  serverSide: true,
  language: DataTableEs,
  ajax: {
    data(d) {
            d.show_deleted = showDeleted.value; // envía al backend
        }
  }
};

onMounted(() => {
    dt = table.value.dt;
});
const showDeleted = ref(false);

const deleting = ref(false) // evita doble click

const deleteMachine = (id, plate) => {
    console.log(route('admin.machines.destroy', { id }))

    if (deleting.value) return

    Swal.fire({
        title: '¿Eliminar esta maquinaria?',
        text: `Patente: ${plate}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            deleting.value = true

            router.delete(route('admin.machines.destroy', { id }), {
                preserveScroll: true,
                onSuccess: () => {
                    // Feedback visual con SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Maquinaria eliminada',
                        showConfirmButton: false,
                        timer: 1500
                    })

                    // Recarga la tabla sin perder la página
                    dt.ajax.reload(null, false)
                },
                onFinish: () => {
                    deleting.value = false
                },
                onError: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar la maquinaria'
                    })
                }
            })
        }
    })
}


</script>

<template>
    <Head title="Maquinarias" />
    <AppMain>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Maquinarias
                </h2>
                <ButtonColor
                    color="green"
                    :href="route('admin.machines.add')"
                    class="ml-4"
                >
                    Crear maquinaria
                </ButtonColor>
            </div>  
        </template>
        <div class="py-4">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-4">
                    <DataTable
                        :ajax="route('admin.machines.table')"
                        :columns="columns"
                        ref="table"
                        :options="dt_options"
                        class="cell-border compact"
                    >
                        <template #action="props">
                            <ButtonGroup>
                                <!-- Editar -->
                                <ButtonColor
                                    color="blue"
                                    :href="route('admin.machines.edit', { id: props.rowData.id })"
                                    title="Editar"
                                >
                                    <FontAwesomeIcon :icon="faPen" class="size-4"/>
                                </ButtonColor>

                               <!-- Eliminar -->
                                <ButtonColor
                                    type="button"
                                    color="red"
                                    title="Eliminar maquinaria"
                                    :disabled="props.rowData.deleted || deleting"
                                    class="transition opacity-90 hover:opacity-100 disabled:opacity-40 disabled:cursor-not-allowed"
                                    @mousedown.stop="deleteMachine(props.rowData.id, props.rowData.plate)"
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
