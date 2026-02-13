<script setup>
import AppMain from '@/Layouts/AppMain.vue';
import Modal from '@/Components/Modal.vue';

import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ButtonGroup from '@/Components/ButtonGroup.vue';
import ButtonColor from '@/Components/ButtonColor.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faAdd,faPen,faTrash,faLock } from '@fortawesome/free-solid-svg-icons';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import {DataTableEs} from '@/Composables/datatableEs.js';

import { useForm } from '@inertiajs/vue3';
import {onMounted, ref} from "vue";

DataTable.use(DataTablesCore);

const columns = [
  { data: 'id', title: 'Nº',width:'1%' },
  { data: 'name', title: 'Permiso' },
  { data: null,render: '#action', title: 'Acción',width:'1%', className: 'ip-0' }
];

const form = useForm ({
    name: "",
    guard_name: "",
});

let modalform=ref(false);
let id=null;

let dt;
const table = ref();
const formulario = ref(null);
 
onMounted(() => {
    dt = table.value.dt;
});

function rowEdit(row){
    id=row.id;
    form.name=row.name
        .replaceAll(/&quot;/g, '"')
        .replaceAll(/&#039;/g, "'");

    form.guard_name=row.guard_name
        .replaceAll(/&quot;/g, '"')
        .replaceAll(/&#039;/g, "'");
        
    modalform.value=true;
}

function add(){
    id=null;
    form.reset();
    modalform.value=true;
}
 
function store(e){
    if(id==null){
        form.post(route('admin.permissions.create'), {
            onSuccess: () => {
                form.reset();
                dt.ajax.reload();
                modalform.value=false;
            },
        });
    }else{
        form.put(route('admin.permissions.update', { id }), {
            onSuccess: () => {
                form.reset();
                dt.ajax.reload();
                modalform.value=false;
            },
        });
    }
}
</script>
<template>
    <AppMain title="Permissions">
        <Modal :show="modalform" ref="formulario">
            <form @submit.prevent="store" class="p-4 sm:p-6 lg:p-8" >
                <h1>Permisos</h1>
                <div class="mt-4">
                    <InputLabel for="name" value="Nombre Permisos" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                </div>
                <div class="mt-4">
                    <InputLabel for="guard_name" value="Guard Name" />
                    <SelectInput
                        id="guard_name"
                        v-model="form.guard_name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        >
                        <option value="web">Web</option>
                        <option value="api">API</option>
                    </SelectInput>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <SecondaryButton @click="modalform=false">
                        Cerrar
                    </SecondaryButton>
                    <PrimaryButton>
                        {{id ? "Actualizar" : "Agregar"}}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <template #header>
            <div class="flex flex-row items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight grow">
                    <font-awesome-icon :icon="faLock"/>
                    Listado de Permisos
                </h2>
                <ButtonColor color="green" @click="add">
                    <font-awesome-icon :icon="faAdd" class="size-4 sm:pe-2"/>
                    <span class="hidden sm:inline">Agregar Permisos</span>
                </ButtonColor>
            </div>
        </template>

        <div class="bg-white w-max sm:w-auto my-8 mx-auto max-w-7xl mt-2 rounded-lg shadow-md">
            <div class="px-2 pt-2">
                <DataTable :ajax="route('admin.permissions.table')" :columns="columns" ref="table" :options="{select: true,serverSide: true,language:DataTableEs}" class="display cell-border compact">
                    <template #action="props">                    
                        <ButtonGroup>
                            <ButtonColor color="blue"
                                @click="rowEdit(props.rowData)"
                                title="Editar"
                                >
                                <font-awesome-icon :icon="faPen" class="size-4"/>
                            </ButtonColor>
                            <ButtonColor color="red"
                                @click="edit(props.rowData)"
                                title="Eliminar"
                                >
                                <font-awesome-icon :icon="faTrash" class="size-4"/>
                            </ButtonColor>
                        </ButtonGroup>
                    </template>
                </DataTable>
            </div>
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
