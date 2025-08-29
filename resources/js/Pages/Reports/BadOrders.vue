<script setup>
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import ExcelJS from 'exceljs';
import jQuery from 'jquery';
import { router } from '@inertiajs/vue3';

window.$ = window.jQuery = jQuery;
DataTable.use(DataTablesCore);

const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');
const isLoading = ref(false);
const totalCount = ref(0);

const props = defineProps({
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

const layoutComponent = computed(() => {
    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});

onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
});

const filteredData = computed(() => {
    let filtered = [...props.bo];

    if (selectedStores.value.length > 0) {
        filtered = filtered.filter(item =>
            selectedStores.value.includes(item.storename)
        );
    }

    return filtered;
});

const hasData = computed(() => {
    return filteredData.value && filteredData.value.length > 0;
});

const footerTotals = computed(() => {
    return filteredData.value.reduce((acc, row) => {
        const totalWaste = parseFloat(row.throw_away || 0) +
                         parseFloat(row.early_molds || 0) +
                         parseFloat(row.pull_out || 0) +
                         parseFloat(row.rat_bites || 0) +
                         parseFloat(row.ant_bites || 0);
        const price = parseFloat(row.price || 0);
        const totalPrice = price * totalWaste;

        return {
            throw_away: (acc.throw_away || 0) + parseFloat(row.throw_away || 0),
            early_molds: (acc.early_molds || 0) + parseFloat(row.early_molds || 0),
            pull_out: (acc.pull_out || 0) + parseFloat(row.pull_out || 0),
            rat_bites: (acc.rat_bites || 0) + parseFloat(row.rat_bites || 0),
            ant_bites: (acc.ant_bites || 0) + parseFloat(row.ant_bites || 0),
            total_waste: (acc.total_waste || 0) + totalWaste,
            total_price: (acc.total_price || 0) + totalPrice
        };
    }, {
        throw_away: 0,
        early_molds: 0,
        pull_out: 0,
        rat_bites: 0,
        ant_bites: 0,
        total_waste: 0,
        total_price: 0
    });
});

const columns = [
    {
        data: 'itemid',
        title: 'Item ID',
        footer: 'Grand Total'
    },
    {
        data: 'itemname',
        title: 'Item Name',
        footer: ''
    },
    {
        data: 'category',
        title: 'Category',
        footer: ''
    },
    {
        data: 'storename',
        title: 'Store Name',
        footer: ''
    },
    {
        data: 'batchdate',
        title: 'Batch Date',
        render: (data) => {
            return data ? new Date(data).toLocaleDateString() : '';
        },
        footer: ''
    },
    {
        data: 'waste_declaration_date',
        title: 'Waste Declaration Date',
        render: (data) => {
            return data ? new Date(data).toLocaleDateString() : '';
        },
        footer: ''
    },
    {
        data: 'price',
        title: 'PRICE (INCL. TAX)',
        render: (data) => {
            const value = parseFloat(data || 0);
            return isNaN(value) ? '0.00' : value.toFixed(2);
        },
        footer: '',
        className: 'text-right'
    },
    {
        data: 'throw_away',
        title: 'THROW AWAY',
        render: (data) => {

            const value = parseFloat(data || 0);
            return isNaN(value) ? '0' : parseInt(value, 10).toString();
        },
        footer: function() {

            const total = footerTotals.value.throw_away || 0;
            return parseInt(total, 10).toString();
        },
        className: 'text-right'
    },
    {
        data: 'early_molds',
        title: 'EARLY MOLDS',
        render: (data) => {

            const value = parseFloat(data || 0);
            return isNaN(value) ? '0' : parseInt(value, 10).toString();
        },
        footer: function() {

            const total = footerTotals.value.early_molds || 0;
            return parseInt(total, 10).toString();
        },
        className: 'text-right'
    },
    {
        data: 'pull_out',
        title: 'PULL OUT',
        render: (data) => {

            const value = parseFloat(data || 0);
            return isNaN(value) ? '0' : parseInt(value, 10).toString();
        },
        footer: function() {

            const total = footerTotals.value.pull_out || 0;
            return parseInt(total, 10).toString();
        },
        className: 'text-right'
    },
    {
        data: 'rat_bites',
        title: 'RAT BITES',
        render: (data) => {

            const value = parseFloat(data || 0);
            return isNaN(value) ? '0' : parseInt(value, 10).toString();
        },
        footer: function() {

            const total = footerTotals.value.rat_bites || 0;
            return parseInt(total, 10).toString();
        },
        className: 'text-right'
    },
    {
        data: 'ant_bites',
        title: 'ANT BITES',
        render: (data) => {

            const value = parseFloat(data || 0);
            return isNaN(value) ? '0' : parseInt(value, 10).toString();
        },
        footer: function() {

            const total = footerTotals.value.ant_bites || 0;
            return parseInt(total, 10).toString();
        },
        className: 'text-right'
    },
    {
        data: null,
        title: 'TOTAL WASTE',
        render: (data, type, row) => {

            const total = parseFloat(row.throw_away || 0) +
                          parseFloat(row.early_molds || 0) +
                          parseFloat(row.pull_out || 0) +
                          parseFloat(row.rat_bites || 0) +
                          parseFloat(row.ant_bites || 0);
            return parseInt(total, 10).toString();
        },
        footer: function() {

            const total = footerTotals.value.total_waste || 0;
            return parseInt(total, 10).toString();
        },
        className: 'text-right font-bold'
    },
    {
        data: null,
        title: 'TOTAL PRICE',
        render: (data, type, row) => {
            const totalWaste = parseFloat(row.throw_away || 0) +
                             parseFloat(row.early_molds || 0) +
                             parseFloat(row.pull_out || 0) +
                             parseFloat(row.rat_bites || 0) +
                             parseFloat(row.ant_bites || 0);
            const price = parseFloat(row.price || 0);
            const totalPrice = price * totalWaste;
            return isNaN(totalPrice) ? '0.00' : totalPrice.toFixed(2);
        },
        footer: function() {

            const total = footerTotals.value.total_price || 0;
            return isNaN(total) ? '0.00' : total.toFixed(2);
        },
        className: 'text-right font-bold'
    }
];

const exportToExcel = (dt) => {
    try {
        isLoading.value = true;
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('BO Data');

        worksheet.columns = [
            { header: 'Item ID', key: 'itemid', width: 15 },
            { header: 'Item Name', key: 'itemname', width: 30 },
            { header: 'Category', key: 'category', width: 15 },
            { header: 'Store Name', key: 'storename', width: 20 },
            { header: 'Batch Date', key: 'batchdate', width: 15 },
            { header: 'Waste Declaration Date', key: 'waste_declaration_date', width: 20 },
            { header: 'Price (Incl. Tax)', key: 'price', width: 15 },
            { header: 'Throw Away', key: 'throw_away', width: 12 },
            { header: 'Early Molds', key: 'early_molds', width: 12 },
            { header: 'Pull Out', key: 'pull_out', width: 12 },
            { header: 'Rat Bites', key: 'rat_bites', width: 12 },
            { header: 'Ant Bites', key: 'ant_bites', width: 12 },
            { header: 'Total Waste', key: 'total_waste', width: 15 },
            { header: 'Total Price', key: 'total_price', width: 15 }
        ];

        worksheet.getRow(1).font = { bold: true };
        worksheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF343A40' }
        };
        worksheet.getRow(1).font = { color: { argb: 'FFFFFFFF' } };

        const filteredRows = dt.rows({ search: 'applied' }).data().toArray();

        filteredRows.forEach(row => {
            const totalWaste = parseFloat(row.throw_away || 0) +
                            parseFloat(row.early_molds || 0) +
                            parseFloat(row.pull_out || 0) +
                            parseFloat(row.rat_bites || 0) +
                            parseFloat(row.ant_bites || 0);

            const price = parseFloat(row.price || 0);
            const totalPrice = price * totalWaste;

            const excelRow = worksheet.addRow({
                itemid: row.itemid,
                itemname: row.itemname,
                category: row.category,
                storename: row.storename,
                batchdate: row.batchdate ? new Date(row.batchdate) : null,
                waste_declaration_date: row.waste_declaration_date ? new Date(row.waste_declaration_date) : null,
                price: price,

                throw_away: parseInt(parseFloat(row.throw_away || 0), 10),
                early_molds: parseInt(parseFloat(row.early_molds || 0), 10),
                pull_out: parseInt(parseFloat(row.pull_out || 0), 10),
                rat_bites: parseInt(parseFloat(row.rat_bites || 0), 10),
                ant_bites: parseInt(parseFloat(row.ant_bites || 0), 10),
                total_waste: parseInt(totalWaste, 10),
                total_price: totalPrice
            });

            for (let i = 8; i <= 13; i++) {
                if (excelRow.getCell(i).value !== null) {
                    excelRow.getCell(i).numFmt = '0';
                }
            }

            excelRow.getCell(7).numFmt = '#,##0.00';
            excelRow.getCell(14).numFmt = '#,##0.00';

            if (excelRow.getCell(5).value) excelRow.getCell(5).numFmt = 'yyyy-mm-dd';
            if (excelRow.getCell(6).value) excelRow.getCell(6).numFmt = 'yyyy-mm-dd';
        });

        const totals = filteredRows.reduce((acc, row) => {
            const totalWaste = parseFloat(row.throw_away || 0) +
                            parseFloat(row.early_molds || 0) +
                            parseFloat(row.pull_out || 0) +
                            parseFloat(row.rat_bites || 0) +
                            parseFloat(row.ant_bites || 0);

            const price = parseFloat(row.price || 0);
            const totalPrice = price * totalWaste;

            return {
                throw_away: acc.throw_away + parseFloat(row.throw_away || 0),
                early_molds: acc.early_molds + parseFloat(row.early_molds || 0),
                pull_out: acc.pull_out + parseFloat(row.pull_out || 0),
                rat_bites: acc.rat_bites + parseFloat(row.rat_bites || 0),
                ant_bites: acc.ant_bites + parseFloat(row.ant_bites || 0),
                total_waste: acc.total_waste + totalWaste,
                total_price: acc.total_price + totalPrice
            };
        }, {
            throw_away: 0,
            early_molds: 0,
            pull_out: 0,
            rat_bites: 0,
            ant_bites: 0,
            total_waste: 0,
            total_price: 0
        });

        const totalRow = worksheet.addRow({
            itemid: 'Total',
            itemname: '',
            category: '',
            storename: '',
            batchdate: '',
            waste_declaration_date: '',
            price: '',

            throw_away: parseInt(totals.throw_away, 10),
            early_molds: parseInt(totals.early_molds, 10),
            pull_out: parseInt(totals.pull_out, 10),
            rat_bites: parseInt(totals.rat_bites, 10),
            ant_bites: parseInt(totals.ant_bites, 10),
            total_waste: parseInt(totals.total_waste, 10),
            total_price: totals.total_price
        });

        totalRow.font = { bold: true };
        totalRow.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF007BFF' }
        };
        totalRow.font = { color: { argb: 'FFFFFFFF' } };

        for (let i = 8; i <= 13; i++) {
            if (totalRow.getCell(i).value !== null) {
                totalRow.getCell(i).numFmt = '0';
            }
        }

        totalRow.getCell(14).numFmt = '#,##0.00';

        workbook.xlsx.writeBuffer().then((buffer) => {
            const blob = new Blob([buffer], { type: 'application/octet-stream' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            const now = new Date();
            const dateStr = `${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')}`;
            link.download = `Bad_Orders_Report_${dateStr}.xlsx`;
            link.click();
            isLoading.value = false;
            alert('Export completed successfully!');
        }).catch(error => {

            isLoading.value = false;
            alert('Error exporting to Excel: ' + error.message);
        });
    } catch (error) {

        isLoading.value = false;
        alert('Error preparing Excel export: ' + error.message);
    }
};

const options = {
    responsive: true,
    order: [[3, 'asc'], [0, 'asc']],
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    dom: 'Blfrtip',
    scrollX: true,
    scrollY: "50vh",
    buttons: [
        'copy',
        {
            text: 'Export Excel',
            action: function(e, dt, node, config) {
                exportToExcel(dt);
            }
        },
        'pdf',
        'print'

    ],
    drawCallback: function(settings) {

        const api = new DataTablesCore.Api(settings);

        totalCount.value = api.rows({ search: 'applied' }).count();

        const totals = {
            throw_away: 0,
            early_molds: 0,
            pull_out: 0,
            rat_bites: 0,
            ant_bites: 0,
            total_waste: 0,
            total_price: 0
        };

        api.rows({ search: 'applied' }).every(function(rowIdx) {
            const data = this.data();
            const totalWaste = parseFloat(data.throw_away || 0) +
                         parseFloat(data.early_molds || 0) +
                         parseFloat(data.pull_out || 0) +
                         parseFloat(data.rat_bites || 0) +
                         parseFloat(data.ant_bites || 0);

            const price = parseFloat(data.price || 0);
            const totalPrice = price * totalWaste;

            totals.throw_away += parseFloat(data.throw_away || 0);
            totals.early_molds += parseFloat(data.early_molds || 0);
            totals.pull_out += parseFloat(data.pull_out || 0);
            totals.rat_bites += parseFloat(data.rat_bites || 0);
            totals.ant_bites += parseFloat(data.ant_bites || 0);
            totals.total_waste += totalWaste;
            totals.total_price += totalPrice;
        });

        const footerRow = api.table().footer().querySelectorAll('td, th');
        const columns = ['throw_away', 'early_molds', 'pull_out', 'rat_bites', 'ant_bites', 'total_waste', 'total_price'];
        columns.forEach((column, idx) => {
            const colIndex = idx + 7;
            if (footerRow[colIndex]) {
                if (column === 'total_price') {

                    footerRow[colIndex].textContent = parseFloat(totals[column]).toFixed(2);
                } else {

                    footerRow[colIndex].textContent = parseInt(totals[column], 10).toString();
                }
            }
        });
    }
}

const handleFilterChange = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert('Start date cannot be later than end date');
        startDate.value = '';
        endDate.value = '';
        return;
    }

    isLoading.value = true;

    router.get(
        route('reports.bo'),
        {
            startDate: startDate.value,
            endDate: endDate.value,
            stores: selectedStores.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                isLoading.value = false;
            },
            onError: (errors) => {
                isLoading.value = false;
                alert('Error loading data: ' + Object.values(errors).join(', '));
            }
        }
    );
};

const clearFilters = () => {
    selectedStores.value = [];
    startDate.value = '';
    endDate.value = '';
    handleFilterChange();
};

let filterTimeout;
watch([selectedStores, startDate, endDate], () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(handleFilterChange, 500);
}, { deep: true });

onUnmounted(() => {
    clearTimeout(filterTimeout);
});
</script>

<template>
    <component :is="layoutComponent" active-tab="REPORTS">
        <template v-slot:main>
            <!-- Loading Overlay -->
            <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center">
                <div class="bg-white p-4 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <svg class="animate-spin h-6 w-6 mr-3 text-blue-500" xmlns="http:
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading...</span>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow z-[999] sticky top-0">
                <div v-if="userRole.toUpperCase() === 'ADMIN' || userRole.toUpperCase() === 'SUPERADMIN'"
                     class="flex-1 min-w-[200px]">
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

            <!-- Table Section -->
            <TableContainer>
                <div class="flex justify-between items-center mb-4">
                    <div class="text-xl font-semibold">Bad Orders Report</div>
                    <div v-if="hasData" class="text-sm text-gray-600">
                        Showing {{ totalCount }} record(s)
                    </div>
                </div>

                <div v-if="!hasData" class="p-8 text-center bg-gray-50 rounded-lg">
                    <div class="text-gray-500 mb-4">
                        <svg xmlns="http:
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-lg font-medium">No waste records found</p>
                    </div>
                    <p class="text-gray-600">
                        Try adjusting your search filters or select a different date range.
                    </p>
                </div>

                <DataTable
                    v-if="hasData"
                    :data="filteredData"
                    :columns="columns"
                    class="w-full relative display"
                    :options="options"
                >
                    <tfoot>
                        <tr>
                            <th>Grand Total</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <!-- MODIFIED: Display all totals as integers, except total price with 2 decimal places -->
                            <th>{{ parseInt(footerTotals.throw_away, 10) }}</th>
                            <th>{{ parseInt(footerTotals.early_molds, 10) }}</th>
                            <th>{{ parseInt(footerTotals.pull_out, 10) }}</th>
                            <th>{{ parseInt(footerTotals.rat_bites, 10) }}</th>
                            <th>{{ parseInt(footerTotals.ant_bites, 10) }}</th>
                            <th>{{ parseInt(footerTotals.total_waste, 10) }}</th>
                            <th>{{ parseFloat(footerTotals.total_price).toFixed(2) }}</th>
                        </tr>
                    </tfoot>
                </DataTable>
            </TableContainer>
        </template>
    </component>
</template>

<style>

table.dataTable {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    font-family: 'Arial', sans-serif;
}

table.dataTable thead {
    background-color: #343a40;
    color: #ffffff;
    text-align: center;
}

table.dataTable tbody {
    background-color: #f8f9fa;
}

table.dataTable tbody tr:nth-child(odd) {
    background-color: #e9ecef;
}

table.dataTable tbody tr:hover {
    background-color: #ddd;
    cursor: pointer;
}

table.dataTable th, table.dataTable td {
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #dee2e6;
}

table.dataTable th {
    font-size: 14px;
    font-weight: bold;
}

table.dataTable td {
    font-size: 13px;
}

.dataTable tfoot {
    background-color: #007bff;
    color: white;
    font-weight: bold;
    text-align: center;
}

.dataTable tfoot td, .dataTable tfoot th {
    padding: 12px 15px;
}

.dt-buttons {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    position: absolute;
    z-index: 1;
    margin-left: 250px;
    margin-top: -10px;
    gap: 10px;
}

.dt-buttons .buttons-copy,
.dt-button,
.dt-buttons .buttons-print {
    padding: 10px;
    background-color: blue;
    margin: 10px;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.dt-buttons .buttons-copy:hover,
.dt-button:hover,
.dt-buttons .buttons-print:hover {
    background-color: darkblue;
}

.dataTables_filter {
    float: right;
    padding-bottom: 20px;
    position: relative;
    z-index: 999;
}

.dataTables_filter input {
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 14px;
    margin-left: 8px;
}

.dataTables_paginate {
    margin-top: 15px;
    text-align: right;
}

.dataTables_paginate .paginate_button {
    padding: 5px 10px;
    margin: 0 2px;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
    background-color: #fff;
}

.dataTables_paginate .paginate_button.current {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.dataTables_paginate .paginate_button:hover:not(.current) {
    background-color: #e9ecef;
}

.dataTables_length {
    margin-bottom: 15px;
}

.dataTables_length select {
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    margin: 0 5px;
}

.dataTables_info {
    margin-top: 15px;
    color: #666;
}

@media (max-width: 768px) {
    .dt-buttons {
        position: static;
        justify-content: center;
        margin-bottom: 15px;
    }

    .dataTables_filter {
        float: none;
        text-align: center;
        margin-bottom: 15px;
    }

    .dataTables_length {
        text-align: center;
    }

    .dataTables_paginate {
        text-align: center;
    }

    table.dataTable th,
    table.dataTable td {
        padding: 8px;
        font-size: 12px;
    }

    .dt-buttons .buttons-copy,
    .dt-button,
    .dt-buttons .buttons-print {
        padding: 8px;
        font-size: 12px;
        margin: 5px;
    }
}

@media print {
    .dt-buttons,
    .dataTables_filter,
    .dataTables_length,
    .dataTables_paginate {
        display: none !important;
    }

    table.dataTable {
        width: 100% !important;
    }

    table.dataTable th,
    table.dataTable td {
        padding: 8px;
        border: 1px solid #000;
    }
}

.dataTables_processing {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 1rem;
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.dataTables_scrollBody::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.dataTables_scrollBody::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.highlight {
    background-color: #ffffcc !important;
}

table.dataTable td:nth-child(7),
table.dataTable td:nth-child(8),
table.dataTable td:nth-child(9),
table.dataTable td:nth-child(10),
table.dataTable td:nth-child(11),
table.dataTable td:nth-child(12) {
    text-align: right;
}

table.dataTable thead th:nth-child(7),
table.dataTable thead th:nth-child(8),
table.dataTable thead th:nth-child(9),
table.dataTable thead th:nth-child(10),
table.dataTable thead th:nth-child(11),
table.dataTable thead th:nth-child(12),
table.dataTable tfoot th:nth-child(7),
table.dataTable tfoot th:nth-child(8),
table.dataTable tfoot th:nth-child(9),
table.dataTable tfoot th:nth-child(10),
table.dataTable tfoot th:nth-child(11),
table.dataTable tfoot th:nth-child(12) {
    text-right: right;
}

#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    margin-bottom: 10px;
    padding: 15px 20px;
    border-radius: 4px;
    color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease-in-out;
    max-width: 300px;
}

.toast-success {
    background-color: #28a745;
}

.toast-error {
    background-color: #dc3545;
}

.toast-info {
    background-color: #17a2b8;
}

.toast-warning {
    background-color: #ffc107;
    color: #212529;
}

.toast-removing {
    animation: slideOut 0.3s ease-in-out forwards;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>