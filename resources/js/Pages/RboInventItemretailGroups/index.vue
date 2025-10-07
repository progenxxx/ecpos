<script setup>
import Create from "@/Components/Inventory/rboinventitemretailgroups/Create.vue";
import Update from "@/Components/Inventory/rboinventitemretailgroups/Update.vue";
import Delete from "@/Components/Inventory/rboinventitemretailgroups/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/RetailPanel.vue";
import { ref } from "vue";
import Excel from "@/Components/Exports/Excel.vue";

import Add from "@/Components/Svgs/Add.vue";
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";


import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const GROUPID = ref('');
const NAME = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    rboinventitemretailgroups: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'GROUPID', title: 'GROUPID' },
    { data: 'NAME', title: 'NAME' },
   
    {
        data: null,
        render: '#action',
        title: 'Action'
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
    responsive: true,
};


const toggleUpdateModal = (newGROUPID, newNAME) => {
    GROUPID.value = newGROUPID;
    NAME.value = newNAME;
  
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newGROUPID) => {
    GROUPID.value = newGROUPID;
    showDeleteModal.value = true;
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
const deleteModalHandler = () => {
    showDeleteModal.value = false;
};

</script>

<template>
    <Main active-tab="RETAILGROUP">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate" :GROUPID="GROUPID" :NAME="NAME" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="rboinventitemretailgroups" :GROUPID="GROUPID" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <TableContainer>
                <!-- Header with Add Button -->
                <div class="px-3 sm:px-4 md:px-6 pb-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Retail Groups</h2>
                        <PrimaryButton
                            type="button"
                            @click="toggleCreateModal"
                            class="bg-navy hover:bg-navy-dark"
                        >
                            <Add class="h-3 sm:h-4" />
                            <span class="hidden sm:inline ml-2">Add Group</span>
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden px-3 sm:px-4 space-y-3 pb-4">
                    <div
                        v-for="group in rboinventitemretailgroups"
                        :key="group.GROUPID"
                        class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base mb-1">{{ group.NAME }}</h3>
                                <p class="text-xs sm:text-sm text-gray-500">ID: {{ group.GROUPID }}</p>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-3">
                            <button
                                @click="toggleUpdateModal(group.GROUPID, group.NAME)"
                                class="flex-1 px-3 py-1.5 bg-blue-600 text-white text-xs sm:text-sm rounded hover:bg-blue-700 transition"
                            >
                                Update
                            </button>
                            <button
                                @click="toggleDeleteModal(group.GROUPID)"
                                class="flex-1 px-3 py-1.5 bg-red-600 text-white text-xs sm:text-sm rounded hover:bg-red-700 transition"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <DataTable :data="rboinventitemretailgroups" :columns="columns" class="w-full relative display" :options="options">
                        <template #action="data">
                            <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.GROUPID, data.cellData.NAME)" class="me-1">
                                Update
                            </PrimaryButton>
                            <DangerButton type="button" @click="toggleDeleteModal(data.cellData.GROUPID)">
                                Delete
                            </DangerButton>
                        </template>
                    </DataTable>
                </div>
            </TableContainer>
        </template>
    </Main>
</template>
