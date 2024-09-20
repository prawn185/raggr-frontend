<template>
  <div class="container mx-auto px-4 py-8">
    <h4 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200">Documents</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="doc in documents" :key="doc.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl">
        <div class="relative h-56">
          <img v-if="isImage(doc.type)" :src="doc.path" :alt="doc.name" class="w-full h-full object-cover object-center">
          <div v-else class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-gray-700">
            <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        <div class="p-6">
          <h5 class="text-xl font-semibold mb-3 text-gray-900 dark:text-gray-100">{{ doc.name }}</h5>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">{{ doc.description }}</p>
          <div class="text-sm text-gray-500 dark:text-gray-300 space-y-2">
            <p>
              <span class="font-medium text-gray-700 dark:text-gray-200">Status:</span>
              <span v-if="doc.status === 'processing'" class="inline-flex items-center">
                Processing
                <svg class="animate-spin ml-2 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              <span v-else-if="doc.status === 'uploaded'" class="inline-flex items-center text-green-500">
                Uploaded
                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </span>
              <span v-else>{{ doc.status }}</span>
            </p>
            <p><span class="font-medium text-gray-700 dark:text-gray-200">Tags:</span> {{ doc.tags }}</p>
            <p><span class="font-medium text-gray-700 dark:text-gray-200">Contact:</span> {{ doc.contact_details }}</p>
            <p><span class="font-medium text-gray-700 dark:text-gray-200">Dates:</span> {{ doc.dates }}</p>
          </div>
          <div class="flex justify-end mt-6 space-x-4">
            <button @click="$emit('edit', doc)" class="text-blue-500 hover:text-blue-600 transition-colors duration-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="$emit('delete', doc.id)" class="text-red-500 hover:text-red-600 transition-colors duration-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

defineProps({
  documents: Array,
});

defineEmits(['edit', 'delete']);

const isImage = (type) => {
  return ['image/jpeg', 'image/png', 'image/gif'].includes(type);
};
</script>
