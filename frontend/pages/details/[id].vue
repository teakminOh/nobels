<template>
  <div class="max-w-2xl mx-auto mt-6 p-6 bg-white shadow-md rounded-lg">
    <NuxtLink
      :to="{ path: '/', query: { fromPage: route.query.fromPage, fromPerPage: route.query.fromPerPage }}"
      class="-ml-4 -mt-4 mb-4 flex items-center gap-2 text-blue hover:underline"
    >
    <svg
              xmlns="http://www.w3.org/2000/svg"
              width="40"
              height="40"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1"
            >
              <path d="M15 18l-6-6 6-6"/>
            </svg>
     <span>Back to Table</span>
    </NuxtLink>
    <h1 v-if="laureate?.fullname && laureate.fullname !== 'N/A'" class="text-2xl font-bold mb-6">
      {{ laureate.fullname }}
    </h1>
    <h1 v-else-if="laureate?.organisation && laureate.organisation !== 'N/A'" class="text-2xl font-bold mb-6">
      {{ laureate.organisation }}
    </h1>
    <h1 v-else class="text-2xl font-bold mb-6">Laureate Details</h1>

    <div v-if="laureate" class="space-y-4">
      <p v-if="laureate.sex && laureate.sex !== 'N/A'">
        <strong class="font-semibold">Sex:</strong> {{ laureate.sex }}
      </p>
      <p v-if="laureate.birth_year && laureate.birth_year !== 'N/A'">
        <strong class="font-semibold">Birth Year:</strong> {{ laureate.birth_year }}
      </p>
      <p v-if="laureate.death_year && laureate.death_year !== 'N/A'">
        <strong class="font-semibold">Death Year:</strong> {{ laureate.death_year }}
      </p>
      <p>
        <strong class="font-semibold">Countries:</strong> {{ laureate.countries || 'N/A' }}
      </p>

      <div v-if="laureate.prizes && laureate.prizes.length">
        <strong class="font-semibold">Prizes:</strong>
        <ul class="list-disc pl-5 mt-1 space-y-2">
          <li v-for="(prize, index) in laureate.prizes" :key="index">
            <span>{{ prize.year || 'N/A' }}</span> - 
            <span class="capitalize">{{ prize.category || 'N/A' }}</span>
            <div class="font-medium mt-1">
              <p><strong>Contribution (EN):</strong> {{ prize.contrib_en || 'Not Available' }}</p>
              <p><strong>Contribution (SK):</strong> {{ prize.contrib_sk || 'Not Available' }}</p>
            </div>
          </li>
        </ul>
      </div>
      <div v-else>
        <strong class="font-semibold">Prizes:</strong> No prizes found
      </div>
    </div>

    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <div v-else class="text-gray-600">Loading...</div>
  </div>
</template>

<script setup>
const route = useRoute();
const { getLaureateById } = useApi();
const laureate = ref(null);
const error = ref(null);

onMounted(async () => {
  try {
    const data = await getLaureateById(route.params.id);
    if (data.error) throw new Error(data.error);
    laureate.value = data.laureate;
    if (!laureate.value) throw new Error('Laureate not found');
    console.log('Laureate data:', JSON.stringify(laureate.value, null, 2)); // Debug output
  } catch (e) {
    error.value = e.message || 'Failed to load details';
    console.error('Error loading laureate:', e);
  }
});
</script>