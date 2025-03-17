import { useAuthStore } from "../stores/auth";

export default defineNuxtPlugin(async (nuxtApp) => {
  const authStore = useAuthStore();

  if (import.meta.client) {
    const url = new URL(window.location.href);
    const tokenFromUrl = url.searchParams.get('token');
    if (tokenFromUrl) {
      // Save token from URL (Google OAuth callback) to localStorage
      localStorage.setItem('auth_token', tokenFromUrl);
      authStore.validateStoredToken(tokenFromUrl);
      
      // Optionally remove the token from the URL to clean it up
      url.searchParams.delete('token');
      window.history.replaceState(null, '', url.toString());
    } else {
      // If no token in the URL, check localStorage
      const storedToken = localStorage.getItem('auth_token');
      if (storedToken) {
        authStore.validateStoredToken(storedToken);
      }
    }
  }

  return {
    provide: {
      auth: authStore
    }
  };
});
