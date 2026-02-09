<script setup>
import AppMain from '@/Layouts/AppMain.vue';
import ButtonColor from '@/Components/ButtonColor.vue';
import { Head } from '@inertiajs/vue3';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPen } from '@fortawesome/free-solid-svg-icons'

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import {DataTableEs} from '@/Composables/datatableEs.js';
import 'datatables.net-responsive-dt';
import {onMounted, ref} from "vue";
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
  language:DataTableEs
};
onMounted(() => {
    dt = table.value.dt;
});

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
                    <DataTable :ajax="route('admin.machines.table')" :columns="columns" ref="table" :options="dt_options" class="cell-border compact">
                        <template #action="props">                    
                            <ButtonGroup>
                                <ButtonColor color="blue"
                                    :href="route('admin.machines.edit', { id: props.rowData.id })"
                                    title="Editar"
                                    >
                                    <FontAwesomeIcon :icon="faPen" class="size-4"/>
                                </ButtonColor>
                            </ButtonGroup>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AppMain>
</template>
