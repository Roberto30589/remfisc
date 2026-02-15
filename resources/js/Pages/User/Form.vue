<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import { useForm } from '@inertiajs/vue3'
import ButtonColor from '@/Components/ButtonColor.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import { watch } from 'vue'

const props = defineProps({
  user: Object,
  roles: Array,
  shifts: Array,
})

const form = useForm({
  rut: props.user?.rut ?? '',
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',

  password: '',
  password_confirmation: '',

  updatePassword: false,

  roles: props.user
    ? props.user.roles.map(r => r.id)
    : [],

  shifts: props.user
    ? props.user.shifts.map(s => s.id)
    : [],
})

// Valida RUT 
const validarRut = (rut) => {
  if (!rut) return false

  const limpio = rut.replace(/[^0-9kK]/g, '').toLowerCase()

  if (limpio.length < 8 || limpio.length > 9) return false

  const dv = limpio.slice(-1)
  const numero = limpio.slice(0, -1)

  let suma = 0
  let multiplicador = 2

  for (let i = numero.length - 1; i >= 0; i--) {
    suma += parseInt(numero[i]) * multiplicador
    multiplicador = multiplicador === 7 ? 2 : multiplicador + 1
  }

  const resto = suma % 11
  let digito = 11 - resto

  if (digito === 11) digito = '0'
  else if (digito === 10) digito = 'k'
  else digito = digito.toString()

  return dv === digito
}

// Valida en tiempo real
watch(() => form.rut, (value) => {
  if (!value) {
    form.clearErrors('rut')
    return
  }

  if (!validarRut(value)) {
    form.setError('rut', 'El RUT no es válido.')
  } else {
    form.clearErrors('rut')
  }
})

// Envío del formulario
const submit = () => {

  if (!validarRut(form.rut)) {
    form.setError('rut', 'El RUT no es válido.')
    return
  }

  props.user
    ? form.put(route('admin.users.update', props.user.id))
    : form.post(route('admin.users.store'))
}
</script>

<template>
  <AppMain>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">
        {{ props.user ? 'Editar Usuario' : 'Crear Usuario' }}
      </h2>
    </template>

    <div class="max-w-2xl mx-auto mt-6 p-4 bg-white shadow rounded">
      <form @submit.prevent="submit" class="space-y-4">

        <!-- RUT -->
        <div>
          <InputLabel value="RUT" />
          <TextInput v-model="form.rut" class="w-full" />
          <InputError :message="form.errors.rut" />
        </div>

        <!-- Nombre -->
        <div>
          <InputLabel value="Nombre" />
          <TextInput v-model="form.name" class="w-full" />
          <InputError :message="form.errors.name" />
        </div>

        <!-- Email -->
        <div>
          <InputLabel value="Correo electrónico" />
          <TextInput v-model="form.email" type="email" class="w-full" />
          <InputError :message="form.errors.email" />
        </div>

        <!-- Actualizar contraseña -->
        <div v-if="props.user" class="flex items-center gap-2">
          <input type="checkbox" v-model="form.updatePassword">
          <span class="text-sm">Actualizar contraseña</span>
        </div>

        <!-- Password -->
        <div v-if="!props.user || form.updatePassword">
          <InputLabel value="Contraseña" />
          <TextInput v-model="form.password" type="password" class="w-full" />
          <InputError :message="form.errors.password" />
        </div>

        <!-- Confirmar Password -->
        <div v-if="!props.user || form.updatePassword">
          <InputLabel value="Confirmar contraseña" />
          <TextInput v-model="form.password_confirmation" type="password" class="w-full" />
        </div>

        <!-- Roles -->
        <div>
          <InputLabel value="Roles" />
          <div class="grid grid-cols-3 gap-4">
            <label class="border rounded p-1" v-for="role in roles" :key="role.id">
              <input type="checkbox" :value="role.id" v-model="form.roles">
              {{ role.name }}
            </label>
          </div>
          <InputError :message="form.errors.roles" />
        </div>

        <!-- Botón -->
        <ButtonColor :color="props.user ? 'blue' : 'green'" type="submit">
          {{ props.user ? 'Actualizar Usuario' : 'Crear Usuario' }}
        </ButtonColor>

      </form>
    </div>
  </AppMain>
</template>
