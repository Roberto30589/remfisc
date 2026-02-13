<script setup>
import AppMain from '@/Layouts/AppMain.vue';
import Modal from '@/Components/Modal.vue';

import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ButtonGroup from '@/Components/ButtonGroup.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faAdd,faPen,faTrash,faUserTag } from '@fortawesome/free-solid-svg-icons';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import {DataTableEs} from '@/Composables/datatableEs.js';

import { useForm } from '@inertiajs/vue3';
import {onMounted, ref} from "vue";
import ButtonColor from '@/Components/ButtonColor.vue';

DataTable.use(DataTablesCore);


const props = defineProps({
    permissions: Array,
});

const columns = [
  { data: 'id', title: 'Nº',width:'1%' },
  { data: 'name', title: 'Role',width:'10%' },
  //permisos
    { data: 'permissions', title: 'Permisos', render: (data) => {
        return data.map(permission => permission.name).join(', ');
    }},
  { data: null,render: '#action', title: 'Acción',width:'1%', className: 'ip-0' }
];

const form = useForm ({
    name: "",
    permissions: [],
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
    form.permissions = row.permissions.map(permission => permission.id);
    modalform.value=true;
}

function add(){
    id=null;
    form.reset();
    modalform.value=true;
}
 
function store(e){
    if(id==null){
        form.post(route('admin.roles.create'), {
            onSuccess: () => {
                form.reset();
                dt.ajax.reload();
                modalform.value=false;
            },
        });
    }else{
        form.put(route('admin.roles.update', { id }), {
            onSuccess: () => {
                form.reset();
                dt.ajax.reload();
                modalform.value=false;
            },
        });
    }
}


const togglePermission = (permissionId) => {
    if (form.permissions.includes(permissionId)) {
        form.permissions = form.permissions.filter(id => id !== permissionId);
    } else {
        form.permissions.push(permissionId);
    }
};
</script>
<template>
    <AppMain title="roles">
        <Modal :show="modalform" ref="formulario">
            <form @submit.prevent="store" class="p-4 sm:p-6 lg:p-8" >
                <h1>Rol</h1>
                <div class="mt-4">
                    <InputLabel for="name" value="Nombre Rol" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autocomplete="name"
                    />
                </div>

                <div class="my-4">
                    <h2><b>Permisos del Rol</b></h2>
                    <label v-for="(permission,index) in permissions" :key="permission.id" class="flex items-center mt-4">
                        <input 
                        type="checkbox" 
                        :checked="form.permissions.includes(permission.id)"
                        @change="togglePermission(permission.id)"
                        v-bind:value="permission.id"
                        />
                        <span class="ml-2">{{ permission.name }}</span>
                    </label>
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
                    <font-awesome-icon :icon="faUserTag"/>
                    Listado de Roles
                </h2>
                <ButtonColor color="green" @click="add">
                    <font-awesome-icon :icon="faAdd" class="size-4 sm:pe-2"/>
                    <span class="hidden sm:inline">Agregar Rol</span>
                </ButtonColor>
            </div>
        </template>

        <div class="bg-white w-max sm:w-auto my-8 mx-auto max-w-7xl mt-2 rounded-lg shadow-md">
            <div class="px-2 pt-2">
                <DataTable :ajax="route('admin.roles.table')" :columns="columns" ref="table" :options="{select: true,serverSide: true,language:DataTableEs}" class="display cell-border compact">
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
