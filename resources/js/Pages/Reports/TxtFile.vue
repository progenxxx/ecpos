
 <script setup>
  import { defineProps } from 'vue';
  import { useForm } from '@inertiajs/vue3';
  import Main from "@/Layouts/AdminPanel.vue";
  import TableContainer from "@/Components/Tables/TableContainer.vue";
  import DataTable from 'datatables.net-vue3';
  import DataTablesCore from 'datatables.net';
  import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
  import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
  import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
  import ExcelIcon from "@/Components/Svgs/Excel.vue";
  import Search from "@/Components/Svgs/SearchColored.vue";
  import { ref, computed, isRef, unref } from 'vue';
  DataTable.use(DataTablesCore);

  const emit = defineEmits();

  const props = defineProps({
    orders: {
      type: Array,
      required: true,
    },
  });

  const form = useForm({
    StartDate: '',
    EndDate: '',
  });

  const submitForm = () => {
  form.get(route('orderingconso.getrange'), {
    preserveScroll: true,
  });
};
  const toggleActive = () => {
    emit('toggleActive');
  };

  const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
};

const currentDate = computed(() => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}${month}${day}`;
});

const columns = [
    { data: 'STOREID', title: 'STOREID' },
    {
        data: 'POSTEDDATETIME',
        title: 'POSTEDDATETIME',
        render: function(data, type, row) {
            const date = new Date(data);
            return date.toLocaleDateString();
        }
    },
    { data: 'ITEMID', title: 'ITEMID' },
    { data: 'COUNTED', title: 'COUNTED' },
];

function generateTextFileContent(orders, columns) {
    const dataRows = orders.map(order => {
        const rowData = columns.map(column => {
            if (column.data === 'POSTEDDATETIME') {
                const date = new Date(order[column.data]);
                return date.toLocaleDateString();
            } else if (column.data === 'COUNTED') {

                return Math.floor(order[column.data]) || '';
            } else {
                return order[column.data] || '';
            }
        });
        return rowData.join('|');
    });

    return dataRows.join('\n');
}

function downloadTextFile(filename, content) {
  const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function generateAndDownloadTextFile() {

    const storeID = props.orders[0].STOREID;
    const postedDate = new Date(props.orders[0].POSTEDDATETIME);
    const formattedDate = `${postedDate.getFullYear()}${(postedDate.getMonth() + 1).toString().padStart(2, '0')}${postedDate.getDate().toString().padStart(2, '0')}`;

    const filename = `${storeID}${formattedDate}.txt`;

    const header = `${storeID}|${postedDate.toLocaleDateString()}`;

    const dataRows = props.orders.map(order => `${order.ITEMID}|${Math.floor(order.COUNTED)}`);
    const content = [header, ...dataRows].join('\n');

    downloadTextFile(filename, content);
}

</script>

<template>
  <Main active-tab="SYNC">
    <template v-slot:main>
      <TableContainer>
        <div class="absolute adjust">

          <div class="flex justify-start items-center">

            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <input type="hidden" name="_token" :value="$page.props.csrf_token">
                <div date-rangepicker  class="flex items-center">
                <div class="relative ml-5 ">
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http:
                    </div>

                <input
                id="StartDate"
                type="date"
                v-model="form.StartDate"
                @input="formattedDate1"
                :placeholder="formattedDate1"
                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date start"
                required
                />
                <InputError :message="form.errors.StartDate" class="mt-2" />
                </div>

                <span class="mx-4 text-gray-500">to</span>

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
            </form>

            <TransparentButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                <Search class="h-8" />
            </TransparentButton>

            &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<button @click="generateAndDownloadTextFile">Generate and Download Text File</button>

           <SuccessButton
                  type="button"
                  @click="exportToExcel"
                  class="m-6 bg-green"
                >
                  <ExcelIcon class="h-4" />
            </SuccessButton>

          </div>
        </div>

        <DataTable :data="orders" :columns="columns" class="w-full relative display" :options="options">
          <template #action="data">
            <div class="flex justify-start">
            </div>
          </template>
        </DataTable>
      </TableContainer>
    </template>
  </Main>
</template>

