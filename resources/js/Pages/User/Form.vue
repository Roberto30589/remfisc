<script setup>
import AppMain from '@/Layouts/AppMain.vue';
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import { DataTableEs } from '@/Composables/datatableEs.js';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faPen, faTrash, faAdd, faUsers } from '@fortawesome/free-solid-svg-icons';
import ButtonColor from '@/Components/ButtonColor.vue';
import ButtonGroup from '@/Components/ButtonGroup.vue';
import Modal from '@/Components/Modal.vue';
import { usePermission } from '@/Composables/permission';

const { hasPermission } = usePermission();
DataTable.use(DataTablesCore);

const table = ref();
let dt;

const columns = [
    { data: 'id', title: 'Nº', width:'1%' },
    { data: 'name', title: 'Nombre' },
    { data: 'email', title: 'Correo' },
    { data: 'roles', render:'#roles', title: 'Roles' },
    { data: 'shifts', render:'#shifts', title: 'Turno' },
    { data: null, render:'#action', title: 'Acción', width:'1%' }
];

onMounted(() => {
    dt = table.value.dt;
});

let modalOpen = ref(false);
let selectedRow = ref(null);

const deleteShow = (rowData) => {
    modalOpen.value = true;
    selectedRow.value = rowData;
};

const deleteRow = () => {
    const form = useForm({});
    form.delete(route('admin.users.delete', { id: selectedRow.value.id }), {
        onSuccess: () => {
            modalOpen.value = false;
            dt.ajax.reload();
        },
        onError: () => {
            modalOpen.value = false;
        }
    });
};
</script>

<template>
  <AppMain title="Usuarios">
    <div class="bg-white w-max sm:w-auto mx-auto max-w-7xl my-2 p-2 rounded shadow">
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800">
          <font-awesome-icon :icon="faUsers" class="pe-2"/>Listado de Usuarios
        </h2>

        <ButtonColor color="green" :href="route('admin.users.add')">
          <font-awesome-icon :icon="faAdd" class="me-2"/> Agregar usuario
        </ButtonColor>
      </div>

      <DataTable
        :ajax="route('admin.users.table')"
        :columns="columns"
        ref="table"
        :options="{select:true, serverSide:true, language: DataTableEs}"
        class="display cell-border compact"
      >
        <template #roles="props">
          <ul>
            <li v-for="role in props.rowData.roles" :key="role.id">
              {{ role.name }}
            </li>
          </ul>
        </template>

        <template #shifts="props">
          <ul>
            <li v-for="shift in props.rowData.shifts" :key="shift.id">
              {{ shift.name }}
            </li>
          </ul>
        </template>

        <template #action="props">
          <ButtonGroup>
            <ButtonColor
              color="blue"
              v-if="hasPermission('user.Update')"
              :href="route('admin.users.select', { id: props.rowData.id })"
              title="Editar"
            >
              <font-awesome-icon :icon="faPen"/>
            </ButtonColor>

            <ButtonColor
              color="red"
              v-if="hasPermission('user.Delete')"
              @click="deleteShow(props.rowData)"
              title="Eliminar"
            >
              <font-awesome-icon :icon="faTrash"/>
            </ButtonColor>
          </ButtonGroup>
        </template>
      </DataTable>
    </div>

    <Modal title="Eliminar Usuario" :show="modalOpen" @close="modalOpen=false">
      <div class="p-3">
        <p>
          ¿Está seguro de que desea eliminar al usuario
          <b>{{ selectedRow ? selectedRow.name : '' }}</b>?
        </p>
      </div>
      <div class="flex justify-end mt-4 gap-2">
        <ButtonColor color="gray" @click="modalOpen=false">Cancelar</ButtonColor>
        <ButtonColor color="red" @click="deleteRow">Eliminar</ButtonColor>
      </div>
    </Modal>
  </AppMain>
</template>
