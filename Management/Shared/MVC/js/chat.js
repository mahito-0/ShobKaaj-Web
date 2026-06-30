const chatWindow = document.getElementById('chat-window');
const messagesContainer = document.getElementById('chat-messages');
const inputField = document.getElementById('chat-input');
const loader = document.getElementById('chat-loader');

// Toggle chat window visibility
function toggleChat() {
    if (chatWindow.classList.contains('chat-hidden')) {
        chatWindow.classList.remove('chat-hidden');
        chatWindow.classList.add('chat-visible');
        setTimeout(() => inputField.focus(), 100);
    } else {
        chatWindow.classList.remove('chat-visible');
        chatWindow.classList.add('chat-hidden');
    }
}

function handleEnter(e) {
    if (e.key === 'Enter') sendMessage();
}

async function sendMessage() {
    const text = inputField.value.trim();
    if (!text) return;

    appendMessage(text, 'user-msg');
    inputField.value = '';

    loader.style.display = 'block';
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    try {
        // Correct path to chat.php (it is in the sibling 'php' folder)
        // AJAX request to send message to the chatbot
        const response = await fetch('../php/chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: text
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Hide loader and create the bot message container once
        loader.style.display = 'none';
        const botMessageDiv = document.createElement('div');
        botMessageDiv.className = `message bot-msg`;
        messagesContainer.insertBefore(botMessageDiv, loader);

        // Stream the response
        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            const lines = chunk.split('\n\n');
            
            for (let line of lines) {
                if (line.startsWith('data: ')) {
                    const dataStr = line.substring(6);
                    if (!dataStr.trim()) continue;
                    
                    try {
                        const dataObj = JSON.parse(dataStr);
                        if (dataObj.reply) {
                            botMessageDiv.innerText += dataObj.reply;
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        } else if (dataObj.error) {
                            botMessageDiv.innerText += "\n⚠️ " + dataObj.error;
                        }
                    } catch (e) {
                        console.warn("JSON Parse Error in stream:", e);
                    }
                }
            }
        }

    } catch (error) {
        console.error("Chat Error:", error);
        loader.style.display = 'none';
        appendMessage("❌ Error: Could not connect to AI. Check console for details.", 'bot-msg');
    }
}

function appendMessage(text, className) {
    const div = document.createElement('div');
    div.className = `message ${className}`;
    div.innerText = text;
    messagesContainer.insertBefore(div, loader);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}