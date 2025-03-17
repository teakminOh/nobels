// middleware/auth.js
import { useAuthStore } from "../stores/auth";

export default defineNuxtRouteMiddleware((to, from) => {
  // Run on client-side only
  if (import.meta.client) {
    const authStore = useAuthStore();
    
    console.log('Auth middleware - checking token');
    
    // If there's a token in localStorage but user isn't logged in, validate the token
    const storedToken = localStorage.getItem('auth_token');
    if (storedToken && !authStore.isLoggedIn) {
      // Try to validate and set the token
      authStore.validateStoredToken(storedToken);
    }
    
    // Define public routes that anyone can access (static routes)
    const publicRoutes = ['/login', '/register', '/', '/auth/forgot-password', '/auth/reset-password'];
    
    // Check if the current route is public
    // Allow any route that exactly matches a public route or starts with "/details/"
    const isPublicRoute = publicRoutes.includes(to.path) || to.path.startsWith('/details/');
    
    // After validation attempt, check authentication status and route
    if (authStore.isLoggedIn) {
      // User is logged in
      if (['/login', '/register'].includes(to.path)) {
        // Prevent logged-in users from accessing login/register pages
        console.log('Already authenticated, redirecting to main page');
        return navigateTo('/');
      }
    } else {
      // User is not logged in
      if (!isPublicRoute) {
        // Prevent accessing protected routes when not logged in
        console.log('Not authenticated, redirecting to login');
        return navigateTo('/login');
      }
    }
  }
});
