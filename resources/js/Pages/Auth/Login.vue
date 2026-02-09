<script setup>
import { ref, computed } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// Formulario Inertia
const form = useForm({
    rut: '',
    password: '',
    remember: false,
});

// Estado de validación del RUT
const rutError = ref('');
const rutValid = ref(false);

// Formatea el RUT agregando puntos y guion
const formatRut = (rut) => {
    if (!rut) return '';
    let value = rut.replace(/[^0-9kK]/g, '').toUpperCase();
    if (value.length <= 1) return value;
    const dv = value.slice(-1);
    let number = value.slice(0, -1);
    number = number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return `${number}-${dv}`;
};

// Valida el dígito verificador del RUT
const validateRut = (rut) => {
    if (!rut) return false;

    // Elimina puntos y guion
    let value = rut.replace(/\./g, '').replace(/-/g, '').toUpperCase();
    if (value.length < 2) return false;

    const num = value.slice(0, -1); // Parte numérica
    const dv = value.slice(-1);     // Dígito verificador

    let sum = 0;
    let multiplier = 2;

    // Multiplicamos de derecha a izquierda
    for (let i = num.length - 1; i >= 0; i--) {
        sum += parseInt(num[i]) * multiplier;
        multiplier = multiplier < 7 ? multiplier + 1 : 2;
    }

    const dvCalc = 11 - (sum % 11);
    const dvExpected = dvCalc === 11 ? '0' : dvCalc === 10 ? 'K' : dvCalc.toString();

    return dv.toUpperCase() === dvExpected;
};

// Se dispara al escribir en el campo RUT
const onRutInput = () => {
    form.rut = formatRut(form.rut);
    rutValid.value = validateRut(form.rut);
    rutError.value = rutValid.value ? '' : 'RUT inválido';
};

// Computed para estilo del input según validación
const rutInputClass = computed(() => {
    if (!form.rut) return '';
    return rutValid.value
        ? 'border-green-500 focus:border-green-500 focus:ring-green-500'
        : 'border-red-500 focus:border-red-500 focus:ring-red-500';
});

// Enviar formulario
const submit = () => {
    if (!validateRut(form.rut)) {
        rutError.value = 'RUT inválido';
        return;
    }

    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <!-- Mensaje de status -->
        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <!-- RUT -->
            <div>
                <InputLabel for="rut" value="RUT" />

                <TextInput
                    id="rut"
                    type="text"
                    name="rut"
                    class="mt-1 block w-full" 
                    v-bind:class="rutInputClass"
                    v-model="form.rut"
                    @input="onRutInput"
                    required
                    autofocus
                    autocomplete="rut"
                />

                <InputError class="mt-2" :message="rutError || form.errors.rut" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    name="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Remember me -->
            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">
                        Remember me
                    </span>
                </label>
            </div>

            <!-- Botón y reset password -->
            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
