<template>
    <div>
        <div class="mb-4">
            <button @click="activeTab = 'files'" :class="{ 'bg-blue-500 text-white': activeTab === 'files', 'bg-gray-200': activeTab !== 'files' }" class="px-4 py-2 rounded-l">Files</button>
            <button @click="activeTab = 'upload'" :class="{ 'bg-blue-500 text-white': activeTab === 'upload', 'bg-gray-200': activeTab !== 'upload' }" class="px-4 py-2 rounded-r">Upload</button>
        </div>

        <div v-if="activeTab === 'files'">
            <ul>
                <li v-for="document in documents" :key="document.id" class="mb-2">
                    {{ document.id }}
                    {{ document.name }}
                    <button @click="$emit('preview', document)" class="ml-2 bg-blue-500 text-white px-2 py-1 rounded">Preview</button>
                    <button @click="$emit('edit', document)" class="ml-2 bg-green-500 text-white px-2 py-1 rounded">Edit</button>
                    <button @click="$emit('delete', document.id)" class="ml-2 bg-red-500 text-white px-2 py-1 rounded">Delete</button>
                </li>
            </ul>
        </div>

        <div v-if="activeTab === 'upload'">
            <input type="file" @change="handleFileUpload" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            <button @click="uploadFiles" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Upload</button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    documents: Array,
});

const emit = defineEmits(['preview', 'edit', 'delete', 'upload']);

const activeTab = ref('files');
const selectedFiles = ref([]);

const handleFileUpload = (event) => {
    selectedFiles.value = event.target.files;
};

const uploadFiles = () => {
    if (selectedFiles.value.length > 0) {
        emit('upload', selectedFiles.value);
        selectedFiles.value = [];
    }
};
</script>