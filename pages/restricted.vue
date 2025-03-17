<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h1 class="text-center text-3xl font-extrabold text-gray-900 mb-2">
        Zabezpečená stránka
      </h1>
      <h2 class="text-center text-lg text-gray-600 mb-8">
        Obsah tejto stránky je dostupný len po prihlásení.
      </h2>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>

        <div v-else-if="authStore.isLoggedIn" class="space-y-6">
          <div class="pb-3 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">
              Vitaj {{ authStore.fullname }}
            </h3>
          </div>
          
          <div class="space-y-3">
            <p class="text-gray-700">
              <span class="font-medium">E-mail:</span> {{ authStore.email }}
            </p>
            
            <div v-if="authStore.gid" class="text-gray-700">
              <p class="font-medium">Si prihlásený cez Google účet</p>
              <p class="text-sm text-gray-500">ID: {{ authStore.gid }}</p>
            </div>
            
            <div v-else class="text-gray-700">
              <p class="font-medium">Si prihlásený cez lokálne údaje</p>
              <p><span class="text-sm text-gray-500">Dátum vytvorenia konta:</span> {{ authStore.created_at }}</p>
            </div>
          </div>
          
          <div class="pt-4 flex items-center justify-between">
            <button 
              @click="logout" 
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Odhlásenie
            </button>
            
            <NuxtLink 
              to="/" 
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Úvodná stránka
            </NuxtLink>
          </div>
        </div>

        <div v-else class="space-y-6">
          <div class="bg-red-50 border-l-4 border-red-400 p-4" v-if="error">
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
          
          <div class="text-center">
            <p class="mb-4 text-gray-700">Nie ste prihlásený.</p>
            
            <div class="space-y-3">
              <NuxtLink 
                to="/login" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Prihlásiť sa lokálne
              </NuxtLink>
              
              <NuxtLink 
                to="/google-login" 
                class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <span>Prihlásiť sa cez Google</span>
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '~/stores/auth';


const authStore = useAuthStore();
const loading = ref(true);
const error = ref('');

onMounted(() => {
  console.log('Restricted: Mounted - isLoggedIn:', authStore.isLoggedIn, 'token:', authStore.token);
  loading.value = false;
  if (!authStore.isLoggedIn) {
    error.value = 'Nie ste prihlásený.';
    console.log('Restricted: Not logged in, error set');
  } else {
    console.log('Restricted: Logged in, displaying content');
  }
});

const logout = async () => {
  authStore.logout(); // Clears JWT client-side
  await navigateTo('/login');
};
</script>