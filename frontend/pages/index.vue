<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Nobel Laureates</h1>

    <div class="p-4 shadow-lg rounded-lg">
      <NobelFilters
        :selectedYear="selectedYear"
        :selectedCategory="selectedCategory"
        :laureateType="laureateType"
        :perPage="perPage"
        :years="years"
        :categories="categories"
        @update="handleFilterChange"
      />

      <NobelTable
        :laureates="laureates"
        :selectedYear="selectedYear"
        :selectedCategory="selectedCategory"
        :page="page"
        :per-page="perPage"
        @sort="handleSort"
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
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useApi } from '../composables/useApi';

const route = useRoute();
const { getLaureates } = useApi();

const laureates = ref([]);
const years = ref([]);
const categories = ref([]);
const selectedYear = ref('');
const selectedCategory = ref('');
const laureateType = ref('all');
const perPage = ref(10);
const page = ref(1);
const total = ref(0);
const sortKey = ref('year');
const sortOrder = ref('ASC');

const totalPages = computed(() =>
  perPage.value === 'all' ? 1 : Math.ceil(total.value / perPage.value)
);

const fetchData = async () => {
  const params = {
    page: page.value,
    perPage: perPage.value,
    year: selectedYear.value,
    category: selectedCategory.value,
    type: laureateType.value,
    sort: sortKey.value === 'name' ? 'surname' : sortKey.value,
    order: sortOrder.value,
  };
  const data = await getLaureates(params);
  laureates.value = data.laureates;
  total.value = data.total;
  if (!years.value.length) years.value = data.years;
  if (!categories.value.length) categories.value = data.categories;
};

const handleFilterChange = (filters) => {
  selectedYear.value = filters.year;
  selectedCategory.value = filters.category;
  laureateType.value = filters.type;
  perPage.value = filters.perPage;
  page.value = 1; // Reset to page 1 on filter change
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
    fetchData();
  }
};

onMounted(() => {
  // Restore pagination from query parameters
  const queryPage = parseInt(route.query.page);
  const queryPerPage = route.query.perPage === 'all' ? 'all' : parseInt(route.query.perPage);

  if (queryPage && !isNaN(queryPage)) page.value = queryPage;
  if (queryPerPage) perPage.value = queryPerPage;

  fetchData();
});
</script>