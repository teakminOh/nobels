<template>
  <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Resetovať heslo</h2>
    
    <div class="mb-6">
      <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Emailová adresa</label>
      <input 
        id="email"
        v-model="email" 
        type="email" 
        placeholder="Enter your email" 
        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        :class="{'border-red-500': errorMessage}"
      />
    </div>
    
    <button 
      @click="sendResetLink" 
      class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md shadow transition duration-150 ease-in-out"
    >
      Poslať resetovací link
    </button>
    
    <div v-if="message" class="mt-4 p-3 bg-green-50 text-green-700 rounded-md">
      {{ message }}
    </div>
    
    <div v-if="errorMessage" class="mt-4 p-3 bg-red-50 text-red-700 rounded-md">
      {{ errorMessage }}
    </div>
    
    <p class="mt-6 text-center text-gray-600 text-sm">
      Pamätáte si heslo? 
      <NuxtLink to ="/login" class="text-blue-600 hover:text-blue-800 font-medium">Prihláste sa</NuxtLink>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useApi } from '~/composables/useApi';

const email = ref('');
const message = ref('');
const errorMessage = ref('');
const { fetchApi } = useApi();

async function sendResetLink() {
  try {
    // Reset messages
    message.value = '';
    errorMessage.value = '';
    
    // Make the API request
    const response = await fetchApi('/forgot-password.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded', // Match backend expectation
      },
      body: new URLSearchParams({ email: email.value }), // Serialize body as form data
    });
    
    // Handle successful response
    message.value = response.message; // Access response.message directly, not data.value.message
  } catch (error) {
    // Handle errors (e.g., CORS errors, network errors, or non-200 status codes)
    errorMessage.value = error.message || 'An error occurred. Please try again later.';
    console.error('Error:', error);
  }
}
</script>