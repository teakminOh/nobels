<!-- NobelTable.vue -->
<template>
  <!-- Desktop/Tablet View -->
  <div class="hidden sm:block overflow-hidden rounded-lg">
    <table class="min-w-full table-fixed divide-y divide-gray-200">
      <thead>
        <tr>
          <th 
            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-left text-xs font-semibold text-white uppercase tracking-wider cursor-pointer w-1/5"
            @click="emit('sort', 'surname')"
          >
            <div class="flex items-center space-x-1">
              <span>Priezvisko/Organizácia</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
              </svg>
            </div>
          </th>
          <th 
            v-if="!selectedYear" 
            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-left text-xs font-semibold text-white uppercase tracking-wider cursor-pointer w-1/5"
            @click="emit('sort', 'year')"
          >
            <div class="flex items-center space-x-1">
              <span>Rok</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
              </svg>
            </div>
          </th>
          <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-left text-xs font-semibold text-white uppercase tracking-wider w-1/5">
            Krajina
          </th>
          <th 
            v-if="!selectedCategory" 
            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-left text-xs font-semibold text-white uppercase tracking-wider cursor-pointer w-1/5"
            @click="emit('sort', 'category')"
          >
            <div class="flex items-center space-x-1">
              <span>Kategória</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
              </svg>
            </div>
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr 
          v-for="(laureate, index) in laureates" 
          :key="laureate.id" 
          @click="navigateToDetails(laureate.id)" 
          class="hover:bg-blue-50 transition-colors duration-150 ease-in-out cursor-pointer"
          :class="{'bg-gray-50': index % 2 === 0}"
        >
          <td class="px-6 py-4 whitespace-nowrap" :title="laureate.fullname || laureate.organisation">
            <NuxtLink 
              :to="getDetailsLink(laureate.id)" 
              class="text-blue-600 hover:text-blue-900 font-medium hover:underline block max-w-xs"
              @click.prevent
            >
              <span class="truncate block">{{ displayName(laureate) }}</span>
            </NuxtLink>
          </td>
          <td v-if="!selectedYear" class="px-6 py-4 whitespace-nowrap text-gray-700" :title="laureate.prizes.map(prize => prize.year).join(', ')">
            {{ laureate.prizes.map(prize => prize.year).join(', ') }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-gray-700" :title="laureate.countries">
            <div class="flex items-center">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ laureate.countries }}
              </span>
            </div>
          </td>
          <td v-if="!selectedCategory" class="px-6 py-4 whitespace-nowrap text-gray-700" :title="[...new Set(laureate.prizes.map(prize => prize.category))].join(', ')">
            <div class="flex flex-wrap gap-1">
              <span 
                v-for="category in [...new Set(laureate.prizes.map(prize => prize.category))]" 
                :key="category"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
              >
                {{ category }}
              </span>
            </div>
          </td>
        </tr>
        <!-- Empty state row -->
        <tr v-if="laureates.length === 0">
          <td :colspan="getColspan" class="px-6 py-10 text-center text-gray-500 bg-gray-50">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="mt-2 text-sm font-medium">Nik sa nenašiel s Vašimi požiadavkami.</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Mobile View -->
  <div class="sm:hidden space-y-3">
    <div 
      v-for="laureate in laureates" 
      :key="laureate.id"
      @click="navigateToDetails(laureate.id)"
      class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300"
    >
      <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-4 py-2">
        <NuxtLink 
          :to="getDetailsLink(laureate.id)" 
          class="text-white font-medium hover:underline block max-w-[250px]" 
          @click.prevent
        >
          <span class="truncate block">{{ displayName(laureate) }}</span>
        </NuxtLink>
      </div>
      <div class="px-4 py-3 space-y-2">
        <div v-if="!selectedYear" class="flex">
          <span class="text-gray-500 font-medium w-24">Rok:</span>
          <span class="text-gray-800">{{ laureate.prizes.map(prize => prize.year).join(', ') }}</span>
        </div>
        <div class="flex">
          <span class="text-gray-500 font-medium w-24">Krajina:</span>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {{ laureate.countries }}
          </span>
        </div>
        <div v-if="!selectedCategory" class="flex">
          <span class="text-gray-500 font-medium w-24">Kategória:</span>
          <div class="flex flex-wrap gap-1">
            <span 
              v-for="category in [...new Set(laureate.prizes.map(prize => prize.category))]" 
              :key="category"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
            >
              {{ category }}
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Empty state for mobile -->
    <div v-if="laureates.length === 0" class="bg-white rounded-lg shadow overflow-hidden p-6 text-center">
      <svg class="w-12 h-12 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="mt-2 text-gray-500">Nik sa nenašiel s Vašimi požiadavkami.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

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
    const nameParts = laureate.fullname.trim().split(/\s+/);
    return nameParts[nameParts.length - 1];
  }
  return laureate.organisation || 'N/A';
};

const getColspan = computed(() => {
  let count = 1;
  if (!props.selectedYear) count++;
  if (!props.selectedCategory) count++;
  count++;
  return count;
});
</script>

<style scoped>
/* Additional styling can be added here if needed */
</style>
