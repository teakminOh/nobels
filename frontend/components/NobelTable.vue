<!-- NobelTable.vue -->
<template>
  <table class="w-full">
    <thead>
      <tr class="bg-gray-100">
        <th class="border p-2 text-left cursor-pointer" @click="emit('sort', 'surname')">Name</th>
        <th v-if="!selectedYear" class="border p-2 text-left">Years</th>
        <th class="border p-2 text-left">Countries</th>
        <th v-if="!selectedCategory" class="border p-2 text-left">Categories</th>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="laureate in laureates"
        :key="laureate.id"
        @click="navigateToDetails(laureate.id)"
        class="hover:bg-gray-100 cursor-pointer"
      >
        <td class="border p-2">
          <NuxtLink :to="getDetailsLink(laureate.id)" class="text-blue-600 hover:underline">
            {{ laureate.fullname || laureate.organisation }}
          </NuxtLink>
        </td>
        <td v-if="!selectedYear" class="border p-2">
          {{ laureate.prizes.map(prize => prize.year).join(', ') }}
        </td>
        <td class="border p-2">{{ laureate.countries }}</td>
        <td v-if="!selectedCategory" class="border p-2">
          {{ [...new Set(laureate.prizes.map(prize => prize.category))].join(', ') }}
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script setup>
const props = defineProps({
  laureates: { type: Array, required: true },
  selectedYear: { type: String, default: '' },
  selectedCategory: { type: String, default: '' },
  page: { type: Number, default: 1 },
  perPage: { type: [Number, String], default: 10 }
});

const emit = defineEmits(['sort']);

const getDetailsLink = (id) => ({
  path: `/details/${id}`,
  query: {
    page: props.page,
    perPage: props.perPage
  }
});

const navigateToDetails = (id) => {
  navigateTo(getDetailsLink(id));
};
</script>