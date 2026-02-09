<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'

import DataTable from 'datatables.net-vue3'
import DataTablesCore from 'datatables.net-dt'
import { DataTableEs } from '@/Composables/datatableEs.js'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPen, faTrash, faAdd, faUsers } from '@fortawesome/free-solid-svg-icons'

import ButtonGroup from '@/Components/ButtonGroup.vue'
import ButtonColor from '@/Components/ButtonColor.vue'
import Modal from '@/Components/Modal.vue'

import { usePermission } from '@/Composables/permission'
const { hasPermission } = usePermission()

DataTable.use(DataTablesCore)

/* =========================
 * DATATABLE CONFIG
 * ========================= */
const columns = [
    {
        data: null,
        title: 'Nº',
        width: '1%',
        render: (data, type, row, meta) => meta.row + 1
    },
    { data: 'name', title: 'Nombre' },
    { data: 'email', title: 'Correo' },
    { data: 'roles', render: '#roles', title: 'Roles' },
    { data: 'shifts', render: '#shifts', title: 'Turno' },
    { data: null, render: '#action', title: 'Acción', width: '1%' }
]

const table = ref(null)
let dt = null

onMounted(() => {
    dt = table.value.dt
})

/* =========================
 * DELETE
 * ========================= */
const modalOpen = ref(false)
const selectedRow = ref(null)

const deleteForm = useForm({})

const deleteShow = (row) => {
    selectedRow.value = row
    modalOpen.value = true
}

const deleteRow = () => {
    deleteForm.delete(route('users/delete', { id: selectedRow.value.id }), {
        onSuccess: () => {
            modalOpen.value = false
            selectedRow.value = null
            dt.ajax.reload()
        },
        onError: () => {
            modalOpen.value = false
        }
    })
}
</script>

<template>
    <AppMain title="Usuarios">
        <div class="bg-white max-w-7xl mx-auto my-2 p-4 rounded shadow">

            <!-- HEADER -->
            <div class="flex items-center mb-3">
                <h2 class="font-semibold text-xl text-gray-800 grow">
                    <font-awesome-icon :icon="faUsers" class="me-2" />
                    Listado de Usuarios
                </h2>

                <ButtonColor
                    v-if="hasPermission('user.Create')"
                    color="green"
                    :href="route('users/add')"
                >
                    <font-awesome-icon :icon="faAdd" class="me-2" />
                    Agregar usuario
                </ButtonColor>
            </div>

            <!-- TABLE -->
            <DataTable
                ref="table"
                class="display cell-border compact"
                :ajax="route('users/table')"
                :columns="columns"
                :options="{
                    serverSide: true,
                    processing: true,
                    language: DataTableEs
                }"
            >
                <!-- ROLES -->
                <template #roles="props">
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-for="role in props.rowData.roles"
                            :key="role.id"
                            class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"
                        >
                            {{ role.name }}
                        </span>
                    </div>
                </template>

                <!-- SHIFTS -->
                <template #shifts="props">
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-for="shift in props.rowData.shifts"
                            :key="shift.id"
                            class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded"
                        >
                            {{ shift.name }}
                        </span>
                    </div>
                </template>

                <!-- ACTIONS -->
                <template #action="props">
                    <ButtonGroup>
                        <ButtonColor
                            v-if="hasPermission('user.Update')"
                            color="blue"
                            :href="route('users/select', { id: props.rowData.id })"
                            title="Editar"
                        >
                            <font-awesome-icon :icon="faPen" />
                        </ButtonColor>

                        <ButtonColor
                            v-if="hasPermission('user.Delete')"
                            color="red"
                            title="Eliminar"
                            @click="deleteShow(props.rowData)"
                        >
                            <font-awesome-icon :icon="faTrash" />
                        </ButtonColor>
                    </ButtonGroup>
                </template>
            </DataTable>
        </div>

        <!-- MODAL DELETE -->
        <Modal :show="modalOpen" @close="modalOpen = false">
            <template #title>
                Eliminar Usuario
            </template>

            <div class="p-4">
                <p>
                    ¿Está seguro de que desea eliminar al usuario
                    <b>{{ selectedRow?.name }}</b>?
                </p>
            </div>

            <div class="flex justify-end gap-2 p-4 border-t">
                <ButtonColor color="gray" @click="modalOpen = false">
                    Cancelar
                </ButtonColor>
                <ButtonColor
                    color="red"
                    :disabled="deleteForm.processing"
                    @click="deleteRow"
                >
                    Eliminar
                </ButtonColor>
            </div>
        </Modal>
    </AppMain>
</template>

<style scoped>
table {
    border-collapse: collapse;
}
th,
td {
    text-align: left;
}
</style>
