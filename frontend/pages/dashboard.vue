<template>
  <div class="min-h-screen bg-gray-50 flex flex-col py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h1 class="text-center text-3xl font-extrabold text-gray-900 mb-8">Dashboard</h1>

      <!-- Name Change Form -->
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Zmeniť meno</h2>
        <form @submit.prevent="updateFullname">
          <div class="mb-4">
            <label for="fullname" class="block text-sm font-medium text-gray-700">Nové meno</label>
            <input
              id="fullname"
              v-model="fullname"
              type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
              required
            />
          </div>
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            {{ loading ? 'Ukladám...' : 'Uložiť' }}
          </button>
          <p v-if="fullnameError" class="mt-2 text-sm text-red-600">{{ fullnameError }}</p>
        </form>
      </div>

      <!-- Password Change Form -->
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Zmeniť heslo</h2>
        <form @submit.prevent="updatePassword">
          <div class="mb-4">
            <label for="currentPassword" class="block text-sm font-medium text-gray-700">Súčasné heslo</label>
            <input
              id="currentPassword"
              v-model="currentPassword"
              type="password"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
              required
            />
          </div>
          <div class="mb-4">
            <label for="newPassword" class="block text-sm font-medium text-gray-700">Nové heslo</label>
            <input
              id="newPassword"
              v-model="newPassword"
              type="password"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
              required
            />
          </div>
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            {{ loading ? 'Ukladám...' : 'Uložiť' }}
          </button>
          <p v-if="passwordError" class="mt-2 text-sm text-red-600">{{ passwordError }}</p>
        </form>
      </div>

      <!-- Login History -->
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">História prihlásení</h2>
        <div v-if="loadingHistory" class="flex justify-center py-4">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>
        <div v-else-if="authStore.loginHistory.length">
          <ul class="space-y-2">
            <li v-for="entry in authStore.loginHistory" :key="entry.id" class="text-gray-700">
              {{ entry.login_time }} - {{ entry.login_type === 'google' ? 'Google' : 'Lokálne' }}
            </li>
          </ul>
        </div>
        <p v-else class="text-gray-500">Žiadna história prihlásení.</p>
        <p v-if="historyError" class="mt-2 text-sm text-red-600">{{ historyError }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();

// Name Change
const fullname = ref(authStore.fullname || '');
const fullnameError = ref('');
const loading = ref(false);

// Password Change
const currentPassword = ref('');
const newPassword = ref('');
const passwordError = ref('');

// Login History
const loadingHistory = ref(false);
const historyError = ref('');

const updateFullname = async () => {
  loading.value = true;
  fullnameError.value = '';
  const result = await authStore.updateFullname(fullname.value);
  loading.value = false;
  if (!result.success) fullnameError.value = result.message;
};

const updatePassword = async () => {
  loading.value = true;
  passwordError.value = '';
  const result = await authStore.updatePassword(currentPassword.value, newPassword.value);
  loading.value = false;
  if (result.success) {
    currentPassword.value = '';
    newPassword.value = '';
  } else {
    passwordError.value = result.message;
  }
};

onMounted(async () => {
  loadingHistory.value = true;
  const result = await authStore.fetchLoginHistory();
  loadingHistory.value = false;
  if (!result.success) historyError.value = result.message;
});
</script>
