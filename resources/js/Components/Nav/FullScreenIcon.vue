<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isFullscreen = ref(false);

const handleFullscreenChange = () => {
    isFullscreen.value = document.fullscreenElement !== null;
};

const toggleFullscreen = () => {
    const exitMethod =
                    document.exitFullscreen
                    || document.mozCancelFullScreen
                    || document.webkitExitFullscreen
                    || document.msExitFullscreen;
    const requestMethod =
                    document.documentElement.requestFullscreen
                || document.documentElement.mozRequestFullScreen
                || document.documentElement.webkitRequestFullscreen
                || document.documentElement.msRequestFullscreen;

    if (exitMethod && document.fullscreenElement) {
        exitMethod.call(document);
        return false;
    } else if (requestMethod && !document.fullscreenElement) {
        requestMethod.call(document.documentElement);
        return true;
    } else {

        return null;
    }
};

onMounted(() => {
    handleFullscreenChange();
    document.addEventListener('fullscreenchange', handleFullscreenChange);
});

onUnmounted(() => {
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
});

</script>

<template>
    <button @click="toggleFullscreen" alt="Svg fullscreen Icon" class="w-4 h-4 ">
        <svg v-if="isFullscreen" class="fill-current" viewBox="0 0 24 24" fill="none" xmlns="http:
        </svg>

        <svg v-else class="fill-current"  viewBox="0 0 24 24" fill="none" xmlns="http:

    </button>
</template>
