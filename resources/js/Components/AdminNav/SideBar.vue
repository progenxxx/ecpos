<script setup>
import Dashboard from "@/Components/Svgs/Dashboard.vue";
import RetailItems from "@/Components/Svgs/RetailItems.vue";
import Categories from "@/Components/Svgs/Categories.vue";
import Announcement from "@/Components/Svgs/Announcement.vue";
import PartyCake from "@/Components/Svgs/PartyCake143.vue";
import Manage from "@/Components/Svgs/Manage.vue";
import Order from "@/Components/Svgs/Order.vue";
import Register from "@/Components/Svgs/Register.vue";
import Store from "@/Components/Svgs/Store.vue";
import Opic from "@/Components/Svgs/Opic.vue";
import Reports from "@/Components/Svgs/Reports.vue";
import Stock from "@/Components/Svgs/Stock.vue";
import Logout from "@/Components/Svgs/Logout.vue";
import Receipt from "@/Components/Svgs/Picklist.vue";
import List from "@/Components/Nav/List.vue";
import Customers from "@/Components/Svgs/Customers-black.vue";
import Attendance from "@/Components/Svgs/Attendance.vue";
import BatchCount from "@/Components/Svgs/batchcount.vue";
import Tag from "@/Components/Svgs/Discount-tag.vue";
import Version from "@/Components/Svgs/Version.vue";

import { ref, computed, defineProps, toRefs, onMounted } from 'vue';

const props = defineProps({
    isSidebarOpen: {
        type: Boolean,
        default: true
    },
    activeTab: {
        type: String,
        default: "HOME"
    },
    auth: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits();

const logout = () => {
    emit('logout');
};

// Snowflake animation
const snowflakes = ref([]);

onMounted(() => {
    // Generate random snowflakes
    for (let i = 0; i < 30; i++) {
        snowflakes.value.push({
            id: i,
            left: Math.random() * 100,
            animationDelay: Math.random() * 5,
            animationDuration: 5 + Math.random() * 10,
            size: 5 + Math.random() * 10,
            opacity: 0.3 + Math.random() * 0.5
        });
    }
});
</script>

<template>
    <!-- Overlay for mobile to close sidebar when clicking outside -->
    <div 
        v-if="isSidebarOpen" 
        class="fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden"
        @click="$emit('toggle-sidebar')"
    ></div>

    <div :class="[
        'rounded-tr-3xl rounded-br-3xl p-4 bg-navy top-0 bottom-0 left-0 w-28 text-black font-bold absolute z-20 pt-12 pb-2 flex flex-col justify-between transition-all duration-500 ease-in-out px-2 overflow-hidden',
        (isSidebarOpen ? 'translate-x-0' : 'translate-x-[-100%]')
    ]" v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'DISPATCH' || $page.props.auth.user.role === 'ADMIN' || $page.props.auth.user.role === 'STORE' || $page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" >

        <!-- Christmas Background Pattern with opacity -->
        <div class="absolute inset-0 opacity-15 pointer-events-none z-0">
            <!-- Christmas tree pattern -->
            <div class="absolute top-10 left-1/2 -translate-x-1/2 w-16 h-20">
                <svg viewBox="0 0 100 120" class="w-full h-full text-green-400 opacity-50">
                    <polygon points="50,10 20,50 30,50 10,80 40,80 25,110 75,110 60,80 90,80 70,50 80,50" fill="currentColor"/>
                    <rect x="45" y="110" width="10" height="10" fill="#8B4513"/>
                </svg>
            </div>

            <!-- Decorative snowflakes pattern -->
            <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="christmas-pattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                        <circle cx="20" cy="20" r="2" fill="white" opacity="0.3"/>
                        <circle cx="60" cy="40" r="1.5" fill="white" opacity="0.4"/>
                        <circle cx="40" cy="70" r="2.5" fill="white" opacity="0.3"/>
                        <circle cx="80" cy="80" r="1" fill="white" opacity="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#christmas-pattern)"/>
            </svg>
        </div>

        <!-- Animated Snowflakes -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
            <div
                v-for="snowflake in snowflakes"
                :key="snowflake.id"
                class="absolute text-white snowflake"
                :style="{
                    left: snowflake.left + '%',
                    animationDelay: snowflake.animationDelay + 's',
                    animationDuration: snowflake.animationDuration + 's',
                    fontSize: snowflake.size + 'px',
                    opacity: snowflake.opacity
                }"
            >
                ❄
            </div>
        </div>

        <ul class="flex justify-center flex-col items-center gap-3 text-xs relative z-10">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full">
                ADMIN
            </li>

            <li v-if="$page.props.auth.user.role != 'DISPATCH'" class="tooltip tooltip-right tooltip-primary z-40 " data-tip="Master">
                <List :active-tab="activeTab" tabName="DASHBOARD" url="/admin">
                    <Dashboard class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role != 'DISPATCH'" class="tooltip tooltip-right tooltip-primary z-40" data-tip="Retails">
                <List :active-tab="activeTab" tabName="RETAILITEMS" url="/items">
                    <RetailItems class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'STORE' && $page.props.auth.user.role != 'DISPATCH'" class="tooltip tooltip-right tooltip-primary" data-tip="Create Order">
                <List :active-tab="activeTab" tabName="ORDER" url="/order">
                    <Order class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'DISPATCH' || $page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" class="tooltip tooltip-right tooltip-primary" data-tip="FINISHED GOODS">
                <List :active-tab="activeTab" tabName="FG" url="/dispatch-inventory">
                    <Manage class="h-6 lg:h-8"/> 
                </List>
            </li>

            <!-- <div class="tooltip tooltip-right tooltip-primary" data-tip="Attendance">
                <List :active-tab="activeTab" tabName="ATTENDANCE" url="/attendance"><Attendance class="h-6 lg:h-8"/> </List>
            </div> -->

            <!-- <div class="tooltip tooltip-right tooltip-primary" data-tip="Customer">
                <List :active-tab="activeTab" tabName="CUSTOMERS" url="/customers"><Customers class="h-6 lg:h-8"/> </List>
            </div> -->

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Discount">
                <List :active-tab="activeTab" tabName="DISCOUNT" url="/discountsv2"><Tag class="h-6 lg:h-8"/> </List>
            </div>

            <!-- <div class="tooltip tooltip-right tooltip-primary" data-tip="BatchCount">
                <List :active-tab="activeTab" tabName="Batch Count" url="/StockCounting"><BatchCount class="h-6 lg:h-8"/> </List>
            </div> -->

            <li v-if="$page.props.auth.user.role != 'DISPATCH'" class="tooltip tooltip-right tooltip-primary" data-tip="REPORTS">
                <List :active-tab="activeTab" tabName="REPORTS" url="/reports">
                    <Reports class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" class="tooltip tooltip-right tooltip-primary" data-tip="Process">
                <List :active-tab="activeTab" tabName="FGCOUNT" url="/mgcount">
                    <Opic class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" class="tooltip tooltip-right tooltip-primary" data-tip="FINAL DR">
                    <List :active-tab="activeTab" tabName="FINALDR" url="/fdr-daterange?EndDate=1997-08-23&STORE=MRPA"> 
                    <Receipt class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN' || $page.props.auth.user.role === 'OPIC'" class="tooltip tooltip-right tooltip-primary" data-tip="Store">
                <List :active-tab="activeTab" tabName="STORE" url="/store">
                    <Store class="h-6 lg:h-8" />
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" class="tooltip tooltip-right tooltip-primary" data-tip="App Version">
                <List :active-tab="activeTab" tabName="VERSION" url="/app-versions">
                    <Version class="h-6 lg:h-8"/>
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" class="tooltip tooltip-right tooltip-primary" data-tip="Register">
                <List :active-tab="activeTab" tabName="REGISTER" url="/signup">
                    <Register class="h-6 lg:h-8"/>
                </List>
            </li>

            <li class="tooltip tooltip-right tooltip-primary" data-tip="Logout">
                <List :active-tab="activeTab" tabName="SETTINGS">
                    <button @click="logout"><Logout class="h-6 lg:h-8"/></button>
                </List>
            </li>
        </ul>
    </div>
</template>

<style scoped>
@keyframes snowfall {
    0% {
        transform: translateY(-10px) translateX(0);
    }
    50% {
        transform: translateY(50vh) translateX(20px);
    }
    100% {
        transform: translateY(100vh) translateX(0);
    }
}

.snowflake {
    animation: snowfall linear infinite;
    will-change: transform;
}

/* Crystal/sparkle effect */
@keyframes sparkle {
    0%, 100% {
        opacity: 0.3;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.2);
        filter: brightness(1.5);
    }
}

.snowflake:nth-child(3n) {
    animation: snowfall linear infinite, sparkle 2s ease-in-out infinite;
}
</style>