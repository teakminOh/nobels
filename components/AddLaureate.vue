<template>
  <div
    class="fixed inset-0 flex items-center justify-center z-50"
    style="background: rgba(0, 0, 0, 0.5);"
  >
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <div class="flex justify-between items-center">
          <h1 class="text-2xl font-bold text-white">Add New Nobel Laureate</h1>
          <button @click="closeModal" class="text-white hover:text-gray-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto p-6 max-h-[calc(90vh-80px)]">
        <form @submit.prevent="submitForm" :novalidate="bulkPayload !== null">
          <!-- Laureate Type -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Laureate Type</label>
            <div class="flex space-x-4">
              <label class="inline-flex items-center">
                <input type="radio" value="person" v-model="form.laureateType" class="form-radio text-indigo-600" />
                <span class="ml-2">Person</span>
              </label>
              <label class="inline-flex items-center">
                <input type="radio" value="organisation" v-model="form.laureateType" class="form-radio text-indigo-600" />
                <span class="ml-2">Organisation</span>
              </label>
            </div>
          </div>

          <!-- Personal Information -->
          <div v-if="form.laureateType === 'person'" class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
            <input
              v-model="form.fullname"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              required
            />
          </div>

          <!-- Organisation Information -->
          <div v-if="form.laureateType === 'organisation'" class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Organisation Name *</label>
            <input
              v-model="form.organisation"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              required
            />
          </div>

          <!-- Sex (only for persons) -->
          <div v-if="form.laureateType === 'person'" class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Sex</label>
            <select
              v-model="form.sex"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Select Sex</option>
              <option value="M">Male</option>
              <option value="F">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <!-- Common Fields -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Birth Year *</label>
              <input
                v-model="form.birth_year"
                type="number"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                required
              />
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Death Year (if applicable)</label>
              <input
                v-model="form.death_year"
                type="number"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
          </div>

          <!-- Countries (multi-select) -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Countries</label>
            <select
              v-model="form.countries"
              multiple
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option v-for="country in availableCountries" :key="country" :value="country">
                {{ country }}
              </option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple countries</p>
          </div>

          <!-- Prizes Information -->
          <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-bold text-gray-800">Prizes</h2>
              <button 
                type="button" 
                @click="addPrize" 
                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
              >
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Prize
              </button>
            </div>
            
            <div
              v-for="(prize, index) in form.prizes"
              :key="index"
              class="mb-6 p-4 border border-gray-200 rounded-lg shadow-sm bg-gray-50"
            >
              <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-700">Prize #{{ index + 1 }}</h3>
                <button
                  type="button"
                  @click="removePrize(index)"
                  class="text-red-500 hover:text-red-700 focus:outline-none"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>

              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Prize Year *</label>
                  <input
                    v-model="prize.year"
                    type="number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                  <select
                    v-model="prize.category"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    required
                  >
                    <option value="">Select Category</option>
                    <option value="peace">Peace</option>
                    <option value="chemistry">Chemistry</option>
                    <option value="physics">Physics</option>
                    <option value="literature">Literature</option>
                    <option value="medicine">Medicine</option>
                  </select>
                </div>
              </div>

              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Contribution (English)
                </label>
                <textarea
                  v-model="prize.contrib_en"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  rows="2"
                  required
                ></textarea>
              </div>
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Contribution (Slovak)
                </label>
                <textarea
                  v-model="prize.contrib_sk"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  rows="2"
                  required
                ></textarea>
              </div>
              
              <!-- Literature-specific fields -->
              <div v-if="prize.category === 'literature'" class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-md font-semibold text-gray-700 mb-3">Literature Details</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Language (Slovak)</label>
                    <input
                      v-model="prize.details.language_sk"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Language (English)</label>
                    <input
                      v-model="prize.details.language_en"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Genre (Slovak)</label>
                    <input
                      v-model="prize.details.genre_sk"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Genre (English)</label>
                    <input
                      v-model="prize.details.genre_en"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      required
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error and Success Messages -->
          <div v-if="error" class="mb-6 p-4 bg-red-50 rounded-md border border-red-200">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-600">{{ error }}</h3>
              </div>
            </div>
          </div>
          <div v-if="success" class="mb-6 p-4 bg-green-50 rounded-md border border-green-200">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-green-600">{{ success }}</h3>
                <ul v-if="prizeMessages.length" class="mt-2 list-disc pl-5 text-sm text-green-600">
                  <li v-for="(msg, i) in prizeMessages" :key="i">{{ msg }}</li>
                </ul>
              </div>
            </div>
          </div>
          
          <!-- Bulk Upload -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Bulk JSON upload</label>
            <div class="flex items-center">
              <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <span>Choose file</span>
                <input type="file" accept=".json" @change="handleFileUpload" class="sr-only" />
              </label>
              <span class="ml-3 text-sm text-gray-500">{{ fileSelected ? 'File selected' : 'No file selected' }}</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Upload a JSON file containing an array of laureate objects</p>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end">
            <button 
              type="button" 
              @click="closeModal" 
              class="mr-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Submit
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref } from 'vue';
import { useApi } from '../composables/useApi';

const { fetchApi } = useApi();
const emit = defineEmits(['close']);

const form = ref({
  laureateType: 'person', // Default selection is person
  fullname: '',
  organisation: '',
  sex: '',
  birth_year: '',
  death_year: '',
  countries: [],
  prizes: [
    {
      year: '',
      category: '',
      contrib_en: '',
      contrib_sk: '',
      details: {
        language_sk: '',
        language_en: '',
        genre_sk: '',
        genre_en: '',
      }
    }
  ],
});

const availableCountries = ref([
  'USA',
  'UK',
  'Germany',
  'France',
  'Slovakia',
]);

const error = ref('');
const success = ref('');
const prizeMessages = ref([]); // Reactive variable for prize messages.
const bulkPayload = ref(null);
const fileSelected = ref(false);

const addPrize = () => {
  form.value.prizes.push({
    year: '',
    category: '',
    contrib_en: '',
    contrib_sk: '',
    details: {
      language_sk: '',
      language_en: '',
      genre_sk: '',
      genre_en: '',
    }
  });
};

const removePrize = (index) => {
  // Since the laureate isn't saved yet, simply remove it from local state.
  form.value.prizes.splice(index, 1);
};

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) {
    fileSelected.value = false;
    return;
  }
  
  fileSelected.value = true;
  const reader = new FileReader();
  reader.onload = () => {
    try {
      bulkPayload.value = JSON.parse(reader.result);
      error.value = '';
    } catch {
      error.value = 'Invalid JSON format';
      bulkPayload.value = null;
    }
  };
  reader.readAsText(file);
}

const validateForm = () => {
  if (form.value.laureateType === 'person' && !form.value.fullname) {
    error.value = 'Full name is required';
    return false;
  }
  if (form.value.laureateType === 'organisation' && !form.value.organisation) {
    error.value = 'Organisation name is required';
    return false;
  }
  if (!form.value.birth_year) {
    error.value = 'Birth year is required';
    return false;
  }
  if (form.value.prizes.length === 0) {
    error.value = 'At least one prize is required';
    return false;
  }
  for (let i = 0; i < form.value.prizes.length; i++) {
    const prize = form.value.prizes[i];
    if (!prize.year) {
      error.value = `Prize #${i + 1}: Year is required`;
      return false;
    }
    if (!prize.category) {
      error.value = `Prize #${i + 1}: Category is required`;
      return false;
    }
    if (!prize.contrib_en || !prize.contrib_sk) {
      error.value = `Prize #${i + 1}: Contributions are required`;
      return false;
    }
    if (prize.category === 'literature') {
      if (!prize.details.language_en || !prize.details.language_sk || 
          !prize.details.genre_en || !prize.details.genre_sk) {
        error.value = `Prize #${i + 1}: Literature details are required`;
        return false;
      }
    }
  }
  return true;
}

const submitForm = async () => {
  error.value = '';
  success.value = '';
  prizeMessages.value = [];
  
  try {
    // Use bulk payload if exists, otherwise use form data.
    const payload = bulkPayload.value !== null
      ? bulkPayload.value
      : JSON.parse(JSON.stringify(form.value));
    
    // Optional validations for single submission.
    if (bulkPayload.value === null && !validateForm()) {
      return;
    }
    
    const data = await fetchApi('/addLaureate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    
    if (data.error) {
      throw new Error(data.error || 'Something went wrong');
    }
    
    success.value = data.message;
    if (data.messages && data.messages.length > 0) {
      prizeMessages.value = data.messages;
    }
    
    // If single submission, reset the form.
    if (bulkPayload.value === null) {
      form.value = {
        laureateType: 'person',
        fullname: '',
        organisation: '',
        sex: '',
        birth_year: '',
        death_year: '',
        countries: [],
        prizes: [
          {
            year: '',
            category: '',
            contrib_en: '',
            contrib_sk: '',
            details: {
              language_sk: '',
              language_en: '',
              genre_sk: '',
              genre_en: '',
            }
          }
        ],
      };
    }
    
    bulkPayload.value = null;
    fileSelected.value = false;
  } catch (err) {
    error.value = err.message;
    console.error('Form submission error:', err);
  }
};

const closeModal = () => {
  emit('close');
};
</script>