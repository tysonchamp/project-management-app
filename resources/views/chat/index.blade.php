@extends('layouts.app')

@section('content')
    <div class="flex h-[calc(100vh-10rem)] overflow-hidden bg-white rounded-lg shadow-lg border border-gray-200">
        <!-- Sidebar -->
        <div class="w-1/4 bg-gray-50 border-r border-gray-200 flex flex-col">
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
        <div class="flex-1 flex flex-col bg-white">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-white">
                <div class="flex items-center space-x-3">
                    <h3 id="chat-header-name" class="text-lg font-bold text-gray-800">Select a chat</h3>
                </div>
            </div>

            <!-- Messages -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                <!-- Messages will be loaded here -->
                <div class="text-center text-gray-500 mt-10">Select a conversation to start chatting</div>
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-gray-200 bg-white">
                <form id="chat-form" class="flex space-x-3" onsubmit="sendMessage(event)">
                    <input type="hidden" id="current_receiver_id">
                    <input type="hidden" id="current_is_group" value="0">

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

        function selectChat(userId, isGroup, name) {
            currentReceiverId = userId;
            currentIsGroup = isGroup;

            // Update Header
            document.getElementById('chat-header-name').textContent = name;

            // Update Inputs
            document.getElementById('current_receiver_id').value = userId || '';
            document.getElementById('current_is_group').value = isGroup ? '1' : '0';

            const input = document.getElementById('message-input');
            const btn = document.getElementById('send-btn');
            input.disabled = false;
            btn.disabled = false;
            input.focus();

            // Highlight Active
            document.querySelectorAll('button').forEach(b => b.classList.remove('bg-indigo-50', 'bg-gray-100'));
            if (isGroup) {
                document.getElementById('group-chat-btn').classList.add('bg-indigo-50');
            } else {
                document.getElementById('user-chat-btn-' + userId).classList.add('bg-gray-100');
            }

            // Clear and Fetch
            document.getElementById('messages-container').innerHTML =
                '<div class="text-center text-gray-500 mt-4">Loading messages...</div>';

            if (pollInterval) clearInterval(pollInterval);
            fetchMessages();
            pollInterval = setInterval(fetchMessages, 3000); // Poll every 3 seconds
        }

        async function fetchMessages() {
            if (!currentReceiverId && !currentIsGroup) return;

            try {
                const params = new URLSearchParams({
                    user_id: currentReceiverId || '',
                    is_group: currentIsGroup ? '1' : '0'
                });

                const response = await fetch(`{{ route('chat.messages.fetch') }}?${params}`);
                const messages = await response.json();

                renderMessages(messages);
            } catch (error) {
                console.error('Error fetching messages:', error);
            }
        }

        function renderMessages(messages) {
            const container = document.getElementById('messages-container');

            if (messages.length === 0) {
                if (container.innerHTML.includes('Loading')) {
                    container.innerHTML = '<div class="text-center text-gray-400 mt-10">No messages yet. Say hello!</div>';
                }
                return;
            }

            // Simple diff check to avoid full re-render flickering if possible, 
            // but for now we'll just re-render to ensure accuracy. 
            // Ideally we'd append only new ones. for simplicity in this artifact, direct replacement.
            // To improve UX, we check if we are at bottom to auto-scroll.
            const isAtBottom = container.scrollHeight - container.scrollTop === container.clientHeight;

            let html = '';
            let lastDate = null;

            messages.forEach(msg => {
                const isMe = msg.sender_id == currentUserCheck;
                const date = new Date(msg.created_at).toLocaleDateString();
                const time = new Date(msg.created_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                if (date !== lastDate) {
                    html += `<div class="text-center text-xs text-gray-400 my-4"><span>${date}</span></div>`;
                    lastDate = date;
                }

                if (isMe) {
                    html += `
                    <div class="flex justify-end">
                        <div class="max-w-xs lg:max-w-md bg-indigo-600 text-white rounded-lg py-2 px-4 shadow rounded-br-none">
                            <p class="text-sm">${escapeHtml(msg.message)}</p>
                            <p class="text-xs text-indigo-200 text-right mt-1">${time}</p>
                        </div>
                    </div>
                `;
                } else {
                    html += `
                    <div class="flex justify-start">
                        <div class="max-w-xs lg:max-w-md bg-white text-gray-800 rounded-lg py-2 px-4 shadow rounded-bl-none border border-gray-100">
                            ${currentIsGroup ? `<p class="text-xs text-indigo-600 font-bold mb-1">${escapeHtml(msg.sender.name)}</p>` : ''}
                            <p class="text-sm">${escapeHtml(msg.message)}</p>
                            <p class="text-xs text-gray-400 mt-1">${time}</p>
                        </div>
                    </div>
                `;
                }
            });

            // Only update if content is different (simple check)
            if (container.innerHTML.length !== html.length) { // Weak check but sufficient for basic content changes
                container.innerHTML = html;
                // Scroll to bottom
                container.scrollTop = container.scrollHeight;
            }
        }

        async function sendMessage(e) {
            e.preventDefault();
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            if (!message) return;

            // Optimistic UI update could go here
            input.value = '';

            try {
                const formData = new FormData();
                formData.append('message', message);
                if (currentIsGroup) {
                    formData.append('is_group', '1');
                } else {
                    formData.append('receiver_id', currentReceiverId);
                }
                formData.append('_token', '{{ csrf_token() }}');

                await fetch(`{{ route('chat.messages.send') }}`, {
                    method: 'POST',
                    body: formData
                });

                fetchMessages(); // Immediate fetch
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message');
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
@endsection
