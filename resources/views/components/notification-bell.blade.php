<div x-data="notificationBell()" x-init="init()" class="notification-bell">
    <!-- Icona SVG campanellina -->
    <button 
        @click="openPanel()"
        class="btn btn-link position-relative p-0"
        style="font-size: 1rem; color: #333; border: none; background: none; cursor: pointer; line-height: 1;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span 
            x-show="unreadCountValue > 0"
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
            style="font-size: 0.6rem; padding: 0.35rem 0.5rem;">
            <span x-text="unreadCountValue"></span>
        </span>
    </button>

    <!-- Pannello Laterale Notifiche -->
    <div 
        class="notification-panel"
        x-bind:class="{ 'show': panelOpen }"
        x-show="panelOpen"
        @click.away="closePanel()">
        
        <!-- Header -->
        <div class="notification-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-size: 1.25rem; font-weight: 600; color: #333;">Notifiche</h5>
                <button 
                    type="button" 
                    @click="closePanel()"
                    class="btn-close" 
                    aria-label="Close"></button>
            </div>
        </div>

        <!-- Body -->
        <div class="offcanvas-body p-0" style="overflow-y: auto; height: calc(100% - 130px);">
            <!-- Empty State -->
            <template x-if="notifications.length === 0">
                <div class="text-center py-5 text-muted" style="padding: 3rem 2rem;">
                    <p style="font-size: 0.95rem;">Nessuna notifica al momento</p>
                </div>
            </template>

            <!-- Lista Notifiche -->
            <div class="list-group list-group-flush">
                <template x-for="notification in notifications" :key="notification.id">
                    <div 
                        class="list-group-item list-group-item-action"
                        x-bind:class="{ 'list-group-item-light': notification.is_read }"
                        @click="openNotification(notification)"
                        style="cursor: pointer; border-left: 4px solid #0d6efd; transition: all 0.2s;">
                        
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex: 1;">
                                <h6 class="mb-1" x-text="notification.title"></h6>
                                <p class="mb-2 small text-dark" x-text="notification.message"></p>
                                <small class="text-muted" x-text="formatDate(notification.created_at)"></small>
                            </div>
                            <button 
                                @click.stop="deleteNotification(notification.id)"
                                type="button"
                                class="btn-close btn-sm ms-2"
                                style="margin-top: 2px;">
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <div class="notification-footer" x-show="notifications.length > 0">
            <button 
                @click="markAllAsRead()" 
                type="button"
                class="btn btn-outline-primary w-100 btn-sm">
                Segna tutte come lette
            </button>
        </div>
    </div>

    <!-- Backdrop Overlay -->
    <div 
        x-show="panelOpen"
        @click="closePanel()"
        style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); z-index: 1040; cursor: pointer;"
        x-transition></div>
</div>

<style>
.notification-bell button {
    transition: transform 0.2s, opacity 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-bell button:hover {
    transform: scale(1.15);
    opacity: 0.8;
}

.notification-panel {
    position: fixed;
    right: 0;
    top: 0;
    bottom: 0;
    width: 400px;
    background: white;
    z-index: 1050;
    box-shadow: -3px 0 15px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateX(100%);
    display: flex;
    flex-direction: column;
    border-radius: 16px 0 0 16px;
}

.notification-panel.show {
    transform: translateX(0);
}

.notification-header {
    flex-shrink: 0;
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 16px 0 0 0;
}

.offcanvas-body {
    flex: 1;
    overflow-y: auto;
}

.notification-footer {
    flex-shrink: 0;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0 0 0 16px;
    min-height: 70px;
    display: flex;
    align-items: center;
}

.list-group-item {
    border-left: 4px solid transparent !important;
    border-bottom: 1px solid #dee2e6 !important;
    padding: 1rem;
    border-radius: 0;
}

.list-group-item:not(.list-group-item-light) {
    background-color: #e7f3ff !important;
    font-weight: 500;
}

.list-group-item:hover {
    background-color: #f0f8ff !important;
}

.list-group-item.list-group-item-light {
    background-color: #fff !important;
}

.list-group-item h6 {
    font-size: 0.95rem;
    font-weight: 600;
    color: #333;
}

.list-group-item p {
    color: #555;
    line-height: 1.5;
}

@media (max-width: 576px) {
    .notification-panel {
        width: 100%;
        border-radius: 0;
    }
    
    .notification-header {
        border-radius: 0;
    }
    
    .notification-footer {
        border-radius: 0;
    }
}
</style>

<script>
function notificationBell() {
    return {
        panelOpen: false,
        notifications: [],
        unreadCountValue: 0,
        poller: null,
        csrfToken: '',

        init() {
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            this.fetchNotifications();
            this.poller = setInterval(() => this.fetchNotifications(), 15000);
        },
        
        openPanel() {
            console.log('Open panel clicked');
            this.panelOpen = true;
            this.fetchNotifications();
        },

        closePanel() {
            this.panelOpen = false;
        },

        async fetchNotifications() {
            try {
                const response = await fetch('/notifications?per_page=50');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                console.log('Notifications fetched:', data);
                this.notifications = data.data || [];
                this.unreadCountValue = Number(data.unread_count ?? this.notifications.filter(n => !n.is_read).length);
            } catch (error) {
                console.error('Error fetching notifications:', error);
                this.notifications = [];
            }
        },

        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/notifications/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.is_read = true;
                }

                this.unreadCountValue = Math.max(0, this.unreadCountValue - 1);
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('/notifications/read-all', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                this.notifications.forEach(n => n.is_read = true);
                this.unreadCountValue = 0;
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },

        async deleteNotification(notificationId) {
            try {
                const response = await fetch(`/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const deleted = this.notifications.find(n => n.id === notificationId);
                this.notifications = this.notifications.filter(n => n.id !== notificationId);
                if (deleted && !deleted.is_read) {
                    this.unreadCountValue = Math.max(0, this.unreadCountValue - 1);
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
            }
        },

        async openNotification(notification) {
            if (!notification.is_read) {
                await this.markAsRead(notification.id);
            }

            if (notification.target_url) {
                window.location.href = notification.target_url;
            }
        },

        formatDate(date) {
            const now = new Date();
            const notifDate = new Date(date);
            const diffMs = now.getTime() - notifDate.getTime();
            const diffMins = Math.floor(diffMs / 60000);

            if (diffMins < 1) return 'Adesso';
            if (diffMins < 60) return `${diffMins}m fa`;
            
            const diffHours = Math.floor(diffMins / 60);
            if (diffHours < 24) return `${diffHours}h fa`;
            
            const diffDays = Math.floor(diffHours / 24);
            if (diffDays < 7) return `${diffDays}d fa`;
            
            return notifDate.toLocaleDateString('it-IT');
        },

    }
}
</script>