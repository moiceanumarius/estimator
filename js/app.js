// Generate Fibonacci numbers from 1 to 21, then add 40, 100 and coffee break
function getFibonacciUpTo100() {
    const fibs = [1];
    let a = 1, b = 2;
    while (a <= 21) {
        if (b <= 21) {
            fibs.push(b);
        }
        const next = a + b;
        a = b;
        b = next;
    }
    // Add additional values after 21
    fibs.push(40, 100, '☕');
    return fibs.filter((num, idx, arr) => arr.indexOf(num) === idx);
}

// Render Fibonacci buttons
function renderFibButtons(selected) {
    const fibs = getFibonacciUpTo100();
    const container = document.getElementById('fib-buttons');
    
    // Check if buttons already exist and have the correct state
    const existingButtons = container.querySelectorAll('.fib-button');
    const hasCorrectButtons = existingButtons.length === fibs.length;
    
    if (hasCorrectButtons) {
        // Update existing buttons instead of re-rendering
        existingButtons.forEach((btn, index) => {
            const num = fibs[index];
            const isSelected = selected === num;
            const isDisabled = votesRevealed; // Disable for all users when votes are revealed
            
            // Update button state
            btn.disabled = isDisabled;
            
            // Update button styling
            if (isDisabled) {
                // Disabled state styling for all users (including admin)
                btn.style.setProperty('background-color', '#f8f9fa', 'important');
                btn.style.setProperty('color', '#6c757d', 'important');
                btn.style.setProperty('opacity', '0.65', 'important');
                btn.style.setProperty('cursor', 'not-allowed', 'important');
                btn.style.transform = '';
            } else if (isSelected) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary', 'text-white', 'selected');
                btn.style.setProperty('background-color', '#375a7f', 'important');
                btn.style.setProperty('color', '#ffffff', 'important');
                btn.style.setProperty('opacity', '1', 'important');
                btn.style.setProperty('cursor', 'pointer', 'important');
                btn.style.transform = 'scale(1.08)';
                btn.dataset.hovered = 'false';
            } else {
                btn.classList.remove('btn-primary', 'text-white', 'selected');
                btn.classList.add('btn-outline-primary');
                if (btn.dataset.hovered !== 'true') {
                    btn.style.setProperty('background-color', '#ffffff', 'important');
                    btn.style.setProperty('color', '#375a7f', 'important');
                }
                btn.style.setProperty('opacity', '1', 'important');
                btn.style.setProperty('cursor', 'pointer', 'important');
                btn.style.transform = '';
            }
            
            // Update click handler for existing buttons
            btn.onclick = () => {
                if (!votesRevealed) { // Only allow clicks if votes are not revealed (all users)
                    selectFib(num);
                }
            };
            
            // Update hover event listeners for existing buttons
            btn.onmouseover = () => {
                if (isDisabled) return; // No hover effects for disabled buttons (all users)
                
                btn.style.transform = 'scale(1.08)';
                if (selected !== num) {
                    btn.style.setProperty('background-color', '#dee2e6', 'important');
                    btn.style.setProperty('color', '#375a7f', 'important');
                    btn.dataset.hovered = 'true';
                } else {
                    btn.style.setProperty('background-color', '#375a7f', 'important');
                    btn.style.setProperty('color', '#ffffff', 'important');
                }
            };
            
            btn.onmouseout = () => {
                if (isDisabled) return; // No hover effects for disabled buttons (all users)
                
                if (selected !== num) {
                    btn.style.transform = '';
                    btn.style.setProperty('background-color', '#ffffff', 'important');
                    btn.style.setProperty('color', '#375a7f', 'important');
                    btn.dataset.hovered = 'false';
                } else {
                    btn.style.transform = 'scale(1.08)';
                    btn.style.setProperty('background-color', '#375a7f', 'important');
                    btn.style.setProperty('color', '#ffffff', 'important');
                }
            };
        });
        return; // Exit early if buttons were updated
    }
    
    // If buttons don't exist or have wrong count, re-render completely
    container.innerHTML = '';
    
    fibs.forEach(num => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-lg mb-2 me-2 rounded-pill fib-button';
        
        // Special styling for coffee break button
        if (num === '☕') {
            btn.style.fontSize = '1.5rem';
            btn.title = 'Coffee Break';
        }
        
        btn.disabled = votesRevealed; // Disable for all users when votes are revealed
        
        if (votesRevealed) {
            // Disabled state styling for all users (including admin)
            btn.style.setProperty('background-color', '#f8f9fa', 'important');
            btn.style.setProperty('color', '#6c757d', 'important');
            btn.style.setProperty('opacity', '0.65', 'important');
            btn.style.setProperty('cursor', 'not-allowed', 'important');
        } else if (selected === num) {
            btn.classList.add('btn-primary', 'text-white', 'selected');
            btn.style.setProperty('background-color', '#375a7f', 'important');
            btn.style.setProperty('color', '#ffffff', 'important');
            btn.style.setProperty('opacity', '1', 'important');
            btn.style.setProperty('cursor', 'pointer', 'important');
        } else {
            btn.classList.add('btn-outline-primary');
            btn.style.setProperty('background-color', '#ffffff', 'important');
            btn.style.setProperty('color', '#375a7f', 'important');
            btn.style.setProperty('opacity', '1', 'important');
            btn.style.setProperty('cursor', 'pointer', 'important');
        }
        
        btn.textContent = num;
        btn.onclick = () => {
            if (!votesRevealed) { // Only allow clicks if votes are not revealed (all users)
                selectFib(num);
            }
        };
        
        // Add hover effects
        btn.onmouseover = () => {
            if (votesRevealed) return; // No hover effects for disabled buttons (all users)
            
            btn.style.transform = 'scale(1.08)';
            if (selected !== num) {
                btn.style.setProperty('background-color', '#dee2e6', 'important');
                btn.style.setProperty('color', '#375a7f', 'important');
                btn.dataset.hovered = 'true';
            } else {
                btn.style.setProperty('background-color', '#375a7f', 'important');
                btn.style.setProperty('color', '#ffffff', 'important');
            }
        };
        
        btn.onmouseout = () => {
            if (votesRevealed) return; // No hover effects for disabled buttons (all users)
            
            if (selected !== num) {
                btn.style.transform = '';
                btn.style.setProperty('background-color', '#ffffff', 'important');
                btn.style.setProperty('color', '#375a7f', 'important');
                btn.dataset.hovered = 'false';
            } else {
                btn.style.transform = 'scale(1.08)';
                btn.style.setProperty('background-color', '#375a7f', 'important');
                btn.style.setProperty('color', '#ffffff', 'important');
            }
        };
        
        if (selected === num && !votesRevealed) btn.style.transform = 'scale(1.08)';
        
        container.appendChild(btn);
    });
}

function getRoomId() {
    const params = new URLSearchParams(window.location.search);
    let roomId = params.get('room') || localStorage.getItem('roomId');
    if (!roomId) {
        window.location.href = 'login.php';
    }
    localStorage.setItem('roomId', roomId);
    return roomId;
}

// Global variables
const roomId = getRoomId();
let currentUser = null;
let users = [];
let votesRevealed = false;
let selectedVote = null;
let hoverStates = new Map();
let websocket = null;
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;

// WebSocket connection management
function connectWebSocket() {
    // Only connect if we have a currentUser
    if (!currentUser || !currentUser.name) {
        console.log('WebSocket connection delayed - waiting for user initialization');
        console.log('currentUser:', currentUser);
        return;
    }
    
    const host = window.location.hostname;
    const isHttps = window.location.protocol === 'https:';
    const protocol = isHttps ? 'wss:' : 'ws:';
    const wsPath = isHttps ? '/ws' : `:8080`;
    const wsUrl = isHttps
        ? `${protocol}//${host}${wsPath}?room=${encodeURIComponent(roomId)}&user=${encodeURIComponent(currentUser.name)}`
        : `${protocol}//${host}${wsPath}?room=${encodeURIComponent(roomId)}&user=${encodeURIComponent(currentUser.name)}`;
    
    console.log('Attempting to connect to WebSocket:', wsUrl);
    console.log('roomId:', roomId);
    console.log('currentUser:', currentUser);
    console.log('host:', host);
    console.log('protocol:', protocol);
    console.log('Using protocol:', protocol, 'path:', wsPath);
    
    try {
        websocket = new WebSocket(wsUrl);
        
        websocket.onopen = () => {
            console.log('WebSocket connected for user:', currentUser.name);
            reconnectAttempts = 0;
            
            // Start heartbeat to keep connection alive
            startHeartbeat();
        };
        
        websocket.onmessage = (event) => {
            console.log('WebSocket message received:', event.data);
            try {
                const data = JSON.parse(event.data);
                handleWebSocketMessage(data);
            } catch (error) {
                console.error('Error parsing WebSocket message:', error);
            }
        };
        
        websocket.onclose = (event) => {
            console.log('WebSocket disconnected:', event.code, event.reason);
            
            // Stop heartbeat
            stopHeartbeat();
            
            // Handle different close codes
            if (event.code === 1000) {
                console.log('WebSocket closed normally');
                return;
            }
            
            if (event.code === 1006) {
                console.log('WebSocket connection lost unexpectedly');
            }
            
            if (reconnectAttempts < maxReconnectAttempts) {
                reconnectAttempts++;
                const delay = Math.min(2000 * Math.pow(2, reconnectAttempts - 1), 10000); // Exponential backoff, max 10s
                console.log(`Attempting to reconnect in ${delay}ms (${reconnectAttempts}/${maxReconnectAttempts})...`);
                setTimeout(connectWebSocket, delay);
            } else {
                console.log('Max reconnection attempts reached, falling back to polling');
                startPolling();
            }
        };
        
        websocket.onerror = (error) => {
            console.error('WebSocket error:', error);
            // Don't close here, let onclose handle reconnection
        };
        
    } catch (error) {
        console.error('Failed to create WebSocket connection:', error);
        startPolling();
    }
}

// Heartbeat management
let heartbeatInterval = null;
let heartbeatTimeout = null;

function startHeartbeat() {
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
    }
    
    // Send ping every 25 seconds (server sends heartbeat every 30s)
    heartbeatInterval = setInterval(() => {
        if (websocket && websocket.readyState === WebSocket.OPEN) {
            try {
                websocket.send(JSON.stringify({
                    type: 'ping',
                    timestamp: Date.now()
                }));
                
                // Set timeout for pong response
                if (heartbeatTimeout) {
                    clearTimeout(heartbeatTimeout);
                }
                
                heartbeatTimeout = setTimeout(() => {
                    console.log('Heartbeat timeout - reconnecting...');
                    if (websocket) {
                        websocket.close(1000, 'Heartbeat timeout');
                    }
                }, 10000); // 10 second timeout
                
            } catch (error) {
                console.error('Error sending heartbeat:', error);
            }
        }
    }, 25000);
}

function stopHeartbeat() {
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
        heartbeatInterval = null;
    }
    if (heartbeatTimeout) {
        clearTimeout(heartbeatTimeout);
        heartbeatTimeout = null;
    }
}

// Helper function to ensure WebSocket is connected before sending messages
async function ensureWebSocketConnection() {
    if (!websocket || websocket.readyState !== WebSocket.OPEN) {
        console.log('WebSocket not connected, attempting to reconnect...');
        console.log('Current WebSocket state:', websocket?.readyState);
        
        // Reset reconnect attempts to allow immediate reconnection
        reconnectAttempts = 0;
        
        // Try to connect immediately
        connectWebSocket();
        
        // Wait a bit for the connection to establish
        let attempts = 0;
        const maxWaitAttempts = 20; // Increased wait time
        
        while (attempts < maxWaitAttempts) {
            if (websocket && websocket.readyState === WebSocket.OPEN) {
                console.log('WebSocket reconnected successfully');
                return true;
            }
            
            await new Promise(resolve => setTimeout(resolve, 500));
            attempts++;
        }
        
        console.log('Failed to reconnect WebSocket after', maxWaitAttempts, 'attempts');
        return false;
    }
    
    return true;
}

// Helper function to send WebSocket message with automatic reconnection
async function sendWebSocketMessage(message) {
    if (await ensureWebSocketConnection()) {
        try {
            console.log('Sending WebSocket message:', message);
            websocket.send(JSON.stringify(message));
            return true;
        } catch (error) {
            console.error('Error sending WebSocket message:', error);
            return false;
        }
    } else {
        console.log('Could not send WebSocket message - connection failed');
        return false;
    }
}

function handleWebSocketMessage(data) {
    console.log('Handling WebSocket message:', data);
    
    // Handle heartbeat responses
    if (data.type === 'pong') {
        if (heartbeatTimeout) {
            clearTimeout(heartbeatTimeout);
            heartbeatTimeout = null;
        }
        console.log('Heartbeat response received');
        return;
    }
    
    if (data.type === 'heartbeat') {
        // Respond to server heartbeat
        if (websocket && websocket.readyState === WebSocket.OPEN) {
            try {
                websocket.send(JSON.stringify({
                    type: 'pong',
                    timestamp: Date.now()
                }));
            } catch (error) {
                console.error('Error responding to heartbeat:', error);
            }
        }
        return;
    }
    
    if (data.type === 'connection_established') {
        console.log('WebSocket connection established:', data.payload);
        return;
    }
    
    if (data.type === 'connection_timeout') {
        console.log('Connection timeout, reconnecting...');
        if (websocket) {
            websocket.close(1000, 'Connection timeout');
        }
        return;
    }
    
    if (data.type === 'error') {
        console.error('WebSocket server error:', data.payload);
        return;
    }
    
    switch (data.type) {
        case 'votes_update':
            console.log('Processing votes_update message');
            updateVotesAndUsers(data.payload);
            break;
        case 'user_joined':
            console.log('Processing user_joined message');
            // If we have users data in the payload, use it directly
            if (data.payload && data.payload.users) {
                console.log('Using users data from payload');
                updateVotesAndUsers({
                    votes: data.payload.users,
                    revealed: votesRevealed
                });
            } else {
                console.log('Fetching full state for user_joined');
                // Otherwise fetch the full state
                fetchVotesAndUsers();
            }
            break;
        case 'user_left':
            console.log('Processing user_left message');
            fetchVotesAndUsers(); // Refresh full state
            break;
        case 'vote_revealed':
            console.log('Processing vote_revealed message');
            votesRevealed = true;
            renderFibButtons(selectedVote);
            renderUsers();
            setupFlipButton();
            break;
        case 'vote_reset':
            console.log('Processing vote_reset message');
            votesRevealed = false;
            // Clear local selection and local users' votes immediately
            selectedVote = null;
            users = (users || []).map(u => ({ ...u, vote: null, hasVoted: false }));
            renderFibButtons(selectedVote);
            renderUsers();
            setupFlipButton();
            break;
        case 'user_removed':
            console.log('Processing user_removed message');
            if (data.payload.userId === currentUser?.id || data.payload.userName === currentUser?.name) {
                alert('You have been removed from the room by the admin.');
                // Clear local/session state and close WS
                try {
                    localStorage.removeItem('currentUser');
                    localStorage.removeItem('roomId');
                    document.cookie = "currentUser=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    document.cookie = "PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    if (websocket) { websocket.close(1000, 'Removed by admin'); websocket = null; }
                } catch {}
                window.location.href = 'index.php';
            } else {
                fetchVotesAndUsers();
            }
            break;
        default:
            console.log('Unknown WebSocket message type:', data.type);
    }
}

function updateVotesAndUsers(data) {
    users = (data.votes || []).map(u => ({
        ...u,
        hasVoted: (u.hasVoted === true) || (u.vote !== null && u.vote !== undefined)
    }));
    const newVotesRevealed = data.revealed;
    
    // Update currentUser with real role from users[] and in localStorage
    const me = users.find(u => u.id === currentUser?.id);
    if (me) {
        currentUser = { name: me.name, isAdmin: me.isAdmin, id: me.id };
        localStorage.setItem('currentUser', JSON.stringify(me));
    } else {
        // User not found in room - this could happen if the user just joined
        // and the list hasn't been updated yet, so don't close the connection
        console.log('User not found in current users list, but keeping connection open');
        // Only close connection if we're sure the user was removed by admin
        // This will be handled by the user_removed message
    }
    
    const newSelectedVote = me && me.vote !== null ? me.vote : null;
    
    // Check if this is the first load or if values have actually changed
    const isFirstLoad = selectedVote === null;
    const voteChanged = selectedVote !== newSelectedVote;
    const revealChanged = votesRevealed !== newVotesRevealed;
    
    if (isFirstLoad || voteChanged || revealChanged) {
        selectedVote = newSelectedVote;
        votesRevealed = newVotesRevealed;
        renderFibButtons(selectedVote);
    } else {
        selectedVote = newSelectedVote;
        votesRevealed = newVotesRevealed;
    }
    
    renderUsers();
    setupFlipButton();
    
    // Update user name and type in navbar
    const currentUserNameSpan = document.getElementById('current-user-name');
    const currentUserTypeSpan = document.getElementById('current-user-type');
    if (currentUserNameSpan) {
        currentUserNameSpan.textContent = currentUser.name;
    }
    if (currentUserTypeSpan) {
        if (currentUser.isAdmin) {
            currentUserTypeSpan.innerHTML = '<span class="badge bg-warning text-dark ms-2"><i class="bi bi-award-fill"></i> admin</span>';
        } else {
            currentUserTypeSpan.innerHTML = '<span class="badge bg-secondary ms-2">user</span>';
        }
    }
    
    // Update invite button visibility for admin
    const inviteBtn = document.getElementById('invite-btn');
    if (inviteBtn) {
        if (currentUser.isAdmin) {
            inviteBtn.style.display = 'inline-block';
        } else {
            inviteBtn.style.display = 'none';
        }
    }
    
    // Update invite modal room ID
    if (window.inviteModal) {
        window.inviteModal.setRoomId(roomId);
    }
}

// Fallback to polling if WebSocket fails
function startPolling() {
    console.log('Starting polling fallback');
    if (websocket) {
        websocket.close();
        websocket = null;
    }
    
    // Set up polling for live updates
    const pollingInterval = setInterval(fetchVotesAndUsers, 2000);
    
    // Store interval reference for cleanup
    window.pollingInterval = pollingInterval;
}

async function getSessionUser() {
    try {
        const resp = await fetch('api.php?action=session-user', { cache: 'no-store' });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const text = await resp.text();
        let data;
        try { data = JSON.parse(text); } catch (e) {
            console.error('Invalid JSON from session-user:', text);
            throw e;
        }
        if (data && data.user) {
            return data.user;
        }
    } catch (e) {}
    return null;
}

// Initialize user from session (no longer using localStorage for authentication)
async function initializeUser() {
    const user = await getSessionUser();
    if (!user) {
        if (localStorage.getItem('justLoggedOut')) {
            localStorage.removeItem('justLoggedOut');
            window.location.href = 'index.php';
            return; // Stop execution here
        }
        if (!window.location.pathname.endsWith('index.php')) {
            alert('You are not authenticated or have been removed from the room.');
            document.cookie = "PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            window.location.href = 'index.php';
            return; // Stop execution here
        }
        throw new Error('User not authenticated');
    }
    currentUser = { name: user.name, isAdmin: user.isAdmin, id: user.id };
}

async function fetchVotesAndUsers() {
    try {
        // First check if user is still authenticated
        const sessionUser = await getSessionUser();
        if (!sessionUser) {
            // User is no longer authenticated, stop polling and redirect
            if (websocket) {
                websocket.close();
                websocket = null;
            }
            
            // Check if this is a logout to avoid showing "removed by admin" alert
            if (!localStorage.getItem('justLoggedOut')) {
                localStorage.removeItem('currentUser');
                localStorage.removeItem('roomId');
                document.cookie = "currentUser=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie = "PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            }
            window.location.href = 'index.php';
            return; // Stop execution here
        }
        
        const url = 'api.php?action=votes&room=' + encodeURIComponent(roomId) + '&user=' + encodeURIComponent(currentUser.name);
        const resp = await fetch(url, { cache: 'no-store' });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const text = await resp.text();
        let data;
        try { data = JSON.parse(text); } catch (e) {
            console.error('Invalid JSON from votes endpoint. Raw response:', text);
            throw e;
        }
        
        users = (data.votes || []).map(u => ({
            ...u,
            hasVoted: (u.hasVoted === true) || (u.vote !== null && u.vote !== undefined)
        }));
        const newVotesRevealed = data.revealed;
        
        // Update currentUser with real role from users[] and in localStorage
        const me = users.find(u => u.id === currentUser.id);
        if (me) {
            currentUser = { name: me.name, isAdmin: me.isAdmin, id: me.id };
            localStorage.setItem('currentUser', JSON.stringify(me));
        } else {
            // User not found in room - this could happen if the user just joined
            // and the list hasn't been updated yet, so don't close the connection
            console.log('User not found in current users list during fetchVotesAndUsers, but keeping connection open');
            // Only close connection if we're sure the user was removed by admin
            // This will be handled by the user_removed message
        }
        
        const newSelectedVote = me && me.vote !== null ? me.vote : null;
        
        // Check if this is the first load or if values have actually changed
        const isFirstLoad = selectedVote === null;
        const voteChanged = selectedVote !== newSelectedVote;
        const revealChanged = votesRevealed !== newVotesRevealed;
        
        if (isFirstLoad || voteChanged || revealChanged) {
            selectedVote = newSelectedVote;
            votesRevealed = newVotesRevealed;
            renderFibButtons(selectedVote);
        } else {
            selectedVote = newSelectedVote;
            votesRevealed = newVotesRevealed;
        }
        
        renderUsers();
        setupFlipButton();
        
        // Update user name and type in navbar
        const currentUserNameSpan = document.getElementById('current-user-name');
        const currentUserTypeSpan = document.getElementById('current-user-type');
        if (currentUserNameSpan) {
            currentUserNameSpan.textContent = currentUser.name;
        }
        if (currentUserTypeSpan) {
            if (currentUser.isAdmin) {
                currentUserTypeSpan.innerHTML = '<span class="badge bg-warning text-dark ms-2"><i class="bi bi-award-fill"></i> admin</span>';
            } else {
                currentUserTypeSpan.innerHTML = '<span class="badge bg-secondary ms-2">user</span>';
            }
        }
        
        // Update invite button visibility for admin
        const inviteBtn = document.getElementById('invite-btn');
        if (inviteBtn) {
            if (currentUser.isAdmin) {
                inviteBtn.style.display = 'inline-block';
            } else {
                inviteBtn.style.display = 'none';
            }
        }
        
        // Update invite modal room ID
        if (window.inviteModal) {
            window.inviteModal.setRoomId(roomId);
        }
        
    } catch (error) {
        console.error('Error fetching votes and users:', error);
    }
}

function renderVotingStats() {
    const statsContainer = document.getElementById('stats-container');
    const votingStats = document.getElementById('voting-stats');
    
    if (!votesRevealed) {
        votingStats.style.display = 'none';
        return;
    }
    
    // Calculate statistics
    const voteCounts = {};
    let totalVotes = 0;
    
    users.forEach(user => {
        if (user.vote !== null) {
            voteCounts[user.vote] = (voteCounts[user.vote] || 0) + 1;
            totalVotes++;
        }
    });
    
    if (totalVotes === 0) {
        votingStats.style.display = 'none';
        return;
    }
    
    votingStats.style.display = 'block';
    statsContainer.innerHTML = '';
    
    // Sort votes
    const sortedVotes = Object.keys(voteCounts).sort((a, b) => parseInt(a) - parseInt(b));
    
    sortedVotes.forEach(vote => {
        const count = voteCounts[vote];
        const percentage = ((count / totalVotes) * 100).toFixed(1);
        
        const statRow = document.createElement('div');
        statRow.className = 'mb-2';
        
        const label = document.createElement('div');
        label.className = 'd-flex justify-content-between align-items-center mb-1';
        label.innerHTML = `
            <span class="fw-semibold">${vote}</span>
            <span class="text-muted small">${count} vote${count > 1 ? 's' : ''} (${percentage}%)</span>
        `;
        
        const progressBar = document.createElement('div');
        progressBar.className = 'progress stats-progress-bar';
        progressBar.innerHTML = `
            <div class="progress-bar bg-primary" role="progressbar" style="width: ${percentage}%" 
                 aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100"></div>
        `;
        
        statRow.appendChild(label);
        statRow.appendChild(progressBar);
        statsContainer.appendChild(statRow);
    });
}

function renderUsers() {
    const usersDiv = document.getElementById('users');
    const currentHoverStates = new Map();
    
    // Clear existing content before adding new users
    usersDiv.innerHTML = '';
    
    // Sort users to show admin first
    const sortedUsers = [...users].sort((a, b) => {
        // Admin comes first (true = 1, false = 0, so we reverse the order)
        if (a.isAdmin && !b.isAdmin) return -1;
        if (!a.isAdmin && b.isAdmin) return 1;
        return 0; // Keep original order for users of same type
    });
    
    sortedUsers.forEach(user => {
        const row = document.createElement('div');
        row.className = 'd-flex align-items-center justify-content-between mb-2 position-relative user-row';
        row.setAttribute('data-username', user.name);
        
        const leftSide = document.createElement('div');
        leftSide.className = 'd-flex align-items-center';
        
        // Add voting dot (based on vote presence)
        if (user.vote !== null && user.vote !== undefined) {
            const dot = document.createElement('span');
            dot.className = 'me-2';
            dot.innerHTML = '<span class="badge bg-success rounded-circle" style="width:14px;height:14px;display:inline-block;"></span>';
            leftSide.appendChild(dot);
        } else {
            const dot = document.createElement('span');
            dot.className = 'me-2';
            dot.innerHTML = '<span style="width:14px;height:14px;display:inline-block;"></span>';
            leftSide.appendChild(dot);
        }
        
        // Add small user icon before name (yellow for admin, grey for users)
        const userIcon = document.createElement('i');
        userIcon.className = `bi bi-person-fill me-2 ${user.isAdmin ? 'text-warning' : 'text-secondary'} fs-6`;
        leftSide.appendChild(userIcon);

        // Add user name
        const nameSpan = document.createElement('span');
        nameSpan.textContent = user.name;
        nameSpan.className = 'fw-semibold';
        leftSide.appendChild(nameSpan);
        
        // Add admin/user badge
        if (user.isAdmin) {
            const crown = document.createElement('span');
            crown.className = 'ms-2';
            crown.innerHTML = '<span class="badge bg-warning text-dark"><i class="bi bi-award-fill"></i> admin</span>';
            leftSide.appendChild(crown);
        } else {
            const badge = document.createElement('span');
            badge.className = 'ms-2';
            badge.innerHTML = '<span class="badge bg-secondary">user</span>';
            leftSide.appendChild(badge);
        }
        
        // Add "Set admin" button for admin (to promote other users)
        if (currentUser.isAdmin && user.id !== currentUser.id && !user.isAdmin) {
            const promoteBtn = document.createElement('button');
            promoteBtn.className = 'btn btn-outline-warning btn-sm ms-2 user-promote-btn';
            promoteBtn.innerHTML = '<i class="bi bi-arrow-up-circle"></i> Set admin';
            promoteBtn.title = 'Promote user to admin';
            promoteBtn.style.fontSize = '0.75rem';
            promoteBtn.setAttribute('data-username', user.name);
            
            // Check if user was in hover state before update
            const wasHovered = hoverStates.get(user.name);
            promoteBtn.style.opacity = wasHovered ? '1' : '0';
            
            promoteBtn.onclick = async (e) => {
                e.stopPropagation();
                if (confirm(`Promote ${user.name} to admin? You will become a regular user.`)) {
                    try {
                        const response = await fetch('api.php?action=promote-user&room=' + encodeURIComponent(roomId), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ 
                                newAdminId: user.id, 
                                currentAdminId: currentUser.id,
                                room: roomId 
                            })
                        });
                        if (response.ok) {
                            // Refresh the page to get the latest settings
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error promoting user:', error);
                    }
                }
            };
            
            leftSide.appendChild(promoteBtn);
        }
        
        // Add remove button for admin
        let removeBtn = null;
        if (currentUser.isAdmin && user.id !== currentUser.id) {
            removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-outline-danger ms-2 user-remove-btn';
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.title = 'Remove user from room';
            removeBtn.setAttribute('data-username', user.name);
            
            // Check if user was in hover state before update
            const wasHovered = hoverStates.get(user.name);
            removeBtn.style.opacity = wasHovered ? '1' : '0';
            
            removeBtn.onclick = async (e) => {
                e.stopPropagation();
                if (confirm(`Remove ${user.name} from the room?`)) {
                    try {
                        const response = await fetch('api.php?action=logout&room=' + encodeURIComponent(roomId), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: user.id, room: roomId })
                        });
                        if (response.ok) {
                            // Notify others via WebSocket as well
                            await sendWebSocketMessage({
                                type: 'remove_user',
                                payload: { userName: user.name, userId: user.id }
                            });
                            fetchVotesAndUsers();
                        }
                    } catch (err) {
                        console.error('Error removing user:', err);
                    }
                }
            };
            
            leftSide.appendChild(removeBtn);
        }
        
        // Add hover event listeners if user has admin action buttons
        if (currentUser.isAdmin && user.id !== currentUser.id) {
            const promoteBtn = leftSide.querySelector('.user-promote-btn');
            
            row.onmouseenter = () => {
                if (promoteBtn) promoteBtn.style.opacity = '1';
                if (removeBtn) removeBtn.style.opacity = '1';
                currentHoverStates.set(user.name, true);
            };
            
            row.onmouseleave = () => {
                if (promoteBtn) promoteBtn.style.opacity = '0';
                if (removeBtn) removeBtn.style.opacity = '0';
                currentHoverStates.set(user.name, false);
            };
        }
        
        row.appendChild(leftSide);
        
        // Add vote value if revealed
        if (votesRevealed && user.vote !== null) {
            const vote = document.createElement('span');
            vote.className = 'fs-5 text-primary fw-bold';
            vote.textContent = user.vote;
            row.appendChild(vote);
        } else {
            const emptySpace = document.createElement('span');
            emptySpace.style.width = '40px';
            row.appendChild(emptySpace);
        }
        
        usersDiv.appendChild(row);
    });
    
    // Update global hover state
    hoverStates = currentHoverStates;
    
    // Render voting statistics after users are displayed
    renderVotingStats();
}

async function selectFib(num) {
    // Prevent voting if votes are revealed
    if (votesRevealed) {
        console.log('Voting is disabled - votes have been revealed');
        return;
    }
    
    try {
        console.log('Sending vote:', num, 'for user:', currentUser.name);
        await fetch('api.php?action=vote&room=' + encodeURIComponent(roomId), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: currentUser.id, vote: num, room: roomId })
        });
        
        selectedVote = num;
        renderFibButtons(selectedVote); // Update immediately for better UX
        
        // Update the user's vote in the local users array for immediate feedback
        const me = users.find(u => u.id === currentUser.id);
        if (me) {
            me.vote = num;
            renderUsers(); // Update user list immediately
            setupFlipButton(); // Reevaluate flip state
        }
        
        // Send WebSocket message to notify other users with automatic reconnection
        const message = {
            type: 'vote',
            userId: currentUser.id,
            vote: num
        };
        await sendWebSocketMessage(message);
        
    } catch (error) {
        console.error('Error selecting Fibonacci number:', error);
    }
}

function setupFlipButton() {
    const flipSection = document.getElementById('flip-section');
    const flipBtn = document.getElementById('flip-btn');
    const resetBtn = document.getElementById('reset-btn');
    
        if (currentUser && currentUser.isAdmin) {
        flipSection.style.display = 'flex';
        
        const updateFlipReset = () => {
            // Disable flip if no one has voted
            const anyVote = users.some(u => u.hasVoted);
            flipBtn.disabled = !anyVote;
            if (votesRevealed) {
                flipBtn.style.display = 'none';
                resetBtn.style.display = 'inline-block';
            } else {
                flipBtn.style.display = 'inline-block';
                resetBtn.style.display = 'none';
            }
        };
        
        updateFlipReset();
        
        flipBtn.onclick = async () => {
            try {
                // Immediately disable all Fibonacci buttons for admin
                votesRevealed = true;
                renderFibButtons(selectedVote); // Force immediate update of buttons
                
                await fetch('api.php?action=flip&room=' + encodeURIComponent(roomId), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room: roomId })
                });
                
                // Send WebSocket message to notify other users with automatic reconnection
                await sendWebSocketMessage({
                    type: 'flip'
                });
                
                updateFlipReset();
                fetchVotesAndUsers();
            } catch (error) {
                console.error('Error flipping votes:', error);
                // Revert if there was an error
                votesRevealed = false;
                renderFibButtons(selectedVote);
            }
        };
        
        resetBtn.onclick = async () => {
            try {
                // Immediately enable all Fibonacci buttons for admin
                votesRevealed = false;
                selectedVote = null;
                // Clear local user votes to instantly update UI
                users = (users || []).map(u => ({ ...u, vote: null, hasVoted: false }));
                renderFibButtons(selectedVote); // Force immediate update of buttons
                renderUsers();
                
                await fetch('api.php?action=resetflip&room=' + encodeURIComponent(roomId), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room: roomId })
                });
                
                // Send WebSocket message to notify other users with automatic reconnection
                await sendWebSocketMessage({
                    type: 'reset'
                });
                
                updateFlipReset();
                fetchVotesAndUsers();
            } catch (error) {
                console.error('Error resetting votes:', error);
                // Revert if there was an error
                votesRevealed = true;
                renderFibButtons(selectedVote);
            }
        };
    } else {
        flipSection.style.display = 'none';
        if (flipBtn) flipBtn.style.display = 'none';
        if (resetBtn) resetBtn.style.display = 'none';
    }
}

// Initialize application
function initializeApp() {
    initializeUser().then(() => {
        fetchVotesAndUsers();
        
        // Set up WebSocket for real-time updates (after user is initialized)
        connectWebSocket();
        
        // Set up logout functionality
        const logoutLink = document.getElementById('logout-link');
        if (logoutLink) {
            logoutLink.onclick = async function(e) {
                e.preventDefault();
                try {
                    await fetch('api.php?action=logout&room=' + encodeURIComponent(roomId), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: currentUser.id, room: roomId })
                    });
                } catch (error) {
                    console.error('Error during logout:', error);
                } finally {
                    // Stop WebSocket before logout
                    if (websocket) {
                        websocket.close(1000, 'User logout');
                        websocket = null;
                    }
                    
                    // Stop heartbeat
                    stopHeartbeat();
                    
                    // Stop polling if it was started as fallback
                    if (window.pollingInterval) {
                        clearInterval(window.pollingInterval);
                        window.pollingInterval = null;
                    }
                    
                    localStorage.setItem('justLoggedOut', '1');
                    localStorage.removeItem('currentUser');
                    localStorage.removeItem('roomId');
                    document.cookie = "currentUser=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    document.cookie = "PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    window.location.href = 'index.php';
                }
            };
        }
    }).catch((error) => {
        // Handle errors from initializeUser (like redirects)
        if (error.message !== 'User not authenticated' && error.message !== 'User just logged out') {
            console.error('Error initializing app:', error);
        }
    });
}

// Start the application when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeApp);
