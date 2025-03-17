<template>
  <div class="min-h-screen bg-gray-50 flex flex-col py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h1 class="text-center text-3xl font-extrabold text-gray-900 mb-8">Nastavenia</h1>

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
      <div v-if="!authStore.isGoogleUser" class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 mb-6">
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
          <p v-if="successPassword" class="mt-2 text-sm text-center text-green-600">{{ successPassword }}</p>
          <p v-if="passwordError" class="mt-2 text-sm text-center text-red-600">{{ passwordError }}</p>
        </form>
      </div>

      <!-- Disable 2FA Section (only for non-Google users) -->
      <div v-if="!authStore.isGoogleUser" class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Spravovať 2FA</h2>
        <button @click="toggle2FA" :disabled="loadingToggle2FA"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white"
          :class="twoFaEnabled ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600'">
          {{ loadingToggle2FA ? (twoFaEnabled ? 'Vypínam 2FA...' : 'Zapínam 2FA...') : (twoFaEnabled ? 'Dočasne vypnúť 2FA' : 'Zapnúť 2FA') }}
        </button>
        <p v-if="toggle2FAMessage" class="mt-2 text-sm text-green-600">{{ toggle2FAMessage }}</p>
        <p v-if="toggle2FAError" class="mt-2 text-sm text-red-600">{{ toggle2FAError }}</p>
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
        <p v-if="historyError" class="mt-2 text-sm text-center text-red-600">{{ historyError }}</p>
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
const successPassword = ref('');
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
    successPassword.value = 'Heslo bolo zmenené.';
  } else {
    passwordError.value = result.message;
  }
};

// Reactive flag indicating if 2FA is currently enabled.
// For example, if the user record contains a 2FA secret, set it to true.
const twoFaEnabled = ref(true); // Adjust initial value as needed
const loadingToggle2FA = ref(false);
const toggle2FAMessage = ref('');
const toggle2FAError = ref('');
// Toggle function: if enabled, disable; if disabled, reset (enable) 2FA.
const toggle2FA = async () => {
  loadingToggle2FA.value = true;
  toggle2FAMessage.value = '';
  toggle2FAError.value = '';

  try {
    let response;
    if (twoFaEnabled.value) {
      // 2FA is enabled: disable it
      response = await authStore.disable2FA();
      if (response.success) {
        toggle2FAMessage.value = response.message || '2FA bolo vypnuté.';
        twoFaEnabled.value = false;
      } else {
        toggle2FAError.value = response.message || 'Nepodarilo sa vypnúť 2FA.';
      }
    } else {
      // 2FA is disabled: reset (enable) it.
      response = await authStore.reset2FA();
      if (response.success) {
        toggle2FAMessage.value = response.message || '2FA bolo zapnuté.';
        twoFaEnabled.value = true;
      } else {
        toggle2FAError.value = response.message || 'Nepodarilo sa zapnúť 2FA.';
      }
    }
  } catch (err) {
    console.error('Toggle 2FA error:', err);
    toggle2FAError.value = 'Chyba pri prepínaní 2FA.';
  } finally {
    loadingToggle2FA.value = false;
  }
};

onMounted(async () => {
  loadingHistory.value = true;
  const result = await authStore.fetchLoginHistory();
  loadingHistory.value = false;
  if (!result.success) historyError.value = result.message;
});
</script>
