<script setup>
import Create from "@/Components/Staff/Create.vue";
import Update from "@/Components/Staff/Update.vue";
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

const id = ref('');
const name = ref('');
const passcode = ref('');
const role = ref('');
const userid = ref('');
const image = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    staff: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'id', title: 'ID' },
    { data: 'name', title: 'Name' },
    {
        data: 'role',
        title: 'Role',
        render: function(data) {
            return data === 'ST' ? 'Staff' : data === 'SV' ? 'Supervisor' : data;
        }
    },
    {
        data: 'passcode',
        title: 'Passcode',
        render: function() {
            return '••••';
        }
    },
    { data: 'storeid', title: 'Store' },
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
};

const toggleUpdateModal = (staffId, staffName, staffPasscode, staffRole, staffUserid, staffImage) => {
    id.value = staffId;
    name.value = staffName;
    passcode.value = staffPasscode;
    role.value = staffRole;
    userid.value = staffUserid;
    image.value = staffImage || '';
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

const deleteStaff = (staffId, staffName) => {
    if (confirm(`Are you sure you want to delete ${staffName}?`)) {
        deleteForm.delete(`/staff/${staffId}`, {
            onSuccess: () => {
                // Page will reload automatically
            },
            onError: (errors) => {
                console.error('Delete staff errors:', errors);
                alert('Error deleting staff member');
            }
        });
    }
};
</script>

<template>
    <Main active-tab="STAFF">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler" />
            <Update
                :show-modal="showModalUpdate"
                :staff-id="id"
                :name="name"
                :passcode="passcode"
                :role="role"
                :userid="userid"
                :image="image"
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

                <DataTable :data="staff" :columns="columns" class="w-full relative display" :options="options">
                    <template #action="data">
                        <div class="flex justify-start">
                            <TransparentButton
                                type="button"
                                @click="toggleUpdateModal(
                                    data.cellData.id,
                                    data.cellData.name,
                                    data.cellData.passcode,
                                    data.cellData.role,
                                    data.cellData.userid,
                                    data.cellData.image
                                )"
                                class="me-1"
                            >
                                <editblue class="h-4" />
                            </TransparentButton>

                            <TransparentButton
                                type="button"
                                @click="deleteStaff(data.cellData.id, data.cellData.name)"
                                class="me-1 text-red-600 hover:text-red-800"
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