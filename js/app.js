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
        
        if (selected === num) {
            btn.classList.add('btn-primary', 'text-white', 'selected');
        } else {
            btn.classList.add('btn-outline-primary');
            btn.style.background = '#fff';
        }
        
        btn.textContent = num;
        btn.onclick = () => selectFib(num);
        btn.disabled = votesRevealed;
        
        // Add hover effects
        btn.onmouseover = () => {
            btn.style.transform = 'scale(1.08)';
            if (selected !== num) btn.style.background = '#dee2e6';
        };
        
        btn.onmouseout = () => {
            btn.style.transform = selected === num ? 'scale(1.08)' : '';
            if (selected !== num) btn.style.background = '#fff';
        };
        
        if (selected === num) btn.style.transform = 'scale(1.08)';
        
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
let pollingInterval = null;

async function getSessionUser() {
    try {
        const resp = await fetch('api.php?action=session-user');
        const data = await resp.json();
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
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
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
        
        const resp = await fetch('api.php?action=votes&room=' + encodeURIComponent(roomId) + '&user=' + encodeURIComponent(currentUser.name));
        const data = await resp.json();
        
        users = data.votes || [];
        votesRevealed = data.revealed;
        
        // Update currentUser with real role from users[] and in localStorage
        const me = users.find(u => u.id === currentUser.id);
        if (me) {
            currentUser = { name: me.name, isAdmin: me.isAdmin, id: me.id };
            localStorage.setItem('currentUser', JSON.stringify(me));
        } else {
            // User not found in room (removed by admin)
            // Stop polling to prevent infinite loop
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
            
            localStorage.removeItem('currentUser');
            localStorage.removeItem('roomId');
            document.cookie = "currentUser=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            alert('You have been removed from the room by the admin.');
            window.location.href = 'index.php';
            return; // Stop execution here
        }
        
        selectedVote = me && me.vote !== null ? me.vote : null;
        renderFibButtons(selectedVote);
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
        
        // Add voting dot
        if (user.hasVoted) {
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
                    const response = await fetch('api.php?action=logout&room=' + encodeURIComponent(roomId), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: user.id, room: roomId })
                    });
                    if (response.ok) {
                        fetchVotesAndUsers();
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
    try {
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
            me.hasVoted = true;
            renderUsers(); // Update user list immediately
        }
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
                await fetch('api.php?action=flip&room=' + encodeURIComponent(roomId), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room: roomId })
                });
                votesRevealed = true;
                updateFlipReset();
                fetchVotesAndUsers();
            } catch (error) {
                console.error('Error flipping votes:', error);
            }
        };
        
        resetBtn.onclick = async () => {
            try {
                await fetch('api.php?action=resetflip&room=' + encodeURIComponent(roomId), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room: roomId })
                });
                votesRevealed = false;
                updateFlipReset();
                fetchVotesAndUsers();
            } catch (error) {
                console.error('Error resetting votes:', error);
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
        
        // Set up polling for live updates
        pollingInterval = setInterval(fetchVotesAndUsers, 2000);
        
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
                    // Stop polling before logout
                    if (pollingInterval) {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
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
