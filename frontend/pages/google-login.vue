<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Prihlásenie cez Google</h1>
    <button
      @click="startGoogleLogin"
      class="bg-red-500 text-white p-2 rounded"
      :disabled="loading"
    >
      {{ loading ? 'Načítavam...' : 'Prihlásiť sa cez Google' }}
    </button>
    <p v-if="error" class="text-red-600 mt-2">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useApi } from '~/composables/useApi';
import { useRouter } from 'vue-router';

const { fetchApi } = useApi();
const router = useRouter();

const loading = ref(false);
const error = ref('');

const startGoogleLogin = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await fetchApi('/oauth2callback.php', {
      method: 'GET',
      credentials: 'include'
    });
    if (response.success && response.auth_url) {
      window.location.href = response.auth_url; // Redirect to Google auth page
    } else {
      error.value = response.message || 'Nepodarilo sa spustiť prihlásenie cez Google.';
    }
  } catch (err) {
    error.value = 'Chyba: ' + err.message;
  } finally {
    loading.value = false;
  }
};

// Handle callback (this page will reload after Google redirect)
onMounted(async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const code = urlParams.get('code');
  if (code) {
    try {
      const response = await fetchApi('/oauth2callback.php?code=' + code, {
        method: 'GET',
        credentials: 'include'
      });
      if (response.success) {
        router.push('/restricted'); // Redirect to restricted page on success
      } else {
        error.value = response.message || 'Prihlásenie cez Google zlyhalo.';
      }
    } catch (err) {
      error.value = 'Chyba pri spracovaní odpovede: ' + err.message;
    }
  }
});
</script>