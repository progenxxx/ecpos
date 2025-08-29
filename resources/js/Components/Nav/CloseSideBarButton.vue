<script setup>
import { ref } from "vue";

const emit = defineEmits();

const toggleSidebar = () => {
    emit('toggleSidebar');
};

const props = defineProps({
    isSidebarOpen: {
        type: Boolean,
        default: true
    }
});

const src = ref('arrow_to_left.svg');
</script>

<template>
    <button
        :class="[
            'h-8 w-8 absolute top-0 p-2  bg-navy text-gray-900 z-50 transition-all duration-500 ease-in-out rounded-full',
            (isSidebarOpen ? 'left-[6rem] ' : '-left-4 -rotate-180')
        ]"
        @click="toggleSidebar"
    >
    <svg class="" viewBox="0 0 24 24" fill="white" xmlns="http:

    </button>
</template>
