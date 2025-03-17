<template>
  <div class="container min-h-screen mx-auto p-12 max-w-xl">
    <div class="bg-white rounded-lg shadow-lg p-8">
      <header class="text-center mb-8">
        <h1 class="mb-6 text-center text-3xl font-extrabold text-gray-900">Registrácia</h1>
        <h2 class="text-gray-600">Vytvorenie nového používateľského konta</h2>
      </header>
      <main>
        <form @submit.prevent="register" class="space-y-6">
          <div>
            <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">Meno:</label>
            <input
              id="firstname"
              v-model="firstname"
              type="text"
              class="border border-gray-300 p-3 w-full rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="napr. John"
              required
            >
            <p v-if="errors.firstname" class="text-red-600 text-sm mt-1">{{ errors.firstname }}</p>
          </div>
          <div>
            <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Priezvisko:</label>
            <input
              id="lastname"
              v-model="lastname"
              type="text"
              class="border border-gray-300 p-3 w-full rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="napr. Doe"
              required
            >
            <p v-if="errors.lastname" class="text-red-600 text-sm mt-1">{{ errors.lastname }}</p>
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail:</label>
            <input
              id="email"
              v-model="email"
              type="email"
              class="border border-gray-300 p-3 w-full rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="napr. johndoe@example.com"
              required
            >
            <p v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email }}</p>
          </div>
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Heslo:</label>
            <input
              id="password"
              v-model="password"
              type="password"
              class="border border-gray-300 p-3 w-full rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
            >
            <p v-if="errors.password" class="text-red-600 text-sm mt-1">{{ errors.password }}</p>
          </div>
          <button
            type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md shadow transition duration-150 ease-in-out"
            :disabled="loading"
          >
            {{ loading ? 'Registrujem...' : 'Vytvoriť konto' }}
          </button>
        </form>
        
        <div v-if="success" class="mt-8 p-4 bg-green-50 border border-green-200 rounded-md">
          <p class="text-green-700 font-medium">{{ message }}</p>
          <div class="mt-4">
            <p class="mb-2">Zadajte kód: <strong class="font-mono bg-gray-100 p-1 rounded">{{ secret }}</strong> do aplikácie pre 2FA</p>
            <p>alebo naskenujte QR kód:</p>
            <div class="flex justify-center my-4">
              <img :src="qrCode" alt="QR kód pre 2FA" class="border p-2 rounded-md shadow-sm">
            </div>
            <p class="text-center">
              Teraz sa môžete
              <NuxtLink to="/login" class="text-blue-600 hover:text-blue-800 font-medium">prihlásiť</NuxtLink>.
            </p>
          </div>
        </div>
        
        <p v-else-if="error" class="mt-6 p-3 bg-red-50 text-red-700 rounded-md text-center">{{ error }}</p>
        
        <p v-if="!success" class="mt-6 text-center text-gray-600">
          Už máte konto?
          <NuxtLink to="/login" class="text-blue-600 hover:text-blue-800 font-medium">Prihláste sa tu.</NuxtLink>
        </p>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useApi } from '~/composables/useApi';

const { fetchApi } = useApi();

const firstname = ref('');
const lastname = ref('');
const email = ref('');
const password = ref('');
const loading = ref(false);
const success = ref(false);
const message = ref('');
const qrCode = ref('');
const secret = ref('');
const error = ref('');
const errors = ref({});

const register = async () => {
  loading.value = true;
  success.value = false;
  error.value = '';
  errors.value = {};

  try {
    const response = await fetchApi('/register.php', {
      method: 'POST',
      body: JSON.stringify({
        firstname: firstname.value,
        lastname: lastname.value,
        email: email.value,
        password: password.value
      }),
      credentials: 'include'
    });

    if (response.success) {
      success.value = true;
      message.value = response.message;
      qrCode.value = response.qr_code;
      secret.value = response.secret;
    } else {
      if (response.errors) {
        response.errors.forEach(err => {
          if (err.includes('meno')) errors.value.firstname = err;
          else if (err.includes('priezvisko')) errors.value.lastname = err;
          else if (err.includes('e-mail')) errors.value.email = err;
          else if (err.includes('heslo')) errors.value.password = err;
        });
      }
      error.value = response.message || 'Registrácia zlyhala';
    }
  } catch (err) {
    error.value = 'Chyba pri registrácii: ' + err.message;
  } finally {
    loading.value = false;
  }
};
</script>