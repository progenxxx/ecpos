<script setup>
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import { useRouter } from 'vue-router'
import { computed } from 'vue'
import Create from "@/Components/CRUD/Customers/Create.vue";
import Update from "@/Components/CRUD/Customers/Update.vue";
import Delete from "@/Components/CRUD/Customers/Delete.vue";
import More from "@/Components/CRUD/Customers/More.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Excel from "@/Components/Exports/Excel.vue";

import Add from "@/Components/Svgs/Add.vue";    
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import Import from "@/Components/Svgs/Import.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";
import Back from "@/Components/Svgs/Back.vue";

import TableContainer from "@/Components/Tables/TableContainer.vue";
/* import Main from "@/Layouts/Main.vue"; */
import { ref } from "vue";


import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const accountnum = ref('');
const name = ref('');
const address = ref('');
const currency = ref('');
const phone = ref('');
const blocked = ref('');
const creditmax = ref('');
const country = ref('');
const zipcode = ref('');
const state = ref('');
const email = ref('');
const cellularphone = ref('');
const gender = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);
const showModalMore = ref(false);

const props = defineProps({
    customers: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'accountnum', title: 'ACCOUNTNUM' },
    { data: 'name', title: 'NAME' },
    { data: 'address', title: 'ADDRESS' },
    { data: 'phone', title: 'PHONE' },
    /* { data: 'currency', title: 'CURRENCY' },
    { data: 'blocked', title: 'BLOCKED' },
    { data: 'creditmax', title: 'CREDITMAX' },
    { data: 'country', title: 'COUNTRY' },
    { data: 'zipcode', title: 'ZIPCODE' },
    { data: 'county', title: 'COUNTY' }, */
    { data: 'email', title: 'EMAIL' },
    /* { data: 'cellularphone', title: 'CELLULARPHONE' }, */
    /* { data: 'dataareaid', title: 'DATAAREAID' }, */
    { data: 'gender', title: 'GENDER' },
    {
        data: null,
        render: '#action',
        title: 'ACTIONS'
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
    responsive: true,
};


const toggleUpdateModal = (newACCOUNTNUM, newNAME, newADDRESS, newPHONE, newCURRENCY, newBLOCKED, newCREDITMAX, newCOUNTRY, newZIPCODE, newSTATE, newEMAIL, newCELLULARPHONE, newGENDER) => {
    
    accountnum.value = newACCOUNTNUM;
    name.value = newNAME;
    address.value = newADDRESS;
    phone.value = newPHONE;
    currency.value = newCURRENCY;
    blocked.value = newBLOCKED;
    creditmax.value = newCREDITMAX;
    country.value = newCOUNTRY;
    zipcode.value = newZIPCODE;
    state.value = newSTATE;
    email.value = newEMAIL;
    cellularphone.value = newCELLULARPHONE;
    /* dataareaid.value = newDATAAREAID; */
    gender.value = newGENDER;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newACCOUNTNUM) => {
    accountnum.value = newACCOUNTNUM;
    showDeleteModal.value = true;
};

const toggleCreateModal = () => {
    showCreateModal.value = true;
};

const toggleMoreModal = (newACCOUNTNUM) => {
    accountnum.value = newACCOUNTNUM;
    showModalMore.value = true;
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
const MoreModalHandler = () => {
    showModalMore.value = false;
};

const router = useRouter()

/* const accountNumber = computed(() => {
  return props.data && props.data.cellData ? props.data.cellData.accountnum : ''
})

const redirectToLedger = (accountnum) => {
  if (accountnum) {
    router.push({ name: 'ledger', params: { accountnum } })
  }
} */

/* const navigateToCustomerLedger = () => {
  window.alert('You are Redirecting to Customer Ledger Entries');
  window.location.href = '/ledger/$accountnum';
}; */

/* const navigateToCustomerLedger = (accountnum) => {
  console.log('Redirecting to Customer Ledger Entries for account:', accountnum);
  window.location.href = `/ledger/${accountnum}`;
}; */

const navigateToCustomerLedger = (accountnum) => {
  console.log('Redirecting to Customer Ledger Entries for account:', accountnum);
  window.location.href = `/ledger/${accountnum}`;
};

</script>

<template>
    <Main active-tab="CUSTOMERS">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate"  :accountnum="accountnum" :name="name" :address="address" :currency="currency" :phone="phone" :blocked="blocked" :creditmax="creditmax" :country="country"
            :zipcode="zipcode" :state="state" :email="email" :cellularphone="cellularphone" :gender="gender" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="customers" :accountnum="accountnum" @toggle-active="deleteModalHandler"  />

            <More
            :show-modal="showModalMore"
            :accountnum="accountnum"
            @toggle-active="MoreModalHandler"
            />
        </template>

        <template v-slot:main>
            <TableContainer>
                <!-- Header with Actions -->
                <div class="px-3 sm:px-4 md:px-6 pb-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Customers</h2>
                        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                            <PrimaryButton
                                type="button"
                                @click="toggleCreateModal"
                                class="bg-navy hover:bg-navy-dark flex-1 sm:flex-none"
                            >
                                <Add class="h-3 sm:h-4" />
                                <span class="hidden sm:inline ml-2">Add Customer</span>
                            </PrimaryButton>
                            <Excel
                                :data="customers"
                                :headers="['ACCOUNTNUM', 'NAME', 'ADDRESS', 'PHONE', 'EMAIL', 'GENDER']"
                                :row-name-props="['accountnum', 'name', 'address', 'phone', 'email', 'gender']"
                                class="flex-1 sm:flex-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden px-3 sm:px-4 space-y-3 pb-4 max-h-[70vh] overflow-y-auto">
                    <div
                        v-for="customer in customers"
                        :key="customer.accountnum"
                        class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base mb-1">{{ customer.name }}</h3>
                                <p class="text-xs sm:text-sm text-gray-500 font-mono">{{ customer.accountnum }}</p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <button
                                    @click="toggleUpdateModal(
                                        customer.accountnum, customer.name, customer.address, customer.phone,
                                        customer.currency, customer.blocked, customer.creditmax, customer.country,
                                        customer.zipcode, customer.state, customer.email, customer.cellularphone,
                                        customer.gender)"
                                    class="text-blue-600 hover:text-blue-800 p-1"
                                    title="Edit"
                                >
                                    <editblue class="h-5 w-5 sm:h-6 sm:w-6" />
                                </button>
                                <button
                                    @click="navigateToCustomerLedger(customer.accountnum)"
                                    class="text-gray-600 hover:text-gray-800 p-1"
                                    title="View Ledger"
                                >
                                    <moreblue class="h-5 w-5 sm:h-6 sm:w-6" />
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1.5 text-xs sm:text-sm">
                            <div class="flex" v-if="customer.address">
                                <span class="text-gray-500 min-w-[70px]">Address:</span>
                                <span class="text-gray-900 flex-1 break-words">{{ customer.address }}</span>
                            </div>
                            <div class="flex" v-if="customer.phone">
                                <span class="text-gray-500 min-w-[70px]">Phone:</span>
                                <span class="text-gray-900">{{ customer.phone }}</span>
                            </div>
                            <div class="flex" v-if="customer.email">
                                <span class="text-gray-500 min-w-[70px]">Email:</span>
                                <span class="text-gray-900 break-words">{{ customer.email }}</span>
                            </div>
                            <div class="flex" v-if="customer.gender">
                                <span class="text-gray-500 min-w-[70px]">Gender:</span>
                                <span class="text-gray-900">{{ customer.gender }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <DataTable :data="customers" :columns="columns" class="w-full relative display" :options="options">
                        <template #action="data">
                            <div class="flex justify-start">
                                <TransparentButton type="button" @click="toggleUpdateModal(
                                    data.cellData.accountnum, data.cellData.name, data.cellData.address, data.cellData.phone,
                                    data.cellData.currency, data.cellData.blocked, data.cellData.creditmax, data.cellData.country,
                                    data.cellData.zipcode, data.cellData.state, data.cellData.email, data.cellData.cellularphone,
                                    data.cellData.gender)" class="me-1">
                                    <editblue class="h-6 cursor-pointer"></editblue>
                                </TransparentButton>

                                <TransparentButton
                                    class="ml-5 cursor-pointer"
                                    @click="navigateToCustomerLedger(data.cellData.accountnum)"
                                >
                                    <moreblue class="h-6"></moreblue>
                                </TransparentButton>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </TableContainer>
        </template>
    </Main>
</template>


