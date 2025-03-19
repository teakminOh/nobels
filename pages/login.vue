<template>
  <ClientOnly>
    <div class="min-h-screen bg-gray-50 justify-center py-12 sm:px-6 lg:px-8">
      <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
          <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mb-6 text-center text-3xl font-extrabold text-gray-900">
              Prihlásenie do účtu
            </h2>
          </div>
          <form @submit.prevent="login" class="space-y-6">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">
                E-mail
              </label>
              <div class="mt-1">
                <input id="email" v-model="email" type="email" required
                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              </div>
            </div>
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">
                Heslo
              </label>
              <div class="mt-1">
                <input id="password" v-model="password" type="password" required
                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              </div>
            </div>
            <!-- 2FA field is only shown if required -->
            <div v-if="show2fa">
              <label for="2fa" class="block text-sm font-medium text-gray-700">
                2FA kód
              </label>
              <div class="mt-1">
                <input id="2fa" v-model="twoFaCode" type="text" required
                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              </div>
            </div>
            <div>
              <button type="submit" :disabled="loading"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                {{ loading ? 'Prihlasujem...' : 'Prihlásiť sa' }}
              </button>
              <!-- Forgot Password Link -->
              <div class="mt-2 text-sm text-center">
                <NuxtLink to="/auth/forgot-password" class="font-medium text-blue-600 hover:text-blue-500">
                  Zabudli ste heslo?
                </NuxtLink>
              </div>
            </div>
            <!-- Green prompt for 2FA -->
            <div v-if="prompt" class="rounded-md bg-green-50 p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <!-- Optional: add an info icon -->
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-green-800">
                    Poznámka
                  </h3>
                  <div class="mt-2 text-sm text-green-700">
                    {{ prompt }}
                  </div>
                </div>
              </div>
            </div>
            <!-- Error message -->
            <div v-if="error" class="rounded-md bg-red-50 p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <!-- Optional: add an error icon -->
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-red-800">
                    Chyba pri prihlásení
                  </h3>
                  <div class="mt-2 text-sm text-red-700">
                    {{ error }}
                  </div>
                </div>
              </div>
            </div>
          </form>
          <div class="mt-6">
            <div class="relative">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">
                  Alebo pokračujte cez
                </span>
              </div>
            </div>
            <div class="mt-6">
              <button @click="googleLogin" :disabled="loading"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                {{ loading ? 'Načítavam...' : 'Prihlásiť sa cez Google' }}
              </button>
            </div>
          </div>
          <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
              Nemáte ešte účet?
              <NuxtLink to="/register" class="font-medium text-blue-600 hover:text-blue-500">
                Zaregistrujte sa
              </NuxtLink>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Fallback for SSR -->
    <template #fallback>
      <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
          <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Prihlásenie do účtu
          </h2>
          <p class="mt-2 text-center text-sm text-gray-600">
            Načítavam prihlasovací formulár...
          </p>
        </div>
      </div>
    </template>
  </ClientOnly>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '~/stores/auth';
import { useApi } from '~/composables/useApi';
import { navigateTo } from '#app';

const authStore = useAuthStore();
const { fetchApi } = useApi();

const email = ref('');
const password = ref('');
const twoFaCode = ref('');
const error = ref('');
const prompt = ref('');
const loading = ref(false);
// Flag to indicate whether the 2FA field should be shown
const show2fa = ref(false);

const login = async () => {
  loading.value = true;
  error.value = '';
  prompt.value = ''; // Clear previous prompt

  // If the 2FA field is not visible, send an empty 2FA code
  const response = await authStore.login(
    email.value,
    password.value,
    show2fa.value ? twoFaCode.value : ''
  );
  console.log('Login response:', response);

  // Check if the response indicates that 2FA is required.
  // Instead of setting an error, we set a green prompt.
  if (!show2fa.value && response.message && response.message.toLowerCase().includes('2fa required')) {
    show2fa.value = true;
    prompt.value = 'Zadajte prosím 2FA kód';
  } else if (response.success) {
    await navigateTo('/restricted');
  } else {
    error.value = response.message || 'Prihlásenie zlyhalo';
  }
  loading.value = false;
};

// const googleLogin = async () => {
//   loading.value = true;
//   const response = await fetchApi('/oauth2callback.php', { method: 'GET' });
//   if (response.success && response.auth_url) {
//     window.location.href = response.auth_url;
//   } else {
//     error.value = 'Google login failed';
//   }
//   loading.value = false;
// };

const googleLogin = async () => {
  loading.value = true;
  const response = await $fetch('https://node87.webte.fei.stuba.sk/nobels/backend/oauth2callback.php', {
    method: 'GET',
  });
  if (response.success && response.auth_url) {
    window.location.href = response.auth_url;
  } else {
    error.value = 'Google login failed';
  } 
  loading.value = false;
};
</script>
