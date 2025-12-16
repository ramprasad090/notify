<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Real-Time Notification System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --border: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --pending-bg: #fef3c7;
            --pending-border: #fbbf24;
            --processed-bg: #d1fae5;
            --processed-border: #10b981;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background-color: var(--bg-primary);
            border-bottom: 1px solid var(--border);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.025em;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--text-muted);
        }

        .status-dot.connected {
            background-color: var(--success);
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
        }

        .card-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            padding: 0 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-warning {
            background-color: var(--pending-bg);
            color: #92400e;
        }

        .badge-success {
            background-color: var(--processed-bg);
            color: #065f46;
        }

        .messages-container {
            flex: 1;
            max-height: 600px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .messages-container::-webkit-scrollbar {
            width: 6px;
        }

        .messages-container::-webkit-scrollbar-track {
            background: var(--bg-secondary);
            border-radius: 3px;
        }

        .messages-container::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        .messages-container::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        .message-item {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .message-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            transition: all 0.3s ease;
        }

        .message-item.pending {
            background-color: #fffbeb;
            border-color: var(--pending-border);
        }

        .message-item.pending::before {
            background-color: var(--pending-border);
        }

        .message-item.processed {
            background-color: #f0fdf4;
            border-color: var(--processed-border);
        }

        .message-item.processed::before {
            background-color: var(--processed-border);
        }

        .message-item.new {
            animation: slideIn 0.4s ease-out;
        }

        .message-item.moving {
            animation: moveToLeft 0.6s ease-in-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes moveToLeft {
            0% {
                transform: translateX(0) scale(1);
            }
            50% {
                transform: translateX(-10px) scale(1.05);
            }
            100% {
                transform: translateX(0) scale(1);
            }
        }

        .message-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .message-sender {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary);
        }

        .message-time {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .message-content {
            color: var(--text-primary);
            font-size: 0.9375rem;
            word-wrap: break-word;
            margin-bottom: 0.5rem;
        }

        .message-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .message-status {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .message-status.pending {
            background-color: var(--pending-bg);
            color: #92400e;
        }

        .message-status.processed {
            background-color: var(--processed-bg);
            color: #065f46;
        }

        .message-status .spinner {
            width: 12px;
            height: 12px;
            border: 2px solid #fbbf24;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .form-card {
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            transition: all 0.2s;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea.form-input {
            resize: vertical;
            min-height: 120px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 0.9375rem;
            font-weight: 500;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--primary-dark);
            box-shadow: var(--shadow-md);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .empty-state-text {
            font-size: 0.9375rem;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .flow-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            gap: 1rem;
            background-color: var(--bg-secondary);
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .flow-arrow {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: bold;
        }

        .flow-step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkmark {
            color: var(--success);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">Real-Time Notifications</div>
            <div class="status-badge">
                <div class="status-dot" id="connectionStatus"></div>
                <span id="connectionText">Connecting...</span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Total Messages</div>
                <div class="stat-value" id="totalMessages">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Queue</div>
                <div class="stat-value" id="pendingMessages">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Processed</div>
                <div class="stat-value" id="processedMessages">0</div>
            </div>
        </div>

        <div class="flow-indicator">
            <div class="flow-step">
                <span>Send Message</span>
            </div>
            <span class="flow-arrow">→</span>
            <div class="flow-step">
                <span>📝 Pending (Right)</span>
            </div>
            <span class="flow-arrow">→</span>
            <div class="flow-step">
                <span>⚙️ Processing</span>
            </div>
            <span class="flow-arrow">→</span>
            <div class="flow-step">
                <span class="checkmark">✓</span>
                <span>Processed (Left)</span>
            </div>
        </div>

        <div class="main-grid">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="checkmark">✓</span>
                        Processed Messages
                        <span class="badge badge-success" id="processedBadge">0</span>
                    </h2>
                    <p class="card-subtitle">Successfully processed and broadcast</p>
                </div>
                <div class="messages-container" id="processedContainer">
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <div class="empty-state-text">No processed messages yet</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        ⏳ Pending Messages
                        <span class="badge badge-warning" id="pendingBadge">0</span>
                    </h2>
                    <p class="card-subtitle">Waiting for queue processing</p>
                </div>
                <div class="messages-container" id="pendingContainer">
                    <div class="empty-state">
                        <div class="empty-state-icon">📬</div>
                        <div class="empty-state-text">No pending messages</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <h2 class="card-title">Send New Message</h2>
                <p class="card-subtitle">Broadcast to all connected users</p>
            </div>

            <div id="alertContainer"></div>

            <form id="messageForm">
                <div class="form-group">
                    <label for="senderId" class="form-label">Sender ID</label>
                    <input
                        type="number"
                        id="senderId"
                        class="form-input"
                        placeholder="Enter your user ID"
                        required
                        min="1"
                    >
                </div>

                <div class="form-group">
                    <label for="message" class="form-label">Message</label>
                    <textarea
                        id="message"
                        class="form-input"
                        placeholder="Type your message here..."
                        required
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Send Message
                </button>
            </form>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Configuration
        const API_URL = '/api/messages';
        const PUSHER_KEY = '{{ env('VITE_PUSHER_APP_KEY') }}';
        const PUSHER_CLUSTER = '{{ env('VITE_PUSHER_APP_CLUSTER') }}';

        // State
        let pendingMessages = [];
        let processedMessages = [];
        let pusher = null;
        let channel = null;

        // DOM Elements
        const pendingContainer = document.getElementById('pendingContainer');
        const processedContainer = document.getElementById('processedContainer');
        const messageForm = document.getElementById('messageForm');
        const alertContainer = document.getElementById('alertContainer');
        const connectionStatus = document.getElementById('connectionStatus');
        const connectionText = document.getElementById('connectionText');
        const totalMessagesEl = document.getElementById('totalMessages');
        const pendingMessagesEl = document.getElementById('pendingMessages');
        const processedMessagesEl = document.getElementById('processedMessages');
        const pendingBadge = document.getElementById('pendingBadge');
        const processedBadge = document.getElementById('processedBadge');
        const submitBtn = document.getElementById('submitBtn');

        // Initialize Pusher
        function initPusher() {
            try {
                pusher = new Pusher(PUSHER_KEY, {
                    cluster: PUSHER_CLUSTER,
                    forceTLS: true
                });

                channel = pusher.subscribe('messages');

                pusher.connection.bind('connected', () => {
                    updateConnectionStatus(true);
                });

                pusher.connection.bind('disconnected', () => {
                    updateConnectionStatus(false);
                });

                pusher.connection.bind('error', (err) => {
                    console.error('Pusher connection error:', err);
                    updateConnectionStatus(false);
                });

                channel.bind('message.received', (data) => {
                    handleBroadcastMessage(data);
                });

            } catch (error) {
                console.error('Failed to initialize Pusher:', error);
                showAlert('Failed to connect to real-time service', 'error');
            }
        }

        // Update connection status
        function updateConnectionStatus(connected) {
            if (connected) {
                connectionStatus.classList.add('connected');
                connectionText.textContent = 'Connected';
            } else {
                connectionStatus.classList.remove('connected');
                connectionText.textContent = 'Disconnected';
            }
        }

        // Fetch existing messages
        async function fetchMessages() {
            try {
                const response = await fetch(API_URL);
                const result = await response.json();

                if (result.success && result.data.length > 0) {
                    // Separate pending and processed messages
                    result.data.forEach(msg => {
                        if (msg.is_processed) {
                            processedMessages.unshift(msg);
                        } else {
                            pendingMessages.unshift(msg);
                        }
                    });

                    renderMessages();
                }
            } catch (error) {
                console.error('Failed to fetch messages:', error);
            }
        }

        // Handle broadcast message (processed message from queue)
        function handleBroadcastMessage(messageData) {
            // Remove from pending if it exists
            const pendingIndex = pendingMessages.findIndex(m => m.id === messageData.id);
            if (pendingIndex !== -1) {
                pendingMessages.splice(pendingIndex, 1);
            }

            // Add to processed (check for duplicates)
            const existsInProcessed = processedMessages.some(m => m.id === messageData.id);
            if (!existsInProcessed) {
                processedMessages.unshift(messageData);
            }

            renderMessages(true);
        }

        // Add new pending message to state
        function addPendingMessage(messageData) {
            // Check if not already in pending
            const exists = pendingMessages.some(m => m.id === messageData.id);
            if (!exists) {
                pendingMessages.unshift(messageData);
                renderMessages();
            }
        }

        // Render messages in both containers
        function renderMessages(animate = false) {
            renderPendingMessages(animate);
            renderProcessedMessages(animate);
            updateStats();
        }

        // Render pending messages
        function renderPendingMessages(animate = false) {
            if (pendingMessages.length === 0) {
                pendingContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📬</div>
                        <div class="empty-state-text">No pending messages</div>
                    </div>
                `;
                return;
            }

            const html = pendingMessages.map((msg, index) => {
                const date = new Date(msg.created_at);
                const timeStr = date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                return `
                    <div class="message-item pending ${animate && index === 0 ? 'new' : ''}" data-id="${msg.id}">
                        <div class="message-header">
                            <span class="message-sender">User #${msg.sender_id}</span>
                            <span class="message-time">${timeStr}</span>
                        </div>
                        <div class="message-content">${escapeHtml(msg.message)}</div>
                        <div class="message-footer">
                            <span class="message-status pending">
                                <span class="spinner"></span>
                                Processing...
                            </span>
                        </div>
                    </div>
                `;
            }).join('');

            pendingContainer.innerHTML = html;
        }

        // Render processed messages
        function renderProcessedMessages(animate = false) {
            if (processedMessages.length === 0) {
                processedContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <div class="empty-state-text">No processed messages yet</div>
                    </div>
                `;
                return;
            }

            const html = processedMessages.map((msg, index) => {
                const date = new Date(msg.created_at);
                const timeStr = date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                return `
                    <div class="message-item processed ${animate && index === 0 ? 'moving' : ''}" data-id="${msg.id}">
                        <div class="message-header">
                            <span class="message-sender">User #${msg.sender_id}</span>
                            <span class="message-time">${timeStr}</span>
                        </div>
                        <div class="message-content">${escapeHtml(msg.message)}</div>
                        <div class="message-footer">
                            <span class="message-status processed">
                                <span class="checkmark">✓</span>
                                Processed
                            </span>
                        </div>
                    </div>
                `;
            }).join('');

            processedContainer.innerHTML = html;
        }

        // Update statistics
        function updateStats() {
            totalMessagesEl.textContent = pendingMessages.length + processedMessages.length;
            pendingMessagesEl.textContent = pendingMessages.length;
            processedMessagesEl.textContent = processedMessages.length;
            pendingBadge.textContent = pendingMessages.length;
            processedBadge.textContent = processedMessages.length;
        }

        // Handle form submission
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const senderId = document.getElementById('senderId').value;
            const message = document.getElementById('message').value;

            if (!senderId || !message) {
                showAlert('Please fill in all fields', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        sender_id: parseInt(senderId),
                        message: message
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Message sent and queued for processing!', 'success');
                    document.getElementById('message').value = '';

                    // Add to pending messages immediately
                    addPendingMessage(result.data);
                } else {
                    showAlert(result.errors ? Object.values(result.errors).flat().join(', ') : 'Failed to send message', 'error');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                showAlert('Failed to send message. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Message';
            }
        });

        // Show alert
        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);

            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize app
        document.addEventListener('DOMContentLoaded', () => {
            fetchMessages();
            initPusher();
        });
    </script>
</body>
</html>
