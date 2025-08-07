// Modal functionality for invite link
class InviteModal {
    constructor() {
        this.inviteBtn = document.getElementById('invite-btn');
        this.inviteModal = null;
        this.inviteLinkInput = document.getElementById('inviteLinkInput');
        this.copyBtn = document.getElementById('copyBtn');
        this.roomId = null;
        
        this.init();
    }
    
    init() {
        if (this.inviteBtn) {
            this.inviteModal = new bootstrap.Modal(document.getElementById('inviteModal'));
            this.setupEventListeners();
        }
    }
    
    setupEventListeners() {
        if (this.inviteBtn) {
            this.inviteBtn.addEventListener('click', () => this.showInviteModal());
        }
        
        if (this.copyBtn) {
            this.copyBtn.addEventListener('click', () => this.copyInviteLink());
        }
    }
    
    setRoomId(roomId) {
        this.roomId = roomId;
    }
    
    showInviteModal() {
        if (!this.roomId) {
            console.error('Room ID not set');
            return;
        }
        
        const inviteLink = window.location.origin + window.location.pathname.replace('index.php', 'login.php') + '?room=' + encodeURIComponent(this.roomId);
        this.inviteLinkInput.value = inviteLink;
        this.inviteModal.show();
    }
    
    async copyInviteLink() {
        try {
            this.inviteLinkInput.select();
            this.inviteLinkInput.setSelectionRange(0, 99999);
            await navigator.clipboard.writeText(this.inviteLinkInput.value);
            
            this.copyBtn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            setTimeout(() => {
                this.copyBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copy';
            }, 2000);
        } catch (error) {
            console.error('Failed to copy invite link:', error);
            // Fallback for older browsers
            this.inviteLinkInput.select();
            document.execCommand('copy');
        }
    }
}

// Initialize modal when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.inviteModal = new InviteModal();
});


