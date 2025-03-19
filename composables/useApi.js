export const useApi = () => {
  const baseURL = 'https://node87.webte.fei.stuba.sk/backend'; // Your backend URL

  const fetchApi = async (endpoint, options = {}) => {
    return await $fetch(`${baseURL}${endpoint}`, {
      ...options,
      credentials: 'include',
      headers: { ...options.headers, 'Accept': 'application/json' }
    });
  };

  async function deletePrize(prizeId, laureateId) {
    return await fetchApi('/deletePrize.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ prizeId, laureateId })
    });
  }

  async function deleteLaureate({ id }) {
    return await fetchApi('/deleteLaureate.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
  }
  

  return {
    async getLaureates(params = {}) {
      return await fetchApi('/api.php', { query: params });
    },
    async getLaureateById(id) {
      return await fetchApi('/details.php', { query: { id } });
    },
    async updateLaureate(payload) {
      return await fetchApi('/updateLaureate.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
    },
    deletePrize,
    deleteLaureate,  // Added function to delete a laureate
    fetchApi,
  };
};
