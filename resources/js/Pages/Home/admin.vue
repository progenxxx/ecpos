<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
import axios from 'axios';
import AdminPanel from "@/Layouts/AdminPanel.vue";
import Store from "@/Components/Svgs/Store.vue";
import Peso from "@/Components/Svgs/Peso.vue";
import Receipt from "@/Components/Svgs/Receipt.vue";
import Income from "@/Components/Svgs/Income.vue";
import Discounts from "@/Components/Svgs/Discounts.vue";
import { CubeIcon } from '@heroicons/vue/24/outline';
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import WasteAnalysis from '../Home/wasteanalysis.vue';
import ProductAnalysis from '../Home/ProductAnalysis.vue';
import FloatingChatBot from '@/Components/ChatBot/FloatingChatBot.vue';

// Register Chart.js components
Chart.register(...registerables);

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const props = defineProps({
    username: {
        type: String,
        default: 'User'
    },
    announcements: {
        type: Array,
        required: true,
    },
    metrics: {
        type: Object,
        required: true,
        default: () => ({
            totalGross: 0,
            totalNetsales: 0,
            totalDiscount: 0,
            totalCost: 0,
            totalVat: 0,
            totalVatableSales: 0,
            paymentBreakdown: {
                cash: 0,
                gcash: 0,
                paymaya: 0,
                card: 0,
                loyaltyCard: 0,
                foodPanda: 0,
                grabFood: 0
            }
        })
    }
});

const localMetrics = ref(props.metrics);
const dateRangeError = ref('');

const monthlySalesRef = ref(null);
let monthlySalesChart = null;

const topWasteRef = ref(null);
let topWasteChart = null;

const topProductsRef = ref(null);
const bottomProductsRef = ref(null);
let topProductsChart = null;
let bottomProductsChart = null;

// New chart refs
const transactionSalesRef = ref(null);
let transactionSalesChart = null;

const salesByHourRef = ref(null);
let salesByHourChart = null;

const topStoresRef = ref(null);
let topStoresChart = null;

const advancedAnalysisRef = ref(null);
let advancedAnalysisChart = null;

const receivedDeliveryVsSalesRef = ref(null);
let receivedDeliveryVsSalesChart = null;

const salesByCategoryRef = ref(null);
let salesByCategoryChart = null;

const topVarianceStoresRef = ref(null);
let topVarianceStoresChart = null;

const stores = ref([]);
const selectedStores = ref([]);
const products = ref([]);
const selectedProducts = ref([]);
const categories = ref([]);
const selectedCategories = ref([]);
const categoryDropdownOpen = ref(false);
const categoryDropdownRef = ref(null);

// Transaction product dropdown state
const transactionProductDropdownOpen = ref(false);
const transactionProductDropdownRef = ref(null);
const productSearchTerm = ref('');

const fetchStores = async () => {
  try {
    const response = await axios.get(route('get.stores'));
    stores.value = response.data;
    selectedStores.value = response.data;  // By default, all stores are selected
  } catch (error) {
    console.error('Error fetching stores:', error);
  }
};

const fetchProducts = async (searchTerm = '') => {
  try {
    console.log('Fetching products with search term:', searchTerm);
    const response = await axios.get(route('get.products'), {
      params: { search: searchTerm }
    });
    console.log('Products loaded:', response.data.length);
    products.value = response.data;
  } catch (error) {
    console.error('Error fetching products:', error);
    if (error.response) {
      console.error('Products error response:', error.response.data);
    }
  }
};

const fetchCategories = async (searchTerm = '') => {
  try {
    console.log('Fetching categories...');
    const response = await axios.get(route('get.categories'), {
      params: { search: searchTerm }
    });
    console.log('Categories response:', response.data);
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
    if (error.response) {
      console.error('Categories error response:', error.response.data);
    }
  }
};

// Category dropdown functions
const toggleCategory = (categoryValue) => {
  const index = selectedCategories.value.indexOf(categoryValue);
  if (index === -1) {
    selectedCategories.value.push(categoryValue);
  } else {
    selectedCategories.value.splice(index, 1);
  }
};

const toggleAllCategories = () => {
  if (selectedCategories.value.length === categories.value.length) {
    selectedCategories.value = [];
  } else {
    selectedCategories.value = categories.value.map(cat => cat.value);
  }
};

const getCategoryLabel = (categoryValue) => {
  const category = categories.value.find(cat => cat.value === categoryValue);
  return category ? category.label : categoryValue;
};

// Close dropdown when clicking outside
const handleCategoryDropdownClickOutside = (event) => {
  if (categoryDropdownRef.value && !categoryDropdownRef.value.contains(event.target)) {
    categoryDropdownOpen.value = false;
  }
};

// Transaction product dropdown functions
const toggleProduct = (productId) => {
  console.log('Toggling product:', productId);
  console.log('Current selected products before toggle:', selectedProducts.value);
  
  const index = selectedProducts.value.indexOf(productId);
  if (index === -1) {
    selectedProducts.value.push(productId);
    console.log('Added product:', productId);
  } else {
    selectedProducts.value.splice(index, 1);
    console.log('Removed product:', productId);
  }
  
  console.log('Selected products after toggle:', selectedProducts.value);
};

const toggleAllProducts = () => {
  if (selectedProducts.value.length === filteredProducts.value.length && filteredProducts.value.length > 0) {
    selectedProducts.value = [];
  } else {
    selectedProducts.value = filteredProducts.value.map(product => product.itemid);
  }
};

const getProductLabel = (productId) => {
  const product = products.value.find(p => p.itemid === productId);
  return product ? product.itemname : productId;
};

// Filtered products based on search term
const filteredProducts = computed(() => {
  if (!productSearchTerm.value) {
    return products.value;
  }
  return products.value.filter(product => 
    product.itemname.toLowerCase().includes(productSearchTerm.value.toLowerCase()) ||
    product.itemid.toLowerCase().includes(productSearchTerm.value.toLowerCase())
  );
});

// Debounced search function
const debouncedProductSearch = debounce(() => {
  // Search is handled by computed filteredProducts
}, 300);

// Simple debounce function
function debounce(func, delay) {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(this, args), delay);
  };
}

// Close transaction product dropdown when clicking outside
const handleTransactionProductDropdownClickOutside = (event) => {
  if (transactionProductDropdownRef.value && !transactionProductDropdownRef.value.contains(event.target)) {
    transactionProductDropdownOpen.value = false;
    productSearchTerm.value = ''; // Reset search when closing
  }
};

// State variables
const hoveredCard = ref(null);
const isVisible = ref(false);
const chartRef = ref(null);
const topBottomProductsRef = ref(null);
let paymentChart = null;
let topBottomProductsChart = null;

// Loading states
const isLoadingMetrics = ref(false);
const isLoadingCharts = ref(false);

// Set default date range to last 30 days to ensure we have data
const getDefaultDateRange = () => {
  const today = new Date();
  const thirtyDaysAgo = new Date();
  thirtyDaysAgo.setDate(today.getDate() - 30);
  
  return {
    start_date: thirtyDaysAgo.toISOString().split('T')[0],
    end_date: today.toISOString().split('T')[0]
  };
};

const selectedDateRange = ref(getDefaultDateRange());

// Product type filter for Popular Products & Products Under Monitoring
const selectedProductType = ref('all');

// Filter states for new charts
const transactionSalesFilter = ref('gross');
const topStoresFilter = ref('grossamount');

// New: Date Range Validation Computed Property
const isValidDateRange = computed(() => {
    const startDate = new Date(selectedDateRange.value.start_date);
    const endDate = new Date(selectedDateRange.value.end_date);
    const today = new Date();

    return (
        startDate <= endDate && 
        startDate <= today &&
        endDate <= today
    );
});


// Currency formatting utility
const formatCurrency = (value) => {
    const numValue = Number(value);
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(isNaN(numValue) ? 0 : numValue);
};

// Metrics cards computation (continued)
const metricsCards = computed(() => {
    /* const metrics = props.metrics || {}; */
    const metrics = localMetrics.value || {};
    return [
        {
            title: "TOTAL GROSS",
            value: formatCurrency(metrics.totalGross),
            icon: Discounts,
            color: "text-blue-600",
            bgColor: "bg-blue-100",
            description: "Total revenue before deductions"
        },
        {
            title: "TOTAL NETSALES",
            value: formatCurrency(metrics.totalNetsales),
            icon: Income,
            color: "text-green-600",
            bgColor: "bg-green-100",
            description: "Revenue after all deductions"
        },
        {
            title: "TOTAL DISCOUNT",
            value: formatCurrency(metrics.totalDiscount),
            icon: Discounts,
            color: "text-amber-600",
            bgColor: "bg-amber-100",
            description: "Sum of all applied discounts"
        },
        {
            title: "TOTAL COST",
            value: formatCurrency(metrics.totalCost),
            icon: CubeIcon,
            color: "text-purple-600",
            bgColor: "bg-purple-100",
            description: "Aggregate cost of goods sold"
        },
        {
            title: "TOTAL VAT",
            value: formatCurrency(metrics.totalVat),
            icon: Receipt,
            color: "text-pink-600",
            bgColor: "bg-pink-100",
            description: "Value Added Tax collected"
        },
        {
            title: "VATABLE SALES",
            value: formatCurrency(metrics.totalVatableSales),
            icon: Peso,
            color: "text-indigo-600",
            bgColor: "bg-indigo-100",
            description: "Sales subject to VAT"
        }
    ];
});

/* const initializePaymentChart = () => {
    if (!chartRef.value) return;
    
    const ctx = chartRef.value.getContext('2d');
    
    if (paymentChart) {
        paymentChart.destroy();
    }

    // Use localMetrics instead of props.metrics
    const paymentBreakdown = localMetrics.value?.paymentBreakdown || {};
    const labels = Object.keys(paymentBreakdown).map(label => 
        label.replace(/([A-Z])/g, ' $1').trim()
    );
    const data = Object.values(paymentBreakdown).map(value => 
        Number(value) || 0
    );

    paymentChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F43F5E', 
                    '#6366F1', '#8B5CF6', '#EC4899', 
                    '#F97316'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.parsed;
                            const percentage = ((value / total) * 100).toFixed(2);
                            return `${context.label}: ${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}; */

const initializePaymentChart = () => {
    if (!chartRef.value) return;
    
    const ctx = chartRef.value.getContext('2d');
    
    if (paymentChart) {
        paymentChart.destroy();
    }

    // Use localMetrics instead of props.metrics
    const paymentBreakdown = localMetrics.value?.paymentBreakdown || {};
    const labels = Object.keys(paymentBreakdown).map(label => {
        const value = paymentBreakdown[label];
        const formattedLabel = label.replace(/([A-Z])/g, ' $1').trim();
        const formattedValue = formatCurrency(value);
        return `${formattedLabel} (${formattedValue})`;
    });
    const data = Object.values(paymentBreakdown).map(value => 
        Number(value) || 0
    );

    paymentChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F43F5E', 
                    '#6366F1', '#8B5CF6', '#EC4899', 
                    '#F97316'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.parsed;
                            const percentage = ((value / total) * 100).toFixed(2);
                            return `${context.label}: ${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
};

const fetchTopBottomProducts = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
          store.NAME || (typeof store === 'object' ? store.NAME : store)
      ),
      product_type: selectedProductType.value
    };

    console.log('Top/Bottom Products Payload:', payload);

    const response = await axios.post(route('get.top.bottom.products'), payload);

    console.log('Top/Bottom Products Response:', response.data);
    console.log('Response Status:', response.status);

    if (response.status !== 200) {
      throw new Error(`API returned status: ${response.status}`);
    }

    const topProducts = Array.isArray(response?.data?.topProducts) 
      ? response.data.topProducts 
      : [];
    const bottomProducts = Array.isArray(response?.data?.bottomProducts) 
      ? response.data.bottomProducts 
      : [];
    const summary = response?.data?.summary || {};

    console.log('Processed Data:', {
      topProductsCount: topProducts.length,
      bottomProductsCount: bottomProducts.length,
      summaryData: summary
    });

    initializeTopBottomProductsChart(topProducts, bottomProducts, summary);
  } catch (error) {
    console.error('Error fetching top/bottom products:', error);
    initializeTopBottomProductsChart([], [], {});
  }
};

// Initialize top and bottom products chart
const initializeTopBottomProductsChart = (topProducts, bottomProducts, summary = {}) => {
    if (!topBottomProductsRef.value) return;
    
    const ctx = topBottomProductsRef.value.getContext('2d');
    
    if (topBottomProductsChart) {
        topBottomProductsChart.destroy();
    }

    // Handle empty data case
    if ((!topProducts || topProducts.length === 0) && (!bottomProducts || bottomProducts.length === 0)) {
        topBottomProductsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['No Data Available'],
                datasets: [{
                    label: 'No products data available',
                    data: [0],
                    backgroundColor: 'rgba(128, 128, 128, 0.3)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Popular Products & Products Under Monitoring (No Data)',
                        font: { size: 14, weight: 'bold' }
                    }
                }
            }
        });
        return;
    }

    console.log('Top Products Data:', topProducts);
    console.log('Bottom Products Data:', bottomProducts);

    // Prepare data for chart - take top 10 and bottom 10 for better visibility
    const topProductsSlice = (topProducts || []).slice(0, 10);
    const bottomProductsSlice = (bottomProducts || []).slice(0, 10);
    
    const topProductNames = topProductsSlice.map(p => p.itemname || 'Unknown');
    const bottomProductNames = bottomProductsSlice.map(p => p.itemname || 'Unknown');
    const topProductSales = topProductsSlice.map(p => Number(p.total_sales || 0));
    const bottomProductSales = bottomProductsSlice.map(p => Number(p.total_sales || 0));

    // Combine data for a single chart
    const allLabels = [...topProductNames.map(name => `🔥 ${name}`), ...bottomProductNames.map(name => `⚠️ ${name}`)];
    const allData = [...topProductSales, ...bottomProductSales];
    const backgroundColors = [
        // Top products - green shades
        ...Array(topProductsSlice.length).fill('rgba(34, 197, 94, 0.6)'),
        // Bottom products - red shades  
        ...Array(bottomProductsSlice.length).fill('rgba(239, 68, 68, 0.6)')
    ];
    const borderColors = [
        ...Array(topProductsSlice.length).fill('rgba(34, 197, 94, 1)'),
        ...Array(bottomProductsSlice.length).fill('rgba(239, 68, 68, 1)')
    ];

    topBottomProductsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: allLabels,
            datasets: [{
                label: 'Sales Amount (₱)',
                data: allData,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: (() => {
                        const filterText = (() => {
                            switch(selectedProductType.value) {
                                case 'regular': return '(Regular Products Only)';
                                case 'non_product': return '(Non Products Only)';
                                default: return '(All Products)';
                            }
                        })();
                        return `Popular Products & Products Under Monitoring ${filterText}`;
                    })(),
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                },
                legend: {
                    display: false // Hide legend since we use different colors for different categories
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            // Remove emoji from tooltip title for cleaner display
                            return context[0].label.replace(/🔥|⚠️/g, '').trim();
                        },
                        label: function(context) {
                            const value = context.parsed.y;
                            const index = context.dataIndex;
                            
                            let label = `Sales: ₱${value.toLocaleString()}`;
                            
                            // Determine if this is a top or bottom product
                            const isTopProduct = index < topProductsSlice.length;
                            const productData = isTopProduct ? 
                                topProductsSlice[index] : 
                                bottomProductsSlice[index - topProductsSlice.length];
                            
                            if (productData) {
                                const productCategory = productData.product_category || 'unknown';
                                const retailDepartment = productData.retail_department || 'Unknown';
                                const storeCount = productData.store_count || 0;
                                
                                label += `\nQuantity: ${(productData.total_quantity || 0).toLocaleString()}`;
                                label += `\nCategory: ${productCategory === 'non_product' ? 'Non Product' : 'Regular Product'}`;
                                label += `\nDepartment: ${retailDepartment}`;
                                label += `\nStores: ${storeCount}`;
                                label += `\nType: ${isTopProduct ? 'Top Performer' : 'Under Monitoring'}`;
                            }
                            
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Sales Amount (₱)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
};

const fetchMetrics = async () => {
  try {
    isLoadingMetrics.value = true;
    console.log('Selected Stores:', selectedStores.value);

    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store =>
        typeof store === 'object' ? store.NAME : store
      )
    };

    console.log('Payload for metrics:', payload);

    const response = await axios.post(route('get.metrics'), payload);

    console.log('Metrics response:', response.data);

    localMetrics.value = response.data || props.metrics;
    initializePaymentChart();
  } catch (error) {
    console.error('Error fetching metrics:', error);
    // Fallback to props.metrics if request fails
    localMetrics.value = props.metrics;
    initializePaymentChart();
  } finally {
    isLoadingMetrics.value = false;
  }
};

const fetchMonthlySales = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    const response = await axios.post(route('get.monthly.sales'), payload);
    initializeMonthlySalesChart(response.data || []);
  } catch (error) {
    console.error('Error fetching monthly sales:', error);
    initializeMonthlySalesChart([]);
  }
};

const initializeMonthlySalesChart = (monthlySalesData) => {
  if (!monthlySalesRef.value) return;
  
  const ctx = monthlySalesRef.value.getContext('2d');
  
  if (monthlySalesChart) {
    monthlySalesChart.destroy();
  }

  // Handle empty data
  const data = Array.isArray(monthlySalesData) ? monthlySalesData : [];
  const labels = data.length > 0 ? data.map(item => item.label) : ['No Data'];
  const salesData = data.length > 0 ? data.map(item => item.total_sales) : [0];

  monthlySalesChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Monthly Sales',
        data: salesData,
        borderColor: 'rgba(75, 192, 192, 1)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        tension: 0.1,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return formatCurrency(context.parsed.y);
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Sales Amount'
          },
          ticks: {
            callback: function(value) {
              return formatCurrency(value);
            }
          }
        }
      }
    }
  });
};

const fetchTopWastes = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    const response = await axios.post(route('get.top.wastes'), payload);
    
    console.log('Waste Analysis Response:', response.data);

    const topWastesOverall = Array.isArray(response?.data?.topWastesOverall) 
      ? response.data.topWastesOverall 
      : [];
      
    const wasteSummaryByType = Array.isArray(response?.data?.wasteSummaryByType) 
      ? response.data.wasteSummaryByType 
      : [];

    initializeTopWasteChart(topWastesOverall, wasteSummaryByType);
  } catch (error) {
    console.error('Error fetching top wastes:', error);
    initializeTopWasteChart([], []);
  }
};

const initializeTopWasteChart = (topWastes, wasteSummaryByType = []) => {
    if (!topWasteRef.value) return;
    
    const ctx = topWasteRef.value.getContext('2d');
    
    if (topWasteChart) {
        topWasteChart.destroy();
    }

    // Handle empty data
    if (!topWastes || topWastes.length === 0) {
        topWasteChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['No Data'],
                datasets: [{
                    label: 'No waste data available',
                    data: [0],
                    backgroundColor: 'rgba(128, 128, 128, 0.3)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
        return;
    }

    // Prepare data for chart
    const wasteItemNames = topWastes.slice(0, 10).map(w => {
        const itemName = w.itemname || w.ITEMID;
        // Truncate long names for better display
        return itemName.length > 20 ? itemName.substring(0, 20) + '...' : itemName;
    });
    const wasteQuantities = topWastes.slice(0, 10).map(w => Math.abs(Number(w.total_waste_quantity || 0)));
    const wasteCosts = topWastes.slice(0, 10).map(w => Math.abs(Number(w.total_waste_cost || 0)));
    const wasteTypes = topWastes.slice(0, 10).map(w => w.waste_types || 'Various');

    topWasteChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: wasteItemNames,
            datasets: [
                {
                    label: 'Waste Quantity',
                    data: wasteQuantities,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Waste Cost (₱)',
                    data: wasteCosts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1',
                    fill: false,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Top Waste Items (Stock Counting Analysis)',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                },
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            const index = context[0].dataIndex;
                            const fullName = topWastes[index]?.itemname || topWastes[index]?.ITEMID || 'Unknown';
                            return fullName;
                        },
                        label: function(context) {
                            const value = context.parsed.y;
                            const datasetLabel = context.dataset.label;
                            const index = context.dataIndex;
                            const wasteTypesForItem = wasteTypes[index];
                            
                            let label = `${datasetLabel}: `;
                            if (context.dataset.label === 'Waste Cost (₱)') {
                                label += formatCurrency(value);
                            } else {
                                label += value.toFixed(2) + ' units';
                            }
                            
                            return label;
                        },
                        afterLabel: function(context) {
                            const index = context.dataIndex;
                            const wasteTypesForItem = wasteTypes[index];
                            return `Waste Types: ${wasteTypesForItem}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Items'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Waste Quantity (units)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Waste Cost (₱)'
                    },
                    grid: {
                        drawOnChartArea: true,
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
};

const fetchTransactionSales = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      filter_by: transactionSalesFilter.value,
      stores: selectedStores.value.map(store => store.NAME || store),
      products: selectedProducts.value
    };

    console.log('Fetching transaction sales with payload:', payload);
    console.log('Selected products:', selectedProducts.value);
    console.log('Selected stores:', selectedStores.value);
    
    const response = await axios.post(route('get.transaction.sales'), payload);
    console.log('Transaction sales response:', response.data);
    console.log('Transaction sales data count:', response.data?.data?.length || 0);
    
    initializeTransactionSalesChart(response.data || { data: [], summary: {} });
  } catch (error) {
    console.error('Error fetching transaction sales:', error);
    if (error.response) {
      console.error('Transaction sales error response:', error.response.data);
    }
    initializeTransactionSalesChart({ data: [], summary: {} });
  }
};

const initializeTransactionSalesChart = (data) => {
  if (!transactionSalesRef.value) return;
  
  const ctx = transactionSalesRef.value.getContext('2d');
  
  if (transactionSalesChart) {
    transactionSalesChart.destroy();
  }

  const chartData = data.data || [];
  console.log('Initializing chart with data:', chartData);
  
  const labels = chartData.map(item => item.label);
  const values = chartData.map(item => item.total_value);
  
  console.log('Chart labels:', labels);
  console.log('Chart values:', values);

  // If no data, show a message
  if (chartData.length === 0) {
    console.log('No data to display in chart');
  }

  transactionSalesChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels.length > 0 ? labels : ['No Data'],
      datasets: [{
        label: transactionSalesFilter.value === 'qty' ? 'Total Quantity' : 'Total Sales (₱)',
        data: values.length > 0 ? values : [0],
        borderColor: 'rgba(59, 130, 246, 1)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: `Transaction ${transactionSalesFilter.value === 'qty' ? 'Quantity' : 'Sales'} Trend${selectedProducts.value.length > 0 ? ` (${selectedProducts.value.length} products selected)` : ''}`,
          font: { size: 14, weight: 'bold' }
        },
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return transactionSalesFilter.value === 'qty' 
                ? value.toLocaleString() + ' items'
                : '₱' + value.toLocaleString();
            }
          }
        }
      }
    }
  });
};

const fetchSalesByHour = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    const response = await axios.post(route('get.sales.by.hour'), payload);
    initializeSalesByHourChart(response.data || { data: [], summary: {} });
  } catch (error) {
    console.error('Error fetching sales by hour:', error);
    initializeSalesByHourChart({ data: [], summary: {} });
  }
};

const initializeSalesByHourChart = (data) => {
  if (!salesByHourRef.value) return;
  
  const ctx = salesByHourRef.value.getContext('2d');
  
  if (salesByHourChart) {
    salesByHourChart.destroy();
  }

  const chartData = data.data || [];
  const labels = chartData.map(item => item.label);
  const sales = chartData.map(item => item.total_sales);

  salesByHourChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Sales (₱)',
        data: sales,
        backgroundColor: 'rgba(16, 185, 129, 0.6)',
        borderColor: 'rgba(16, 185, 129, 1)',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: 'Sales by Hour',
          font: { size: 14, weight: 'bold' }
        },
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₱' + value.toLocaleString();
            }
          }
        }
      }
    }
  });
};

const fetchTopStores = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      ),
      filter_by: topStoresFilter.value
    };

    const response = await axios.post(route('get.top.stores'), payload);
    initializeTopStoresChart(response.data || { data: [], summary: {} });
  } catch (error) {
    console.error('Error fetching top stores:', error);
    initializeTopStoresChart({ data: [], summary: {} });
  }
};

const initializeTopStoresChart = (data) => {
  if (!topStoresRef.value) return;
  
  const ctx = topStoresRef.value.getContext('2d');
  
  if (topStoresChart) {
    topStoresChart.destroy();
  }

  const chartData = data.data || [];
  const storeNames = chartData.map(item => item.store);
  const values = chartData.map(item => item.total_value);

  topStoresChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: storeNames,
      datasets: [{
        label: getFilterLabel(topStoresFilter.value),
        data: values,
        backgroundColor: 'rgba(139, 92, 246, 0.6)',
        borderColor: 'rgba(139, 92, 246, 1)',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: `Top Stores by ${getFilterLabel(topStoresFilter.value)}`,
          font: { size: 14, weight: 'bold' }
        },
        legend: { display: false }
      },
      scales: {
        x: {
          ticks: {
            maxRotation: 45,
            minRotation: 45
          }
        },
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return topStoresFilter.value === 'qty' 
                ? value.toLocaleString() + ' items'
                : '₱' + value.toLocaleString();
            }
          }
        }
      }
    }
  });
};

const getFilterLabel = (filter) => {
  const labels = {
    grossamount: 'Gross Amount',
    discamount: 'Discount Amount',
    netamount: 'Net Amount',
    qty: 'Quantity'
  };
  return labels[filter] || 'Gross Amount';
};

const fetchAdvancedAnalysis = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    const response = await axios.post(route('get.advanced.analysis'), payload);
    initializeAdvancedAnalysisChart(response.data || { salesTrend: [], storeComparison: [], categoryPerformance: [] });
  } catch (error) {
    console.error('Error fetching advanced analysis:', error);
    initializeAdvancedAnalysisChart({ salesTrend: [], storeComparison: [], categoryPerformance: [] });
  }
};

const initializeAdvancedAnalysisChart = (data) => {
  if (!advancedAnalysisRef.value) return;
  
  const ctx = advancedAnalysisRef.value.getContext('2d');
  
  if (advancedAnalysisChart) {
    advancedAnalysisChart.destroy();
  }

  const salesTrend = data.salesTrend || [];
  const labels = salesTrend.map(item => item.label);
  const grossSales = salesTrend.map(item => item.gross_sales);
  const netSales = salesTrend.map(item => item.net_sales);
  const discounts = salesTrend.map(item => item.discount_amount);

  advancedAnalysisChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Gross Sales',
          data: grossSales,
          borderColor: 'rgba(59, 130, 246, 1)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4
        },
        {
          label: 'Net Sales',
          data: netSales,
          borderColor: 'rgba(16, 185, 129, 1)',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4
        },
        {
          label: 'Discounts',
          data: discounts,
          borderColor: 'rgba(239, 68, 68, 1)',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          tension: 0.4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: 'Advanced Sales Analysis',
          font: { size: 14, weight: 'bold' }
        },
        legend: {
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₱' + value.toLocaleString();
            }
          }
        }
      }
    }
  });
};

const fetchReceivedDeliveryVsSales = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    const response = await axios.post(route('get.received.delivery.vs.sales'), payload);
    initializeReceivedDeliveryVsSalesChart(response.data || { data: [], summary: {} });
  } catch (error) {
    console.error('Error fetching received delivery vs sales:', error);
    initializeReceivedDeliveryVsSalesChart({ data: [], summary: {} });
  }
};

const initializeReceivedDeliveryVsSalesChart = (data) => {
  if (!receivedDeliveryVsSalesRef.value) return;
  
  const ctx = receivedDeliveryVsSalesRef.value.getContext('2d');
  
  if (receivedDeliveryVsSalesChart) {
    receivedDeliveryVsSalesChart.destroy();
  }

  const chartData = data.data || [];
  const labels = chartData.map(item => item.label);
  const receivedDelivery = chartData.map(item => item.received_delivery);
  const totalSales = chartData.map(item => item.total_sales);

  receivedDeliveryVsSalesChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Received Delivery',
          data: receivedDelivery,
          borderColor: 'rgba(59, 130, 246, 1)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4,
          fill: false
        },
        {
          label: 'Total Sales',
          data: totalSales,
          borderColor: 'rgba(16, 185, 129, 1)',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4,
          fill: false
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: 'Received Delivery vs Sales',
          font: { size: 14, weight: 'bold' }
        },
        legend: {
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return value.toLocaleString() + ' units';
            }
          }
        }
      }
    }
  });
};

const fetchSalesByCategory = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      ),
      categories: selectedCategories.value
    };

    console.log('Fetching sales by category with payload:', payload);
    const response = await axios.post(route('get.sales.by.category'), payload);
    console.log('Sales by category response:', response.data);
    initializeSalesByCategoryChart(response.data || { data: [], summary: {} });
  } catch (error) {
    console.error('Error fetching sales by category:', error);
    if (error.response) {
      console.error('Error response:', error.response.data);
    }
    initializeSalesByCategoryChart({ data: [], summary: {} });
  }
};

const initializeSalesByCategoryChart = (data) => {
  if (!salesByCategoryRef.value) return;
  
  const ctx = salesByCategoryRef.value.getContext('2d');
  
  if (salesByCategoryChart) {
    salesByCategoryChart.destroy();
  }

  const chartData = data.data || [];
  
  if (!chartData || chartData.length === 0) {
    salesByCategoryChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['No Data Available'],
        datasets: [{
          data: [1],
          backgroundColor: ['rgba(128, 128, 128, 0.3)']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          title: {
            display: true,
            text: 'Sales by Category (No Data)',
            font: { size: 14, weight: 'bold' }
          },
          legend: { display: false }
        }
      }
    });
    return;
  }

  // Take top 10 categories and group the rest as "Others"
  const topCategories = chartData.slice(0, 10);
  const otherCategories = chartData.slice(10);
  
  let categories = topCategories.map(item => item.category);
  let sales = topCategories.map(item => item.total_sales);
  
  if (otherCategories.length > 0) {
    const otherTotalSales = otherCategories.reduce((sum, item) => sum + item.total_sales, 0);
    categories.push(`Others (${otherCategories.length} categories)`);
    sales.push(otherTotalSales);
  }

  const backgroundColors = [
    'rgba(59, 130, 246, 0.8)',
    'rgba(16, 185, 129, 0.8)', 
    'rgba(239, 68, 68, 0.8)',
    'rgba(245, 158, 11, 0.8)',
    'rgba(139, 92, 246, 0.8)',
    'rgba(236, 72, 153, 0.8)',
    'rgba(34, 197, 94, 0.8)',
    'rgba(168, 85, 247, 0.8)',
    'rgba(251, 146, 60, 0.8)',
    'rgba(14, 165, 233, 0.8)',
    'rgba(156, 163, 175, 0.8)' // Gray for "Others"
  ];

  salesByCategoryChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: categories,
      datasets: [{
        data: sales,
        backgroundColor: backgroundColors.slice(0, categories.length),
        borderColor: backgroundColors.slice(0, categories.length).map(color => color.replace('0.8', '1')),
        borderWidth: 2,
        hoverOffset: 8
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: 'Sales by Category',
          font: { size: 14, weight: 'bold' }
        },
        legend: {
          position: 'right',
          labels: {
            padding: 15,
            usePointStyle: true,
            font: {
              size: 11
            }
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const value = context.parsed;
              const percentage = ((value / total) * 100).toFixed(1);
              return `${context.label}: ₱${value.toLocaleString()} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
};

const fetchTopVarianceStores = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    const response = await axios.post(route('get.top.variance.stores'), payload);
    initializeTopVarianceStoresChart(response.data || { data: [], summary: {} });
  } catch (error) {
    console.error('Error fetching top variance stores:', error);
    initializeTopVarianceStoresChart({ data: [], summary: {} });
  }
};

const initializeTopVarianceStoresChart = (data) => {
  if (!topVarianceStoresRef.value) return;
  
  const ctx = topVarianceStoresRef.value.getContext('2d');
  
  if (topVarianceStoresChart) {
    topVarianceStoresChart.destroy();
  }

  const chartData = data.data || [];
  const stores = chartData.map(item => item.store);
  const variances = chartData.map(item => item.total_variance);

  topVarianceStoresChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: stores,
      datasets: [{
        label: 'Total Variance',
        data: variances,
        backgroundColor: 'rgba(239, 68, 68, 0.6)',
        borderColor: 'rgba(239, 68, 68, 1)',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: 'Top 10 Stores Under Monitoring (Highest Variances)',
          font: { size: 14, weight: 'bold' }
        },
        legend: { display: false }
      },
      scales: {
        x: {
          ticks: {
            maxRotation: 45,
            minRotation: 45
          }
        },
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return value.toLocaleString() + ' units';
            }
          }
        }
      }
    }
  });
};

watch([
  () => selectedDateRange.value.start_date, 
  () => selectedDateRange.value.end_date, 
  () => JSON.stringify(selectedStores.value)  // Use JSON stringify to detect array changes
], () => {
  if (isValidDateRange.value) {
    dateRangeError.value = '';
    fetchMetrics();
    fetchTopBottomProducts();
    fetchMonthlySales();
    fetchTopWastes();
    fetchTransactionSales();
    fetchSalesByHour();
    fetchTopStores();
    fetchAdvancedAnalysis();
    fetchReceivedDeliveryVsSales();
    fetchSalesByCategory();
    fetchTopVarianceStores();
  } else {
    dateRangeError.value = 'Invalid date range. Please check your selections.';
  }
});

// Watch for product type changes
watch(() => selectedProductType.value, () => {
  if (isValidDateRange.value) {
    fetchTopBottomProducts();
  }
});

// Watch for transaction sales filter changes
watch(() => transactionSalesFilter.value, () => {
  if (isValidDateRange.value) {
    fetchTransactionSales();
  }
});

// Watch for top stores filter changes
watch(() => topStoresFilter.value, () => {
  if (isValidDateRange.value) {
    fetchTopStores();
  }
});

// Watch for selected products changes
watch(() => JSON.stringify(selectedProducts.value), () => {
  console.log('Products watcher triggered, new products:', selectedProducts.value);
  console.log('Is valid date range:', isValidDateRange.value);
  if (isValidDateRange.value) {
    console.log('Calling fetchTransactionSales from watcher');
    fetchTransactionSales();
  }
});

// Watch for selected categories changes
watch(() => JSON.stringify(selectedCategories.value), () => {
  if (isValidDateRange.value) {
    fetchSalesByCategory();
  }
});

onMounted(() => {
  // Initialize payment chart with server-side data first
  initializePaymentChart();
  
  // Then fetch store data and update charts
  fetchStores();
  fetchProducts();
  fetchCategories();
  fetchMetrics();
  fetchTopBottomProducts();
  fetchMonthlySales();
  fetchTopWastes();
  fetchTransactionSales();
  fetchSalesByHour();
  fetchTopStores();
  fetchAdvancedAnalysis();
  fetchReceivedDeliveryVsSales();
  fetchSalesByCategory();
  fetchTopVarianceStores();
  
  // Add click outside listeners for dropdowns
  document.addEventListener('click', handleCategoryDropdownClickOutside);
  document.addEventListener('click', handleTransactionProductDropdownClickOutside);
});

onUnmounted(() => {
  // Remove click outside listeners
  document.removeEventListener('click', handleCategoryDropdownClickOutside);
  document.removeEventListener('click', handleTransactionProductDropdownClickOutside);
});
</script>

<template>
    <AdminPanel active-tab="DASHBOARD">
        <template #main>
            <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6 space-y-6 relative overflow-hidden">
                <!-- Subtle Background Particles/Overlay -->
                <div class="absolute inset-0 opacity-10 bg-pattern z-0"></div>

                <!-- Enhanced Filtering Section with Elegant Design -->
                <div 
                    class="relative z-10 bg-white/80 rounded-3xl shadow-2xl border border-blue-100/50 p-6 transform transition-all duration-500 hover:scale-[1.02]"
                >
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <!-- Date Range and Welcome Section -->
                        <div class="flex flex-col w-full md:w-auto">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between w-full gap-4">
                                <!-- Welcome Message - Simpler, cleaner style -->
                                <div>
                                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-1">
                                        Welcome back, <span class="text-blue-600">{{ username }}!</span>
                                    </h1>
                                    <p class="text-sm text-gray-500">Track your sales, analyze performance, and manage your business</p>
                                </div>

                                <!-- Date Range Controls - Compact design -->
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                    <div class="flex flex-col">
                                        <label class="text-xs font-medium text-gray-500 mb-1">Start Date</label>
                                        <input
                                            type="date"
                                            v-model="selectedDateRange.start_date"
                                            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                            :class="{'border-red-500': dateRangeError}"
                                        >
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="text-xs font-medium text-gray-500 mb-1">End Date</label>
                                        <input
                                            type="date"
                                            v-model="selectedDateRange.end_date"
                                            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                            :class="{'border-red-500': dateRangeError}"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div
                                v-if="dateRangeError"
                                class="text-red-500 text-xs mt-2 flex items-center gap-1"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ dateRangeError }}</span>
                            </div>
                        </div>

                        <!-- Store Multi-Select - Cleaner design -->
                        <div class="w-full lg:w-auto">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Filter by Stores</label>
                            <MultiSelectDropdown
                                :modelValue="selectedStores"
                                @update:modelValue="selectedStores = $event"
                                :options="stores"
                                :multiple="true"
                                class="w-full lg:w-64"
                            />
                        </div>
                    </div>
                </div>

                <!-- Metrics Grid - Clean, minimal design like dashboard.png -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                    <div v-for="(card, index) in metricsCards" :key="card.title" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition-shadow duration-200 relative">
                        <!-- Loading Overlay -->
                        <div v-if="isLoadingMetrics" class="absolute inset-0 bg-white/90 backdrop-blur-sm z-20 flex items-center justify-center rounded-xl">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-8 h-8 border-3 border-gray-200 border-t-blue-500 rounded-full animate-spin"></div>
                                <span class="text-xs text-gray-500">Loading...</span>
                            </div>
                        </div>

                        <div class="flex items-start justify-between mb-3">
                            <div :class="[card.bgColor, 'p-2.5 rounded-lg']">
                                <component :is="card.icon" :class="[card.color, 'w-5 h-5 md:w-6 md:h-6']" />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">
                                {{ card.title }}
                            </p>
                            <p class="text-xl md:text-2xl font-bold text-gray-800 mb-1">
                                {{ card.value }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ card.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Charts and Announcements Grid - Clean design -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
                    <!-- Payment Methods Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition-shadow duration-200 relative">
                        <!-- Loading Overlay for Chart -->
                        <div v-if="isLoadingMetrics" class="absolute inset-0 bg-white/90 backdrop-blur-sm z-30 flex items-center justify-center rounded-xl">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-10 h-10 border-3 border-gray-200 border-t-blue-500 rounded-full animate-spin"></div>
                                <span class="text-sm text-gray-600">Loading chart...</span>
                            </div>
                        </div>

                        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm4 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                            </svg>
                            Payment Methods
                        </h3>
                        <div class="h-[300px] md:h-[400px]">
                            <canvas ref="chartRef" class="w-full h-full"></canvas>
                        </div>
                    </div>

                    <!-- Announcements Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition-shadow duration-200">
                        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            Announcements
                        </h3>
                        <div class="max-h-[300px] md:max-h-[400px] overflow-y-auto space-y-3">
                            <template v-if="announcements.length">
                                <div
                                    v-for="(announcement, index) in announcements"
                                    :key="index"
                                    class="p-3 md:p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-200"
                                >
                                    <h4 class="font-semibold text-gray-800 text-sm mb-1">
                                        {{ announcement.title }}
                                    </h4>
                                    <p class="text-gray-600 text-xs md:text-sm">
                                        {{ announcement.description }}
                                    </p>
                                </div>
                            </template>
                            <p v-else class="text-gray-400 text-center py-8 text-sm">
                                No announcements available
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Top/Bottom Products Section - Clean design -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                        <h3 class="text-base md:text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                            Popular Products & Products Under Monitoring
                        </h3>
                        
                        <!-- Product Type Filter -->
                        <div class="flex items-center space-x-3">
                            <label for="productTypeFilter" class="text-sm font-medium text-gray-700">Filter by:</label>
                            <select 
                                id="productTypeFilter"
                                v-model="selectedProductType" 
                                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
                            >
                                <option value="all">All Products</option>
                                <option value="regular">Regular Products Only</option>
                                <option value="non_product">Non Products Only</option>
                            </select>
                        </div>
                    </div>
                    <div class="h-[500px] relative z-10">
                        <canvas ref="topBottomProductsRef" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Monthly Sales and Wastes Sections would follow similar design -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <div class="absolute inset-0 opacity-10 bg-teal-100"></div>
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                            Monthly Sales Trend
                        </h3>
                        <div class="h-[400px] relative z-10">
                            <canvas ref="monthlySalesRef" class="w-full h-full"></canvas>
                        </div>
                    </div>
                    
                    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <div class="absolute inset-0 opacity-10 bg-red-100"></div>
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Top Waste Items (Stock Counting Analysis)
                        </h3>
                        <div class="h-[400px] relative z-10">
                            <canvas ref="topWasteRef" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- New Charts Section -->
                <!-- Transaction Sales Chart -->
                <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.01] transition-all duration-300">
                    <div class="absolute inset-0 opacity-10 bg-blue-100"></div>
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 relative z-10">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center mb-3 sm:mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            Transaction Sales Trend
                        </h3>
                        <div class="flex items-center space-x-3">
                            <label for="transactionSalesFilter" class="text-sm font-medium text-gray-700">Filter by:</label>
                            <select 
                                id="transactionSalesFilter"
                                v-model="transactionSalesFilter" 
                                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                            >
                                <option value="gross">Gross Amount</option>
                                <option value="qty">Quantity</option>
                            </select>
                            
                            <!-- Product Multi-Select with Search -->
                            <div class="w-80">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Products (Optional)</label>
                                <div class="relative" ref="transactionProductDropdownRef">
                                    <button
                                        @click="transactionProductDropdownOpen = !transactionProductDropdownOpen"
                                        type="button"
                                        class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                    >
                                        <span class="block truncate text-gray-700">
                                            <span v-if="selectedProducts.length === 0" class="text-gray-500">Search and select products...</span>
                                            <span v-else-if="selectedProducts.length === 1">{{ getProductLabel(selectedProducts[0]) }}</span>
                                            <span v-else>{{ selectedProducts.length }} products selected</span>
                                        </span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': transactionProductDropdownOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>

                                    <div v-if="transactionProductDropdownOpen" class="absolute z-[9999] mt-1 w-full bg-white shadow-2xl border border-gray-300 rounded-lg overflow-hidden" style="background-color: white !important;">
                                        <!-- Search Input - Fixed at top -->
                                        <div class="p-3 border-b border-gray-200 bg-white" style="background-color: white !important;">
                                            <input
                                                v-model="productSearchTerm"
                                                @input="debouncedProductSearch"
                                                @click.stop
                                                type="text"
                                                placeholder="Search products by name or ID..."
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                            />
                                        </div>
                                        
                                        <!-- Scrollable content area -->
                                        <div class="max-h-80 overflow-y-auto bg-white" style="background-color: white !important;">
                                            <!-- Select All Option -->
                                            <div class="border-b border-gray-100 bg-white" style="background-color: white !important;">
                                                <div
                                                    class="px-4 py-3 cursor-pointer hover:bg-blue-100 flex items-center select-none"
                                                    @click.stop.prevent="toggleAllProducts"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded pointer-events-none"
                                                        :checked="selectedProducts.length === filteredProducts.length && filteredProducts.length > 0"
                                                        :indeterminate="selectedProducts.length > 0 && selectedProducts.length < filteredProducts.length"
                                                        tabindex="-1"
                                                        readonly
                                                    >
                                                    <span class="ml-3 font-medium text-gray-900">
                                                        Select All ({{ filteredProducts.length }} items)
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Product Options -->
                                            <div 
                                                v-for="product in filteredProducts" 
                                                :key="product.itemid" 
                                                class="px-4 py-2 cursor-pointer hover:bg-blue-50 flex items-center border-b border-gray-50 select-none bg-white"
                                                style="background-color: white !important;"
                                                @click.stop.prevent="toggleProduct(product.itemid)"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded pointer-events-none"
                                                    :checked="selectedProducts.includes(product.itemid)"
                                                    tabindex="-1"
                                                    readonly
                                                >
                                                <div class="ml-3 flex-1">
                                                    <div class="text-sm font-medium text-gray-900">{{ product.itemname }}</div>
                                                    <div class="text-xs text-gray-500">ID: {{ product.itemid }}</div>
                                                </div>
                                            </div>
                                            
                                            <!-- No results message -->
                                            <div v-if="filteredProducts.length === 0" class="px-4 py-8 text-center text-gray-500">
                                                <div class="text-sm">No products found</div>
                                                <div class="text-xs mt-1">Try different search terms</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1" v-if="products.length === 0">Loading products...</div>
                                <div class="text-xs text-gray-500 mt-1" v-else>
                                    {{ filteredProducts.length }} products available
                                    <span v-if="selectedProducts.length > 0" class="text-blue-600">
                                        ({{ selectedProducts.length }} selected)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="h-[400px] relative z-0">
                        <canvas ref="transactionSalesRef" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Sales by Hour and Top Stores Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Sales by Hour Chart -->
                    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <div class="absolute inset-0 opacity-10 bg-green-100"></div>
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Sales by Hour
                        </h3>
                        <div class="h-[400px] relative z-10">
                            <canvas ref="salesByHourRef" class="w-full h-full"></canvas>
                        </div>
                    </div>

                    <!-- Top Stores Chart -->
                    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <div class="absolute inset-0 opacity-10 bg-purple-100"></div>
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 relative z-10">
                            <h3 class="text-xl font-bold text-gray-800 flex items-center mb-3 sm:mb-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Top Stores Performance
                            </h3>
                            <div class="flex items-center space-x-3">
                                <label for="topStoresFilter" class="text-sm font-medium text-gray-700">Filter by:</label>
                                <select 
                                    id="topStoresFilter"
                                    v-model="topStoresFilter" 
                                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
                                >
                                    <option value="grossamount">Gross Amount</option>
                                    <option value="discamount">Discount Amount</option>
                                    <option value="netamount">Net Amount</option>
                                    <option value="qty">Quantity</option>
                                </select>
                            </div>
                        </div>
                        <div class="h-[400px] relative z-10">
                            <canvas ref="topStoresRef" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Advanced Analysis Chart -->
                <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.01] transition-all duration-300">
                    <div class="absolute inset-0 opacity-10 bg-indigo-100"></div>
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Advanced Data Analysis
                    </h3>
                    <div class="h-[500px] relative z-10">
                        <canvas ref="advancedAnalysisRef" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- New Reports Section -->
                <!-- Received Delivery vs Sales and Sales by Category Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Received Delivery vs Sales Chart -->
                    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <div class="absolute inset-0 opacity-10 bg-cyan-100"></div>
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            Received Delivery vs Sales
                        </h3>
                        <div class="h-[400px] relative z-10">
                            <canvas ref="receivedDeliveryVsSalesRef" class="w-full h-full"></canvas>
                        </div>
                    </div>

                    <!-- Sales by Category Chart -->
                    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <div class="absolute inset-0 opacity-10 bg-orange-100"></div>
                        <div class="flex justify-between items-start mb-6 relative z-10">
                            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                                Sales by Category
                            </h3>
                            
                            <!-- Category Multi-Select Filter -->
                            <div class="w-80">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Filter Categories (Optional)</label>
                                <div class="relative" ref="categoryDropdownRef">
                                    <button
                                        @click="categoryDropdownOpen = !categoryDropdownOpen"
                                        type="button"
                                        class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm transition-colors duration-200"
                                    >
                                        <span class="block truncate text-gray-700">
                                            <span v-if="selectedCategories.length === 0" class="text-gray-500">Select categories...</span>
                                            <span v-else-if="selectedCategories.length === 1">{{ getCategoryLabel(selectedCategories[0]) }}</span>
                                            <span v-else>{{ selectedCategories.length }} categories selected</span>
                                        </span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': categoryDropdownOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>

                                    <div v-if="categoryDropdownOpen" class="absolute z-20 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                        <!-- Select All Option -->
                                        <div class="border-b border-gray-200">
                                            <div
                                                class="px-3 py-2 cursor-pointer hover:bg-orange-50 flex items-center transition-colors duration-150"
                                                @click="toggleAllCategories"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                                    :checked="selectedCategories.length === categories.length"
                                                    :indeterminate="selectedCategories.length > 0 && selectedCategories.length < categories.length"
                                                    readonly
                                                >
                                                <span class="ml-2 font-medium text-gray-900">Select All</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Category Options -->
                                        <div 
                                            v-for="category in categories" 
                                            :key="category.value" 
                                            class="px-3 py-2 cursor-pointer hover:bg-orange-50 flex items-center transition-colors duration-150"
                                            @click="toggleCategory(category.value)"
                                        >
                                            <input
                                                type="checkbox"
                                                class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                                :checked="selectedCategories.includes(category.value)"
                                                readonly
                                            >
                                            <span class="ml-2 text-gray-900">{{ category.label }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1" v-if="categories.length === 0">Loading categories...</div>
                                <div class="text-xs text-gray-500 mt-1" v-else>
                                    {{ categories.length }} categories available
                                    <span v-if="selectedCategories.length > 0" class="text-orange-600">
                                        ({{ selectedCategories.length }} selected)
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="h-[400px] relative z-10">
                            <canvas ref="salesByCategoryRef" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Variance Stores Chart -->
                <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.01] transition-all duration-300">
                    <div class="absolute inset-0 opacity-10 bg-red-100"></div>
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Top 10 Stores Under Monitoring (Highest Variances)
                    </h3>
                    <div class="h-[400px] relative z-10">
                        <canvas ref="topVarianceStoresRef" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        </template>
    </AdminPanel>
    
    <!-- Floating ChatBot -->
    <FloatingChatBot />
</template>

<style scoped>
.bg-pattern {
    background-image: linear-gradient(45deg, rgba(59, 130, 246, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%);
    background-size: 20px 20px;
}

.animate-pulse-slow {
    animation: pulse 3s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.metric-card {
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

/* Bubble effect on hover */
.metric-card::after {
    content: '';
    position: absolute;
    top: 10%;
    left: 10%;
    width: 40px;
    height: 40px;
    background-color: rgba(59, 130, 246, 0.2);
    border-radius: 50%;
    opacity: 0;
    transform: scale(0.5);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.metric-card:hover::after {
    opacity: 1;
    transform: scale(1);
}
</style>