<template>
  <div class="max-w-7xl container mx-auto p-4">
    <!-- <h1 class="text-2xl font-bold mb-6">Nobel Laureates</h1> -->

    <div class="shadow-lg rounded-lg">
      <NobelFilters
        :selectedYear="selectedYear"
        :selectedCategory="selectedCategory"
        :selectedCountry="selectedCountry"
        :laureateType="laureateType"
        :perPage="perPage"
        :years="years"
        :categories="categories"
        :countries="countries"
        @update="handleFilterChange"
      />
      <button v-if="authStore.isLoggedIn" @click="showModal = true" class="mb-2 px-4 py-2 bg-lime-600 text-white rounded">
        Pridaj laureáta
      </button>
      <AddLaureate
        v-if="showModal"
        @close="showModal = false"
      />
      <NobelTable
        :laureates="laureates"
        :selectedYear="selectedYear"
        :selectedCategory="selectedCategory"
        :page="page"
        :perPage="perPage"
        @sort="handleSort"
        @laureateDeleted="handleLaureateDeleted"
      />

      <Pagination
        :page="page"
        :totalPages="totalPages"
        @changePage="changePage"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeMount, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../composables/useApi';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const { getLaureates } = useApi();
const authStore = useAuthStore();

const laureates = ref([]);
const years = ref([]);
const categories = ref([]);
const countries = ref([]); // Added countries array

const selectedYear = ref('');
const selectedCategory = ref('');
const selectedCountry = ref(''); // Added selectedCountry
const laureateType = ref('all');
const perPage = ref(10);
const page = ref(1);
const total = ref(0);
const sortKey = ref('year');
const sortOrder = ref('ASC');
const showModal = ref(false);

const totalPages = computed(() =>
  perPage.value === 'all' ? 1 : Math.ceil(total.value / perPage.value)
);

const fetchData = async () => {
  const params = {
    page: page.value,
    perPage: perPage.value,
    year: selectedYear.value,
    category: selectedCategory.value,
    country: selectedCountry.value, // Include country in params
    type: laureateType.value,
    sort: sortKey.value === 'name' ? 'surname' : sortKey.value,
    order: sortOrder.value,
  };
  console.log('Fetching data with params:', params);
  const data = await getLaureates(params);
  laureates.value = data.laureates;
  total.value = data.total;
  if (!years.value.length) years.value = data.years;
  if (!categories.value.length) categories.value = data.categories;
  if (!countries.value.length && data.countries) countries.value = data.countries;
};

const handleFilterChange = (filters) => {
  selectedYear.value = filters.year;
  selectedCategory.value = filters.category;
  selectedCountry.value = filters.country; // Update country filter
  laureateType.value = filters.type;
  perPage.value = filters.perPage;
  page.value = 1; // Reset to page 1 on filter change
  updateRouteQuery();
  fetchData();
};

const handleSort = (key) => {
  sortKey.value = key;
  sortOrder.value = sortOrder.value === 'ASC' ? 'DESC' : 'ASC';
  fetchData();
};

const changePage = (newPage) => {
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage;
    updateRouteQuery();
    fetchData();
  }
};

const updateRouteQuery = () => {
  router.replace({
    query: {
      fromPage: page.value.toString(),
      fromPerPage: perPage.value.toString(),
    },
  });
};

const restorePagination = () => {
  console.log('Route query on restore:', route.query);
  const fromPage = parseInt(route.query.fromPage);
  const fromPerPage = route.query.fromPerPage === 'all' ? 'all' : parseInt(route.query.fromPerPage);

  if (fromPage && !isNaN(fromPage)) {
    page.value = fromPage;
    console.log('Restored page to:', page.value);
  }
  if (fromPerPage) {
    perPage.value = fromPerPage;
    console.log('Restored perPage to:', perPage.value);
  }
};

// Use onBeforeMount to ensure state is set before rendering
onBeforeMount(() => {
  restorePagination();
});

// Ensure initial fetch respects restored state
onMounted(() => {
  fetchData();
});

// Watch route changes for navigation (e.g., back/forward)
watch(() => route.query, (newQuery) => {
  console.log('Route query changed:', newQuery);
  restorePagination();
  fetchData();
});

// Handle deletion: remove deleted laureate from local array.
const handleLaureateDeleted = (deletedId) => {
  laureates.value = laureates.value.filter(laureate => laureate.id !== deletedId);
};
</script>
