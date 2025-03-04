<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Nobel Laureates</h1>

    <div class="p-4 shadow-lg rounded-lg">
      <!-- Filters -->
      <div class="flex space-x-4 mb-6">
        <select v-model="selectedYear" @change="fetchData" class="border rounded p-2">
          <option value="">All Years</option>
          <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
        </select>
        <select v-model="selectedCategory" @change="fetchData" class="border rounded p-2">
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
        </select>
        <select v-model="laureateType" @change="fetchData" class="border rounded p-2">
          <option value="all">All Laureates</option>
          <option value="individuals">Individuals</option>
          <option value="organizations">Organizations</option>
        </select>
        <select v-model="perPage" @change="fetchData" class="border rounded p-2">
          <option value="10">10 per page</option>
          <option value="20">20 per page</option>
          <option value="all">All</option>
        </select>
      </div>
      <!-- Table -->
      <table ref="table" class="w-full">
        <thead>
          <tr class="bg-gray-100">
            <th class="border p-2 text-left cursor-pointer" @click="sort('name')">Name</th>
            <th v-if="!selectedYear" class="border p-2 text-left cursor-pointer" @click="sort('year')">Year</th>
            <th class="border p-2 text-left">Country</th>
            <th v-if="!selectedCategory" class="border p-2 text-left cursor-pointer" @click="sort('category')">Category</th>
          </tr>
        </thead>
      </table>
      <!-- Pagination -->
      <div class="mt-4 flex justify-between items-center">
        <div class="space-x-2">
          <button @click="changePage(1)" :disabled="page === 1" class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300">First</button>
          <button @click="changePage(page - 1)" :disabled="page === 1" class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300">Previous</button>
        </div>
        <span>Page {{ page }} of {{ totalPages }}</span>
        <div class="space-x-2">
          <button @click="changePage(page + 1)" :disabled="page === totalPages" class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300">Next</button>
          <button @click="changePage(totalPages)" :disabled="page === totalPages" class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300">Last</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import DataTable from 'datatables.net-dt';

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
const table = ref(null);
let dt;

const totalPages = computed(() => perPage.value === 'all' ? 1 : Math.ceil(total.value / perPage.value));

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

  dt.clear().rows.add(laureates.value).draw();
  dt.column(1).visible(!selectedYear.value); // Year
  dt.column(3).visible(!selectedCategory.value); // Category
};

const getDisplayName = (row) => {
  return row.fullname || row.organisation || '';
};

const sort = (key) => {
  if (key === sortKey.value) {
    sortOrder.value = sortOrder.value === 'ASC' ? 'DESC' : 'ASC';
  } else {
    sortKey.value = key;
    sortOrder.value = 'ASC';
  }
  fetchData();
};

const changePage = (newPage) => {
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage;
    fetchData();
  }
};

onMounted(async () => {
  dt = new DataTable(table.value, {
    data: [],
    columns: [
      { title: 'Name', data: row => getDisplayName(row) },
      { title: 'Year', data: 'year', visible: !selectedYear.value },
      { title: 'Country', data: 'countries' },
      { title: 'Category', data: 'category', visible: !selectedCategory.value }
    ],
    paging: false,
    searching: false,
    ordering: false,
  });

  dt.on('click', 'tr', function () {
    const rowData = dt.row(this).data();
    if (rowData) navigateTo(`/details/${rowData.id}`);
  });

  await fetchData(); // Initial data load
});
</script>