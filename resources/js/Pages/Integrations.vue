<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Integrations</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">GitHub Integration</h3>
                        <form @submit.prevent="integrateGitHub">
                            <div class="mb-4">
                                <InputLabel for="repo_url" value="GitHub Repository URL" />
                                <TextInput
                                    id="repo_url"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.repo_url"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.repo_url" />
                            </div>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Integrate
                            </PrimaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    repo_url: '',
});

const integrateGitHub = () => {
    form.post(route('integrations.github'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('repo_url');
        },
    });
};
</script>
