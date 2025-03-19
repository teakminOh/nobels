import { defineStore } from 'pinia';
import { useApi } from '~/composables/useApi';
import { jwtDecode } from 'jwt-decode';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null,
    isLoggedIn: false,
    fullname: null,
    email: null,
    created_at: null,
    gid: null,
    loginHistory: [], // New state for login history
    type: null
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
        const decoded = jwtDecode(token);
          const now = Date.now() / 1000;
          if (decoded.exp && decoded.exp > now) {
            this.token = token;
            this.isLoggedIn = true;
            this.fullname = decoded.fullname || null;
            this.email = decoded.email || null;
            this.created_at = decoded.created_at || null;
            this.gid = decoded.gid || null;
            localStorage.setItem('auth_token', token);
            console.log('setToken: Token set, isLoggedIn:', this.isLoggedIn);
          } else {
            this.clearState();
            console.log('setToken: Token expired');
          }
        
      } catch (err) {
        console.error('setToken: Invalid token:', err);
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
      this.loginHistory = [];
    },
    isTokenValid() {
      if (!this.token) {
        this.isLoggedIn = false;
        console.log('isTokenValid: No token');
        return false;
      }
      try {
        const decoded = jwtDecode(this.token);
        const now = Date.now() / 1000;
        const isValid = decoded.exp && decoded.exp > now;
        this.isLoggedIn = isValid;
        console.log('isTokenValid: Valid:', isValid);
        return isValid;
      } catch (err) {
        console.error('isTokenValid: Token decode error:', err);
        this.isLoggedIn = false;
        return false;
      }
    },
    validateStoredToken(token) {
      try {
        const decoded = jwtDecode(token);
        if(decoded.type === 'reset'){
          this.clearState();
        }
        else{
        const now = Date.now() / 1000;
        console.log('validateStoredToken: now:', now, 'exp:', decoded.exp);
        if (decoded.exp && decoded.exp > now) {
          this.token = token;
          this.isLoggedIn = true;
          this.fullname = decoded.fullname || null;
          this.email = decoded.email || null;
          this.created_at = decoded.created_at || null;
          this.gid = decoded.gid || null;
          console.log('validateStoredToken: Token valid, isLoggedIn:', this.isLoggedIn);
        } else {
          this.clearState();
          console.log('validateStoredToken: Token expired');
        }
      }
      } catch (err) {
        console.error('validateStoredToken: Invalid token:', err);
        this.clearState();
      }
    },
    async updateFullname(newFullname) {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/update-profile.php', {
          method: 'POST',
          headers: { 'Authorization': `Bearer ${this.token}` },
          body: JSON.stringify({ fullname: newFullname })
        });
        console.log('Update fullname response:', response);
        if (response.success) {
          this.fullname = newFullname;
          // Optionally update token if backend returns a new one
          if (response.token) this.setToken(response.token);
          return { success: true };
        }
        return { success: false, message: response.message || 'Chyba pri zmene mena' };
      } catch (err) {
        console.error('Update fullname error:', err);
        return { success: false, message: 'Chyba pri zmene mena' };
      }
    },
    async reset2FA() {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/reset-2fa.php', {
          method: 'POST',
          headers: { 'Authorization': `Bearer ${this.token}` }
          // No body needed if the endpoint solely relies on the token.
        });
        console.log('Reset 2FA response:', response);
        if (response.success) {
          // Return additional data from the endpoint (QR code URL, new secret)
          return { 
            success: true, 
            message: response.message, 
            qrCodeUrl: response.qrCodeUrl, 
            newSecret: response.newSecret 
          };
        }
        return { success: false, message: response.message || 'Error resetting 2FA' };
      } catch (err) {
        console.error('Reset 2FA error:', err);
        return { success: false, message: 'Error resetting 2FA' };
      }
    },    
    async disable2FA() {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/disable-2fa.php', {
          method: 'POST',
          headers: { 'Authorization': `Bearer ${this.token}` }
          // No body needed if the endpoint solely relies on the token
        });
        console.log('Disable 2FA response:', response);
        if (response.success) {
          return { success: true };
        }
        return { success: false, message: response.message || 'Error disabling 2FA' };
      } catch (err) {
        console.error('Disable 2FA error:', err);
        return { success: false, message: 'Error disabling 2FA' };
      }
    },
    async deleteLaureate(laureateId) {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/deleteLaureate.php', {
          method: 'DELETE',
          headers: { 
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ id: laureateId })
        });
        console.log('Delete laureate response:', response);
        if (response.success) {
          return { success: true, message: response.message || 'Laureate deleted successfully' };
        }
        return { success: false, message: response.error || 'Error deleting laureate' };
      } catch (err) {
        console.error('Delete laureate error:', err);
        return { success: false, message: 'Error deleting laureate' };
      }
    },
    async addLaureate(payload) {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/addLaureate.php', {
          method: 'POST',
          headers: { 
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        console.log('Add laureate response:', response);
        if (response.success) {
          return { 
            success: true, 
            message: response.message || 'Laureate(s) added successfully',
            messages: response.messages || [] // Include prize messages if present
          };
        }
        return { success: false, message: error || 'Error adding laureate' };
      } catch (err) {
        console.error('Add laureate error:', err);
        return { success: false, message: 'Error adding laureate' };
      }
    },
    async updateLaureate(payload) {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/updateLaureate.php', {
          method: 'PUT',
          headers: { 
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        console.log('Update laureate response:', response);
        if (response.success) {
          return { 
            success: true, 
            message: response.message || 'Laureate updated successfully'
          };
        }
        return { success: false, message: response.error || 'Error updating laureate' };
      } catch (err) {
        console.error('Update laureate error:', err);
        return { success: false, message: 'Error updating laureate' };
      }
    },
    async deletePrize(laureateId, prizeId) {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/deletePrize.php', {
          method: 'DELETE',
          headers: { 
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ laureateId, prizeId })
        });
        console.log('Delete prize response:', response);
        if (response.success) {
          return { 
            success: true, 
            message: response.message || 'Prize removed successfully'
          };
        }
        return { success: false, message: response.error || 'Error removing prize' };
      } catch (err) {
        console.error('Delete prize error:', err);
        return { success: false, message: 'Error removing prize' };
      }
    },
    async updatePassword(currentPassword, newPassword) {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/update-password.php', {
          method: 'POST',
          headers: { 'Authorization': `Bearer ${this.token}` },
          body: JSON.stringify({ currentPassword, newPassword })
        });
        console.log('Update password response:', response);
        if (response.success) {
          return { success: true };
        }
        return { success: false, message: response.message || 'Chyba pri zmene hesla' };
      } catch (err) {
        console.error('Update password error:', err);
        return { success: false, message: 'Chyba pri zmene hesla' };
      }
    },
    async fetchLoginHistory() {
      const { fetchApi } = useApi();
      try {
        const response = await fetchApi('/login-history.php', {
          method: 'GET',
          headers: { 'Authorization': `Bearer ${this.token}` }
        });
        console.log('Login history response:', response);
        if (response.success) {
          this.loginHistory = response.history || [];
          return { success: true };
        }
        return { success: false, message: response.message || 'Chyba pri načítaní histórie' };
      } catch (err) {
        console.error('Fetch login history error:', err);
        return { success: false, message: 'Chyba pri načítaní histórie' };
      }
    }
  },
  getters: {
    isGoogleUser() {
      return !!this.gid;
    }
  },
  hydrate(store) {
    const storedToken = localStorage.getItem('auth_token');
    if (storedToken) {
      store.token = storedToken;
      console.log('hydrate: Token set:', storedToken);
    }
  }
});