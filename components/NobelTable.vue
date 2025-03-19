<template>
  <!-- Desktop/Tablet View -->
  <div class="hidden sm:block overflow-x-auto rounded-lg">
    <table class="w-full divide-y divide-gray-200">
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
          <!-- Delete Column -->
          <th class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-center text-xs font-semibold text-white uppercase tracking-wider">

          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr 
          v-for="(laureate, index) in laureates" 
          :key="laureate.id"
          class="hover:bg-blue-50 transition-colors duration-150 ease-in-out cursor-pointer"
          :class="{'bg-gray-50': index % 2 === 0}"
        >
          <td @click="navigateToDetails(laureate.id)" class="px-6 py-4 whitespace-nowrap">
            <NuxtLink 
              :to="getDetailsLink(laureate.id)" 
              class="text-blue-600 hover:text-blue-900 font-medium hover:underline block max-w-xs"
              @click.prevent
            >
              <span class="truncate block">{{ displayName(laureate) }}</span>
            </NuxtLink>
          </td>
          <td v-if="!selectedYear" @click="navigateToDetails(laureate.id)" class="px-6 py-4 whitespace-nowrap text-gray-700">
            {{ laureate.prizes.map(prize => prize.year).join(', ') }}
          </td>
          <td @click="navigateToDetails(laureate.id)" class="px-6 py-4 whitespace-nowrap text-gray-700">
            <div class="flex items-center">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ laureate.countries }}
              </span>
            </div>
          </td>
          <td v-if="!selectedCategory" @click="navigateToDetails(laureate.id)" class="px-6 py-4 whitespace-nowrap text-gray-700">
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
          <!-- Delete Button Column -->
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <button 
              @click.stop="openDeleteModal(laureate.id)" 
              title="Delete Laureate"
              class="cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-1-4h-4a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1z" />
              </svg>
            </button>
          </td>
        </tr>

        <!-- Empty state row -->
        <tr v-if="laureates.length === 0">
          <td :colspan="getColspan" class="px-6 py-10 text-center text-gray-500 bg-gray-50">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
      v-for="(laureate) in laureates" 
      :key="laureate.id"
      class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300"
    >
      <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-4 py-2 flex justify-between items-center">
        <NuxtLink 
          :to="getDetailsLink(laureate.id)" 
          class="text-white font-medium hover:underline block max-w-[250px]" 
          @click.prevent
        >
          <span class="truncate block">{{ displayName(laureate) }}</span>
        </NuxtLink>
        <!-- Delete Button for Mobile -->
        <button @click.stop="openDeleteModal(laureate.id)" title="Delete Laureate">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-1-4h-4a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1z" />
          </svg>
        </button>
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
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="mt-2 text-gray-500">Nik sa nenašiel s Vašimi požiadavkami.</p>
    </div>
  </div>
   <!-- Confirmation Modal -->
   <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
      <h2 class="text-xl font-semibold mb-4">Confirm Deletion</h2>
      <p class="mb-4">Are you sure you want to delete this laureate?</p>
      <div class="flex justify-end gap-4">
        <button @click="cancelDeletion" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
        <button @click="confirmDeletion" class="px-4 py-2 bg-red-500 text-white rounded">Delete</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../composables/useApi';

const router = useRouter();

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

const emit = defineEmits(['sort', 'laureateDeleted']);
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
    await router.push(getDetailsLink(id));
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

const { deleteLaureate } = useApi();
// Reactive variables for deletion modal
const showDeleteModal = ref(false);
const laureateToDelete = ref(null);

const openDeleteModal = (id) => {
  laureateToDelete.value = id;
  showDeleteModal.value = true;
};

const confirmDeletion = async () => {
  try {
    const res = await deleteLaureate({ id: laureateToDelete.value });
    if (res.error) throw new Error(res.error);
    emit('laureateDeleted', laureateToDelete.value);
    showDeleteModal.value = false;
    laureateToDelete.value = null;
  } catch (e) {
    console.error("Failed to delete laureate:", e);
  }
};

const cancelDeletion = () => {
  showDeleteModal.value = false;
  laureateToDelete.value = null;
};

</script>

<style scoped>
/* Additional styling can be added here if needed */
</style>
