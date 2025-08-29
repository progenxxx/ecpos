<script setup>
import Main from "@/Layouts/Main.vue";  
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { router } from '@inertiajs/vue3';
DataTable.use(DataTablesCore);

const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');

const props = defineProps({
    // FIXED: Changed from 'ar' to 'bo' to match controller
    bo: {
        type: Array,
        required: true,
    },
    stores: {
        type: Array,
        required: true,
    },
    userRole: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
        default: () => ({
            startDate: '',
            endDate: '',
            selectedStores: []
        })
    }
});

onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
});

// FIXED: Remove client-side filtering since server handles it
// This eliminates conflicts between server and client filtering
const tableData = computed(() => {
    return props.bo; // Use server-filtered data directly
});

// FIXED: Updated columns to match Bad Orders data structure
const columns = [
    { data: 'storename', title: 'STORE' },
    { data: 'itemid', title: 'ITEM ID' },
    { data: 'itemname', title: 'ITEM NAME' },
    { data: 'category', title: 'CATEGORY' },
    { 
        data: 'batchdate', 
        title: 'BATCH DATE', 
        render: function(data, type, row) {
            if (!data) return '';
            const date = new Date(data);
            return date.toLocaleDateString();  
        }
    },
    { 
        data: 'waste_declaration_date', 
        title: 'WASTE DATE', 
        render: function(data, type, row) {
            if (!data) return '';
            const date = new Date(data);
            return date.toLocaleDateString();  
        }
    },
    { 
        data: 'price', 
        title: 'PRICE',
        render: function(data) {
            return '₱' + parseFloat(data || 0).toFixed(2);
        }
    },
    { 
        data: 'throw_away', 
        title: 'THROW AWAY',
        render: function(data) {
            return parseFloat(data || 0).toFixed(2);
        }
    },
    { 
        data: 'early_molds', 
        title: 'EARLY MOLDS',
        render: function(data) {
            return parseFloat(data || 0).toFixed(2);
        }
    },
    { 
        data: 'pull_out', 
        title: 'PULL OUT',
        render: function(data) {
            return parseFloat(data || 0).toFixed(2);
        }
    },
    { 
        data: 'rat_bites', 
        title: 'RAT BITES',
        render: function(data) {
            return parseFloat(data || 0).toFixed(2);
        }
    },
    { 
        data: 'ant_bites', 
        title: 'ANT BITES',
        render: function(data) {
            return parseFloat(data || 0).toFixed(2);
        }
    },
    { 
        data: 'total_price', 
        title: 'TOTAL PRICE',
        render: function(data) {
            return '₱' + parseFloat(data || 0).toFixed(2);
        }
    }
];

const options = {
    paging: true,
    pageLength: 25,
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
    dom: 'Bfrtip',
    buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
    ],
};

const handleFilterChange = () => {
    // FIXED: Improved date validation
    if (startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        
        if (start > end) {
            alert('Start date cannot be later than end date');
            return;
        }
    }
    
    // FIXED: Use correct route name for Bad Orders
    router.get(
        route('reports.bo'), // Changed from 'reports.ar' to 'reports.bo'
        {
            startDate: startDate.value || null,
            endDate: endDate.value || null,
            stores: selectedStores.value.length > 0 ? selectedStores.value : null
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

let filterTimeout;
watch([selectedStores, startDate, endDate], () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(handleFilterChange, 500);
}, { deep: true });

onUnmounted(() => {
    clearTimeout(filterTimeout);
});

// FIXED: Updated totals calculation for Bad Orders data
const totals = computed(() => {
    return tableData.value.reduce((acc, curr) => {
        acc.throw_away += parseFloat(curr.throw_away || 0);
        acc.early_molds += parseFloat(curr.early_molds || 0);
        acc.pull_out += parseFloat(curr.pull_out || 0);
        acc.rat_bites += parseFloat(curr.rat_bites || 0);
        acc.ant_bites += parseFloat(curr.ant_bites || 0);
        acc.total_price += parseFloat(curr.total_price || 0);
        return acc;
    }, {
        throw_away: 0,
        early_molds: 0,
        pull_out: 0,
        rat_bites: 0,
        ant_bites: 0,
        total_price: 0
    });
});

// FIXED: Clear filters function
const clearFilters = () => {
    selectedStores.value = [];
    startDate.value = '';
    endDate.value = '';
    // Trigger immediate filter change
    handleFilterChange();
};
</script>

<template>
    <Main active-tab="ORDER">
        <template v-slot:main>
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow">
                
                <div v-if="userRole.toUpperCase() === 'ADMIN' || userRole.toUpperCase() === 'SUPERADMIN'" class="flex-1 min-w-[200px]">
                  <MultiSelectDropdown
                    v-model="selectedStores"
                    :options="stores"
                    label="Stores"
                  />
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input
                        type="date"
                        v-model="startDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input
                        type="date"
                        v-model="endDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div class="flex items-end">
                    <button
                        @click="clearFilters"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <details class="collapse collapse-plus bg-gray-100 rounded-none">
                <summary class="collapse-title text-sm font-medium">BAD ORDERS SUMMARY</summary>
                <div class="collapse-content"> 
                    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-sm font-medium text-gray-500 uppercase">Total Throw Away</h3>
                            <p class="mt-1 text-lg font-semibold">{{ totals.throw_away.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-sm font-medium text-gray-500 uppercase">Total Early Molds</h3>
                            <p class="mt-1 text-lg font-semibold">{{ totals.early_molds.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-sm font-medium text-gray-500 uppercase">Total Pull Out</h3>
                            <p class="mt-1 text-lg font-semibold">{{ totals.pull_out.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-sm font-medium text-gray-500 uppercase">Total Rat Bites</h3>
                            <p class="mt-1 text-lg font-semibold">{{ totals.rat_bites.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-sm font-medium text-gray-500 uppercase">Total Ant Bites</h3>
                            <p class="mt-1 text-lg font-semibold">{{ totals.ant_bites.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-sm font-medium text-gray-500 uppercase">Total Price</h3>
                            <p class="mt-1 text-lg font-semibold text-red-600">₱{{ totals.total_price.toFixed(2) }}</p>
                        </div>
                    </div>
                </div>
            </details>

            <TableContainer>
                <DataTable 
                    :data="tableData" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                >
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>