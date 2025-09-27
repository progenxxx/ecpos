<script setup>
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { useForm } from '@inertiajs/vue3';

defineProps({
    showModal: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggleActive']);

const form = useForm({
    version_number: '',
    version_name: '',
    release_notes: '',
    download_url: '',
    force_update: false,
    min_supported_version: '',
});

const createVersion = () => {
    form.post('/app-versions', {
        onSuccess: () => {
            form.reset();
            emit('toggleActive');
        },
        onError: (errors) => {
            console.error('Create version errors:', errors);
        }
    });
};

const closeModal = () => {
    form.reset();
    emit('toggleActive');
};
</script>

<template>
    <Modal :show="showModal" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Create New App Version
            </h2>

            <form @submit.prevent="createVersion" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="version_number" value="Version Number *" />
                        <TextInput
                            id="version_number"
                            v-model="form.version_number"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g., 1.0.0"
                            required
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.version_number" />
                    </div>

                    <div>
                        <InputLabel for="version_name" value="Version Name *" />
                        <TextInput
                            id="version_name"
                            v-model="form.version_name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g., Major Update"
                            required
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.version_name" />
                    </div>

                    <div>
                        <InputLabel for="min_supported_version" value="Min Supported Version *" />
                        <TextInput
                            id="min_supported_version"
                            v-model="form.min_supported_version"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g., 0.9.0"
                            required
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.min_supported_version" />
                    </div>

                    <div>
                        <InputLabel for="download_url" value="Download URL" />
                        <TextInput
                            id="download_url"
                            v-model="form.download_url"
                            type="url"
                            class="mt-1 block w-full"
                            placeholder="https://example.com/app.apk"
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.download_url" />
                    </div>
                </div>

                <div>
                    <InputLabel for="release_notes" value="Release Notes" />
                    <textarea
                        id="release_notes"
                        v-model="form.release_notes"
                        rows="4"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="What's new in this version..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.release_notes" />
                </div>

                <div class="flex items-center">
                    <input
                        id="force_update"
                        v-model="form.force_update"
                        type="checkbox"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <label for="force_update" class="ml-2 text-sm text-gray-700">
                        Force Update (Users cannot skip this update)
                    </label>
                </div>

                <div class="flex items-center justify-end mt-6 space-x-3">
                    <SecondaryButton type="button" @click="closeModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Version' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>