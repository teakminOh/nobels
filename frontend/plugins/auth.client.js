import { useAuthStore } from "../stores/auth";

export default defineNuxtPlugin(async (nuxtApp) => {
  const authStore = useAuthStore();
  
  // Check for token in localStorage
  const storedToken = localStorage.getItem('auth_token');
  if (storedToken) {
    // Now we can call the action
    authStore.validateStoredToken(storedToken);
  }
  
  return {
    provide: {
      auth: authStore
    }
  };
});