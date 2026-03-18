@extends('suppliers.layouts.main')

@section('main-section')
@push('title')
<title>Help And Support</title>
@endpush

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@vite(['resources/js/app.js'])

<div class="row">
    {{-- Customers list --}}
    <div class="col-xl-3 col-lg-4 col-sm-5">
        <div class="card" style="height: 550px;">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Customers</h5>
            </div>
            <div class="card-body p-0" style="overflow-y:auto; height:490px;">
                <ul class="list-unstyled m-0 p-0">
                    @foreach ($customerList as $item)
                        <li class="d-flex align-items-center p-2 customer-item"
                            data-customer-id="{{ $item->customer_id }}"
                            style="cursor:pointer; border-bottom:1px solid #f1f1f1;">
                            <img src="/backend/images/02.png" class="rounded-circle me-2" width="40" height="40">
                            <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ $item->customer_name }}</h6>
                                    <small class="text-muted">Last message preview...</small>
                                </div>
                                @if ($item->unseen_count > 0)
                                    <span class="badge bg-danger rounded-pill ms-2">
                                        {{ $item->unseen_count }}
                                    </span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Chat box --}}
    <div class="col-xl-9 col-lg-8 col-sm-7 d-flex flex-column">
        <div class="card flex-grow-1 d-flex flex-column" style="height:550px;">
            <div class="card-header bg-white d-flex align-items-center">
                <h5 id="chat-header" class="mb-0 flex-grow-1">Inbox</h5>
                <small class="text-muted" id="chat-subheader">Select a customer to start chat</small>
            </div>
            <div class="card-body flex-grow-1 d-flex flex-column" id="chat-box"
                style="background:#e5ddd5; overflow:auto; padding:15px; scroll-behavior: smooth;">
                <div class="text-center text-muted mt-auto">No messages yet</div>
            </div>
            <div class="card-footer p-2 bg-white d-flex">
                <input type="text" id="chat-input" class="form-control me-2 rounded-pill"
                    placeholder="Type a message..." disabled>
                <button id="chat-send" class="btn btn-primary rounded-pill px-4" disabled>Send</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatBox = document.getElementById('chat-box');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    const customerItems = document.querySelectorAll('.customer-item');
    let selectedCustomerId = null;
    let currentChannel = null;

    // Helper: Scroll to bottom
    const scrollToBottom = () => chatBox.scrollTop = chatBox.scrollHeight;

    // Helper: Format date (Today / Yesterday)
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(today.getDate() - 1);

        if (date.toDateString() === today.toDateString()) return "Today";
        if (date.toDateString() === yesterday.toDateString()) return "Yesterday";
        return date.toLocaleDateString();
    }

    // Helper: Render message bubble
    function renderMessage(msg) {
        const wrapper = document.createElement('div');
        wrapper.classList.add('d-flex', 'mb-2');
        wrapper.style.maxWidth = '70%';

        const bubble = document.createElement('div');
        bubble.classList.add('p-2', 'rounded', 'shadow-sm');
        bubble.style.wordWrap = 'break-word';

        if (msg.sender_type === 'supplier') {
            wrapper.classList.add('ms-auto', 'justify-content-end');
            bubble.classList.add('bg-primary', 'text-white', 'rounded-end');
        } else {
            wrapper.classList.add('me-auto', 'justify-content-start');
            bubble.classList.add('bg-white', 'text-dark', 'rounded-start');
        }

        bubble.innerHTML = msg.message +
            `<div style="font-size:0.7rem; color:#555; text-align:right;">${new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</div>`;
        wrapper.appendChild(bubble);
        chatBox.appendChild(wrapper);
    }

    // Mark messages as seen
    async function markAsSeen(customerId) {
        try {
            await axios.post("{{ route('supplier/chat.markSeen') }}", {
                _token: "{{ csrf_token() }}",
                customer_id: customerId
            });
            // Remove unseen badge
            document.querySelector(`.customer-item[data-customer-id="${customerId}"] .badge`)?.remove();
        } catch (err) {
            console.error("Mark seen failed:", err);
        }
    }

    // Load and display messages
    async function loadChat(customerId) {
        try {
            const { data } = await axios.get(`/chat/${customerId}`);
            chatBox.innerHTML = '';
            let lastDate = null;
            if (data.length === 0) {
                chatBox.innerHTML = '<div class="text-center text-muted mt-auto">No messages yet</div>';
            } else {
                data.forEach(msg => {
                    const msgDate = formatDate(msg.created_at);
                    if (msgDate !== lastDate) {
                        const sep = document.createElement('div');
                        sep.classList.add('text-center', 'my-2', 'text-muted');
                        sep.innerText = msgDate;
                        chatBox.appendChild(sep);
                        lastDate = msgDate;
                    }
                    renderMessage(msg);
                });
            }
            scrollToBottom();
            await markAsSeen(customerId);
        } catch (err) {
            console.error('Failed to load messages:', err);
        }
    }

    // Handle customer click
    customerItems.forEach(item => {
        item.addEventListener('click', async () => {
            selectedCustomerId = item.dataset.customerId;
            chatInput.disabled = false;
            chatSend.disabled = false;
            document.getElementById('chat-header').innerText = item.querySelector('h6').innerText;
            document.getElementById('chat-subheader').innerText = 'Online';
            if (currentChannel && window.Echo) window.Echo.leave(`chat.${currentChannel}`);
            await loadChat(selectedCustomerId);

            // Real-time listen
            if (window.Echo) {
                currentChannel = selectedCustomerId;
                window.Echo.channel(`chat.${selectedCustomerId}`)
                    .listen('MessageSent', async (e) => {
                        renderMessage(e.message);
                        scrollToBottom();
                        await markAsSeen(selectedCustomerId);
                    });
            }
        });
    });

    // Send message
    async function sendMessage() {
        const msg = chatInput.value.trim();
        if (!msg || !selectedCustomerId) return;
        try {
            await axios.post('/chat/send', {
                customer_id: selectedCustomerId,
                message: msg,
                sender_type: 'supplier'
            });
            chatInput.value = '';
        } catch (err) {
            console.error('Failed to send message:', err);
        }
    }

    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });
});
</script>
@endsection
