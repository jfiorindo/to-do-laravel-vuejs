<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue'


const mostrarSenha = ref(false)

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onSuccess: () => {
            const user = usePage().props.auth.user;

            if (user?.is_admin) {
                router.visit('/admin');
            } else {
                router.visit('/tasks');
            }
        },
        onFinish: () => form.reset('password'),
    });
};

</script>

<template>
 <Head title="Login" />
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded shadow max-w-md w-full">
      <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Login</h2>

      <form @submit.prevent="submit">
        <!-- Status -->
        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
          {{ status }}
        </div>

        <!-- Email -->
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            required
            autofocus
            class="border p-2 rounded w-full mt-1"
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <!-- Senha -->
        <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
        
        <div class="relative">
            <input
            :type="mostrarSenha ? 'text' : 'password'"
            id="password"
            v-model="form.password"
            required
            class="border p-2 rounded w-full mt-1 pr-10"
            />
            <button
            type="button"
            @click="mostrarSenha = !mostrarSenha"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500"
            tabindex="-1"
            >
            {{ mostrarSenha ? '🔐' : '🔓' }}
            </button>
        </div>

        <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <!-- Lembrar -->
        <div class="flex items-center mb-4">
          <label class="flex items-center text-sm text-gray-600">
            <input
              type="checkbox"
              name="remember"
              v-model="form.remember"
              class="mr-2"
            />
            Lembrar de mim
          </label>
        </div>

        <!-- Links e Botão -->
        <div class="flex items-center justify-between">
          <button
            type="submit"
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Entrar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
