export const useApi = () => {
  const baseURL = 'https://node87.webte.fei.stuba.sk/backend'; // Relative path to the backend

  const fetchApi = async (endpoint, options = {}) => {
    return await $fetch(`${baseURL}${endpoint}`, {
      ...options,
      credentials: 'include', // Send PHPSESSID
      headers: { ...options.headers, 'Accept': 'application/json' }
    });
  };

  return {
    async getLaureates(params = {}) {
      return await fetchApi('/api.php', { query: params });
    },
    async getLaureateById(id) {
      return await fetchApi('/details.php', { query: { id } });
    },
    fetchApi,
  };
};