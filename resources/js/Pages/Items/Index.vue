<script setup>
import { useForm, router } from '@inertiajs/vue3';
import Create from "@/Components/Items/Create.vue";
import Enable from "@/Components/Items/Enable.vue";
import Update from "@/Components/Items/Update.vue";
import UpdateMOQ from "@/Components/Items/UpdateMOQ.vue";
import More from "@/Components/Items/More.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import Excel from "@/Components/Exports/Excel.vue";

import Add from "@/Components/Svgs/Add.vue";
import Enabled from "@/Components/Svgs/Enabled.vue";
import Import from "@/Components/Svgs/Import.vue";
import MenuIcon from "@/Components/Svgs/Menu.vue";
import CloseIcon from "@/Components/Svgs/Close.vue";

import { ref, computed, toRefs, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

// Reactive refs
const itemid = ref('');
const itemname = ref('');
const cost = ref('');
const itemgroup = ref('');
const specialgroup = ref('');
const price = ref('');
const moq = ref('');
const manilaprice = ref('');
const foodpandaprice = ref('');
const grabfoodprice = ref('');
const mallprice = ref('');
const foodpandamallprice = ref('');
const grabfoodmallprice = ref('');
const production = ref('');
const default1 = ref(false);
const default2 = ref(false);
const default3 = ref(false);

// Live edit functionality
const isLiveEditEnabled = ref(false);
const editingCell = ref(null);
const tempEditValue = ref('');
const savingCell = ref(null);

const selectedItems = ref([]);
const showImportModal = ref(false);
const showItemInfoModal = ref(false);
const selectedItemInfo = ref(null);
const showFloatingMenu = ref(false);
const showContextMenu = ref(false);
const contextMenuX = ref(0);
const contextMenuY = ref(0);
const contextMenuItem = ref(null);

// Click tracking - FIXED to use right-click for links
const clickTimeout = ref(null);
const clickCount = ref(0);
const longPressTimeout = ref(null);
const isLongPress = ref(false);

// Pagination and filtering state
const currentPage = ref(1);
const itemsPerPage = ref(50);
const searchQuery = ref('');
const selectedCategory = ref('');
const selectedStatus = ref('');

// Props with proper validation and defaults
const props = defineProps({
    items: {
        type: Array,
        required: true,
        default: () => []
    },
    itemids: {
        type: Array,
        default: () => []
    },
    auth: {
        type: Object,
        required: true,
        default: () => ({})
    },
    rboinventitemretailgroups: {
        type: Array,
        required: true,
        default: () => []
    },
});

// Computed properties with proper null checking
const layoutComponent = computed(() => {
    return props.auth?.user?.role === 'STORE' ? StorePanel : Main;
});

const { auth } = toRefs(props);
const user = computed(() => auth.value?.user || {});
const userRole = computed(() => user.value?.role || '');

const isOpic = computed(() => userRole.value === 'SUPERADMIN');
const isAdmin = computed(() => userRole.value === 'OPIC');
const isRso = computed(() => userRole.value === 'ADMIN');

// Modal states
const showModalUpdate = ref(false);
const showModalUpdateMOQ = ref(false);
const showCreateModal = ref(false);
const showEnableModal = ref(false);
const showModalMore = ref(false);

// Computed properties for filtering and pagination with proper null checking
const categories = computed(() => {
    if (!props.items || !Array.isArray(props.items)) return [];
    const cats = new Set(props.items.map(item => item?.itemgroup).filter(Boolean));
    return Array.from(cats).sort();
});

const filteredItems = computed(() => {
    if (!props.items || !Array.isArray(props.items)) return [];
    
    let filtered = [...props.items];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(item => 
            item?.itemid?.toLowerCase().includes(query) ||
            item?.itemname?.toLowerCase().includes(query) ||
            item?.barcode?.toLowerCase().includes(query)
        );
    }

    // Category filter
    if (selectedCategory.value) {
        filtered = filtered.filter(item => item?.itemgroup === selectedCategory.value);
    }

    // Status filter
    if (selectedStatus.value !== '') {
        const status = selectedStatus.value === '1';
        filtered = filtered.filter(item => Boolean(item?.Activeondelivery) === status);
    }

    return filtered;
});

const totalPages = computed(() => {
    const itemCount = filteredItems.value?.length || 0;
    return Math.ceil(itemCount / itemsPerPage.value);
});

const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);

const paginatedItems = computed(() => {
    if (!filteredItems.value || !Array.isArray(filteredItems.value)) return [];
    const start = startIndex.value;
    const end = start + itemsPerPage.value;
    return filteredItems.value.slice(start, end);
});

const visiblePages = computed(() => {
    const pages = [];
    const maxVisible = 5;
    const total = totalPages.value;
    const current = currentPage.value;
    
    if (total <= maxVisible) {
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
    } else {
        const start = Math.max(1, current - Math.floor(maxVisible / 2));
        const end = Math.min(total, start + maxVisible - 1);
        
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
    }
    return pages;
});

const allSelected = computed({
    get() {
        if (!paginatedItems.value || paginatedItems.value.length === 0) return false;
        return paginatedItems.value.every(item => selectedItems.value.includes(item?.itemid));
    },
    set(value) {
        if (!paginatedItems.value || paginatedItems.value.length === 0) return;
        
        if (value) {
            const newSelections = paginatedItems.value.map(item => item?.itemid).filter(Boolean);
            selectedItems.value = [...new Set([...selectedItems.value, ...newSelections])];
        } else {
            const pageIds = paginatedItems.value.map(item => item?.itemid).filter(Boolean);
            selectedItems.value = selectedItems.value.filter(id => !pageIds.includes(id));
        }
    }
});

// FIXED: Click handling functions - Changed triple tap to right-click for links
const handleItemClick = (item) => {
    clearTimeout(clickTimeout.value);
    clickCount.value++;

    if (clickCount.value === 1) {
        clickTimeout.value = setTimeout(() => {
            // Single click - do nothing or select item
            clickCount.value = 0;
        }, 300);
    } else if (clickCount.value === 2) {
        // Double click - open update form
        clearTimeout(clickTimeout.value);
        toggleUpdateModal(item);
        clickCount.value = 0;
    }
    // Removed triple click handler
};

// FIXED: Right-click context menu for item links
const handleRightClick = (item, event) => {
    event.preventDefault();
    contextMenuItem.value = item;
    contextMenuX.value = event.clientX;
    contextMenuY.value = event.clientY;
    showContextMenu.value = true;
};

// FIXED: Touch events for mobile - swipe right for links
const handleTouchStart = (item, event) => {
    isLongPress.value = false;
    longPressTimeout.value = setTimeout(() => {
        isLongPress.value = true;
        showItemInfo(item);
    }, 800); // 800ms for long press
};

const handleTouchEnd = (item, event) => {
    clearTimeout(longPressTimeout.value);
    if (!isLongPress.value) {
        handleItemClick(item);
    }
};

// FIXED: Swipe detection for mobile
let touchStartX = 0;
let touchStartY = 0;

const handleTouchStartSwipe = (item, event) => {
    touchStartX = event.touches[0].clientX;
    touchStartY = event.touches[0].clientY;
    handleTouchStart(item, event);
};

const handleTouchEndSwipe = (item, event) => {
    if (!event.changedTouches || event.changedTouches.length === 0) return;
    
    const touchEndX = event.changedTouches[0].clientX;
    const touchEndY = event.changedTouches[0].clientY;
    const deltaX = touchEndX - touchStartX;
    const deltaY = touchEndY - touchStartY;
    
    // Check if it's a horizontal swipe (more horizontal than vertical)
    if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
        if (deltaX > 0) {
            // Swipe right - view links
            handleViewLinks(item);
            return;
        }
    }
    
    handleTouchEnd(item, event);
};

const handleMouseDown = (item, event) => {
    isLongPress.value = false;
    longPressTimeout.value = setTimeout(() => {
        isLongPress.value = true;
        showItemInfo(item);
    }, 800);
};

const handleMouseUp = (item, event) => {
    clearTimeout(longPressTimeout.value);
    if (!isLongPress.value) {
        handleItemClick(item);
    }
};

const showItemInfo = (item) => {
    selectedItemInfo.value = item;
    showItemInfoModal.value = true;
};

// Context menu actions
const handleViewLinksFromContext = () => {
    if (contextMenuItem.value) {
        handleViewLinks(contextMenuItem.value);
    }
    showContextMenu.value = false;
};

const handleEditFromContext = () => {
    if (contextMenuItem.value) {
        toggleUpdateModal(contextMenuItem.value);
    }
    showContextMenu.value = false;
};

const closeContextMenu = () => {
    showContextMenu.value = false;
    contextMenuItem.value = null;
};

// Floating Menu Functions
const toggleFloatingMenu = () => {
    showFloatingMenu.value = !showFloatingMenu.value;
};

const closeFloatingMenu = () => {
    showFloatingMenu.value = false;
};

// Modal handlers - FIXED to pass all default values
const toggleUpdateModal = (item) => {
    if (!item) return;
    
    itemid.value = item.itemid || '';
    itemname.value = item.itemname || '';
    itemgroup.value = item.itemgroup || '';
    price.value = item.price || '';
    cost.value = item.cost || '';
    moq.value = item.moq || '';
    manilaprice.value = item.manilaprice || 0;
    foodpandaprice.value = item.foodpandaprice || 0;
    grabfoodprice.value = item.grabfoodprice || 0;
    mallprice.value = item.mallprice || 0;
    foodpandamallprice.value = item.foodpandamallprice || 0;
    grabfoodmallprice.value = item.grabfoodmallprice || 0;
    production.value = item.production || '';
    // FIXED: Pass actual default values
    default1.value = item.default1 || false;
    default2.value = item.default2 || false;
    default3.value = item.default3 || false;
    showModalUpdate.value = true;
    closeFloatingMenu();
};

const toggleMoreModal = (item) => {
    if (!item) return;
    itemid.value = item.itemid || '';
    showModalMore.value = true;
    closeFloatingMenu();
};

const toggleUpdateMOQModal = (item) => {
    if (!item) return;
    
    itemid.value = item.itemid || '';
    itemname.value = item.itemname || '';
    itemgroup.value = item.itemgroup || '';
    price.value = item.price || '';
    cost.value = item.cost || '';
    moq.value = item.moq || '';
    production.value = item.production || '';
    showModalUpdateMOQ.value = true;
    closeFloatingMenu();
};

const toggleCreateModal = () => {
    showCreateModal.value = true;
    closeFloatingMenu();
};

const updateModalHandler = () => {
    showModalUpdate.value = false;
};

const updateMOQModalHandler = () => {
    showModalUpdateMOQ.value = false;
};

const createModalHandler = () => {
    showCreateModal.value = false;
};

const MoreModalHandler = () => {
    showModalMore.value = false;
};

// Import form
const importForm = useForm({
    file: null,
});

const handleFileUpload = (event) => {
    importForm.file = event.target.files?.[0] || null;
};

const submitImportForm = () => {
    if (!importForm.file) {
        alert('Please select a file to import.');
        return;
    }

    importForm.post('/ImportProducts', {
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
            const fileInput = document.getElementById('fileInput');
            if (fileInput) fileInput.value = '';
            showImportModal.value = false;
            closeFloatingMenu();
            window.location.reload();
        },
        onError: (errors) => {
            console.error('Import failed:', errors);
        },
    });
};

const downloadTemplate = () => {
    window.location.href = '/download-import-template';
    closeFloatingMenu();
};

// Table event handlers
const handleEditItem = (item) => {
    toggleUpdateModal(item);
};

const handleViewLinks = (item) => {
    if (!item?.itemid) return;
    router.visit(route('item-links.index', item.itemid));
    closeFloatingMenu();
};

const handleMoreActions = (item) => {
    toggleMoreModal(item);
};

// FIXED: Enable selected functionality
const handleBulkEnable = () => {
    const validIds = Array.isArray(selectedItems.value) ? selectedItems.value.filter(Boolean) : [];
    
    if (validIds.length === 0) {
        alert('Please select at least one item.');
        return;
    }

    // FIXED: Use the correct endpoint and method for enabling items
    axios.post('/EnableOrder', {
        itemids: validIds
    })
    .then(response => {
        alert(response.data?.message || 'Items enabled successfully');
        location.reload();
    })
    .catch(error => {
        console.error('Error enabling items:', error);
        alert('An error occurred while enabling items.');
    });

    closeFloatingMenu();
};

const handleSelectionChanged = (newSelection) => {
    selectedItems.value = Array.isArray(newSelection) ? newSelection : [];
};

// Export data preparation
const getExportData = () => {
    if (!props.items || !Array.isArray(props.items)) return [];
    
    return props.items.map(item => ({
        PRODUCTCODE: item?.itemid || '',
        DESCRIPTION: item?.itemname || '',
        BARCODE: item?.barcode || '',
        CATEGORY: item?.itemgroup || '',
        RETAILGROUP: item?.specialgroup || '',
        SRP: item?.price ? Number(item.price).toFixed(2) : '0.00',
        MANILA: item?.manilaprice ? Number(item.manilaprice).toFixed(2) : '0.00',
        MALL: item?.mallprice ? Number(item.mallprice).toFixed(2) : '0.00',
        GRABFOOD: item?.grabfoodprice ? Number(item.grabfoodprice).toFixed(2) : '0.00',
        FOODPANDA: item?.foodpandaprice ? Number(item.foodpandaprice).toFixed(2) : '0.00',
        GRABFOODMALL: item?.grabfoodmallprice ? Number(item.grabfoodmallprice).toFixed(2) : '0.00',
        FOODPANDAMALL: item?.foodpandamallprice ? Number(item.foodpandamallprice).toFixed(2) : '0.00'
    }));
};

// Utility functions
const formatCurrency = (value) => {
    if (value == null || value === '') return '0.00';
    return Number(value).toFixed(2);
};

const toggleAllSelection = () => {
    allSelected.value = !allSelected.value;
};

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const products = () => {
    window.location.href = '/items';
    closeFloatingMenu();
};

const nonproducts = () => {
    window.location.href = '/warehouse';
    closeFloatingMenu();
};

// Live edit functions
const startCellEdit = (itemId, field, currentValue) => {
    if (!isLiveEditEnabled.value) return;

    editingCell.value = `${itemId}-${field}`;
    tempEditValue.value = currentValue || '';

    // Auto-focus the input after Vue updates the DOM
    nextTick(() => {
        const input = document.querySelector(`input[data-editing="${itemId}-${field}"]`);
        if (input) {
            input.focus();
            input.select();
        }
    });
};

const cancelCellEdit = () => {
    editingCell.value = null;
    tempEditValue.value = '';
};

const saveCellEdit = async (itemId, field) => {
    if (!tempEditValue.value && tempEditValue.value !== '0') {
        console.log(`Live Edit Mode - Cancelled edit for ${itemId}-${field}: Empty value`);
        cancelCellEdit();
        return;
    }

    const cellKey = `${itemId}-${field}`;
    savingCell.value = cellKey;

    // Log the start of Live Edit Mode operation
    console.log(`===== LIVE EDIT MODE - FRONTEND START =====`, {
        itemId: itemId,
        field: field,
        newValue: tempEditValue.value,
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent
    });

    try {
        const updateData = {
            [field]: tempEditValue.value
        };

        // Add required fields for validation
        const item = props.items.find(i => i.itemid === itemId);
        if (!item) {
            throw new Error(`Item with ID ${itemId} not found in props.items`);
        }

        console.log(`Live Edit Mode - Found item:`, {
            itemId: item.itemid,
            itemName: item.itemname,
            currentValues: {
                cost: item.cost,
                price: item.price,
                manilaprice: item.manilaprice,
                foodpandaprice: item.foodpandaprice,
                grabfoodprice: item.grabfoodprice,
                mallprice: item.mallprice,
                foodpandamallprice: item.foodpandamallprice,
                grabfoodmallprice: item.grabfoodmallprice
            }
        });

        // FIXED: Include itemid in request data (required for validation)
        updateData.itemid = itemId;
        updateData.itemname = item.itemname;
        updateData.itemgroup = item.itemgroup;
        updateData.cost = field === 'cost' ? tempEditValue.value : item.cost;
        updateData.price = field === 'price' ? tempEditValue.value : item.price;
        updateData.manilaprice = field === 'manilaprice' ? tempEditValue.value : (item.manilaprice || 0);
        updateData.foodpandaprice = field === 'foodpandaprice' ? tempEditValue.value : (item.foodpandaprice || 0);
        updateData.grabfoodprice = field === 'grabfoodprice' ? tempEditValue.value : (item.grabfoodprice || 0);
        updateData.mallprice = field === 'mallprice' ? tempEditValue.value : (item.mallprice || 0);
        updateData.foodpandamallprice = field === 'foodpandamallprice' ? tempEditValue.value : (item.foodpandamallprice || 0);
        updateData.grabfoodmallprice = field === 'grabfoodmallprice' ? tempEditValue.value : (item.grabfoodmallprice || 0);
        updateData.production = item.production || 'NEWCOM';
        updateData.moq = item.moq;
        updateData.default1 = item.default1 || false;
        updateData.default2 = item.default2 || false;
        updateData.default3 = item.default3 || false;
        updateData.confirm_defaults = true;

        console.log(`Live Edit Mode - Prepared update data:`, {
            updateData: updateData,
            payloadSize: JSON.stringify(updateData).length
        });

        const requestStart = performance.now();
        console.log(`Live Edit Mode - Sending PUT request to /items/${itemId}`);

        const response = await axios.put(`/items/${itemId}`, updateData);

        const requestEnd = performance.now();
        const requestTime = requestEnd - requestStart;

        console.log(`Live Edit Mode - Request successful:`, {
            responseStatus: response.status,
            responseData: response.data,
            requestTime: `${requestTime.toFixed(2)}ms`,
            responseHeaders: {
                contentType: response.headers['content-type'],
                contentLength: response.headers['content-length']
            }
        });

        // Update the item in the props array with Vue reactivity
        const itemIndex = props.items.findIndex(i => i.itemid === itemId);
        if (itemIndex !== -1) {
            const oldValue = props.items[itemIndex][field];

            // FIXED: Use Vue's reactivity system to update the value
            // Convert to proper data type (number for price fields)
            const newValue = ['cost', 'price', 'manilaprice', 'foodpandaprice', 'grabfoodprice', 'mallprice', 'foodpandamallprice', 'grabfoodmallprice'].includes(field)
                ? Number(tempEditValue.value)
                : tempEditValue.value;

            // Use Vue's $set equivalent or direct assignment (Vue 3 handles reactivity automatically)
            props.items[itemIndex] = { ...props.items[itemIndex], [field]: newValue };

            console.log(`Live Edit Mode - Updated props.items[${itemIndex}].${field}:`, {
                oldValue: oldValue,
                newValue: newValue,
                convertedType: typeof newValue
            });
        } else {
            console.warn(`Live Edit Mode - Could not find item ${itemId} in props.items to update`);
        }

        console.log(`===== LIVE EDIT MODE - FRONTEND SUCCESS =====`, {
            itemId: itemId,
            field: field,
            totalTime: `${requestTime.toFixed(2)}ms`
        });

        // OPTIONAL: Force a small delay to ensure UI updates
        await nextTick();

        // ALTERNATIVE: Use Inertia to refresh page data after successful edit
        // This ensures the display always shows the latest data
        setTimeout(() => {
            console.log('Live Edit Mode - Refreshing page data to ensure display consistency');
            router.reload({ only: ['items'] });
        }, 100);

        cancelCellEdit();
    } catch (error) {
        console.error(`===== LIVE EDIT MODE - FRONTEND ERROR =====`, {
            itemId: itemId,
            field: field,
            newValue: tempEditValue.value,
            errorMessage: error.message,
            errorResponse: error.response ? {
                status: error.response.status,
                statusText: error.response.statusText,
                data: error.response.data,
                headers: error.response.headers
            } : 'No response data',
            errorStack: error.stack,
            networkError: error.code,
            requestConfig: error.config ? {
                method: error.config.method,
                url: error.config.url,
                headers: error.config.headers,
                data: error.config.data
            } : 'No config data'
        });

        // More specific error messages based on error type
        let userMessage = 'Error updating item. Please try again.';
        if (error.response) {
            switch (error.response.status) {
                case 422:
                    userMessage = 'Validation error. Please check your input.';
                    if (error.response.data.errors) {
                        console.log('Validation errors:', error.response.data.errors);
                    }
                    break;
                case 404:
                    userMessage = 'Item not found. Please refresh the page.';
                    break;
                case 500:
                    userMessage = 'Server error occurred. Please try again later.';
                    break;
                case 403:
                    userMessage = 'You do not have permission to edit this item.';
                    break;
                default:
                    userMessage = `Server returned error ${error.response.status}. Please try again.`;
            }
        } else if (error.request) {
            userMessage = 'Network error. Please check your connection and try again.';
        }

        alert(userMessage);
    } finally {
        savingCell.value = null;
    }
};

const handleCellKeyPress = (event, itemId, field) => {
    if (event.key === 'Enter') {
        saveCellEdit(itemId, field);
    } else if (event.key === 'Escape') {
        cancelCellEdit();
    }
};

// FIXED: Click outside handler for context menu
const handleClickOutside = (event) => {
    if (showContextMenu.value) {
        closeContextMenu();
    }
};

// Cleanup timeouts
onUnmounted(() => {
    clearTimeout(clickTimeout.value);
    clearTimeout(longPressTimeout.value);
    document.removeEventListener('click', handleClickOutside);
});

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

// Reset to first page when filters change
watch([searchQuery, selectedCategory, selectedStatus], () => {
    currentPage.value = 1;
});

</script>

<template>
  <Head title="RETAILITEMS">
        <meta name="theme-color" content="#000000" />
        <link rel="manifest" href="/manifest.json" />
        <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
        <meta name="apple-mobile-web-app-status-bar" content="#000000" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="mobile-web-app-capable" content="yes" />
    </Head>

    <component :is="layoutComponent" active-tab="RETAILITEMS">
      <template v-slot:modals>
        <Create 
          :show-modal="showCreateModal" 
          @toggle-active="createModalHandler" 
          :rboinventitemretailgroups="props.rboinventitemretailgroups"
        />

        <Update
          :show-modal="showModalUpdate"
          :itemid="itemid"
          :itemname="itemname"
          :itemgroup="itemgroup"
          :price="price"
          :cost="cost"
          :moq="moq"
          :manilaprice="manilaprice"
          :foodpandaprice="foodpandaprice"
          :grabfoodprice="grabfoodprice"
          :mallprice="mallprice"
          :foodpandamallprice="foodpandamallprice"
          :grabfoodmallprice="grabfoodmallprice"
          :production="production"
          :default1="default1"
          :default2="default2"
          :default3="default3"
          :rboinventitemretailgroups="props.rboinventitemretailgroups"
          @toggle-active="updateModalHandler"
        />

        <UpdateMOQ
          :show-modal="showModalUpdateMOQ"
          :itemid="itemid"
          :itemname="itemname"
          :itemgroup="itemgroup"
          :price="price"
          :cost="cost"
          :moq="moq"
          :production="production"
          @toggle-active="updateMOQModalHandler"
        />

        <More
          :show-modal="showModalMore"
          :itemid="itemid"
          @toggle-active="MoreModalHandler"
        />

        <Enable
          :show-modal="showEnableModal"
          :itemids="selectedItems"
          @click="handleBulkEnable"
        />

        <!-- Context Menu for Right-click -->
        <div v-if="showContextMenu" 
             :style="{ left: contextMenuX + 'px', top: contextMenuY + 'px' }"
             class="fixed bg-white border border-gray-200 rounded-md shadow-lg py-1 z-50">
          <button
            @click="handleEditFromContext"
            class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
          >
            Edit Item
          </button>
          <button
            @click="handleViewLinksFromContext"
            class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
          >
            View Links
          </button>
        </div>

        <!-- Item Info Modal -->
        <div v-if="showItemInfoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="showItemInfoModal = false">
          <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white" @click.stop>
            <div class="mt-3">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Item Information</h3>
              
              <div v-if="selectedItemInfo" class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Item ID</label>
                    <p class="text-sm text-gray-900">{{ selectedItemInfo.itemid }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Item Name</label>
                    <p class="text-sm text-gray-900">{{ selectedItemInfo.itemname }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <p class="text-sm text-gray-900">{{ selectedItemInfo.itemgroup }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Barcode</label>
                    <p class="text-sm text-gray-900">{{ selectedItemInfo.barcode }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Cost</label>
                    <p class="text-sm text-gray-900">₱{{ formatCurrency(selectedItemInfo.cost) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">SRP</label>
                    <p class="text-sm text-gray-900">₱{{ formatCurrency(selectedItemInfo.price) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Manila Price</label>
                    <p class="text-sm text-gray-900">₱{{ formatCurrency(selectedItemInfo.manilaprice) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Mall Price</label>
                    <p class="text-sm text-gray-900">₱{{ formatCurrency(selectedItemInfo.mallprice) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Foodpanda Price</label>
                    <p class="text-sm text-gray-900">₱{{ formatCurrency(selectedItemInfo.foodpandaprice) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">GrabFood Price</label>
                    <p class="text-sm text-gray-900">₱{{ formatCurrency(selectedItemInfo.grabfoodprice) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Foodpanda Mall Price</label>
                    <p class="text-sm text-gray-900">₱{{ formatCurrency(selectedItemInfo.foodpandamallprice) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">GrabFood Mall Price</label>
                    <p class="text-sm text-gray-900">₱{{ formatCurrency(selectedItemInfo.grabfoodmallprice) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Production</label>
                    <p class="text-sm text-gray-900">{{ selectedItemInfo.production }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">MOQ</label>
                    <p class="text-sm text-gray-900">{{ selectedItemInfo.moq || 'Not set' }}</p>
                  </div>
                </div>
              </div>

              <div class="flex justify-end mt-6">
                <button
                  @click="showItemInfoModal = false"
                  class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Import Modal -->
        <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Import Items</h3>
              
              <div class="mb-4">
                <button
                  @click="downloadTemplate"
                  class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-3"
                >
                  Download Template
                </button>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Select CSV File
                </label>
                <input
                  type="file"
                  id="fileInput"
                  @change="handleFileUpload"
                  accept=".csv,.txt"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />
              </div>

              <div class="flex justify-end space-x-3">
                <button
                  @click="showImportModal = false"
                  class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                >
                  Cancel
                </button>
                <button
                  @click="submitImportForm"
                  :disabled="importForm.processing || !importForm.file"
                  class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                >
                  {{ importForm.processing ? 'Importing...' : 'Import' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template v-slot:main>
        <TableContainer>
          <!-- Desktop Header Controls -->
          <div class="hidden lg:block p-4 bg-gray-50 rounded-lg mb-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
              <!-- Live Edit Toggle -->
              <div class="flex items-center">
                <label class="flex items-center cursor-pointer">
                  <input
                    v-model="isLiveEditEnabled"
                    type="checkbox"
                    class="form-checkbox h-4 w-4 text-blue-600 mr-2"
                  >
                  <span class="text-sm font-medium text-gray-700">Live Edit Mode</span>
                </label>
                <span class="ml-2 text-xs text-gray-500">(Click table cells to edit)</span>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-wrap gap-2">
                <PrimaryButton
                  v-if="isOpic"
                  type="button"
                  @click="toggleCreateModal"
                  class="bg-navy hover:bg-navy-dark"
                >
                  <Add class="h-4 mr-2" />
                  Add Item
                </PrimaryButton>

                <PrimaryButton
                  v-if="isAdmin || isOpic"
                  type="button"
                  @click="handleBulkEnable"
                  class="bg-green-600 hover:bg-green-700"
                  :disabled="selectedItems.length === 0"
                >
                  <Enabled class="h-4 mr-2" />
                  Enable Selected ({{ selectedItems.length }})
                </PrimaryButton>

                <!-- Export Button -->
                <Excel
                  :data="getExportData()"
                  :headers="[
                    'PRODUCTCODE', 'DESCRIPTION', 'BARCODE', 'CATEGORY', 'RETAILGROUP', 
                    'SRP', 'MANILA', 'MALL', 'GRABFOOD', 'FOODPANDA', 'GRABFOODMALL', 'FOODPANDAMALL'
                  ]"
                  :row-name-props="[
                    'PRODUCTCODE', 'DESCRIPTION', 'BARCODE', 'CATEGORY', 'RETAILGROUP', 
                    'SRP', 'MANILA', 'MALL', 'GRABFOOD', 'FOODPANDA', 'GRABFOODMALL', 'FOODPANDAMALL'
                  ]"
                  class="bg-green-500 hover:bg-green-600"
                  v-if="isAdmin || isOpic"
                >
                  Export Excel
                </Excel>
                
                <!-- Import Button -->
                <PrimaryButton 
                  class="bg-blue-500 hover:bg-blue-700" 
                  @click="showImportModal = true" 
                  v-if="isOpic"
                >
                  <Import class="h-4 mr-2" />
                  Import CSV
                </PrimaryButton>
              </div>

              <!-- Navigation Buttons -->
              <div class="flex gap-2">
                <PrimaryButton
                  type="button"
                  @click="products"
                  class="bg-navy hover:bg-navy-dark"
                >
                  BW Products
                </PrimaryButton>

                <PrimaryButton
                  type="button"
                  @click="nonproducts"
                  class="bg-red-600 hover:bg-red-700"
                >
                  Warehouse
                </PrimaryButton>
              </div>
            </div>
          </div>

          <!-- Table with Pagination -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200">
              <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                  <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search items by ID, name, or barcode..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                </div>
                
                <!-- Filters -->
                <div class="flex gap-2">
                  <select
                    v-model="selectedCategory"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category" :value="category">
                      {{ category }}
                    </option>
                  </select>
                  
                  <select
                    v-model="selectedStatus"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                  </select>
                </div>
              </div>

              <!-- Instructions for mobile - UPDATED -->
              <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md lg:hidden">
                <p class="text-sm text-blue-800">
                  <strong>Instructions:</strong><br>
                  • Hold press: View item info<br>
                  • Double tap: Edit item<br>
                  • Swipe right: View links<br>
                  • Right-click (desktop): Context menu
                </p>
              </div>
            </div>

            <!-- Items Display -->
            <div class="overflow-x-auto">
              <div class="max-h-96 overflow-y-auto">
                <!-- Mobile View -->
                <div class="lg:hidden">
                  <div v-for="item in paginatedItems" :key="item?.itemid" 
                       class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors"
                       @touchstart="handleTouchStartSwipe(item, $event)"
                       @touchend="handleTouchEndSwipe(item, $event)"
                       @mousedown="handleMouseDown(item, $event)"
                       @mouseup="handleMouseUp(item, $event)"
                       @contextmenu="handleRightClick(item, $event)">
                    
                    <div class="flex items-center justify-between mb-2">
                      <div v-if="isAdmin || isOpic" class="flex items-center">
                        <input
                          type="checkbox"
                          :value="item?.itemid"
                          v-model="selectedItems"
                          class="form-checkbox h-4 w-4 text-blue-600 mr-3"
                        >
                      </div>
                      <span :class="[
                        'px-2 py-1 text-xs rounded-full',
                        item?.Activeondelivery ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      ]">
                        {{ item?.Activeondelivery ? 'Active' : 'Inactive' }}
                      </span>
                    </div>
                    
                    <div class="space-y-2">
                      <div>
                        <div class="font-medium text-gray-900">{{ item?.itemname || '' }}</div>
                        <div class="text-sm text-gray-500 font-mono">{{ item?.itemid || '' }}</div>
                      </div>
                      
                      <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">{{ item?.itemgroup || '' }}</div>
                        <div class="text-sm font-medium">₱{{ formatCurrency(item?.price) }}</div>
                      </div>
                      
                      <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                        <div>Manila: ₱{{ formatCurrency(item?.manilaprice) }}</div>
                        <div>Mall: ₱{{ formatCurrency(item?.mallprice) }}</div>
                        <div>Foodpanda: ₱{{ formatCurrency(item?.foodpandaprice) }}</div>
                        <div>GrabFood: ₱{{ formatCurrency(item?.grabfoodprice) }}</div>
                      </div>
                      
                      <div v-if="isAdmin || isOpic" class="flex justify-end space-x-2 mt-2">
                        <button
                          @click.stop="handleEditItem(item)"
                          class="text-blue-600 hover:text-blue-900 text-sm"
                        >
                          Edit
                        </button>
                        <button
                          @click.stop="handleViewLinks(item)"
                          class="text-green-600 hover:text-green-900 text-sm"
                        >
                          Links
                        </button>
                        <button
                          @click.stop="handleMoreActions(item)"
                          class="text-gray-600 hover:text-gray-900 text-sm"
                        >
                          More
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Desktop View -->
                <table class="min-w-full divide-y divide-gray-200 hidden lg:table">
                  <thead class="bg-gray-50 sticky top-0">
                    <tr>
                      <th v-if="isAdmin || isOpic" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input
                          type="checkbox"
                          :checked="allSelected"
                          @change="toggleAllSelection"
                          class="form-checkbox h-4 w-4 text-blue-600"
                        >
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SRP</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manila</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mall</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foodpanda</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GrabFood</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FP Mall</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GF Mall</th>
                      <th v-if="isAdmin || isOpic" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in paginatedItems" :key="item?.itemid" 
                        class="hover:bg-gray-50 cursor-pointer transition-colors"
                        @click="handleItemClick(item)"
                        @mousedown="handleMouseDown(item, $event)"
                        @mouseup="handleMouseUp(item, $event)"
                        @contextmenu="handleRightClick(item, $event)">
                      <td v-if="isAdmin || isOpic" class="px-6 py-4 whitespace-nowrap" @click.stop>
                        <input
                          type="checkbox"
                          :value="item?.itemid"
                          v-model="selectedItems"
                          class="form-checkbox h-4 w-4 text-blue-600"
                        >
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="[
                          'px-2 py-1 text-xs rounded-full',
                          item?.Activeondelivery ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        ]">
                          {{ item?.Activeondelivery ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ item?.itemid || '' }}</td>
                      <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <div class="max-w-xs truncate" :title="item?.itemname">{{ item?.itemname || '' }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item?.itemgroup || '' }}</td>
                      <!-- Cost Cell -->
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono" @click.stop>
                        <div v-if="editingCell === `${item.itemid}-cost`" class="relative">
                          <input
                            v-model="tempEditValue"
                            @blur="saveCellEdit(item.itemid, 'cost')"
                            @keydown="handleCellKeyPress($event, item.itemid, 'cost')"
                            type="number"
                            step="0.01"
                            class="w-20 px-1 py-1 border rounded text-sm"
                            :disabled="savingCell === `${item.itemid}-cost`"
                            :data-editing="`${item.itemid}-cost`"
                          />
                          <div v-if="savingCell === `${item.itemid}-cost`" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-600 rounded-full border-t-transparent"></div>
                          </div>
                        </div>
                        <span
                          v-else
                          @click="startCellEdit(item.itemid, 'cost', item.cost)"
                          :class="['cursor-pointer hover:bg-gray-100 rounded px-1 py-1', isLiveEditEnabled ? 'hover:ring-2 hover:ring-blue-300' : '']"
                        >
                          ₱{{ formatCurrency(item?.cost) }}
                        </span>
                      </td>

                      <!-- Price Cell -->
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono font-medium" @click.stop>
                        <div v-if="editingCell === `${item.itemid}-price`" class="relative">
                          <input
                            v-model="tempEditValue"
                            @blur="saveCellEdit(item.itemid, 'price')"
                            @keydown="handleCellKeyPress($event, item.itemid, 'price')"
                            type="number"
                            step="0.01"
                            class="w-20 px-1 py-1 border rounded text-sm"
                            :disabled="savingCell === `${item.itemid}-price`"
                            :data-editing="`${item.itemid}-price`"
                          />
                          <div v-if="savingCell === `${item.itemid}-price`" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-600 rounded-full border-t-transparent"></div>
                          </div>
                        </div>
                        <span
                          v-else
                          @click="startCellEdit(item.itemid, 'price', item.price)"
                          :class="['cursor-pointer hover:bg-gray-100 rounded px-1 py-1', isLiveEditEnabled ? 'hover:ring-2 hover:ring-blue-300' : '']"
                        >
                          ₱{{ formatCurrency(item?.price) }}
                        </span>
                      </td>

                      <!-- Manila Price Cell -->
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono" @click.stop>
                        <div v-if="editingCell === `${item.itemid}-manilaprice`" class="relative">
                          <input
                            v-model="tempEditValue"
                            @blur="saveCellEdit(item.itemid, 'manilaprice')"
                            @keydown="handleCellKeyPress($event, item.itemid, 'manilaprice')"
                            type="number"
                            step="0.01"
                            class="w-20 px-1 py-1 border rounded text-sm"
                            :disabled="savingCell === `${item.itemid}-manilaprice`"
                            :data-editing="`${item.itemid}-manilaprice`"
                          />
                          <div v-if="savingCell === `${item.itemid}-manilaprice`" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-600 rounded-full border-t-transparent"></div>
                          </div>
                        </div>
                        <span
                          v-else
                          @click="startCellEdit(item.itemid, 'manilaprice', item.manilaprice)"
                          :class="['cursor-pointer hover:bg-gray-100 rounded px-1 py-1', isLiveEditEnabled ? 'hover:ring-2 hover:ring-blue-300' : '']"
                        >
                          ₱{{ formatCurrency(item?.manilaprice) }}
                        </span>
                      </td>

                      <!-- Mall Price Cell -->
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono" @click.stop>
                        <div v-if="editingCell === `${item.itemid}-mallprice`" class="relative">
                          <input
                            v-model="tempEditValue"
                            @blur="saveCellEdit(item.itemid, 'mallprice')"
                            @keydown="handleCellKeyPress($event, item.itemid, 'mallprice')"
                            type="number"
                            step="0.01"
                            class="w-20 px-1 py-1 border rounded text-sm"
                            :disabled="savingCell === `${item.itemid}-mallprice`"
                            :data-editing="`${item.itemid}-mallprice`"
                          />
                          <div v-if="savingCell === `${item.itemid}-mallprice`" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-600 rounded-full border-t-transparent"></div>
                          </div>
                        </div>
                        <span
                          v-else
                          @click="startCellEdit(item.itemid, 'mallprice', item.mallprice)"
                          :class="['cursor-pointer hover:bg-gray-100 rounded px-1 py-1', isLiveEditEnabled ? 'hover:ring-2 hover:ring-blue-300' : '']"
                        >
                          ₱{{ formatCurrency(item?.mallprice) }}
                        </span>
                      </td>

                      <!-- Foodpanda Price Cell -->
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono" @click.stop>
                        <div v-if="editingCell === `${item.itemid}-foodpandaprice`" class="relative">
                          <input
                            v-model="tempEditValue"
                            @blur="saveCellEdit(item.itemid, 'foodpandaprice')"
                            @keydown="handleCellKeyPress($event, item.itemid, 'foodpandaprice')"
                            type="number"
                            step="0.01"
                            class="w-20 px-1 py-1 border rounded text-sm"
                            :disabled="savingCell === `${item.itemid}-foodpandaprice`"
                            :data-editing="`${item.itemid}-foodpandaprice`"
                          />
                          <div v-if="savingCell === `${item.itemid}-foodpandaprice`" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-600 rounded-full border-t-transparent"></div>
                          </div>
                        </div>
                        <span
                          v-else
                          @click="startCellEdit(item.itemid, 'foodpandaprice', item.foodpandaprice)"
                          :class="['cursor-pointer hover:bg-gray-100 rounded px-1 py-1', isLiveEditEnabled ? 'hover:ring-2 hover:ring-blue-300' : '']"
                        >
                          ₱{{ formatCurrency(item?.foodpandaprice) }}
                        </span>
                      </td>

                      <!-- GrabFood Price Cell -->
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono" @click.stop>
                        <div v-if="editingCell === `${item.itemid}-grabfoodprice`" class="relative">
                          <input
                            v-model="tempEditValue"
                            @blur="saveCellEdit(item.itemid, 'grabfoodprice')"
                            @keydown="handleCellKeyPress($event, item.itemid, 'grabfoodprice')"
                            type="number"
                            step="0.01"
                            class="w-20 px-1 py-1 border rounded text-sm"
                            :disabled="savingCell === `${item.itemid}-grabfoodprice`"
                            :data-editing="`${item.itemid}-grabfoodprice`"
                          />
                          <div v-if="savingCell === `${item.itemid}-grabfoodprice`" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-600 rounded-full border-t-transparent"></div>
                          </div>
                        </div>
                        <span
                          v-else
                          @click="startCellEdit(item.itemid, 'grabfoodprice', item.grabfoodprice)"
                          :class="['cursor-pointer hover:bg-gray-100 rounded px-1 py-1', isLiveEditEnabled ? 'hover:ring-2 hover:ring-blue-300' : '']"
                        >
                          ₱{{ formatCurrency(item?.grabfoodprice) }}
                        </span>
                      </td>

                      <!-- Foodpanda Mall Price Cell -->
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono" @click.stop>
                        <div v-if="editingCell === `${item.itemid}-foodpandamallprice`" class="relative">
                          <input
                            v-model="tempEditValue"
                            @blur="saveCellEdit(item.itemid, 'foodpandamallprice')"
                            @keydown="handleCellKeyPress($event, item.itemid, 'foodpandamallprice')"
                            type="number"
                            step="0.01"
                            class="w-20 px-1 py-1 border rounded text-sm"
                            :disabled="savingCell === `${item.itemid}-foodpandamallprice`"
                            :data-editing="`${item.itemid}-foodpandamallprice`"
                          />
                          <div v-if="savingCell === `${item.itemid}-foodpandamallprice`" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-600 rounded-full border-t-transparent"></div>
                          </div>
                        </div>
                        <span
                          v-else
                          @click="startCellEdit(item.itemid, 'foodpandamallprice', item.foodpandamallprice)"
                          :class="['cursor-pointer hover:bg-gray-100 rounded px-1 py-1', isLiveEditEnabled ? 'hover:ring-2 hover:ring-blue-300' : '']"
                        >
                          ₱{{ formatCurrency(item?.foodpandamallprice) }}
                        </span>
                      </td>

                      <!-- GrabFood Mall Price Cell -->
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono" @click.stop>
                        <div v-if="editingCell === `${item.itemid}-grabfoodmallprice`" class="relative">
                          <input
                            v-model="tempEditValue"
                            @blur="saveCellEdit(item.itemid, 'grabfoodmallprice')"
                            @keydown="handleCellKeyPress($event, item.itemid, 'grabfoodmallprice')"
                            type="number"
                            step="0.01"
                            class="w-20 px-1 py-1 border rounded text-sm"
                            :disabled="savingCell === `${item.itemid}-grabfoodmallprice`"
                            :data-editing="`${item.itemid}-grabfoodmallprice`"
                          />
                          <div v-if="savingCell === `${item.itemid}-grabfoodmallprice`" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded">
                            <div class="animate-spin h-4 w-4 border-2 border-blue-600 rounded-full border-t-transparent"></div>
                          </div>
                        </div>
                        <span
                          v-else
                          @click="startCellEdit(item.itemid, 'grabfoodmallprice', item.grabfoodmallprice)"
                          :class="['cursor-pointer hover:bg-gray-100 rounded px-1 py-1', isLiveEditEnabled ? 'hover:ring-2 hover:ring-blue-300' : '']"
                        >
                          ₱{{ formatCurrency(item?.grabfoodmallprice) }}
                        </span>
                      </td>
                      <td v-if="isAdmin || isOpic" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                        <div class="flex justify-end space-x-2">
                          <button
                            @click="handleEditItem(item)"
                            class="text-blue-600 hover:text-blue-900"
                            title="Edit Item"
                          >
                            Edit
                          </button>
                          <button
                            @click="handleViewLinks(item)"
                            class="text-green-600 hover:text-green-900"
                            title="Item Links"
                          >
                            Links
                          </button>
                          <button
                            @click="handleMoreActions(item)"
                            class="text-gray-600 hover:text-gray-900"
                            title="More Actions"
                          >
                            More
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200" v-if="totalPages > 1">
              <div class="flex-1 flex justify-between sm:hidden">
                <button
                  @click="previousPage"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                  Previous
                </button>
                <button
                  @click="nextPage"
                  :disabled="currentPage === totalPages"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                  Next
                </button>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Showing {{ startIndex + 1 }} to {{ Math.min(startIndex + itemsPerPage, (filteredItems?.length || 0)) }} of {{ filteredItems?.length || 0 }} results
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button
                      @click="previousPage"
                      :disabled="currentPage === 1"
                      class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                      Previous
                    </button>
                    <button
                      v-for="page in visiblePages"
                      :key="page"
                      @click="goToPage(page)"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        page === currentPage
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      ]"
                    >
                      {{ page }}
                    </button>
                    <button
                      @click="nextPage"
                      :disabled="currentPage === totalPages"
                      class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                      Next
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </TableContainer>

        <!-- Mobile Floating Action Button and Menu -->
        <div class="lg:hidden fixed bottom-6 right-6 z-40">
          <!-- Floating Menu Options -->
          <div v-if="showFloatingMenu" class="absolute bottom-16 right-0 bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-56 transform transition-all duration-200 ease-out">
            <!-- Add Item -->
            <button
              v-if="isOpic"
              @click="toggleCreateModal"
              class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
            >
              <Add class="h-4 w-4 mr-3 text-gray-500" />
              Add New Item
            </button>

            <!-- Enable Selected -->
            <button
              v-if="(isAdmin || isOpic) && selectedItems.length > 0"
              @click="handleBulkEnable"
              class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
            >
              <Enabled class="h-4 w-4 mr-3 text-green-500" />
              Enable Selected ({{ selectedItems.length }})
            </button>

            <!-- Export -->
            <div v-if="isAdmin || isOpic" class="px-4 py-3">
              <Excel
                :data="getExportData()"
                :headers="[
                  'PRODUCTCODE', 'DESCRIPTION', 'BARCODE', 'CATEGORY', 'RETAILGROUP', 
                  'SRP', 'MANILA', 'MALL', 'GRABFOOD', 'FOODPANDA', 'GRABFOODMALL', 'FOODPANDAMALL'
                ]"
                :row-name-props="[
                  'PRODUCTCODE', 'DESCRIPTION', 'BARCODE', 'CATEGORY', 'RETAILGROUP', 
                  'SRP', 'MANILA', 'MALL', 'GRABFOOD', 'FOODPANDA', 'GRABFOODMALL', 'FOODPANDAMALL'
                ]"
                class="w-full text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center p-0 bg-transparent border-0 hover:text-gray-900"
              >
                <svg class="h-4 w-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
              </Excel>
            </div>

            <!-- Import -->
            <button
              v-if="isOpic"
              @click="showImportModal = true"
              class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
            >
              <Import class="h-4 w-4 mr-3 text-blue-500" />
              Import CSV
            </button>

            <!-- Download Template -->
            <button
              v-if="isOpic"
              @click="downloadTemplate"
              class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
            >
              <svg class="h-4 w-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Download Template
            </button>

            <div class="border-t border-gray-200 my-2"></div>

            <!-- Navigation -->
            <button
              @click="products"
              class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
            >
              <svg class="h-4 w-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              BW Products
            </button>

            <button
              @click="nonproducts"
              class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
            >
              <svg class="h-4 w-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h1.586a1 1 0 01.707.293l1.414 1.414a1 1 0 00.707.293H19a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
              Warehouse
            </button>
          </div>

          <!-- Main Floating Action Button -->
          <button
            @click="toggleFloatingMenu"
            class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-200 ease-out transform hover:scale-105"
            :class="{ 'rotate-45': showFloatingMenu }"
          >
            <MenuIcon v-if="!showFloatingMenu" class="h-6 w-6" />
            <CloseIcon v-else class="h-6 w-6" />
          </button>
        </div>

        <!-- Overlay to close floating menu -->
        <div v-if="showFloatingMenu" @click="closeFloatingMenu" class="lg:hidden fixed inset-0 bg-black bg-opacity-25 z-30"></div>
      </template>
    </component>
  </template>

