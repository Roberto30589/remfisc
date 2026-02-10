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

//Columnas
const columns = [
  { data: 'id', title: 'ID', width: '5%' },
  { data: 'rut', title: 'RUT' },
  { data: 'name', title: 'Nombre' },
  { data: 'email', title: 'Correo electrónico' },
  {
    data: null,
    render: '#action',
    title: 'Acción',
    width: '1%',
    className: 'ip-0',
    responsivePriority: 1
  }
]

let dt
const table = ref()

const dt_options = {
  responsive: true,
  serverSide: true,
  language: DataTableEs
}

onMounted(() => {
  dt = table.value.dt
})

const deleting = ref(false)


//Eliminar usuario

const deleteUser = (id, name) => {
  if (deleting.value) return

  Swal.fire({
    title: '¿Eliminar usuario?',
    text: `Usuario: ${name}`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      deleting.value = true

      router.delete(route('admin.users.delete', { id }), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire({
            icon: 'success',
            title: 'Usuario eliminado',
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
            text: 'No se pudo eliminar el usuario'
          })
        }
      })
    }
  })
}
</script>

<template>
  <Head title="Usuarios" />

  <AppMain>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Usuarios
        </h2>

        <ButtonColor
          color="green"
          :href="route('admin.users.add')"
        >
          Crear usuario
        </ButtonColor>
      </div>
    </template>

    <div class="py-4">
      <div class="mx-auto max-w-7xl">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-4">

          <DataTable
            :ajax="route('admin.users.table')"
            :columns="columns"
            :options="dt_options"
            ref="table"
            class="cell-border compact"
          >
            <template #action="props">
              <ButtonGroup>

                <!-- Editar -->
                <ButtonColor
                  color="blue"
                  :href="route('admin.users.edit', { id: props.rowData.id })"
                  title="Editar"
                >
                  <FontAwesomeIcon :icon="faPen" class="size-4" />
                </ButtonColor>

                <!-- Eliminar -->
                <ButtonColor
                  type="button"
                  color="red"
                  title="Eliminar usuario"
                  :disabled="deleting"
                  class="transition opacity-90 hover:opacity-100 disabled:opacity-40"
                  @mousedown.stop="deleteUser(props.rowData.id, props.rowData.name)"
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
