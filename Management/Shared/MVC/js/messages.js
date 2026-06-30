/**
 * Messaging System Logic
 */

let activePartnerId = null;
let pollInterval = null;

document.addEventListener('DOMContentLoaded', () => {
    loadConversations();

    // Event Listeners
    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', sendMessage);
    }

    // Auto-open chat from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user_id');
    if (userId) {
        startChatWithUser(userId);
    }

    // Mobile back button — slide back to conversation list
    const mobileBackBtn = document.getElementById('mobileBackBtn');
    if (mobileBackBtn) {
        mobileBackBtn.addEventListener('click', () => {
            const messengerContainer = document.querySelector('.messenger-container');
            if (messengerContainer) messengerContainer.classList.remove('mobile-chat-open');
        });
    }
});

async function loadConversations() {
    const listEl = document.getElementById('conversationsList');
    if (!listEl) return;

    try {
        const response = await API.post('get_conversations', {}, 'messagesAPI');

        if (response.status === 'success') {
            renderConversations(response.conversations);
        } else {
            renderEmptyState(listEl, response.message);
        }
    } catch (e) {
        console.error("Failed to load conversations:", e);
    }
}

function renderConversations(conversations) {
    const listEl = document.getElementById('conversationsList');
    listEl.innerHTML = '';

    if (!conversations || conversations.length === 0) {
        renderEmptyState(listEl, 'No messages yet. Visit a profile to start chatting!');
        return;
    }

    conversations.forEach(conv => {
        const el = document.createElement('div');
        el.className = `conversation-item ${activePartnerId == conv.user_id ? 'active' : ''}`;
        el.onclick = () => selectConversation(conv.user_id);

        const unreadBadge = conv.unread_count > 0 ? `<div class="unread-badge"></div>` : '';
        const avatarUrl = window.getAvatarPath(conv.avatar, conv.role);
        const unreadClass = conv.unread_count > 0 ? 'unread' : '';

        el.innerHTML = `
            <div class="conv-avatar-wrapper">
                <img src="${avatarUrl}" class="avatar" alt="${conv.first_name}">
                ${unreadBadge}
            </div>
            <div class="conv-info">
                <div class="conv-top">
                    <span class="conv-name">${conv.first_name} ${conv.last_name}</span>
                    <span class="conv-time">${formatTime(conv.created_at)}</span>
                </div>
                <div class="conv-preview ${unreadClass}">
                    ${conv.unread_count > 0 ? '• ' : ''} ${conv.last_message}
                </div>
            </div>
        `;
        listEl.appendChild(el);
    });
}

function selectConversation(userId) {
    if (activePartnerId === userId) return;

    activePartnerId = userId;

    // Toggle Panels
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('chatInterface').style.display = 'flex';

    // Mobile: slide to chat panel
    const messengerContainer = document.querySelector('.messenger-container');
    if (messengerContainer && window.innerWidth <= 768) {
        messengerContainer.classList.add('mobile-chat-open');
    }

    // Update Sidebar Selection
    document.querySelectorAll('.conversation-item').forEach(el => {
        el.classList.toggle('active', false);
    });

    // Reload list
    loadConversations();

    // Prepare Chat Area
    const messagesList = document.getElementById('messagesList');
    messagesList.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

    // Start Real-time updates
    if (pollInterval) clearInterval(pollInterval);
    loadMessages();

    pollInterval = setInterval(loadMessages, 3000);
}

async function loadMessages() {
    if (!activePartnerId) return;

    try {
        const response = await API.post('get_messages', { partner_id: activePartnerId }, 'messagesAPI');

        if (response.status === 'success') {
            updateChatHeader(response.partner);
            renderMessages(response.messages);
        }
    } catch (e) {
        console.error("Error loading messages:", e);
    }
}

function updateChatHeader(partner) {
    const nameEl = document.getElementById('chatPartnerName');
    const avatarEl = document.getElementById('chatPartnerAvatar');
    const fullName = `${partner.first_name} ${partner.last_name}`;
    const avatarUrl = window.getAvatarPath(partner.avatar, partner.role);

    if (nameEl) nameEl.textContent = fullName;
    if (avatarEl) avatarEl.src = avatarUrl;
}

function renderMessages(messages) {
    const listEl = document.getElementById('messagesList');

    // Check scroll position
    const isAtBottom = (listEl.scrollHeight - listEl.scrollTop - listEl.clientHeight) < 50;

    // Build the new HTML string
    let newHtml = '';

    messages.forEach(msg => {
        const isMe = msg.sender_id != activePartnerId;
        const type = isMe ? 'sent' : 'received';
        const safeMessage = escapeHtml(msg.message);

        newHtml += `
            <div class="message-wrapper ${type}">
                <div class="message-bubble">${safeMessage}</div>
                <span class="message-time">${formatTimeShort(msg.created_at)}</span>
            </div>
        `;
    });

    // Only update DOM if the content has actually changed
    if (listEl.innerHTML !== newHtml) {
        listEl.innerHTML = newHtml;

        // Auto-scroll logic only if we updated
        if (isAtBottom || messages.length === 0) {
            listEl.scrollTop = listEl.scrollHeight;
        }
    }
}

async function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const text = input.value.trim();

    if (!text || !activePartnerId) return;

    input.value = ''; // Clear input immediately

    try {
        const response = await API.post('send_message', {
            receiver_id: activePartnerId,
            message: text
        }, 'messagesAPI');

        if (response.status === 'success') {
            loadMessages(); // Refresh conversation
            loadConversations(); // Update sidebar
        } else {
            alert(response.message);
        }
    } catch (e) {
        console.error("Send failed:", e);
        alert('Failed to send message. Please try again.');
    }
}

function startChatWithUser(userId) {
    selectConversation(userId);
}

function renderEmptyState(container, message) {
    container.innerHTML = `<div class="empty-state-text">${message}</div>`;
}

// Utilities
function formatTime(sqlDate) {
    if (!sqlDate) return '';
    const date = new Date(sqlDate);
    const now = new Date();
    const diffMs = now - date;
    const diffHours = diffMs / (1000 * 60 * 60);

    if (diffHours < 24) {
        return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    } else {
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
    }
}

function formatTimeShort(sqlDate) {
    if (!sqlDate) return '';
    const date = new Date(sqlDate);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
