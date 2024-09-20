<template>
    <div class="mb-6">
        <h4 class="text-md font-medium mb-2">Upload Document(s)</h4>
        <form @submit.prevent="uploadDocuments" class="space-y-4">
            <div class="mb-4">
                <label for="file-upload" class="block text-sm font-medium text-gray-700">Select File(s)</label>
                <input id="file-upload" type="file" @change="handleFileUpload" class="mt-1 block w-full" required />
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Upload</button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    file: null,
    tags: '',
});

const handleFileUpload = (event) => {
    form.file = event.target.files[0];
};

const uploadDocuments = () => {
    form.post(route('documents.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            // Handle success
            form.reset();
        },
        onError: (errors) => {
            // Handle errors
            console.error(errors);
        },
    });
};

defineEmits(['upload']);
</script>