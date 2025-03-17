<template>
  <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Obnovte heslo</h2>
    
    <div class="space-y-4">
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Nové heslo</label>
        <input 
          id="password"
          type="password" 
          v-model="password" 
          placeholder="Enter your new password" 
          class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>
      
      <div>
        <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Potvrďte heslo</label>
        <input 
          id="confirmPassword"
          type="password" 
          v-model="confirmPassword" 
          placeholder="Confirm your new password" 
          class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>
    </div>
    
    <button 
      @click="resetPassword" 
      class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md shadow transition duration-150 ease-in-out mt-6"
    >
      Resetovať heslo
    </button>
    
    <div v-if="message" class="mt-4 p-3 rounded-md text-center" :class="{
      'bg-red-50 text-red-700': message !== 'Password reset successful.',
      'bg-green-50 text-green-700': message === 'Password reset successful.'
    }">
      {{ message }}
    </div>
    
    <p class="mt-6 text-center text-gray-600 text-sm">
      Pamätáte si heslo? 
      <NuxtLink to="/login" class="text-blue-600 hover:text-blue-800 font-medium">Prihláste sa</NuxtLink>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useApi } from '~/composables/useApi';

const route = useRoute()
const token = route.query.token || ''
const password = ref('')
const confirmPassword = ref('')
const message = ref('')
const { fetchApi } = useApi();

async function resetPassword() {
  try {
    if (password.value !== confirmPassword.value) {
      message.value = "Passwords don't match."
      return
    }

    const response = await fetchApi('/reset-password.php', {
      method: 'POST',
      body: {
        token,
        password: password.value
      }
    })

    // Access message directly from response
    message.value = response.message || 'Password reset successful.'

    if (message.value === 'Password reset successful.') {
      setTimeout(() => {
        navigateTo('/auth/login')
      }, 2000)
    }
  } catch (error) {
    message.value = error.message || 'An error occurred. Please try again.'
    console.error('Error resetting password:', error)
  }
}
</script>