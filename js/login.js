// Login page functionality
function generateRoomId() {
    return 'room-' + Math.random().toString(36).substr(2, 9);
}

function getRoomIdFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get('room');
}

// Initialize page based on room parameter
function initializePage() {
    const roomId = getRoomIdFromUrl();
    if (!roomId) {
        document.getElementById('roomForm').classList.add('show');
    } else {
        document.getElementById('loginForm').classList.add('show');
    }
}

// Create Room flow
async function handleCreateRoom(e) {
    e.preventDefault();
    const username = document.getElementById('username-create').value.trim();
    if (!username) return;
    
    const newRoomId = generateRoomId();
    
    try {
        const resp = await fetch('api.php?action=login&room=' + encodeURIComponent(newRoomId), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: username, isAdmin: true })
        });
        
        const data = await resp.json();
        if (data.success) {
            localStorage.setItem('currentUser', JSON.stringify(data.user));
            localStorage.setItem('roomId', newRoomId);
            document.cookie = "currentUser=" + encodeURIComponent(JSON.stringify(data.user)) + "; path=/";
            window.location.href = 'room.php';
        } else {
            alert('Error creating room!');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error creating room!');
    }
}

// Join Room flow
async function handleJoinRoom(e) {
    e.preventDefault();
    const username = document.getElementById('username-login').value.trim();
    if (!username) return;
    
    const roomId = getRoomIdFromUrl();
    
    try {
        const resp = await fetch('api.php?action=login&room=' + encodeURIComponent(roomId), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: username, isAdmin: false })
        });
        
        const data = await resp.json();
        if (data.success) {
            localStorage.setItem('currentUser', JSON.stringify(data.user));
            localStorage.setItem('roomId', roomId);
            document.cookie = "currentUser=" + encodeURIComponent(JSON.stringify(data.user)) + "; path=/";
            window.location.href = 'index.php?room=' + encodeURIComponent(roomId);
        } else {
            alert('Eroare la autentificare!');
        }
    } catch (error) {
        console.error('Error joining room:', error);
        alert('Eroare la autentificare!');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
    
    // Add event listeners
    const roomForm = document.getElementById('roomForm');
    const loginForm = document.getElementById('loginForm');
    
    if (roomForm) {
        roomForm.addEventListener('submit', handleCreateRoom);
    }
    
    if (loginForm) {
        loginForm.addEventListener('submit', handleJoinRoom);
    }
});
