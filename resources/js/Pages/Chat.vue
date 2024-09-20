<template>
    <Head title="Chat" />

    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-64px)] overflow-hidden">
            <!-- Sidebar -->
            <div class="bg-gray-800 text-white p-4 flex flex-col w-1/6 overflow-hidden">
                <button @click="createNewChat" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded mb-4 shadow-md">
                    New Chat
                </button>
                <hr class="border-gray-600">
                <div class="overflow-y-auto flex-grow mt-3">
                    <div v-for="chat in chats" :key="chat.id" 
                         class="py-2 px-3 rounded cursor-pointer hover:bg-gray-700 flex justify-between items-center my-2"
                         :class="{ 'bg-gray-700': selectedChat && selectedChat.id === chat.id }">
                        <span @click="selectChat(chat)" class="flex-grow cursor-pointer">{{ chat.title }}</span>
                        <button @click="deleteChat(chat)" class="text-red-500 hover:text-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main chat area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <div v-if="selectedChat" class="flex-1 flex flex-col overflow-hidden">
                    <div class="bg-gray-500 p-4 border-b flex justify-between items-center ">
                        <div class="flex items-center">
                            <div v-if="!isEditingTitle" @click="startEditingTitle" class="flex items-center cursor-pointer">
                                <h2 class="text-xl font-semibold mr-2">
                                    {{ selectedChat.title || `Chat ${selectedChat.id}` }}
                                </h2>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </div>
                            <input
                                v-else
                                v-model="editedTitle"
                                @blur="saveEditedTitle"
                                @keyup.enter="saveEditedTitle"
                                class="text-xl font-semibold mr-2 bg-white border border-gray-300 rounded px-2 py-1"
                                ref="titleInput"
                            />
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 text-white">
                        <div v-for="message in messages" :key="message.id" class="mb-4 p-3 rounded-lg" :class="{ 'bg-gray-800 text-white': message.is_user, 'bg-blue-200 text-gray-800': !message.is_user }">
                            <div class="font-semibold mb-1">{{ message.is_user ? 'You' : 'AI' }}</div>
                            <div class="whitespace-pre-wrap">{{ message.content }}</div>
                        </div>
                        <div v-if="isAiTyping" class="mb-4 text-gray-500 italic">
                            AI is writing...
                        </div>
                    </div>
                    <div class="p-4 border-t">
                        <form @submit.prevent="sendMessage" class="flex">
                            <input v-model="newMessage" type="text" placeholder="Type your message..." ref="messageInput"
                                   class="flex-1 rounded-l-lg p-2 border-t mr-0 border-b border-l text-gray-800 border-gray-200 bg-white"  />
                            <button type="submit" class="px-4 rounded-r-lg bg-blue-500 text-white font-bold p-2 uppercase border-blue-500 border-t border-b border-r">
                                Send
                            </button>
                        </form>
                    </div>
                </div>
                <div v-else class="flex-1 flex items-center justify-center flex-col">
                    <p class="text-xl text-gray-500">Select a chat or create a new one to get started</p>
                    <p class="text-sm text-gray-400 mt-2">Debug: Selected Chat: {{ JSON.stringify(selectedChat) }}</p>
                    <p class="text-sm text-gray-400">Debug: Number of Chats: {{ chats.length }}</p>
                    <p class="text-sm text-gray-400">Debug: Chats: {{ JSON.stringify(chats) }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const chats = ref([]);
const selectedChat = ref(null);
const messages = ref([]);
const newMessage = ref('');
const isAiTyping = ref(false);

const isEditingTitle = ref(false);
const editedTitle = ref('');
const titleInput = ref(null);

const messageInput = ref(null);

const createNewChat = async () => {
    try {
        const response = await axios.post(route('chats.store'), {
            title: `New Chat ${Date.now()}` // Add a timestamp to make each title unique
        });
        const newChat = response.data;
        chats.value.unshift(newChat);
        await selectChat(newChat);
    } catch (error) {
        console.error('Error creating new chat:', error);
        alert('Error creating new chat');
    }
};

const selectChat = async (chat) => {
    console.log('Selecting chat:', chat);
    selectedChat.value = chat;
    messages.value = []; // Clear messages immediately
    newMessage.value = ''; // Clear any existing message input
    await fetchMessages(chat.id);
    nextTick(() => {
        messageInput.value?.focus();
    });
};

const fetchChats = async () => {
    try {
        console.log('Fetching chats');
        const response = await axios.get(route('chats.retrieve'));
        console.log('Fetched chats:', response.data);
        chats.value = response.data;
    } catch (error) {
        console.error('Error fetching chats:', error);
        chats.value = [];
    }
};

const fetchMessages = async (chatId) => {
    try {
        const response = await axios.get(route('chats.messages.index', { chat: chatId }));
        messages.value = response.data;
        console.log('Fetched messages:', messages.value);
        // Log the structure of the first message (if available)
        if (messages.value.length > 0) {
            console.log('First message structure:', JSON.stringify(messages.value[0], null, 2));
        }
    } catch (error) {
        console.error('Error fetching messages:', error);
        messages.value = [];
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedChat.value) return;

    try {
        isAiTyping.value = true;
        const response = await axios.post(route('chats.messages.store', { chat: selectedChat.value.id }), {
            content: newMessage.value
        });
        messages.value.push(response.data.userMessage);
        messages.value.push(response.data.aiMessage);
        newMessage.value = '';
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Error: Failed to get response from AI');
    } finally {
        isAiTyping.value = false;
    }
};

const startEditingTitle = () => {
    isEditingTitle.value = true;
    editedTitle.value = selectedChat.value.title;
    nextTick(() => {
        titleInput.value.focus();
    });
};

const saveEditedTitle = async () => {
    if (editedTitle.value && editedTitle.value !== selectedChat.value.title) {
        try {
            const response = await axios.put(route('chats.update', selectedChat.value.id), {
                title: editedTitle.value
            });
            selectedChat.value.title = response.data.title;
            const index = chats.value.findIndex(chat => chat.id === selectedChat.value.id);
            if (index !== -1) {
                chats.value[index].title = response.data.title;
            }
        } catch (error) {
            console.error('Error updating chat title:', error);
            alert('Error updating chat title');
        }
    }
    isEditingTitle.value = false;
};

const deleteChat = async (chat) => {
    if (confirm('Are you sure you want to delete this chat?')) {
        try {
            await axios.delete(route('chats.destroy', chat.id));
            const index = chats.value.findIndex(c => c.id === chat.id);
            if (index !== -1) {
                chats.value.splice(index, 1);
            }
            if (selectedChat.value && selectedChat.value.id === chat.id) {
                selectedChat.value = null;
                messages.value = [];
            }
        } catch (error) {
            console.error('Error deleting chat:', error);
            alert('Error deleting chat');
        }
    }
};

onMounted(() => {
    fetchChats();
});
</script>
