<template>
  <div class="mt-4 flex justify-between items-center">
    <div class="space-x-2">
      <button @click="emit('changePage', 1)" :disabled="page === 1" class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300">First</button>
      <button @click="emit('changePage', page - 1)" :disabled="page === 1" class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300">Previous</button>
    </div>
    <span>Page {{ page }} of {{ totalPages }}</span>
    <div class="space-x-2">
      <button @click="emit('changePage', page + 1)" :disabled="page === totalPages" class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300">Next</button>
      <button @click="emit('changePage', totalPages)" :disabled="page === totalPages" class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300">Last</button>
    </div>
  </div>
</template>

<script setup>
defineProps(['page', 'totalPages']);
const emit = defineEmits(['changePage']);
</script>
