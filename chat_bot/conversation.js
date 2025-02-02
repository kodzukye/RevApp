let isBotResponding = false;

function createMessageElement(sender, text) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;
    
    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';
    contentDiv.textContent = text;
    
    const timeSpan = document.createElement('span');
    timeSpan.className = 'message-time';
    timeSpan.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    messageDiv.append(contentDiv, timeSpan);
    return messageDiv;
}

async function handleUserInput(userInput) {
    const conversationDiv = document.getElementById('conversation');
    const loadingIndicator = document.createElement('div');
    loadingIndicator.className = 'loading-indicator';
    loadingIndicator.textContent = 'RevAPP Bot est entrain de taper...';
    
    try {
        conversationDiv.appendChild(createMessageElement('user', userInput));
        conversationDiv.appendChild(loadingIndicator);
        conversationDiv.scrollTop = conversationDiv.scrollHeight;

        const response = await fetch('rasa-message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: userInput })
        });

        const messages = await response.json();
        conversationDiv.removeChild(loadingIndicator);

        messages.forEach(msg => {
            conversationDiv.appendChild(createMessageElement('bot', msg.message));
        });

    } catch (error) {
        console.error('Error:', error);
        conversationDiv.removeChild(loadingIndicator);
        conversationDiv.appendChild(createMessageElement('bot', "Désolé, il semblerait qu'une erreur est survenue..."));
    }
    
    conversationDiv.scrollTop = conversationDiv.scrollHeight;
}

document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const inputField = document.getElementById('user-input');
    const userInput = inputField.value.trim();
    
    if (userInput && !isBotResponding) {
        isBotResponding = true;
        inputField.value = '';
        await handleUserInput(userInput);
        isBotResponding = false;
    }
});