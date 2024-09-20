<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-1/2">
            <h4 class="text-lg font-semibold mb-4">Edit Document</h4>
            <input v-model="documentName" class="w-full p-2 mb-4 border border-gray-300 rounded-md" />
            <div class="flex justify-end">
                <button @click="save" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 mr-2">Save</button>
                <button @click="$emit('cancel')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancel</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    document: Object,
});

const emit = defineEmits(['save', 'cancel']);

const documentName = ref(props.document.name);

const save = () => {
    emit('save', { ...props.document, name: documentName.value });
};
</script>