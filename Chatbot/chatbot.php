<!-- Floating Chatbot Icon with Popup -->
<div x-data="{ chatOpen: false }" class="z-50" x-cloak>
    <!-- Chatbot Button -->
    <button @click="chatOpen = !chatOpen; if(chatOpen) { loadChatHistory(); }"
        class="fixed bottom-6 right-6 bg-white border border-blue-500 p-2 rounded-full shadow-lg hover:scale-110 transition duration-300 z-50">
        <img src="../images/chatboticon.png" alt="Chatbot" class="w-12 h-12">
    </button>

    <!-- Chatbot Box -->
    <div x-show="chatOpen"
         x-transition
         x-cloak
         class="fixed bottom-20 right-6 w-80 bg-white border border-gray-300 rounded-2xl shadow-xl z-50"
         @click.outside="chatOpen = false">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white px-4 py-3 rounded-t-2xl flex justify-between items-center">
            <span class="font-semibold flex items-center gap-2">
                <img src="../images/chatboticon.png" class="w-5 h-5" />
                Chatbot
            </span>
            <button @click="chatOpen = false"><i class="fas fa-times"></i></button>
        </div>

        <!-- Chat Content -->
        <div class="p-3 h-80 overflow-y-auto space-y-2" id="chatMessages">
            <div class="bg-gray-100 px-4 py-2 rounded-2xl text-sm w-fit shadow">Hai! Ada apa saya boleh bantu?</div>
        </div>

        <!-- Input -->
        <form onsubmit="sendMessage(event)" class="flex border-t border-gray-200">
            <input type="text" id="chatInput" class="flex-grow p-2 text-sm focus:outline-none"
                   placeholder="Tulis mesej anda...">
            <button type="submit" class="bg-blue-500 px-4 text-white text-sm">Hantar</button>
        </form>
    </div>
</div>

<!-- Alpine Cloak Style -->
<style>
    [x-cloak] { display: none !important; }
</style>

<!-- Chat JS -->
<script>
let idleTimer;

function resetIdleTimer() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(userIdlePrompt, 30000);
}

function userIdlePrompt() {
    const messages = document.getElementById('chatMessages');

    const botMsg = document.createElement('div');
    botMsg.className = 'bg-gray-100 px-4 py-2 rounded-2xl text-sm shadow-md w-fit';
    botMsg.innerHTML = "Saya masih di sini! 🧑‍💻<br>Anda boleh pilih salah satu di bawah:";

    const buttonsHtml = `
        <div class=\"space-y-2 mt-2\">
            <button onclick=\"sendQuickReply('Aktiviti')\" class=\"bg-blue-200 hover:bg-blue-300 text-blue-800 font-semibold px-4 py-2 rounded-2xl shadow-md text-sm block w-full\">Aktiviti</button>
            <button onclick=\"sendQuickReply('Persatuan')\" class=\"bg-blue-200 hover:bg-blue-300 text-blue-800 font-semibold px-4 py-2 rounded-2xl shadow-md text-sm block w-full\">Persatuan</button>
            <button onclick=\"sendQuickReply('Bantuan')\" class=\"bg-blue-200 hover:bg-blue-300 text-blue-800 font-semibold px-4 py-2 rounded-2xl shadow-md text-sm block w-full\">Bantuan</button>
        </div>
    `;

    botMsg.innerHTML += buttonsHtml;
    messages.appendChild(botMsg);
    messages.scrollTop = messages.scrollHeight;
}

function showTypingIndicator() {
    const messages = document.getElementById('chatMessages');

    const typing = document.createElement('div');
    typing.id = 'typing-indicator';
    typing.className = 'bg-gray-200 px-3 py-2 rounded-2xl text-sm italic w-fit';
    typing.textContent = 'Bot sedang menaip...';

    messages.appendChild(typing);
    messages.scrollTop = messages.scrollHeight;
}

function removeTypingIndicator() {
    const typing = document.getElementById('typing-indicator');
    if (typing) typing.remove();
}

document.getElementById('chatInput').addEventListener('input', resetIdleTimer);
resetIdleTimer();

function sendMessage(event) {
    event.preventDefault();
    const input = document.getElementById('chatInput');
    const messages = document.getElementById('chatMessages');
    const msg = input.value.trim();
    if (msg === '') return;

    const userMsg = document.createElement('div');
    userMsg.className = 'bg-blue-500 text-white px-4 py-2 rounded-2xl text-sm text-right ml-auto w-fit max-w-[70%] shadow-md';
    userMsg.textContent = msg;
    messages.appendChild(userMsg);
    messages.scrollTop = messages.scrollHeight;
    input.value = '';

    showTypingIndicator();

    fetch('../chatbot/chatbot_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'message=' + encodeURIComponent(msg)
    })
    .then(response => response.json())
    .then(data => {
    removeTypingIndicator();
    const messages = document.getElementById('chatMessages');

    const botMsg = document.createElement('div');
    botMsg.className = 'bg-gray-100 px-4 py-2 rounded-2xl text-sm shadow-md w-fit';

    // Gaya greeting + butang utama
    if (data.type === 'buttons' || data.type === 'greeting_buttons') {
        const greetingText = data.greeting || 'Anda boleh pilih salah satu di bawah:';

        const buttonHtml = `
            <div class="space-y-2 mt-2">
                ${data.buttons.map(button =>
                    `<button onclick="sendQuickReply('${button}')"
                        class="w-full bg-blue-100 text-blue-900 px-4 py-3 rounded-full font-semibold text-sm shadow 
                        hover:bg-blue-200 hover:scale-[1.02] transition-all duration-200">
                        ${button}
                    </button>`
                ).join('')}
            </div>
        `;

        botMsg.innerHTML = `${greetingText}<br>${buttonHtml}`;
    }

    // Gaya aktiviti akan datang (button from DB)
    else if (data.type === 'activity_buttons') {
        const activityHtml = `
            <div class="space-y-2 mt-2">
                ${data.buttons.map(button =>
                    `<button onclick="fetchActivityDetail('${button.id}')"
                        class="w-full bg-green-100 text-green-900 px-4 py-3 rounded-full font-semibold text-sm shadow 
                        hover:bg-green-200 hover:scale-[1.02] transition-all duration-200">
                        📅 ${button.label}
                    </button>`
                ).join('')}
            </div>
        `;
        botMsg.innerHTML = `Berikut aktiviti akan datang:<br>${activityHtml}`;
    }

    // Normal text reply
    else {
        botMsg.innerHTML = data.message;
    }

    messages.appendChild(botMsg);
    messages.scrollTop = messages.scrollHeight;
});

}

function sendQuickReply(text) {
    document.getElementById('chatInput').value = text;
    sendMessage(new Event('submit'));
}

function fetchActivityDetail(activityId) {
    const messages = document.getElementById('chatMessages');

    fetch('../chatbot/chatbot_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'activity_id=' + encodeURIComponent(activityId)
    })
    .then(response => response.json())
    .then(data => {
        const botMsg = document.createElement('div');
        botMsg.className = 'bg-gray-100 px-4 py-2 rounded-2xl text-sm shadow-md w-fit';
        botMsg.innerHTML = data.message;
        messages.appendChild(botMsg);
        messages.scrollTop = messages.scrollHeight;
    });
}

function loadChatHistory() {
    const messages = document.getElementById('chatMessages');
    messages.innerHTML = '';

    const initialGreeting = document.createElement('div');
    initialGreeting.className = 'bg-gray-100 px-4 py-2 rounded-2xl text-sm shadow-md w-fit';
    initialGreeting.textContent = 'Hai! Ada apa saya boleh bantu?';
    messages.appendChild(initialGreeting);

    fetch('load_chat_history.php')
    .then(response => response.json())
    .then(data => {
        data.forEach(chat => {
            const chatBubble = document.createElement('div');
            chatBubble.className = chat.sender === 'student'
                ? 'bg-blue-100 px-4 py-2 rounded-2xl text-sm text-right ml-auto w-fit shadow-md'
                : 'bg-gray-100 px-4 py-2 rounded-2xl text-sm w-fit shadow-md';
            chatBubble.textContent = chat.message;
            messages.appendChild(chatBubble);
        });
        messages.scrollTop = messages.scrollHeight;
    })
    .catch(error => {
        console.error('Error loading chat history:', error);
    });
}
</script>
