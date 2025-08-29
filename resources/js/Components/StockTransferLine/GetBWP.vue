<script setup>
import { ref, watch, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';

const props = defineProps({
    showModal: {
        type: Boolean,
        required: true
    },
    JOURNALID: {
        type: [String, Number],
        required: true
    }
});

const emit = defineEmits(['toggleActive']);

const form = useForm({
    JOURNALID: ''
});

watch(() => props.JOURNALID, (newVal) => {
    if (newVal !== undefined) {
        form.JOURNALID = newVal.toString();

    }
}, { immediate: true });

watch(() => props.showModal, (newVal) => {

}, { immediate: true });

const submit = () => {

    form.patch("/StockTransferLine/getbwproducts", {
        preserveScroll: true,
        onSuccess: () => {

            toggleActive();
        },
        onError: (error) => {

        }
    });
};

const closeModal = () => {
    emit('toggleActive');
};

onMounted(() => {
    if (props.JOURNALID) {
        form.JOURNALID = props.JOURNALID.toString();

    }
});
</script>

<template>
    <Modal
        :show="showModal"
        @close="closeModal"
    >
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Generate Items
            </h2>

            <form @submit.prevent="submit" class="mt-6">
                <div>
                    <TextInput
                        id="JOURNALID"
                        v-model="form.JOURNALID"
                        type="text"
                        class="mt-1 block w-full input !bg-gray-100 !text-black"
                        :is-error="!!form.errors.JOURNALID"
                        disabled
                    />
                    <InputError :message="form.errors.JOURNALID" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <PrimaryButton
                        type="button"
                        class="ml-3 bg-gray-400"
                        @click="closeModal"
                    >
                        Cancel
                    </PrimaryButton>
                    <PrimaryButton
                        type="submit"
                        class="ml-3 bg-navy"
                        :disabled="form.processing || !form.JOURNALID"
                    >
                        Generate
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<style scoped>

.bg-navy {
    @apply bg-blue-900 hover:bg-blue-800 text-white;
}

.input {
    @apply border-gray-300 rounded-md shadow-sm;
}

.input:disabled {
    @apply bg-gray-100 cursor-not-allowed;
}
</style>