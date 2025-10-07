<script setup>
import { useRouter } from 'vue-router';
import Create from "@/Components/Storetable/Create.vue";
import Update from "@/Components/Storetable/Update.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Excel from "@/Components/Exports/Excel.vue";
import Add from "@/Components/Svgs/Add.vue";
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const storeid = ref('');
const name = ref('');
const routes = ref('');
const types = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    rbostoretables: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'STOREID', title: 'StoreID' },
    { data: 'BLOCKED', title: 'BLOCKED' },
    { data: 'NAME', title: 'StoreName' },
    { data: 'ROUTES', title: 'Routes' },
    { data: 'TYPES', title: 'Types' },
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
    responsive: true,
};

const toggleUpdateModal = (newSTOREID, newNAME, newRoutes, newTYPES) => {
    storeid.value = newSTOREID;
    name.value = newNAME;
    routes.value = newRoutes;
    types.value = newTYPES;
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

const router = useRouter();
</script>

<template>
    <Main active-tab="STORE">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update 
                :show-modal="showModalUpdate"  
                :STOREID="storeid" 
                :NAME="name"  
                :ROUTES="routes" 
                :TYPES="types" 
                @toggle-active="updateModalHandler"
            />
        </template>

        <template v-slot:main>
            <TableContainer>
                <!-- Header with Add Button -->
                <div class="px-3 sm:px-4 md:px-6 pb-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Store Management</h2>
                        <PrimaryButton
                            type="button"
                            @click="toggleCreateModal"
                            class="bg-navy hover:bg-navy-dark"
                        >
                            <Add class="h-3 sm:h-4" />
                            <span class="hidden sm:inline ml-2">Add Store</span>
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden px-3 sm:px-4 space-y-3 pb-4">
                    <div
                        v-for="store in rbostoretables"
                        :key="store.STOREID"
                        class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-gray-900 text-sm sm:text-base">{{ store.NAME }}</h3>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-xs rounded-full',
                                            store.BLOCKED ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                                        ]"
                                    >
                                        {{ store.BLOCKED ? 'Blocked' : 'Active' }}
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-500 font-mono">ID: {{ store.STOREID }}</p>
                            </div>
                            <button
                                @click="toggleUpdateModal(store.STOREID, store.NAME, store.ROUTES, store.TYPES)"
                                class="text-blue-600 hover:text-blue-800 p-1"
                            >
                                <editblue class="h-5 w-5 sm:h-6 sm:w-6" />
                            </button>
                        </div>

                        <div class="space-y-1.5 text-xs sm:text-sm">
                            <div class="flex">
                                <span class="text-gray-500 min-w-[80px]">Routes:</span>
                                <span class="text-gray-900 font-medium">{{ store.ROUTES || 'N/A' }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-500 min-w-[80px]">Types:</span>
                                <span class="text-gray-900 font-medium">{{ store.TYPES || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <DataTable :data="rbostoretables" :columns="columns" class="w-full relative display" :options="options">
                        <template #action="data">
                            <div class="flex justify-start">
                                <TransparentButton
                                    type="button"
                                    @click="toggleUpdateModal(data.cellData.STOREID, data.cellData.NAME, data.cellData.ROUTES, data.cellData.TYPES)"
                                    class="me-1"
                                >
                                    <editblue class="h-6 cursor-pointer"></editblue>
                                </TransparentButton>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </TableContainer>
        </template>
    </Main>
</template>
