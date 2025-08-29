<script setup>
import Create from "@/Components/ItemOrders/Create.vue";
import GetBWP from "@/Components/ItemOrders/GetBWP.vue";
import CopyFrom from "@/Components/ItemOrders/CopyFrom.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Trash from "@/Components/Svgs/Trash.vue";
import Back from "@/Components/Svgs/Back.vue";
import Add from "@/Components/Svgs/Add.vue";
import Generate from "@/Components/Svgs/Generate.vue";
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from "vue";
import axios from 'axios';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const JOURNALID = ref('');
const COUNTED = ref('');
const LINENUM = ref('');

const showGetCFModal = ref(false);
const showGetBWModal = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    inventjournaltransrepos: {
        type: Array,
        required: true,
    },
    inventjournaltrans: {
        type: Array,
        required: true,
    },
    journalid: {
        type: [String, Number],
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'ITEMID', title: 'ITEMID' },
    { data: 'itemname', title: 'ITEMNAME' },
    { data: 'itemgroup', title: 'CATEGORY' },
    {
        data: 'COUNTED',
        title: 'COUNTED',
        render: function(data, type, row) {
            if (type === 'display') {
                return `<input type="number" class="counted-input form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="${Number(data).toFixed(0)}" min="0" step="1">`;
            }
            return data;
        }
    },

];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
    drawCallback: function(settings) {
        const api = this.api();
        api.rows().every(function() {
            const rowData = this.data();
            const node = this.node();
            const input = node.querySelector('.counted-input');
            if (input) {
                input.addEventListener('change', (event) => handleCountedChange(event, rowData));
            }
        });
    }
};

const toggleCreateModal = (journalid, newLINENUM) => {
    JOURNALID.value = journalid;
    LINENUM.value = newLINENUM;
    showCreateModal.value = true;

};

const toggleGetBWModal = (journalid) => {
    JOURNALID.value = journalid;
    showGetBWModal.value = true;

};

const toggleGetCFModal = (journalid) => {
    JOURNALID.value = journalid;
    showGetCFModal.value = true;

};

const createModalHandler = () => {
    showCreateModal.value = false;
};

const GetBWModalHandler = () => {
    showGetBWModal.value = false;
};

const GetCFModalHandler = () => {
    showGetCFModal.value = false;
};

const handleCountedChange = async (event, rowData) => {
    const newValue = event.target.value;
    const itemId = rowData.ITEMID;
    const journalId = props.journalid;

    try {
        await updateCountedValue(journalId, itemId, newValue);

        rowData.COUNTED = newValue;
    } catch (error) {

        event.target.value = rowData.COUNTED;
    }
};

const updateCountedValue = async (journalId, itemId, newValue) => {
    try {
        const response = await axios.post('/api/update-counted-value', {
            journalId,
            itemId,
            newValue
        });

        if (response.data.success) {

        } else {
            throw new Error('Update failed');
        }
    } catch (error) {

        throw error;
    }
};

const form = useForm({
    StartDate: '',
    EndDate: '',
  });

const ItemOrders = () => {
  window.location.href = '/order';
};

const DeleteOrders = () => {
  window.location.href = '/DeleteOrders';
};

const handleSelectedItem = (item) => {

};

</script>

<template>
    <Main active-tab="ORDER">
        <template v-slot:modals>
            <Create
                :show-modal="showCreateModal"
                :JOURNALID="JOURNALID"
                :items="props.items"
                @toggle-active="createModalHandler"
                @select-item="handleSelectedItem"
            />
            <GetBWP
                :show-modal="showGetBWModal"
                :JOURNALID="JOURNALID"
                @toggle-active="GetBWModalHandler"
            />
            <CopyFrom
                :show-modal="showGetCFModal"
                :JOURNALID="JOURNALID"
                @toggle-active="GetCFModalHandler"
            />
        </template>

        <template v-slot:main>
            <TableContainer>
                <div class="absolute adjust">
                    <div class="flex justify-start items-center">
                        <PrimaryButton
                        type="button"
                        @click="ItemOrders"
                        class="m-1 ml-2 bg-navy p-10 "
                        >
                        <Back class="h-5" />
                        </PrimaryButton>

                        <!-- <PrimaryButton
                            type="button"
                            @click="toggleCreateModal(journalid)"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-5" />
                        </PrimaryButton> -->

                        <!-- <SecondaryButton
                        type="button"
                        @click="toggleGetCFModal(journalid)"
                        class="m-1 ml-2 bg-navy p-10 "
                        >
                        COPY FROM
                        </SecondaryButton> -->

                        <PrimaryButton
                        type="button"
                        @click="toggleGetBWModal(journalid)"
                        class="m-1 ml-2 bg-navy p-10 "
                        >
                        <!-- <Generate class="h-5" /> -->
                         GENERATE
                        </PrimaryButton>

                        <PrimaryButton
                        type="button"
                        @click="updateAllCountedValues"
                        class="m-1 ml-2 bg-navy p-10 "
                        >
                         Save
                        </PrimaryButton>

                        <!-- <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                        <input type="hidden" name="_token" :value="$page.props.csrf_token">
                        <div date-rangepicker  class="flex items-center">

                        <div class="relative">
                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http:
                            </div>

                            <input
                            id="EndDate"
                            type="date"
                            v-model="form.EndDate"
                            @input="formattedDate2"
                            :placeholder="formattedDate2"
                            class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date end"
                            required
                            />
                            <InputError :message="form.errors.EndDate" class="mt-2" />
                        </div>
                    </div>
                    </form> -->
                    </div>
                </div>
                <DataTable
                    :data="inventjournaltransrepos"
                    :columns="columns"
                    class="w-full relative display"
                    :options="options"
                >
                    <template #action="data">
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 rounded-full" />
                            <span class="ml-2 text-gray-700"></span>
                        </label>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>