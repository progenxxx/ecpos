<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Reset from "@/Components/Resetter/reset.vue";
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import { Link } from '@inertiajs/vue3';

const showResetModal = ref(false);

const ResetModalHandler = () => {
  showResetModal.value = !showResetModal.value;
};

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    userRole: {
        type: String,
        required: true,
    },
});

const layoutComponent = computed(() => {

    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});

const cards = ref([
  {
    id: 1,
    title: 'VARIANCE',
    description: 'Track daily sales variations and trends',
    icon: '',
    animationClass: 'variance-animation',
    route: 'reports.inventory',

  },
  {
    id: 2,
    title: 'ACCOUNT RECEIVABLE',
    description: 'Monitor pending payments and collections',
    icon: '',
    animationClass: 'receivable-animation',
    route: 'reports.ar',

  },
  {
    id: 3,
    title: 'EMPLOYEE CHARGES',
    description: 'Track employee sales performance',
    icon: '',
    animationClass: 'employee-animation',
    route: 'reports.ec',

  },
  {
    id: 4,
    title: 'BAD ORDERS',
    description: 'Monitor and analyze rejected orders',
    icon: '',
    animationClass: 'bad-orders-animation',
    route: 'reports.bo',
  },
  {
    id: 5,
    title: 'REGULAR DISCOUNT',
    description: 'Track standard discount applications',
    icon: '',
    animationClass: 'discount-animation',
    route: 'reports.rd',
  },
  {
    id: 6,
    title: 'MARKETING DISCOUNT',
    description: 'Monitor promotional campaign results',
    icon: '',
    animationClass: 'marketing-animation',
    route: 'reports.md',
  },
  {
    id: 7,
    title: 'SALES',
    description: 'View overall sales performance',
    icon: '',
    animationClass: 'sales-animation',
    route: 'reports.sales',

  },
  {
    id: 8,
    title: 'BIR',
    description: 'Analyze hourly sales trends for better business insights.',
    icon: '⏰',
    animationClass: 'hourly-animation',
    route: 'reports.test',

  },
  {
    id: 9,
    title: 'Transaction Sales',
    description: 'Analyze transaction sales to identify trends and growth.',
    icon: '',
    animationClass: 'comparison-animation',
    route: 'reports.tsales',

  },
  {
    id: 10,
    title: 'ORDERS',
    description: 'Track order volume and status',
    icon: '',
    animationClass: 'orders-animation',
    route: 'orderingconso.index',

  },
  {
    id: 11,
    title: 'DELIVERY',
    description: 'Monitor delivery performance',
    icon: '',
    animationClass: 'delivery-animation',
    route: 'receivedorderconso.ro',

  },
  {
    id: 12,
    title: 'ITEM SALES',
    description: 'Analyze Item Sales',
    icon: '',
    animationClass: 'stocks-animation',
    route: 'reports.itemsales',

  }
]);
</script>

<template>
  <component :is="layoutComponent" active-tab="REPORTS">
    <template v-slot:modals>
      <Reset
        :show-modal="showResetModal"
        @toggle-active="ResetModalHandler"
      />
    </template>

    <template v-slot:main>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
        <!-- <Link
          v-for="card in cards"
          :key="card.id"
          :href="route(card.route)"
          class="block"
        > -->
        <Link
          v-for="card in cards"
          :key="card.id"
          :href="route(card.route, card.params)"
          class="block"
        >
          <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 cursor-pointer">
            <div class="relative h-48 overflow-hidden bg-gradient-to-br from-blue-50 to-purple-50">
              <div :class="['character', card.animationClass]">
                <div class="icon-container">
                  <span class="text-6xl">{{ card.icon }}</span>
                  <div class="animation-elements"></div>
                </div>
              </div>
            </div>

            <div class="p-6">
              <h3 class="text-xl font-bold text-gray-900 mb-2">{{ card.title }}</h3>
              <p class="text-gray-600 text-sm mb-4">{{ card.description }}</p>

              <div class="flex justify-between items-center">
                <span class="text-sm font-semibold text-purple-600">{{ card.stats }}</span>
                <span class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600
                           transition-colors duration-200 text-sm">
                  View Report
                </span>
              </div>
            </div>
          </div>
        </Link>
      </div>
    </template>
  </component>
</template>

<style scoped>
.character {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}

.icon-container {
  position: relative;
  width: 100px;
  height: 100px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.variance-animation .animation-elements::before,
.variance-animation .animation-elements::after {
  content: '₱';
  position: absolute;
  font-size: 24px;
  color: #4CAF50;
  animation: floatUpDown 2s infinite alternate;
}

.variance-animation .animation-elements::before {
  left: -30px;
  animation-delay: 0.5s;
}

.variance-animation .animation-elements::after {
  right: -30px;
  animation-delay: 1s;
}

.receivable-animation .animation-elements {
  position: absolute;
  width: 100%;
  height: 100%;
  animation: rotate 4s infinite linear;
}

.receivable-animation .animation-elements::before {
  content: '';
  position: absolute;
  font-size: 24px;
  animation: bounce 1s infinite;
}

.employee-animation .animation-elements {
  position: absolute;
  width: 100%;
  height: 100%;
}

.employee-animation .animation-elements::before,
.employee-animation .animation-elements::after {
  content: '';
  position: absolute;
  font-size: 20px;
  animation: sparkle 1.5s infinite;
}

.bad-orders-animation .animation-elements::before {
  content: '';
  position: absolute;
  font-size: 24px;
  animation: shake 0.5s infinite;
}

.discount-animation .animation-elements::before {
  content: '%';
  position: absolute;
  font-size: 32px;
  color: #E91E63;
  animation: pulse 1s infinite;
}

.marketing-animation .animation-elements::before {
  content: '';
  position: absolute;
  font-size: 24px;
  animation: targetBounce 1.5s infinite;
}

.sales-animation .animation-elements::before {
  content: '';
  position: absolute;
  font-size: 24px;
  animation: growUp 2s infinite;
}

.orders-animation .animation-elements::before {
  content: '';
  position: absolute;
  font-size: 24px;
  animation: slide 2s infinite;
}

.delivery-animation .animation-elements::before {
  content: '';
  position: absolute;
  font-size: 24px;
  animation: drive 3s infinite linear;
}

.stocks-animation .animation-elements::before {
  content: '';
  position: absolute;
  font-size: 24px;
  animation: stack 2s infinite;
}

.hourly-animation .animation-elements::before {
  content: '⏰';
  position: absolute;
  font-size: 24px;
  animation: tick 1s infinite;
}

.comparison-animation .animation-elements::before {
  content: '';
  position: absolute;
  font-size: 24px;
  animation: compare 2s infinite;
}

@keyframes floatUpDown {
  0% { transform: translateY(0); }
  100% { transform: translateY(-20px); }
}

@keyframes rotate {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}

@keyframes sparkle {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.2); }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}

@keyframes targetBounce {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-10px) rotate(180deg); }
}

@keyframes growUp {
  0% { transform: scale(0.8) translateY(10px); }
  100% { transform: scale(1.1) translateY(-10px); }
}

@keyframes slide {
  0% { transform: translateX(-30px); }
  100% { transform: translateX(30px); }
}

@keyframes drive {
  0% { transform: translateX(-50px); }
  100% { transform: translateX(50px); }
}

@keyframes stack {
  0% { transform: translateY(0) scale(1); }
  50% { transform: translateY(-10px) scale(1.1); }
  100% { transform: translateY(0) scale(1); }
}

@keyframes tick {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes compare {
  0% { transform: scaleY(0.8); }
  50% { transform: scaleY(1.2); }
  100% { transform: scaleY(0.8); }
}
</style>