<script setup>
import { useRouter, useRoute } from 'vue-router';
import Create from "@/Components/Partycakes/Create.vue";
import Update from "@/Components/Partycakes/Update.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Main from "@/Layouts/Main.vue";
import Excel from "@/Components/Exports/Excel.vue";
import Add from "@/Components/Svgs/Add.vue";
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, onMounted, reactive, watch } from "vue";
import { computed, defineProps, toRefs, nextTick } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import axios from 'axios';
DataTable.use(DataTablesCore);

const id = ref('');
const subject = ref('');
const description = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    partycakes: {
        type: Array,
        required: true,
    },
    partycakes1: {
        type: Array,
        required: true,
    },
    partycakes2: {
        type: Array,
        required: true,
    },
    partycakes3: {
        type: Array,
        required: true,
    },
    rbostoretables: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'id', title: 'ID' },
    { data: 'COSNO', title: 'COs No.' },
    { data: 'BRANCH', title: 'BRANCH' },
    { data: 'DATEORDER', title: 'DATEORDER' },
    { data: 'DATEPICKEDUP', title: 'DATEPICKEDUP' },
    { data: 'TIMEPICKEDUP', title: 'DATEPICKEDUP' },
    { data: 'DATEDELIVERED', title: 'DATEDELIVERED' },
    { data: 'TIMEDELIVERED', title: 'TIMEDELIVERED' },
    {
        data: 'created_at',
        title: 'DATE',
        render: function(data, type, row) {
            if (type === 'display' || type === 'filter') {
                return data.split('T')[0];
            }
            return data;
        }
    },
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
};

const toggleUpdateModal = (newID, newSUBJECT, newDESCRIPTION) => {
    id.value = newID;
    subject.value = newSUBJECT;
    description.value = newDESCRIPTION;
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

const route = useRoute()
const router = useRouter()

const cake = reactive({
  COSNO: '',
  BRANCH: '',
  DATEORDER: '',
  DATEPICKEDUP: '',
  TIMEPICKEDUP: '',
  DELIVERED: '',
  TIMEDELIVERED: '',
  BDAYCODENO: '',
  FLAVOR: '',
  MOTIF: '',
  ICING: '',
  DEDICATION: '',
  OTHERS: '',
  file_path: null
})

const cakeId = ref(null)

onMounted(async () => {
  if (route.params && route.params.id) {
    cakeId.value = route.params.id
    try {
      const response = await axios.get(`/api/partys-cakes/${cakeId.value}`)
      Object.assign(cake, response.data)
    } catch (error) {

     => {
  window.location.href = `/pc-posted/${id}`;
};

const process = (id) => {
  window.location.href = `/pc-process/${id}`;
};

const dr = (id) => {
  window.location.href = `/pc-received/${id}`;
};

const { user } = toRefs(props.auth);
const userRole = ref(user.value.role);
const isOpic = computed(() => userRole.value === 'OPIC');
const isStore = computed(() => userRole.value === 'STORE');

const handleSelectedStore = (rbostoretables) => {

};

const getFileUrl = (filePath) => {
    return `/storage/${filePath}`;
};

const getDownloadUrl = (cakeId) => {
  return `/api/cakes/${cakeId}/download`
}

const searchQuery = ref('');
const filteredCakes = ref([]);

const searchCakes = () => {
    if (!searchQuery.value) {
        filteredCakes.value = props.partycakes2;
    } else {
        const query = searchQuery.value.toLowerCase();
        filteredCakes.value = props.partycakes2.filter(cake =>
            (cake.COSNO && cake.COSNO.toLowerCase().includes(query)) ||
            (cake.BRANCH && cake.BRANCH.toLowerCase().includes(query)) ||
            (cake.DATEORDER && cake.DATEORDER.toLowerCase().includes(query)) ||
            (cake.FLAVOR && cake.FLAVOR.toLowerCase().includes(query)) ||
            (cake.DEDICATION && cake.DEDICATION.toLowerCase().includes(query))
        );
    }
};

filteredCakes.value = props.partycakes2;

watch(searchQuery, () => {
    searchCakes();
});

</script>

<template>
    <Main active-tab="PARTYCAKES">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  :rbostoretables="props.rbostoretables"  @select-item="handleSelectedStore"/>
            <Update
                :show-modal="showModalUpdate"
                :ID="id"
                :SUBJECT="subject"
                :DESCRIPTION="description"
                @toggle-active="updateModalHandler"
            />

        </template>

        <template v-slot:main>
            <!-- <TableContainer>
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

                <DataTable :data="announcements" :columns="columns" class="w-full relative display" :options="options">
                    <template #action="data">
                        <div class="flex justify-start">
                            <TransparentButton
                                type="button"
                                @click="toggleUpdateModal(data.cellData.id, data.cellData.SUBJECT, data.cellData.DESCRIPTION)"
                                class="me-1"
                            >
                                <editblue class="h-6 cursor-pointer"></editblue>
                            </TransparentButton>
                        </div>
                    </template>
                </DataTable>
            </TableContainer> -->

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

                <div role="tablist" className="tabs tabs-lifted mt-10 p-5 ">
                    <input type="radio" v-if="isStore" name="my_tabs_2" role="tab" className="tab !bg-gray-100 !text-gray-500 !font-bold" aria-label="COS" defaultChecked />
                    <div role="tabpanel" className="tab-content !bg-gray-200 !border-gray-300 p-6">
                        ORDER STORE

                        <!-- <div className="flex flex-wrap gap-4">
                            <div className="card bg-base-100 w-full w-full sm:w-6/12 shadow-xl">
                                <div className="card-body">
                                <div className="card-actions justify-end">
                                    <button className="btn btn-square btn-sm">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http:
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http:
                                    </svg>
                                    </button>
                                </div>
                                <h2 class="absolute hidden sm:block md:block lg:block">Party Cake Informations</h2>

                                <form>

                                    <div class="grid gap-6 mb-6 md:grid-cols-3">
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>
                                    <div class="grid gap-6 mb-6 md:grid-cols-3">
                                        <div>
                                            <label for="company" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Customer</label>
                                            <input type="text" id="company" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-whitedark:focus:ring-blue-500 dark:focus:border-blue-500" readonly>
                                        </div>
                                        <div>
                                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                                            <input type="tel" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-whitedark:focus:ring-blue-500 dark:focus:border-blue-500"  pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" readonly />
                                        </div>
                                        <div>
                                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone number</label>
                                            <input type="tel" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-whitedark:focus:ring-blue-500 dark:focus:border-blue-500"  pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" readonly />
                                        </div>
                                    </div>

                                    <div class="grid gap-6 mb-6 md:grid-cols-4">
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>

                                    <div class="grid gap-6 mb-6 md:grid-cols-4">
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                        <input type="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  readonly />
                                    </div>

                                    <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="message" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
                                </form>

                                </div>
                            </div>
                        </div> -->

                        <!-- <div class="flex flex-wrap gap-4">
                            <div v-for="cake in partycakes" :key="cake.id" class="card bg-base-100 w-full sm:w-6/12 shadow-xl">
                            <div class="card-body">
                                <div class="card-actions justify-end">
                                <button class="btn btn-square btn-sm">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http:
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http:
                                    </svg>
                                </button>
                                </div>
                                <h2 class="absolute hidden sm:block md:block lg:block">Party Cake Information</h2>

                                <form>
                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                    <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                    <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                    <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="customer" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Customer</label>
                                    <input type="text" id="customer" v-model="cake.CUSTOMERNAME" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly>
                                    </div>
                                    <div>
                                    <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                                    <input type="text" id="address" v-model="cake.ADDRESS" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone number</label>
                                    <input type="tel" id="phone" v-model="cake.TELNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div>

                                <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </form>
                            </div>
                        </div>
                    </div> -->

                    <div class="container mx-auto px-4">
                        <div class="flex flex-wrap -mx-4">

                            <template v-if="!partycakes || partycakes.length === 0">
                                <div class="col-span-full text-center mt-8">
                                    <div class="!bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                                        <p class="text-gray-600 text-base sm:text-lg">No orders</p>
                                    </div>
                                </div>
                            </template>
                        <div v-for="cake in partycakes" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card !bg-gray-100 shadow-xl h-full">
                                <div class="card-body">
                                    <div class="card-actions justify-end">
                                        <!-- <button type="submit" class="btn btn-square btn-sm" id="UPDATE">
                                            <svg fill="#616161" viewBox="0 0 32 32" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;" version="1.1" xml:space="preserve" xmlns="http:
                                        </button> -->

                                        <button type="button" @click="posted(cake.id)" class="btn btn-square !bg-gray-100 btn-sm" id="POST">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http:
                                        </button>
                                    </div>
                                    <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                    <form @submit.prevent="updatePartyCake">
                                    <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                            <div>
                                            <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                            <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                            </div>
                                            <div>
                                            <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                            <input type="text" id="transactstore" v-model="cake.TRANSACTSTORE" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                            </div>
                                            <div>
                                            <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Del. Address</label>
                                            <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                            </div>
                                            <div>
                                            <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                            <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <!-- <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div> -->
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">

                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div>

                                <div>
                                    <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </div>
                                </form>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- <div class="container mx-auto px-4">
                        <div class="flex flex-wrap -mx-4">
                        <div v-for="cake in partycakes" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card bg-base-100 shadow-xl h-full">
                            <div class="card-body">
                                <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                <form @submit.prevent="updateCake(cake)">
                                    <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                        <div>
                                        <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                        <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                        </div>
                                        <div>
                                        <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                        <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                        </div>
                                        <div>
                                        <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                        <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <button type="button" @click="deleteCake(cake)" class="btn btn-error">Delete</button>

                                    <div class="card-actions justify-end mt-2">
                                        <button type="submit" class="btn btn-square btn-sm" id="UPDATE">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http:

                                            </svg>
                                        </button>

                                        <button type="button" @click="deleteCake(cake)" class="btn btn-square btn-sm" id="DELETE">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http:
                                            </svg>
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

        </div>
                    <input
                        type="radio"
                        name="my_tabs_2"
                        role="tab"
                        className="tab !bg-gray-100 !text-gray-500 !font-bold"
                        aria-label="CS"
                        />
                    <div role="tabpanel" className="tab-content !bg-gray-200 !border-gray-300 p-6 ">
                        PENDING

                        <div class="container mx-auto px-4">
                        <div class="flex flex-wrap -mx-4">
                            <template v-if="!partycakes1 || partycakes1.length === 0">
                                <div class="col-span-full text-center mt-8">
                                    <div class="!bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                                        <p class="text-gray-600 text-base sm:text-lg">No Pending</p>
                                    </div>
                                </div>
                            </template>
                        <div v-for="cake in partycakes1" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card !bg-gray-100 shadow-xl h-full">
                            <div class="card-body">
                                <div class="card-actions justify-end">

                                    <button type="button" @click="process(cake.id)" class="btn btn-square !bg-gray-100 btn-sm" id="POST">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http:
                                        </button>
                                </div>
                                <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                <form>

                                <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-3">
                                    <div v-if="cake.file_path" class="mt-2">
                                        <a :href="getDownloadUrl(cake.id)" class="btn !btn-primary !text-white btn-xs sm:btn-sm">
                                            Download Attachment
                                        </a>
                                    </div>
                                </div>

                                <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                    <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">STORE</label>
                                    <input type="text" id="transactstore" v-model="cake.TRANSACTSTORE" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Del. Address</label>
                                    <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                    <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <!-- <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div> -->
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div>

                                <div>
                                    <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </div>
                                </form>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input
                type="radio"
                name="my_tabs_2"
                role="tab"
                className="tab !bg-gray-100 !text-gray-500 !font-bold"
                aria-label="RECORDS"
            />
            <div role="tabpanel" className="tab-content !bg-gray-200 border-base-300 p-6">
                RECORDS

                <div class="container mx-auto px-4">
                    <!-- Search input -->
                    <div class="mb-4">
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search Party Cakes..."
                            class="w-full p-2 border rounded-lg"
                        >
                    </div>

                    <div class="flex flex-wrap -mx-4">
                        <template v-if="!filteredCakes || filteredCakes.length === 0">
                            <div class="col-span-full text-center mt-8">
                                <div class="bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                                    <p class="text-gray-600 text-base sm:text-lg">No Records</p>
                                </div>
                            </div>
                        </template>
                        <div v-for="cake in filteredCakes" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card !bg-gray-100 shadow-xl h-full">
                            <div class="card-body">
                                <div class="card-actions justify-end">

                                    <!-- <button type="button" @click="dr(cake.id)" class="btn btn-square btn-sm" id="POST">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http:
                                        </button> -->

                                        <div v-if="cake.file_path" class="mt-2 absolute">
                                        <a :href="getDownloadUrl(cake.id)" class="btn !btn-primary !text-white btn-xs sm:btn-sm">
                                            Download Attachment
                                        </a>
                                    </div>
                                </div>
                                <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                <form>

                                <!-- <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-3">
                                    <div v-if="cake.file_path" class="mt-2">
                                        <a :href="getDownloadUrl(cake.id)" class="btn !btn-primary !text-white btn-xs sm:btn-sm">
                                            Download Attachment
                                        </a>
                                    </div>
                                </div> -->

                                <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                    <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                    <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                    <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <!-- <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div> -->
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div>

                                <div>
                                    <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </div>

                                <div class="grid gap-6 mb-6 mt-3 md:grid-cols-4">
                                        <div>
                                            <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">SRP</label>
                                            <input type="text" id="bdaycodeno" :value="Number(cake.SRP).toFixed(2)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">DISCOUNT</label>
                                            <input type="text" id="flavor" :value="Number(cake.DISCOUNT).toFixed(2)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PARTIAL PAYMENT</label>
                                            <input type="text" id="motif" :value="Number(cake.PARTIALPAYMENT).toFixed(2)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NETAMOUNT</label>
                                            <input type="text" id="icing" :value="Number(cake.NETAMOUNT).toFixed(2)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">BALANCE AMOUNT</label>
                                    <input type="text" id="dedication" :value="Number(cake.BALANCEAMOUNT).toFixed(2)" class="text-center text-white font-bold bg-red-500 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div>

                                </form>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                    <!-- <input
                        type="radio"
                        name="my_tabs_2"
                        role="tab"
                        className="tab"
                        aria-label="DR PROCESS"
                        />
                    <div role="tabpanel" className="tab-content bg-base-200 border-base-300 p-6">
                        DR PROCESS

                        <div class="container mx-auto px-4">
                        <div class="flex flex-wrap -mx-4">
                            <template v-if="!partycakes3 || partycakes3.length === 0">
                                <div class="col-span-full text-center mt-8">
                                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                                        <p class="text-gray-600 text-base sm:text-lg">No DR Process</p>
                                    </div>
                                </div>
                            </template>
                        <div v-for="cake in partycakes3" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card bg-base-100 shadow-xl h-full">
                            <div class="card-body">
                                <div class="card-actions justify-end">

                                    <button type="button" @click="dr(cake.id)" class="btn btn-square btn-sm" id="POST">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http:
                                        </button>
                                </div>
                                <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                <form>
                                <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                    <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                    <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                    <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div>

                                <div>
                                    <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </div>
                                </form>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

                    </div>

        </template>
    </Main>
</template>
