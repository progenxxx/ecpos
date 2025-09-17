<template>
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Add New Staff Member
                            </h3>
                            <div class="mt-4">
                                <form @submit.prevent="submitForm">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Passcode</label>
                                        <input
                                            v-model="form.passcode"
                                            type="password"
                                            required
                                            minlength="4"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                        <select
                                            v-model="form.role"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                            <option value="">Select Role</option>
                                            <option value="ST">Staff (ST)</option>
                                            <option value="SV">Supervisor (SV)</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">User ID</label>
                                        <input
                                            v-model="form.userid"
                                            type="number"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Image URL (Optional)</label>
                                        <input
                                            v-model="form.image"
                                            type="url"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                    </div>

                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button
                                            type="submit"
                                            :disabled="createForm.processing"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                                        >
                                            {{ createForm.processing ? 'Creating...' : 'Create Staff' }}
                                        </button>
                                        <button
                                            type="button"
                                            @click="closeModal"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['toggle-active'])

const form = reactive({
    name: '',
    passcode: '',
    role: '',
    userid: '',
    image: ''
})

const createForm = useForm({})

const submitForm = () => {
    createForm.post('/staff', {
        data: form,
        onSuccess: () => {
            closeModal()
            resetForm()
        },
        onError: (errors) => {
            console.error('Create staff errors:', errors)
        }
    })
}

const closeModal = () => {
    emit('toggle-active')
}

const resetForm = () => {
    form.name = ''
    form.passcode = ''
    form.role = ''
    form.userid = ''
    form.image = ''
}
</script>