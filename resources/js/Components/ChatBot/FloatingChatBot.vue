<template>
    <div class="fixed bottom-6 right-6 z-50">
        <!-- Chat Toggle Button -->
        <div v-if="!isOpen" class="relative">
            <button
                @click="openChat"
                class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 group"
                :class="{ 'animate-pulse': hasUnreadMessage }"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-2.15-.26l-4.244 1.082a1 1 0 01-1.288-1.288l1.082-4.244A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                </svg>
                
                <!-- Notification Badge -->
                <div v-if="hasUnreadMessage" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold">
                    !
                </div>
            </button>

            <!-- Tooltip -->
            <div class="absolute bottom-full right-0 mb-2 px-3 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                AI Sales Assistant
            </div>
        </div>

        <!-- Chat Window -->
        <div
            v-if="isOpen"
            class="bg-white rounded-lg shadow-2xl w-96 h-[500px] flex flex-col border border-gray-200 overflow-hidden"
        >
            <!-- Chat Header -->
            <div class="bg-blue-600 text-white px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm">AI Sales Assistant</h3>
                        <p class="text-xs text-blue-100">Online</p>
                    </div>
                </div>
                <button
                    @click="closeChat"
                    class="text-blue-100 hover:text-white transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Chat Messages -->
            <div 
                ref="messagesContainer"
                class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
            >
                <!-- Welcome Message -->
                <div v-if="messages.length === 0" class="text-center text-gray-500 text-sm">
                    <div class="mb-4">
                        <div class="w-16 h-16 bg-blue-100 rounded-full mx-auto flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <p class="font-medium text-gray-700">Welcome to your AI Sales Assistant!</p>
                        <p class="text-xs text-gray-500 mt-1">Ask me about your sales performance, strategies, and insights.</p>
                    </div>
                    
                    <!-- Sample Questions -->
                    <div class="text-left">
                        <p class="text-xs font-medium text-gray-600 mb-2">Try asking:</p>
                        <div class="space-y-1">
                            <button 
                                v-for="question in sampleQuestions.slice(0, 3)" 
                                :key="question"
                                @click="sendQuickMessage(question)"
                                class="block w-full text-left px-3 py-2 bg-white rounded border text-xs hover:bg-blue-50 hover:border-blue-300 transition-colors duration-200"
                            >
                                "{{ question }}"
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div
                    v-for="(message, index) in messages"
                    :key="index"
                    :class="message.type === 'user' ? 'flex justify-end' : 'flex justify-start'"
                >
                    <div
                        :class="message.type === 'user' ? 
                            'bg-blue-600 text-white rounded-l-lg rounded-tr-lg' : 
                            'bg-white text-gray-800 rounded-r-lg rounded-tl-lg border'"
                        class="max-w-[80%] px-3 py-2 shadow-sm"
                    >
                        <p class="text-sm whitespace-pre-wrap">{{ message.content }}</p>
                        <span class="text-xs opacity-70 mt-1 block">{{ formatTime(message.timestamp) }}</span>
                    </div>
                </div>

                <!-- Typing Indicator -->
                <div v-if="isTyping" class="flex justify-start">
                    <div class="bg-white text-gray-800 rounded-r-lg rounded-tl-lg border px-3 py-2 shadow-sm">
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="p-4 bg-white border-t">
                <form @submit.prevent="sendMessage" class="flex space-x-2">
                    <input
                        v-model="newMessage"
                        :disabled="isTyping"
                        type="text"
                        placeholder="Ask about sales, strategies, insights..."
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        maxlength="1000"
                    />
                    <button
                        type="submit"
                        :disabled="!newMessage.trim() || isTyping"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-3 py-2 rounded-lg transition-colors duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
                
                <!-- Quick Actions -->
                <div v-if="messages.length > 0" class="mt-2 flex space-x-1 overflow-x-auto">
                    <button
                        v-for="action in quickActions"
                        :key="action"
                        @click="sendQuickMessage(action)"
                        class="flex-shrink-0 text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors duration-200"
                    >
                        {{ action }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';

// Reactive data
const isOpen = ref(false);
const messages = ref([]);
const newMessage = ref('');
const isTyping = ref(false);
const hasUnreadMessage = ref(false);
const sampleQuestions = ref([]);
const messagesContainer = ref(null);

const quickActions = [
    "Today's sales summary",
    "Top performing items",
    "Sales improvement tips",
    "Store performance"
];

// Methods
const openChat = async () => {
    isOpen.value = true;
    hasUnreadMessage.value = false;
    
    // Load welcome message and sample questions if first time
    if (messages.value.length === 0) {
        await loadWelcomeMessage();
        await loadSampleQuestions();
    }
    
    await nextTick();
    scrollToBottom();
};

const closeChat = () => {
    isOpen.value = false;
};

const loadWelcomeMessage = async () => {
    try {
        const response = await axios.get(route('chatbot.welcome'));
        if (response.data.success) {
            messages.value.push({
                type: 'bot',
                content: response.data.message,
                timestamp: new Date()
            });
        }
    } catch (error) {
        console.error('Failed to load welcome message:', error);
    }
};

const loadSampleQuestions = async () => {
    try {
        const response = await axios.get(route('chatbot.sample.questions'));
        if (response.data.success) {
            sampleQuestions.value = response.data.questions;
        }
    } catch (error) {
        console.error('Failed to load sample questions:', error);
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim()) return;
    
    const userMessage = newMessage.value.trim();
    newMessage.value = '';
    
    // Add user message
    messages.value.push({
        type: 'user',
        content: userMessage,
        timestamp: new Date()
    });
    
    await nextTick();
    scrollToBottom();
    
    // Show typing indicator
    isTyping.value = true;
    
    try {
        const response = await axios.post(route('chatbot.send.message'), {
            message: userMessage
        });
        
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate thinking time
        
        if (response.data.success && response.data.response) {
            messages.value.push({
                type: 'bot',
                content: response.data.response,
                timestamp: new Date()
            });
        } else {
            messages.value.push({
                type: 'bot',
                content: response.data.message || 'Sorry, I encountered an issue. Please try again.',
                timestamp: new Date()
            });
        }
    } catch (error) {
        console.error('Failed to send message:', error);
        messages.value.push({
            type: 'bot',
            content: 'Sorry, I\'m having trouble connecting. Please check your internet connection and try again.',
            timestamp: new Date()
        });
    } finally {
        isTyping.value = false;
        await nextTick();
        scrollToBottom();
    }
};

const sendQuickMessage = (message) => {
    newMessage.value = message;
    sendMessage();
};

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

// Watchers
watch(messages, () => {
    nextTick(() => {
        scrollToBottom();
        if (!isOpen.value && messages.value.length > 0) {
            hasUnreadMessage.value = true;
        }
    });
}, { deep: true });

// Lifecycle
onMounted(() => {
    // Show notification after 5 seconds if chat hasn't been opened
    setTimeout(() => {
        if (!isOpen.value && messages.value.length === 0) {
            hasUnreadMessage.value = true;
        }
    }, 5000);
});
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* Animation for new messages */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-enter-active {
    animation: slideIn 0.3s ease-out;
}
</style>