@extends('layouts.app')

@section('content')
    <div
        class="flex h-[calc(100vh-8rem)] md:h-[calc(100vh-10rem)] overflow-hidden bg-white rounded-lg shadow-lg border border-gray-200 relative">
        <!-- Sidebar -->
        <div id="chat-sidebar" class="w-full md:w-1/4 bg-gray-50 border-r border-gray-200 flex flex-col md:flex">
            <div class="p-4 border-b border-gray-200 bg-gray-100">
                <h2 class="text-lg font-semibold text-gray-700">Conversations</h2>
            </div>

            <div class="flex-1 overflow-y-auto">
                <!-- Group Chat Option -->
                <button onclick="selectChat(null, true, 'General Group')" id="group-chat-btn"
                    class="w-full text-left px-4 py-3 hover:bg-indigo-50 focus:outline-none transition-colors border-b border-gray-100 flex items-center space-x-3">
                    <div
                        class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-900">General Group</span>
                        <span class="block text-xs text-gray-500">Team Workspace</span>
                    </div>
                </button>

                <!-- Direct Messages Header -->
                <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Direct Messages
                </div>

                <!-- User List -->
                @foreach ($users as $user)
                    <button onclick="selectChat({{ $user->id }}, false, '{{ $user->name }}')"
                        id="user-chat-btn-{{ $user->id }}"
                        class="user-chat-btn w-full text-left px-4 py-3 hover:bg-gray-50 focus:outline-none transition-colors flex items-center space-x-3">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-900">{{ $user->name }}</span>
                            <span
                                class="block text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Chat Area -->
        <div id="chat-area"
            class="w-full md:flex-1 flex-col bg-white hidden md:flex absolute md:relative inset-0 z-10 md:z-auto">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-white">
                <div class="flex items-center space-x-3">
                    <button onclick="showSidebar()" class="md:hidden text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>
                    <h3 id="chat-header-name" class="text-lg font-bold text-gray-800">Select a chat</h3>
                </div>
            </div>

            <!-- Messages -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                <!-- Messages will be loaded here -->
                <div class="text-center text-gray-500 mt-10">Select a conversation to start chatting</div>
            </div>

            <!-- Attachment Preview -->
            <div id="attachment-preview"
                class="hidden px-4 py-2 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-sm text-gray-600 mr-2">Image selected:</span>
                    <img id="preview-img" src="" class="h-10 w-auto rounded border border-gray-300">
                </div>
                <button type="button" onclick="cancelAttachment()" class="text-gray-400 hover:text-red-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
                <input type="file" id="image-upload" class="hidden" accept="image/*" onchange="handleFileSelect(this)">
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-gray-200 bg-white">
                <form id="chat-form" class="flex space-x-3" onsubmit="sendMessage(event)">
                    <button type="button" onclick="document.getElementById('image-upload').click()"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                            </path>
                        </svg>
                    </button>

                    <input type="text" id="message-input"
                        class="flex-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-3 border"
                        placeholder="Type a message..." disabled>

                    <button type="submit" id="send-btn"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        disabled>
                        <svg class="h-5 w-5 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentReceiverId = null;
        let currentIsGroup = false;
        let pollInterval = null;
        let currentUserCheck = {{ Auth::id() }};
        let currentAttachment = null;
        let storageBaseUrl = "{{ Storage::url('') }}"; // Base URL for public storage

        // Pagination & State
        let firstMessageId = null;
        let lastMessageId = null;
        let isLoadingHistory = false;
        let hasMoreHistory = true;
        let isPolling = false;

        // Clipboard Paste Listener
        document.addEventListener('paste', function(e) {
            if (!currentReceiverId && !currentIsGroup) return; // Only if chat is active

            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (const item of items) {
                if (item.type.indexOf('image') === 0) {
                    const blob = item.getAsFile();
                    handleFileCheck(blob);
                    e.preventDefault();
                    return;
                }
            }
        });

        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                handleFileCheck(input.files[0]);
            }
        }

        function handleFileCheck(file) {
            currentAttachment = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('attachment-preview').classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }

        function cancelAttachment() {
            currentAttachment = null;
            document.getElementById('image-upload').value = '';
            document.getElementById('attachment-preview').classList.add('hidden');
        }

        function selectChat(userId, isGroup, name) {
            currentReceiverId = userId;
            currentIsGroup = isGroup;

            // Reset state
            firstMessageId = null;
            lastMessageId = null;
            hasMoreHistory = true;
            isLoadingHistory = false;
            isPolling = false;
            cancelAttachment(); // Clear drafts

            // Update Header
            document.getElementById('chat-header-name').textContent = name;

            const input = document.getElementById('message-input');
            const btn = document.getElementById('send-btn');
            input.disabled = false;
            btn.disabled = false;
            input.focus();

            // Highlight Active
            document.querySelectorAll('.user-chat-btn').forEach(b => b.classList.remove('bg-gray-100'));
            document.querySelectorAll('button').forEach(b => b.classList.remove('bg-indigo-50'));

            if (isGroup) {
                document.getElementById('group-chat-btn').classList.add('bg-indigo-50');
            } else {
                document.getElementById('user-chat-btn-' + userId).classList.add('bg-gray-100');
            }

            // Mobile View Toggle
            const sidebar = document.getElementById('chat-sidebar');
            const chatArea = document.getElementById('chat-area');
            if (window.innerWidth < 768) { // sm/md breakpoint check
                sidebar.classList.add('hidden');
                chatArea.classList.remove('hidden');
                chatArea.classList.add('flex');
            }

            // Clear Container
            const container = document.getElementById('messages-container');
            container.innerHTML = '<div class="text-center text-gray-400 mt-10">Loading conversations...</div>';

            if (pollInterval) clearInterval(pollInterval);

            loadInitialMessages();
            pollInterval = setInterval(pollNewMessages, 3000);
        }

        function showSidebar() {
            const sidebar = document.getElementById('chat-sidebar');
            const chatArea = document.getElementById('chat-area');

            sidebar.classList.remove('hidden');
            chatArea.classList.add('hidden');
            chatArea.classList.remove('flex');

            // Optional: clear active highlights on mobile if desired, or keep them.
            // Keeping them is better for state.
        }

        async function loadInitialMessages() {
            const messages = await fetchMessages();
            renderMessages(messages, 'initial');
        }

        async function pollNewMessages() {
            if (isPolling) return;

            isPolling = true;
            const params = lastMessageId ? {
                after_id: lastMessageId
            } : {};
            const messages = await fetchMessages(params);

            if (messages.length > 0) {
                const container = document.getElementById('messages-container');
                if (container.innerHTML.includes('No messages yet')) {
                    container.innerHTML = '';
                }
                renderMessages(messages, 'append');
            }
            isPolling = false;
        }

        async function loadOlderMessages() {
            if (isLoadingHistory || !hasMoreHistory || !firstMessageId) return;

            isLoadingHistory = true;

            const container = document.getElementById('messages-container');
            const oldScrollHeight = container.scrollHeight;

            const messages = await fetchMessages({
                before_id: firstMessageId
            });

            if (messages.length === 0) {
                hasMoreHistory = false;
            } else {
                renderMessages(messages, 'prepend');
                container.scrollTop = container.scrollHeight - oldScrollHeight;
            }

            isLoadingHistory = false;
        }

        async function fetchMessages(extraParams = {}) {
            if (!currentReceiverId && !currentIsGroup) return [];

            try {
                const params = new URLSearchParams({
                    user_id: currentReceiverId || '',
                    is_group: currentIsGroup ? '1' : '0',
                    ...extraParams
                });

                const response = await fetch(`{{ route('chat.messages.fetch') }}?${params}`);
                return await response.json();
            } catch (error) {
                console.error('Error fetching messages:', error);
                return [];
            }
        }

        function renderMessages(messages, mode = 'append') {
            const container = document.getElementById('messages-container');

            if (mode === 'initial') {
                container.innerHTML = '';
                if (messages.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-400 mt-10">No messages yet. Say hello!</div>';
                    return;
                }
            }

            if (messages.length === 0) return;

            // Filter out duplicates
            const uniqueMessages = messages.filter(msg => !document.getElementById('msg-' + msg.id));
            if (uniqueMessages.length === 0 && mode !== 'initial') return;

            // Update IDs
            const batchLastId = messages[messages.length - 1].id;
            const batchFirstId = messages[0].id;

            if (mode === 'prepend') {
                firstMessageId = batchFirstId;
            } else if (mode === 'append' || mode === 'initial') {
                if (!firstMessageId) firstMessageId = batchFirstId;
                // Always update lastMessageId to the latest ID from the server
                if (!lastMessageId || batchLastId > lastMessageId) {
                    lastMessageId = batchLastId;
                }
            }

            let html = '';
            uniqueMessages.forEach(msg => {
                const isMe = msg.sender_id == currentUserCheck;
                const time = new Date(msg.created_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                let content = '';

                // Handle Attachments
                if (msg.attachment) {
                    // Construct path using storage base URL
                    // Check if path already starts with slash or not
                    let imgPath = msg.attachment;
                    if (!imgPath.startsWith('http')) {
                        imgPath = storageBaseUrl + imgPath;
                    }

                    content +=
                        `<div class="mb-2"><img src="${imgPath}" class="rounded-lg max-h-60 w-auto cursor-pointer border border-gray-200" onclick="window.open(this.src, '_blank')"></div>`;
                }
                if (msg.message) {
                    content += `<p class="text-sm">${escapeHtml(msg.message)}</p>`;
                }

                const bubble = isMe ?
                    `<div id="msg-${msg.id}" class="flex justify-end mb-4">
                    <div class="max-w-xs lg:max-w-md bg-indigo-600 text-white rounded-lg py-2 px-4 shadow rounded-br-none">
                        ${content}
                        <p class="text-xs text-indigo-200 text-right mt-1">${time}</p>
                    </div>
                </div>` :
                    `<div id="msg-${msg.id}" class="flex justify-start mb-4">
                     <div class="max-w-xs lg:max-w-md bg-white text-gray-800 rounded-lg py-2 px-4 shadow rounded-bl-none border border-gray-100">
                        ${currentIsGroup ? `<p class="text-xs text-indigo-600 font-bold mb-1">${escapeHtml(msg.sender.name)}</p>` : ''}
                        ${content}
                        <p class="text-xs text-gray-400 mt-1">${time}</p>
                    </div>
                </div>`;
                html += bubble;
            });

            if (mode === 'prepend') {
                container.insertAdjacentHTML('afterbegin', html);
            } else {
                container.insertAdjacentHTML('beforeend', html);
                container.scrollTop = container.scrollHeight;
            }
        }

        document.getElementById('messages-container').addEventListener('scroll', function() {
            if (this.scrollTop === 0) {
                loadOlderMessages();
            }
        });

        async function sendMessage(e) {
            e.preventDefault();
            const input = document.getElementById('message-input');
            const message = input.value.trim();

            // Allow send if either message OR attachment exists
            if (!message && !currentAttachment) return;

            input.value = '';

            // Hide preview immediately (optimistic)
            const attachmentToSend = currentAttachment;
            cancelAttachment();

            try {
                const formData = new FormData();
                formData.append('message', message);
                if (currentIsGroup) {
                    formData.append('is_group', '1');
                } else {
                    formData.append('receiver_id', currentReceiverId);
                }
                if (attachmentToSend) {
                    formData.append('attachment', attachmentToSend);
                }
                formData.append('_token', '{{ csrf_token() }}');

                await fetch(`{{ route('chat.messages.send') }}`, {
                    method: 'POST',
                    body: formData
                });

                pollNewMessages();
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message');
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
@endsection
