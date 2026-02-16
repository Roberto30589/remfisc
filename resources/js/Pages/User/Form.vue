<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import { useForm } from '@inertiajs/vue3'
import ButtonColor from '@/Components/ButtonColor.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import { ref } from 'vue';
import { useRut } from '@/Composables/rut.js'; // Import the useRut function
import RutInput from '@/Components/RutInput.vue';
const { formatRut, validateRut } = useRut();

const props = defineProps({
  user: Object,
  roles: Array,
  shifts: Array,
})

const rutIsValid = ref(props.user ? validateRut(props.user.rut) : true);
const handleRutValidity =(isValid) =>{
    rutIsValid.value = isValid;
};

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
});



// Envío del formulario
const submit = () => {

  if (!validateRut(form.rut)) {
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
          <RutInput
              id="rut"
              v-model="form.rut"
              type="text"
              class="mt-1 block w-full"
              required
              @isValidRut="handleRutValidity" />
          <small v-show="!rutIsValid" class="text-red-500">Ingrese el RUT valido para proceder.</small>
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
