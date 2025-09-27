<script setup>
import Create from "@/Components/AppVersion/Create.vue";
import Update from "@/Components/AppVersion/Update.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Add from "@/Components/Svgs/Add.vue";
import editblue from "@/Components/Svgs/editblue.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { useForm } from '@inertiajs/vue3';

DataTable.use(DataTablesCore);

// Reactive variables for update modal
const versionId = ref('');
const versionNumber = ref('');
const versionName = ref('');
const releaseNotes = ref('');
const downloadUrl = ref('');
const forceUpdate = ref(false);
const minSupportedVersion = ref('');
const isActive = ref(false);

const showModalUpdate = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    versions: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'version_number', title: 'Version' },
    { data: 'version_name', title: 'Name' },
    {
        data: 'is_active',
        title: 'Status',
        render: function(data) {
            return data ? '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Active</span>' :
                          '<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Inactive</span>';
        }
    },
    {
        data: 'force_update',
        title: 'Force Update',
        render: function(data) {
            return data ? '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Yes</span>' :
                          '<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">No</span>';
        }
    },
    { data: 'min_supported_version', title: 'Min Version' },
    {
        data: 'created_at',
        title: 'Created',
        render: function(data) {
            return new Date(data).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }
    },
    {
        data: null,
        render: '#action',
        title: 'Actions'
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
    order: [[5, 'desc']], // Order by created date desc
};

const toggleUpdateModal = (version) => {
    versionId.value = version.id;
    versionNumber.value = version.version_number;
    versionName.value = version.version_name;
    releaseNotes.value = version.release_notes || '';
    downloadUrl.value = version.download_url || '';
    forceUpdate.value = version.force_update;
    minSupportedVersion.value = version.min_supported_version;
    isActive.value = version.is_active;
    showModalUpdate.value = true;
};

const toggleCreateModal = () => {
    showCreateModal.value = true;
};

const updateModalHandler = () => {
    showModalUpdate.value = false;
};

const createModalHandler = () => {
    showCreateModal.value = false;
};

const deleteForm = useForm({});
const setActiveForm = useForm({});

const deleteVersion = (version) => {
    if (version.is_active) {
        alert('Cannot delete the active version. Please set another version as active first.');
        return;
    }

    if (confirm(`Are you sure you want to delete version ${version.version_number}?`)) {
        deleteForm.delete(`/app-versions/${version.id}`, {
            onSuccess: () => {
                // Page will reload automatically
            },
            onError: (errors) => {
                console.error('Delete version errors:', errors);
                alert('Error deleting version');
            }
        });
    }
};

const setActiveVersion = (version) => {
    if (version.is_active) {
        return; // Already active
    }

    if (confirm(`Set version ${version.version_number} as active?`)) {
        setActiveForm.post(`/app-versions/${version.id}/set-active`, {
            onSuccess: () => {
                // Page will reload automatically
            },
            onError: (errors) => {
                console.error('Set active version errors:', errors);
                alert('Error setting active version');
            }
        });
    }
};
</script>

<template>
    <Main active-tab="VERSION">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler" />
            <Update
                :show-modal="showModalUpdate"
                :version-id="versionId"
                :version-number="versionNumber"
                :version-name="versionName"
                :release-notes="releaseNotes"
                :download-url="downloadUrl"
                :force-update="forceUpdate"
                :min-supported-version="minSupportedVersion"
                :is-active="isActive"
                @toggle-active="updateModalHandler"
            />
        </template>

        <template v-slot:main>
            <TableContainer>
                <div class="absolute adjust">
                    <div class="flex justify-start items-center">
                        <PrimaryButton
                            type="button"
                            @click="toggleCreateModal"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-4" />
                        </PrimaryButton>
                    </div>
                </div>

                <DataTable :data="versions" :columns="columns" class="w-full relative display" :options="options">
                    <template #action="data">
                        <div class="flex justify-start space-x-1">
                            <!-- Set Active Button -->
                            <TransparentButton
                                v-if="!data.cellData.is_active"
                                type="button"
                                @click="setActiveVersion(data.cellData)"
                                class="text-green-600 hover:text-green-800"
                                title="Set Active"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </TransparentButton>

                            <!-- Edit Button -->
                            <TransparentButton
                                type="button"
                                @click="toggleUpdateModal(data.cellData)"
                                title="Edit"
                            >
                                <editblue class="h-4" />
                            </TransparentButton>

                            <!-- Delete Button -->
                            <TransparentButton
                                v-if="!data.cellData.is_active"
                                type="button"
                                @click="deleteVersion(data.cellData)"
                                class="text-red-600 hover:text-red-800"
                                title="Delete"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </TransparentButton>
                        </div>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>