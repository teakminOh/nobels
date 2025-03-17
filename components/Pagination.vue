<template>
  <ClientOnly>
    <div class="mt-2 flex justify-between items-center p-2">
      <div class="flex justify-between items-center">
        <button
          @click="emit('changePage', 1)"
          :disabled="page === 1"
          class="flex hover:bg-blue-100 transition rounded-md p-1 cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed mr-1"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="40"
            height="40"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            class="text-blue-700"
          >
            <path d="M15 18l-6-6 6-6" />
          </svg>
          <span class="-ml-8">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="40"
              height="40"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.5"
              class="text-blue-700"
            >
              <path d="M15 18l-6-6 6-6" />
            </svg>
          </span>
        </button>
        <button
          @click="emit('changePage', page - 1)"
          :disabled="page === 1"
          class="hover:bg-blue-100 transition rounded-md p-1 cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="40"
            height="40"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            class="text-blue-700"
          >
            <path d="M15 18l-6-6 6-6" />
          </svg>
        </button>
      </div>
      <span class="text-gray-700 font-medium">Page {{ page }} of {{ totalPages }}</span>
      <div class="flex">
        <button
          @click="emit('changePage', page + 1)"
          :disabled="page === totalPages"
          class="hover:bg-blue-100 transition rounded-md p-1 cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="40"
            height="40"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            class="text-blue-700"
          >
            <path d="M9 18l6-6-6-6" />
          </svg>
        </button>
        <button
          @click="emit('changePage', totalPages)"
          :disabled="page === totalPages"
          class="hover:bg-blue-100 transition rounded-md p-1 cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed flex ml-1"
        >
          <span class="-mr-8">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="40"
              height="40"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.5"
              class="text-blue-700"
            >
              <path d="M9 18l6-6-6-6" />
            </svg>
          </span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="40"
            height="40"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            class="text-blue-700"
          >
            <path d="M9 18l6-6-6-6" />
          </svg>
        </button>
      </div>
    </div>
  </ClientOnly>
</template>

<script setup>
defineProps(['page', 'totalPages']);
const emit = defineEmits(['changePage']);
</script>
