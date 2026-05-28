/* public/js/chatbot.js */
// Chatbot Lifecycle, API interactions, Language Adaptation, and Admin Redirection

document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const chatbotFab = document.getElementById('cm-chatbot-fab');
    const chatbotWindow = document.getElementById('cm-chatbot-window');
    const chatbotClose = document.getElementById('cm-chatbot-close');
    const chatbotForm = document.getElementById('cm-chatbot-form');
    const chatbotInput = document.getElementById('cm-chatbot-input');
    const chatbotMessages = document.getElementById('cm-chatbot-messages');

    if (!chatbotFab || !chatbotWindow || !chatbotClose || !chatbotForm || !chatbotInput || !chatbotMessages) {
        return;
    }

    // Active Admin ID (fetched dynamically from layout helper)
    const adminId = parseInt(chatbotWindow.getAttribute('data-admin-id') || '1');

    // i18n Strings (Adaptive base)
    const locale = window.__locale || 'en';
    const isTurkish = locale === 'tr';

    const strings = {
        welcome: isTurkish 
            ? "Merhaba! 👋 Ben CampusMarket yapay zeka asistanıyım. Kampüs pazar yeri kuralları, ödemeler, güvenli alışveriş ve ilan yükleme hakkında sorularınızı yanıtlayabilirim. Nasıl yardımcı olabilirim?"
            : "Hello! 👋 I'm the CampusMarket AI assistant. I can answer questions regarding campus marketplace guidelines, secure payments, safety rules, and listing creation. How can I help you today?",
        placeholder: isTurkish
            ? "Sorunuzu buraya yazın..."
            : "Type your question here...",
        adminAlertTitle: isTurkish
            ? "Yöneticiye Ulaşın"
            : "Contact Administrator",
        adminAlertDesc: isTurkish
            ? "Maalesef bu sorunun cevabını bilmiyorum. Destek almak için lütfen doğrudan yöneticiyle sohbet başlatın."
            : "I'm sorry, I don't know the answer to that question. To get help, please start a direct conversation with the administrator.",
        adminBtnText: isTurkish
            ? "Yönetici Sohbetini Aç"
            : "Open Admin Chatbox"
    };

    // Initialize UI
    chatbotInput.placeholder = strings.placeholder;
    appendBotMessage(strings.welcome);

    // Toggle Chat Window
    chatbotFab.addEventListener('click', () => {
        const isOpen = chatbotWindow.classList.contains('open');
        if (isOpen) {
            chatbotWindow.classList.remove('open');
        } else {
            chatbotWindow.classList.add('open');
            chatbotInput.focus();
            // Clear notification pulse after first open
            chatbotFab.classList.add('read-active');
            chatbotFab.style.setProperty('--cm-pulse-opacity', '0');
            chatbotFab.className = 'cm-chatbot-fab'; // strips notification pseudo-classes
        }
    });

    chatbotClose.addEventListener('click', () => {
        chatbotWindow.classList.remove('open');
    });

    // Form Submission
    chatbotForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const messageText = chatbotInput.value.trim();
        if (!messageText) return;

        // Append User Message
        appendUserMessage(messageText);
        chatbotInput.value = '';
        chatbotInput.focus();

        // Render Typing Indicator
        const typingEl = showTypingIndicator();

        try {
            const response = await fetch(`${window.__baseUrl || '/'}pages/api_chatbot.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: messageText })
            });

            removeTypingIndicator(typingEl);

            if (!response.ok) {
                throw new Error('API failure');
            }

            const data = await response.json();
            if (data.success) {
                if (data.unknown) {
                    appendAdminEscalationCard(data.admin_id || adminId);
                } else {
                    appendBotMessage(data.response);
                }
            } else {
                if (data.error === 'rate_limit') {
                    appendBotMessage(`⚠️ ${data.response}`);
                } else {
                    appendBotMessage(isTurkish 
                        ? "Bir hata oluştu. Lütfen daha sonra tekrar deneyin." 
                        : "An error occurred. Please try again later."
                    );
                }
            }
        } catch (err) {
            removeTypingIndicator(typingEl);
            console.error('Chatbot error:', err);
            appendBotMessage(isTurkish 
                ? "Bağlantı hatası. İnternet bağlantınızı kontrol edip tekrar deneyin." 
                : "Connection error. Please check your internet connection and try again."
            );
        }
    });

    // Message Render Helpers
    function appendUserMessage(text) {
        const bubble = document.createElement('div');
        bubble.className = 'cm-chat-bubble user';
        bubble.textContent = text;
        chatbotMessages.appendChild(bubble);
        scrollToBottom();
    }

    function appendBotMessage(markdownText) {
        const bubble = document.createElement('div');
        bubble.className = 'cm-chat-bubble bot';
        bubble.innerHTML = parseSimpleMarkdown(markdownText);
        chatbotMessages.appendChild(bubble);
        scrollToBottom();
    }

    function appendAdminEscalationCard(targetAdminId) {
        const card = document.createElement('div');
        card.className = 'cm-fallback-card';

        // Direct escalation link to inbox admin chatbox
        const chatboxUrl = `${window.__baseUrl || '/'}pages/messages.php?other_user_id=${targetAdminId}&product_id=0`;

        card.innerHTML = `
            <div class="cm-fallback-title">
                <span>⚠️</span> ${strings.adminAlertTitle}
            </div>
            <div class="cm-fallback-desc">
                ${strings.adminAlertDesc}
            </div>
            <a href="${chatboxUrl}" class="cm-fallback-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px; flex-shrink: 0;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10z"></path></svg>
                <span>${strings.adminBtnText}</span>
            </a>
        `;
        chatbotMessages.appendChild(card);
        scrollToBottom();
    }

    function showTypingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'cm-typing-indicator';
        indicator.innerHTML = `
            <div class="cm-typing-dot"></div>
            <div class="cm-typing-dot"></div>
            <div class="cm-typing-dot"></div>
        `;
        chatbotMessages.appendChild(indicator);
        scrollToBottom();
        return indicator;
    }

    function removeTypingIndicator(el) {
        if (el && el.parentNode) {
            el.parentNode.removeChild(el);
        }
    }

    function scrollToBottom() {
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    // Mini Safe Markdown Parser ([text](url) -> anchor tags)
    function parseSimpleMarkdown(text) {
        // Escape HTML to block script injection (XSS)
        let escaped = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

        // Convert [Title](URL) into safe anchor tags
        return escaped.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');
    }
});
