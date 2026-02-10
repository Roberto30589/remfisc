<script setup>
import AppMain from '@/Layouts/AppMain.vue';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import { DataTableEs } from '@/Composables/datatableEs.js';

import ButtonGroup from '@/Components/ButtonGroup.vue';
import ButtonColor from '@/Components/ButtonColor.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faAdd, faFile, faPen, faUserShield } from '@fortawesome/free-solid-svg-icons'

import {onMounted, ref} from "vue";

import { usePermission } from '@/Composables/permission';

const { hasRoles, hasPermission } = usePermission();

DataTable.use(DataTablesCore);
const columns = [
  { data: 'id', title: 'Nº',width:'1%' },
  { data: 'worker.name', title: 'Trabajador' },
  { data: 'supervisor.name', title: 'Supervisor' },
  { data: 'created_at', render: '#fecha', title: 'Fecha' },
  { data: null,render: '#action', title: 'Acción',width:'1%',className: 'px-4' }
];

let dt;
const table = ref();
 
onMounted(() => {
    dt = table.value.dt;
});
</script>
<template>
    <AppMain title="Plantas">
        <div class="bg-white w-max sm:w-auto mx-auto max-w-7xl my-2 p-2 rounded shadow">
            <div class="flex flex-row items-center" >
                <h2 class="font-semibold text-xl text-gray-800 leading-tight grow">
                    <FontAwesomeIcon :icon="faUserShield" class="pe-2"/>
                    Listado Entregas de EPP
                </h2>
                <ButtonColor color="green" :href="route('eppcontrols/add')" v-if="hasPermission('eppcontrol.Create')" class="bg-green-600 text-white px-2 py-1 rounded">
                    <FontAwesomeIcon :icon="faAdd" class="size-4 sm:pe-2"/>  
                    <span class="hidden sm:inline">Entregar EPP</span>
                </ButtonColor>
            </div>
            <DataTable :ajax="route('eppcontrols/table')" :columns="columns" ref="table" :options="{select: true,serverSide: true,language:DataTableEs,order: [[0, 'desc']]}" class="display cell-border compact">
                <template #fecha="props">
                    {{ new Date(props.rowData.created_at).toLocaleDateString().replaceAll('-', '/') }}
                </template>
                <template #action="props">                    
                    <ButtonGroup>
                        <ButtonColor color="teal"
                            :href="route('eppcontrols/infos', { worker_id: props.rowData.worker.id, date: props.rowData.created_at })"
                            title="Informe"
                            target="_blank"
                            >
                            <FontAwesomeIcon :icon="faFile" class="size-4"/>
                        </ButtonColor>
                        <ButtonColor color="blue"  
                            v-if="hasPermission('eppcontrol.Update') && props.rowData.supervisor_id == null"
                            :href="route('eppcontrols/select', { id: props.rowData.id })"
                            title="Editar"
                            >
                            <FontAwesomeIcon :icon="faPen" class="size-4"/>
                        </ButtonColor>
                    </ButtonGroup>
                </template>
            </DataTable>
        </div>
    </AppMain>
</template>
<style scoped>
    table {
        border-collapse: collapse;
    }
    th, td {
        text-align: left;
    }
</style>
