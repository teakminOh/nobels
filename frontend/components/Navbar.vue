<template>
  <nav class="flex justify-between items-center bg-blue-100 p-2">
    <NuxtLink to="/" class="hover:underline font-bold">Nobel laureates</NuxtLink>
    <div>
      <ClientOnly>
        <div class="flex gap-4 justify-end items-center">
          <NuxtLink v-if="authStore.isLoggedIn" to="/restricted" class="hover:underline font-bold text-blue-800">Info</NuxtLink>
          <span v-if="authStore.isLoggedIn">Vitaj, {{ authStore.fullname }}!</span>
          <NuxtLink
            v-if="authStore.isLoggedIn"
            to="/"
            class="p-3 bg-blue-200 rounded-lg hover:underline"
            @click.prevent="logout"
          >
            Odhlásiť sa
          </NuxtLink>
          <NuxtLink
            v-else
            to="/login"
            class="hover:underline"
          >
            Prihlásiť sa
          </NuxtLink>
        </div>
      </ClientOnly>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();

const logout = async () => {
  authStore.logout();
  await navigateTo('/login');
};
</script>