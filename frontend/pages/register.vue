<template>
  <div class="container mx-auto p-4">
    <header>
      <h1 class="text-2xl font-bold mb-6">Registrácia</h1>
      <h2 class="text-lg mb-4">Vytvorenie nového používateľského konta</h2>
    </header>
    <main>
      <form @submit.prevent="register" class="space-y-4">
        <div>
          <label for="firstname" class="block">Meno:</label>
          <input
            id="firstname"
            v-model="firstname"
            type="text"
            class="border p-2 w-full"
            placeholder="napr. John"
            required
          >
          <p v-if="errors.firstname" class="text-red-600 text-sm">{{ errors.firstname }}</p>
        </div>
        <div>
          <label for="lastname" class="block">Priezvisko:</label>
          <input
            id="lastname"
            v-model="lastname"
            type="text"
            class="border p-2 w-full"
            placeholder="napr. Doe"
            required
          >
          <p v-if="errors.lastname" class="text-red-600 text-sm">{{ errors.lastname }}</p>
        </div>
        <div>
          <label for="email" class="block">E-mail:</label>
          <input
            id="email"
            v-model="email"
            type="email"
            class="border p-2 w-full"
            placeholder="napr. johndoe@example.com"
            required
          >
          <p v-if="errors.email" class="text-red-600 text-sm">{{ errors.email }}</p>
        </div>
        <div>
          <label for="password" class="block">Heslo:</label>
          <input
            id="password"
            v-model="password"
            type="password"
            class="border p-2 w-full"
            required
          >
          <p v-if="errors.password" class="text-red-600 text-sm">{{ errors.password }}</p>
        </div>
        <button
          type="submit"
          class="bg-blue-500 text-white p-2 rounded"
          :disabled="loading"
        >
          {{ loading ? 'Registrujem...' : 'Vytvoriť konto' }}
        </button>
      </form>
      <div v-if="success" class="mt-4">
        <p class="text-green-600">{{ message }}</p>
        <p>Zadajte kód: <strong>{{ secret }}</strong> do aplikácie pre 2FA</p>
        <p>alebo naskenujte QR kód:</p>
        <img :src="qrCode" alt="QR kód pre 2FA" class="mt-2">
        <p>
          Teraz sa môžete
          <NuxtLink to="/login" class="text-blue-600 hover:underline">prihlásiť</NuxtLink>.
        </p>
      </div>
      <p v-else-if="error" class="text-red-600 mt-4">{{ error }}</p>
      <p v-if="!success" class="mt-4">
        Už máte konto?
        <NuxtLink to="/login" class="text-blue-600 hover:underline">Prihláste sa tu.</NuxtLink>
      </p>
    </main>
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