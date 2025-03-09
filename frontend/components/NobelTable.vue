<!-- NobelTable.vue --> 
<template>
  <div class="overflow-x-auto">
    <!-- Desktop/Tablet View -->
    <table class="w-full table-fixed hidden sm:table">
      <thead>
        <tr class="bg-gray-100">
          <th class="border p-2 text-left cursor-pointer w-2/5" @click="emit('sort', 'surname')">Surname/Organisation</th>
          <th v-if="!selectedYear" class="border p-2 text-left cursor-pointer w-1/5" @click="emit('sort', 'year')">Years</th>
          <th class="border p-2 text-left w-1/5">Countries</th>
          <th v-if="!selectedCategory" class="border p-2 text-left cursor-pointer w-1/5 min-w-1/5" @click="emit('sort', 'category')">Categories</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="laureate in laureates" :key="laureate.id" @click="navigateToDetails(laureate.id)" class="hover:bg-gray-100 cursor-pointer">
          <td class="border p-2 truncate" :title="laureate.fullname || laureate.organisation">
            <NuxtLink :to="getDetailsLink(laureate.id)" class="text-blue-600 hover:underline" @click.prevent>
              {{ displayName(laureate) }}
            </NuxtLink>
          </td>
          <td v-if="!selectedYear" class="border p-2 truncate" :title="laureate.prizes.map(prize => prize.year).join(', ')">
            {{ laureate.prizes.map(prize => prize.year).join(', ') }}
          </td>
          <td class="border p-2 truncate" :title="laureate.countries">
            {{ laureate.countries }}
          </td>
          <td v-if="!selectedCategory" class="border p-2 truncate" :title="[...new Set(laureate.prizes.map(prize => prize.category))].join(', ')">
            {{ [...new Set(laureate.prizes.map(prize => prize.category))].join(', ') }}
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Mobile View -->
    <div class="sm:hidden">
      <div v-for="laureate in laureates" :key="laureate.id" 
           @click="navigateToDetails(laureate.id)"
           class="border-b p-4 mb-2 hover:bg-gray-100 cursor-pointer">
        <div class="mb-2">
          <span class="font-bold">Name: </span>
          <NuxtLink :to="getDetailsLink(laureate.id)" class="text-blue-600 hover:underline" @click.prevent>
            {{ displayName(laureate) }}
          </NuxtLink>
        </div>
        <div v-if="!selectedYear" class="mb-2">
          <span class="font-bold">Years: </span>
          <span>{{ laureate.prizes.map(prize => prize.year).join(', ') }}</span>
        </div>
        <div class="mb-2">
          <span class="font-bold">Countries: </span>
          <span>{{ laureate.countries }}</span>
        </div>
        <div v-if="!selectedCategory" class="mb-2">
          <span class="font-bold">Categories: </span>
          <span>{{ [...new Set(laureate.prizes.map(prize => prize.category))].join(', ') }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  laureates: {
    type: Array,
    required: true
  },
  selectedYear: {
    type: String,
    default: ''
  },
  selectedCategory: {
    type: String,
    default: ''
  },
  page: {
    type: Number,
    default: 1
  },
  perPage: {
    type: [Number, String],
    default: 10
  }
});

const emit = defineEmits(['sort']);
const isNavigating = ref(false);

const getDetailsLink = (id) => {
  const link = {
    path: `/details/${id}`,
    query: {
      fromPage: props.page.toString(),
      fromPerPage: props.perPage.toString()
    }
  };
  console.log('Generated details link:', link);
  return link;
};

const navigateToDetails = async (id) => {
  if (isNavigating.value) return;
  isNavigating.value = true;
  try {
    await navigateTo(getDetailsLink(id));
  } finally {
    setTimeout(() => {
      isNavigating.value = false;
    }, 300);
  }
};

const displayName = (laureate) => {
  if (laureate.fullname && laureate.fullname !== 'N/A') {
    // Split the full name and take the last part as the last name
    const nameParts = laureate.fullname.trim().split(/\s+/);
    return nameParts[nameParts.length - 1];
  }
  // Return full organisation name if present, or 'N/A' if neither
  return laureate.organisation || 'N/A';
};
</script>

<style scoped>
td.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>