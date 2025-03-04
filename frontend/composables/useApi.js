export const useApi = () => {
  const baseURL = 'https://node87.webte.fei.stuba.sk/nobels/backend';
  return {
    async getLaureates(params = {}) {
      return await $fetch(`${baseURL}/api.php`, { query: params });
    },
    async getLaureateById(id) {
      return await $fetch(`${baseURL}/details.php`, { query: { id } });
    }
  };
};