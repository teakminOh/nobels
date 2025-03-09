import { defineStore } from 'pinia';
import { useApi } from '~/composables/useApi';
import { jwtDecode } from 'jwt-decode'; // Use named export

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null,
    isLoggedIn: false,
    fullname: null,
    email: null,
    created_at: null,
    gid: null
  }),
  actions: {
    async login(email, password, twoFaCode) {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/login.php', {
          method: 'POST',
          body: JSON.stringify({ email, password, '2fa': twoFaCode })
        });
        console.log('Login response:', response);
        if (response.success) {
          this.setToken(response.token);
        }
        return response;
      } catch (err) {
        console.error('Login error:', err);
        return { success: false, message: 'Chyba pri prihlásení' };
      }
    },
    setToken(token) {
      try {
        const decoded = jwtDecode(token); // Named export
        const now = Date.now() / 1000;
        if (decoded.exp && decoded.exp > now) {
          this.token = token;
          this.isLoggedIn = true;
          this.fullname = decoded.fullname || null;
          this.email = decoded.email || null;
          this.created_at = decoded.created_at || null;
          this.gid = decoded.gid || null;
          localStorage.setItem('auth_token', token);
        } else {
          this.clearState();
          console.log('Token expired on set');
        }
      } catch (err) {
        console.error('Invalid token:', err);
        this.clearState();
      }
    },
    logout() {
      this.clearState();
      localStorage.removeItem('auth_token');
    },
    clearState() {
      this.token = null;
      this.isLoggedIn = false;
      this.fullname = null;
      this.email = null;
      this.created_at = null;
      this.gid = null;
    },
    isTokenValid() {
      if (!this.token) {
        this.isLoggedIn = false;
        return false;
      }
      try {
        const decoded = jwtDecode(this.token); // Named export
        const now = Date.now() / 1000;
        const isValid = decoded.exp && decoded.exp > now;
        this.isLoggedIn = isValid;
        return isValid;
      } catch (err) {
        console.error('Token decode error:', err);
        this.isLoggedIn = false;
        return false;
      }
    },
    validateStoredToken(token) {
      try {
        const decoded = jwtDecode(token); // Named export
        const now = Date.now() / 1000;
        if (decoded.exp && decoded.exp > now) {
          this.token = token;
          this.isLoggedIn = true;
          this.fullname = decoded.fullname || null;
          this.email = decoded.email || null;
          this.created_at = decoded.created_at || null;
          this.gid = decoded.gid || null;
        } else {
          this.clearState();
          console.log('Stored token expired');
        }
      } catch (err) {
        console.error('Stored token invalid:', err);
        this.clearState();
      }
    }
  },
  hydrate(store) {
    const storedToken = localStorage.getItem('auth_token');
    if (storedToken) {
      // Just set the token first
      store.token = storedToken;
    }
  }
});