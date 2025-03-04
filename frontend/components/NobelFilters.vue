<template>
  <div class="flex space-x-4 mb-6">
    <select v-model="localFilters.year" @change="updateFilters" class="border rounded p-2">
      <option value="">All Years</option>
      <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
    </select>
    <select v-model="localFilters.category" @change="updateFilters" class="border rounded p-2">
      <option value="">All Categories</option>
      <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
    </select>
    <select v-model="localFilters.type" @change="updateFilters" class="border rounded p-2">
      <option value="all">All Laureates</option>
      <option value="individuals">Individuals</option>
      <option value="organizations">Organizations</option>
    </select>
    <select v-model="localFilters.perPage" @change="updateFilters" class="border rounded p-2">
      <option value="10">10 per page</option>
      <option value="20">20 per page</option>
      <option value="all">All</option>
    </select>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps(['selectedYear', 'selectedCategory', 'laureateType', 'perPage', 'years', 'categories']);
const emit = defineEmits(['update']);

const localFilters = ref({
  year: props.selectedYear,
  category: props.selectedCategory,
  type: props.laureateType,
  perPage: props.perPage,
});

const updateFilters = () => {
  emit('update', { ...localFilters.value });
};

watch(props, () => {
  localFilters.value = {
    year: props.selectedYear,
    category: props.selectedCategory,
    type: props.laureateType,
    perPage: props.perPage,
  };
});
</script>
