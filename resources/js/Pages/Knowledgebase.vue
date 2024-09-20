<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Knowledgebase</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Document Management</h3>
                        
                        <DocumentTabs 
                            :documents="documents"
                            @preview="previewDocument"
                            @edit="editDocument"
                            @delete="deleteDocument"
                            @upload="uploadDocuments"
                        />

                        <EditDocumentModal 
                            v-if="editingDocument"
                            :document="editingDocument"
                            @save="saveEditedDocument"
                            @cancel="cancelEdit"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import DocumentTabs from '@/Components/Knowledgebase/DocumentTabs.vue';
import EditDocumentModal from '@/Components/Knowledgebase/EditDocumentModal.vue';

const props = defineProps({
    documents: {
        type: Array,
        default: () => [],
    },
});

const documents = ref(props.documents);
const editingDocument = ref(null);
const activeTab = ref('files');

const previewDocument = (document) => {
    // Logic for previewing a document
    console.log('Previewing document:', document);
    // Implement preview functionality here
};

const editDocument = (document) => {
    editingDocument.value = { ...document };
};

const uploadDocuments = async (files) => {
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }

    try {
        const response = await axios.post(route('documents.store'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        documents.value.push(...response.data);
        // The success message is now handled by Laravel's flash message
        // Switch to the 'files' tab after successful upload
        activeTab.value = 'files';
    } catch (error) {
        console.error('Error uploading documents:', error);
        // Set an error flash message
        $page.props.flash.error = 'Error uploading documents. Please try again.';
    }
};

const deleteDocument = async (id) => {
    if (confirm('Are you sure you want to delete this document?')) {
        try {
            await axios.delete(route('documents.destroy', id));
            documents.value = documents.value.filter(doc => doc.id !== id);
        } catch (error) {
            console.error('Error deleting document:', error);
            $page.props.flash.error = error.response?.data?.message || 'An error occurred while deleting the document.';
        }
    }
};

const saveEditedDocument = async () => {
    try {
        const response = await axios.put(route('documents.update', editingDocument.value.id), {
            name: editingDocument.value.name,
        });
        const index = documents.value.findIndex(doc => doc.id === editingDocument.value.id);
        documents.value[index] = response.data;
        editingDocument.value = null;
    } catch (error) {
        console.error('Error updating document:', error);
        $page.props.flash.error = error.response?.data?.message || 'An error occurred while updating the document.';
    }
};

const cancelEdit = () => {
    editingDocument.value = null;
};

const pollInterval = ref(null);

</script>
