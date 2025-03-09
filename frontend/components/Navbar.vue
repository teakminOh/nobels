<template>
  <nav class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex items-center">
          <NuxtLink to="/" class="flex-shrink-0 flex items-center">
            <span class="text-white font-bold text-xl tracking-tight">Nobel Laureates</span>
          </NuxtLink>
        </div>
        
        <ClientOnly>
          <div class="flex items-center">
            <div class="hidden md:flex md:items-center md:space-x-6">
              <NuxtLink v-if="authStore.isLoggedIn" to="/dashboard" class="text-blue-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                Nastavenia
              </NuxtLink>
              
              <NuxtLink 
                v-if="authStore.isLoggedIn" 
                to="/restricted" 
                class="text-blue-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
              >
                Info
              </NuxtLink>
              
              <div v-if="authStore.isLoggedIn" class="flex items-center border-l border-blue-400 pl-4 ml-2">
                <div class="w-8 h-8 rounded-full bg-blue-200 flex items-center justify-center text-blue-800 font-bold mr-2">
                  {{ authStore.fullname ? authStore.fullname.charAt(0).toUpperCase() : 'U' }}
                </div>
                <span class="text-white font-medium">{{ authStore.fullname }}</span>
              </div>
              
              <button
                v-if="authStore.isLoggedIn"
                @click="logout"
                class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-700 focus:ring-white transition duration-150 ease-in-out"
              >
                Odhlásiť sa
              </button>
              
              <NuxtLink
                v-else
                to="/login"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-700 focus:ring-white transition duration-150 ease-in-out"
              >
                Prihlásiť sa
              </NuxtLink>
            </div>
            
            <!-- Mobile menu button -->
            <div class="flex md:hidden">
              <button @click="mobileMenuOpen = !mobileMenuOpen" class="inline-flex items-center justify-center p-2 rounded-md text-blue-100 hover:text-white focus:outline-none">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path v-if="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
            </div>
          </div>
        </ClientOnly>
      </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state -->
    <ClientOnly>
      <div v-if="mobileMenuOpen" class="md:hidden bg-indigo-800">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
          <NuxtLink to="/dashboard" class="text-blue-100 hover:text-white block px-3 py-2 rounded-md text-base font-medium">
            Nastavenia
          </NuxtLink>
          
          <NuxtLink 
            v-if="authStore.isLoggedIn" 
            to="/restricted" 
            class="text-blue-100 hover:text-white block px-3 py-2 rounded-md text-base font-medium"
          >
            Info
          </NuxtLink>
        </div>
        
        <div v-if="authStore.isLoggedIn" class="pt-4 pb-3 border-t border-blue-400">
          <div class="flex items-center px-5">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-blue-200 flex items-center justify-center text-blue-800 font-bold text-lg">
                {{ authStore.fullname ? authStore.fullname.charAt(0).toUpperCase() : 'U' }}
              </div>
            </div>
            <div class="ml-3">
              <div class="text-base font-medium text-white">{{ authStore.fullname }}</div>
            </div>
          </div>
          <div class="mt-3 px-2 space-y-1">
            <button
              @click="logout"
              class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-blue-100 hover:text-white"
            >
              Odhlásiť sa
            </button>
          </div>
        </div>
        
        <div v-else class="px-2 pt-2 pb-3">
          <NuxtLink
            to="/login"
            class="block w-full text-center px-3 py-2 rounded-md text-base font-medium text-white bg-indigo-600 hover:bg-indigo-500"
          >
            Prihlásiť sa
          </NuxtLink>
        </div>
      </div>
    </ClientOnly>
  </nav>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();
const mobileMenuOpen = ref(false);

const logout = async () => {
  authStore.logout();
  await navigateTo('/login');
};
</script>