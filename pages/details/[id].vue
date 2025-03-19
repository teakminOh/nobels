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

    <div v-if="laureate">
      <!-- Display Mode -->
      <div v-if="!editMode">
        <div class="flex items-center mb-6 justify-between">
          <div>
            <h1 v-if="laureate.fullname && laureate.fullname !== 'N/A'" class="text-2xl font-bold ">
              {{ laureate.fullname }}
            </h1>
            <h1 v-else-if="laureate.organisation && laureate.organisation !== 'N/A'" class="text-2xl font-bold">
              {{ laureate.organisation }}
            </h1>
            <h1 v-else class="text-2xl font-bold">Laureate Details</h1>
          </div>
          <button v-if="authStore.isLoggedIn" @click="enterEditMode" class="">
            <svg class="pen-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="blue" width="24" height="24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-1-4L16 9l4 4-6 6-4-1zm-6 2h14" />
            </svg>
          </button>

        </div>

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
          <strong class="font-semibold">Countries:</strong>
          {{ Array.isArray(laureate.countries) ? laureate.countries.join(', ') : laureate.countries || 'N/A' }}
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
                <div v-if="prize.category === 'literature' && prize.details">
                  <p><strong>Language (SK):</strong> {{ prize.details.language_sk || 'N/A' }}</p>
                  <p><strong>Language (EN):</strong> {{ prize.details.language_en || 'N/A' }}</p>
                  <p><strong>Genre (SK):</strong> {{ prize.details.genre_sk || 'N/A' }}</p>
                  <p><strong>Genre (EN):</strong> {{ prize.details.genre_en || 'N/A' }}</p>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <div v-else>
          <strong class="font-semibold">Prizes:</strong> No prizes found
        </div>
        
      </div>
      <!-- Edit Mode -->
      <div v-else-if="authStore.isLoggedIn" class="max-w-2xl mx-auto bg-white overflow-hidden">

    <h1 class="text-2xl font-bold">Edit Nobel Laureate</h1>
 
  
  <div class="p-6 space-y-6">
    <!-- Basic Fields -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="col-span-2">
        <label class="block">
          <span class="text-gray-700 font-medium">Full Name</span>
          <input
            v-model="editableLaureate.fullname"
            type="text"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          />
        </label>
      </div>

      <div v-if="editableLaureate.organisation !== null">
        <label class="block">
          <span class="text-gray-700 font-medium">Organisation</span>
          <input
            v-model="editableLaureate.organisation"
            type="text"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          />
        </label>
      </div>

      <div v-if="editableLaureate.sex !== null">
        <label class="block">
          <span class="text-gray-700 font-medium">Sex</span>
          <select
            v-model="editableLaureate.sex"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          >
            <option value="M">Male</option>
            <option value="F">Female</option>
          </select>
        </label>
      </div>

      <div>
        <label class="block">
          <span class="text-gray-700 font-medium">Birth Year</span>
          <input
            v-model="editableLaureate.birth_year"
            type="number"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          />
        </label>
      </div>

      <div>
        <label class="block">
          <span class="text-gray-700 font-medium">Death Year</span>
          <input
            v-model="editableLaureate.death_year"
            type="number"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          />
        </label>
      </div>

      <div class="col-span-2">
        <label class="block">
          <span class="text-gray-700 font-medium">Countries (comma separated)</span>
          <input
            v-model="editableCountries"
            type="text"
            placeholder="e.g., USA, Germany, France"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          />
        </label>
      </div>
    </div>

    <!-- Editable Prizes -->
    <div class="mt-8">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Prizes</h2>
        <button 
          @click="addPrize" 
          class="flex items-center text-sm px-4 py-2 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
        >
          <span class="mr-1">+</span> Add Prize
        </button>
      </div>
      
      <div class="mt-4 space-y-6">
        <div
          v-for="(prize, index) in editableLaureate.prizes"
          :key="index"
          class="bg-gray-50 border border-gray-200 rounded-lg p-4 shadow-sm"
        >
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="block">
              <span class="text-gray-700 font-medium">Prize Year</span>
              <input
                v-model="prize.year"
                type="number"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
              />
            </label>
            
            <label class="block">
              <span class="text-gray-700 font-medium">Category</span>
              <select
                v-model="prize.category"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
              >
                <option value="">Select Category</option>
                <option value="peace">Peace</option>
                <option value="chemistry">Chemistry</option>
                <option value="physics">Physics</option>
                <option value="literature">Literature</option>
                <option value="medicine">Medicine</option>
              </select>
            </label>
            
            <div class="col-span-2">
              <label class="block">
                <span class="text-gray-700 font-medium">Contribution (EN)</span>
                <textarea
                  v-model="prize.contrib_en"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  rows="2"
                ></textarea>
              </label>
            </div>
            
            <div class="col-span-2">
              <label class="block">
                <span class="text-gray-700 font-medium">Contribution (SK)</span>
                <textarea
                  v-model="prize.contrib_sk"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  rows="2"
                ></textarea>
              </label>
            </div>
          </div>
          
          <!-- Literature-specific fields -->
          <div v-if="prize.category === 'literature'" class="mt-4 pt-4 border-t border-gray-200">
            <h3 class="font-medium text-gray-700 mb-3">Literature Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="block">
                <span class="text-gray-700">Language (SK)</span>
                <input
                  v-model="prize.details.language_sk"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
              </label>
              
              <label class="block">
                <span class="text-gray-700">Language (EN)</span>
                <input
                  v-model="prize.details.language_en"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
              </label>
              
              <label class="block">
                <span class="text-gray-700">Genre (SK)</span>
                <input
                  v-model="prize.details.genre_sk"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
              </label>
              
              <label class="block">
                <span class="text-gray-700">Genre (EN)</span>
                <input
                  v-model="prize.details.genre_en"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
              </label>
            </div>
          </div>
          
          <div class="mt-4 text-right">
            <button
              @click="removePrize(index)"
              class="text-sm px-3 py-1 rounded border border-red-200 text-red-500 hover:bg-red-50 transition-colors"
            >
              Remove Prize
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Save/Cancel Buttons -->
    <div class="flex gap-4 mt-8 pt-4 border-t border-gray-200">
      <button
        @click="saveUpdates"
        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm transition-colors"
      >
        Save Changes
      </button>
      <button
        @click="cancelEdit"
        class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-md shadow-sm transition-colors"
      >
        Cancel
      </button>
    </div>
  </div>
</div>
</div>

    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <div v-else class="text-gray-600">Loading...</div>

    <div v-if="updateSuccess" class="mt-4 text-green-600">
      {{ updateSuccess }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useApi } from '../composables/useApi';
import { useAuthStore } from '~/stores/auth';


const authStore = useAuthStore();

const route = useRoute();
// Notice: We destructure deletePrize from the composable.
const { getLaureateById, updateLaureate, deletePrize } = useApi();

const laureate = ref(null);
const error = ref(null);
const updateSuccess = ref(null);
const editMode = ref(false);

// Editable copy of laureate details
const editableLaureate = ref({});
// For editing countries as comma-separated text.
const editableCountries = ref('');

onMounted(async () => {
  try {
    const data = await getLaureateById(route.params.id);
    if (data.error) throw new Error(data.error);
    laureate.value = data.laureate;
    if (!laureate.value) throw new Error('Laureate not found');
  } catch (e) {
    error.value = e.message || 'Failed to load details';
    console.error('Error loading laureate:', e);
  }
});

// Enter edit mode: copy laureate details into editable copies.
const enterEditMode = () => {
  updateSuccess.value = null;
  editMode.value = true;
  editableLaureate.value = JSON.parse(JSON.stringify(laureate.value));
  // Convert countries to comma-separated string.
  if (Array.isArray(laureate.value.countries)) {
    editableCountries.value = laureate.value.countries.join(', ');
  } else {
    editableCountries.value = laureate.value.countries || '';
  }
};

// Cancel editing.
const cancelEdit = () => {
  editMode.value = false;
};

// Prize editing helpers.
const addPrize = () => {
  if (!editableLaureate.value.prizes) {
    editableLaureate.value.prizes = [];
  }
  editableLaureate.value.prizes.push({
    year: '',
    category: '',
    contrib_en: '',
    contrib_sk: '',
    details: {
      language_sk: '',
      language_en: '',
      genre_sk: '',
      genre_en: ''
    }
  });
};

const removePrize = async (index) => {
  const prize = editableLaureate.value.prizes[index];
  // If the prize has an ID, it exists on the backend.
  if (prize.id) {
    try {
      if (!authStore.isLoggedIn) {
        throw new Error('You must be logged in to delete a prize');
      }

      const res = await authStore.deletePrize(editableLaureate.value.id, prize.id);
      if (!res.success) throw new Error(res.message || 'Failed to delete prize');
      // If deletion succeeded, remove it from the local state.
      editableLaureate.value.prizes.splice(index, 1);
      updateSuccess.value = res.message || 'Prize removed successfully';
    } catch (err) {
      error.value = err.message;
      console.error('Failed to delete prize on backend:', err);
    }
  } else {
    // If not saved, simply remove it locally.
    editableLaureate.value.prizes.splice(index, 1);
    updateSuccess.value = 'Prize removed from local state';
  }
};

const saveUpdates = async () => {
  try {
    if (!authStore.isLoggedIn) {
      throw new Error('You must be logged in to update a laureate');
    }

    const payload = {
      id: editableLaureate.value.id,
      fullname: editableLaureate.value.fullname,
      organisation: editableLaureate.value.organisation,
      sex: editableLaureate.value.sex,
      birth_year: editableLaureate.value.birth_year,
      death_year: editableLaureate.value.death_year,
      countries: editableCountries.value.split(',').map(c => c.trim()).filter(Boolean),
      prizes: editableLaureate.value.prizes
    };

    const res = await authStore.updateLaureate(payload);
    if (!res.success) throw new Error(res.message || 'Failed to update laureate');

    laureate.value = JSON.parse(JSON.stringify(editableLaureate.value));
    laureate.value.countries = payload.countries; // Update countries array
    updateSuccess.value = res.message || 'Laureate updated successfully.';
    editMode.value = false;
    emit('laureateUpdated', laureate.value); // Optional: notify parent
  } catch (err) {
    error.value = err.message;
    console.error('Update failed:', err);
  }
};
</script>

<style scoped>
/* Optional: add styling to further customize the form */
</style>
