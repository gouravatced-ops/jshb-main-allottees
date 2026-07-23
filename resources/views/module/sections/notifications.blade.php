<div class="section-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1" style="color: #0f1b2d;"><i class="fa-solid fa-bell me-2"></i> Notifications</h4>
        <p class="text-muted mb-0" style="font-size: 14px;">View your recent alerts, updates, and delivery statuses.</p>
    </div>
    @if($notifications && $notifications->where('is_read', false)->count() > 0)
        <button id="markAllReadBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-medium" onclick="markAllNotificationsAsRead()">
            <i class="fa-solid fa-check-double me-1"></i> Mark all as read
        </button>
    @endif
</div>

<div class="row">
    <div class="col-12">
        @if($notifications && $notifications->isNotEmpty())
            <div class="notification-list">
                @foreach($notifications as $notif)
                    @php
                        $notifData = [
                            'id' => $notif->id,
                            'subject' => $notif->subject,
                            'message' => $notif->message,
                            'is_read' => (bool)$notif->is_read,
                        ];
                    @endphp
                    <div class="notification-card shadow-sm mb-3 {{ !$notif->is_read ? 'unread-card' : '' }}" id="notif-card-{{ $notif->id }}">
                        <div class="d-flex w-100 align-items-start p-3">
                            <div class="notif-icon-wrap me-3">
                                @if($notif->notification_type === 'success' || strtolower($notif->subject) === 'success')
                                    <div class="icon-circle bg-success-subtle text-success">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                @elseif($notif->notification_type === 'warning')
                                    <div class="icon-circle bg-warning-subtle text-warning">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    </div>
                                @elseif($notif->notification_type === 'danger')
                                    <div class="icon-circle bg-danger-subtle text-danger">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                @else
                                    <div class="icon-circle bg-primary-subtle text-primary">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="notif-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                                    <div class="d-flex align-items-center">
                                        <h6 class="fw-bold mb-0 me-2" style="color: #0f1b2d;">{{ $notif->subject }}</h6>
                                        @if(!$notif->is_read)
                                            <span class="badge bg-primary rounded-pill unread-badge" id="notif-badge-{{ $notif->id }}" style="font-size: 10px;">New</span>
                                        @endif
                                    </div>
                                    <small class="text-muted" style="font-size: 12px;">
                                        <i class="fa-regular fa-clock me-1"></i> {{ $notif->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <p class="mb-2 text-secondary notif-text-preview" style="font-size: 14px; line-height: 1.5;">
                                    {!! nl2br(e(Str::limit($notif->message, 150))) !!}
                                </p>
                                
                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1" style="font-size: 12px;" onclick='openNotifModal(@json($notifData))'>
                                        View Details <i class="fa-solid fa-eye ms-1"></i>
                                    </button>
                                </div>

                                <div class="delivery-status-wrap mt-2 pt-2 border-top">
                                    <span class="delivery-label me-3 text-muted" style="font-size: 12px; font-weight: 500;">Delivery Status:</span>
                                    
                                    <!-- WhatsApp -->
                                    <div class="delivery-badge badge-whatsapp {{ $notif->is_push_sent ? 'success' : 'failed' }}" title="WhatsApp">
                                        <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                                        @if($notif->is_push_sent)
                                            <svg class="status-svg ms-1 text-success" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        @else
                                            <svg class="status-svg ms-1 text-danger" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        @endif
                                    </div>

                                    <!-- SMS -->
                                    <div class="delivery-badge badge-sms {{ $notif->is_sms_sent ? 'success' : 'failed' }}" title="SMS">
                                        <i class="fa-solid fa-comment-sms me-1"></i> SMS
                                        @if($notif->is_sms_sent)
                                            <svg class="status-svg ms-1 text-success" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        @else
                                            <svg class="status-svg ms-1 text-danger" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        @endif
                                    </div>

                                    <!-- Email -->
                                    <div class="delivery-badge badge-email {{ $notif->is_email_sent ? 'success' : 'failed' }}" title="Email">
                                        <i class="fa-solid fa-envelope me-1"></i> Email
                                        @if($notif->is_email_sent)
                                            <svg class="status-svg ms-1 text-success" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        @else
                                            <svg class="status-svg ms-1 text-danger" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state text-center py-5 shadow-sm rounded bg-white">
                <div class="empty-icon mb-3">
                    <i class="fa-regular fa-bell-slash" style="font-size: 48px; color: #dce1e6;"></i>
                </div>
                <h5 class="fw-bold" style="color: #0f1b2d;">No Notifications Yet</h5>
                <p class="text-muted">You don't have any alerts or updates right now.</p>
            </div>
        @endif
    </div>
</div>

<!-- NOTIFICATION DETAIL MODAL (Subject & Message Only) -->
<div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-labelledby="notifModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold" id="notifModalTitle" style="color: #0f1b2d;">
                    Notification Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted fw-bold text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Subject</label>
                    <h5 class="fw-bold mb-0" id="modalNotifSubject" style="color: #0f1b2d;"></h5>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <label class="text-muted fw-bold text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Message</label>
                    <p class="mb-0 text-dark" id="modalNotifMessage" style="font-size: 14px; white-space: pre-line; line-height: 1.6;"></p>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Notification Card Styling */
    .notification-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #f0f2f5;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    
    .notification-card.unread-card {
        border-left: 4px solid #0284c7;
        background-color: #f8fafc;
    }

    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 27, 45, 0.08) !important;
        border-color: #e2e8f0;
    }

    .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .bg-primary-subtle { background-color: #e0f2fe; }
    .text-primary { color: #0284c7 !important; }
    
    .bg-success-subtle { background-color: #dcfce7; }
    .text-success { color: #16a34a !important; }
    
    .bg-warning-subtle { background-color: #fef3c7; }
    .text-warning { color: #d97706 !important; }
    
    .bg-danger-subtle { background-color: #fee2e2; }
    .text-danger { color: #dc2626 !important; }

    /* Delivery Status Badges */
    .delivery-status-wrap {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        border-top-color: #f1f5f9 !important;
    }

    .delivery-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        transition: background 0.2s ease;
    }

    .delivery-badge.success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #16a34a;
    }

    .delivery-badge.failed {
        background: #fef2f2;
        border-color: #fecaca;
        color: #ef4444;
    }

    .delivery-badge i {
        font-size: 12px;
    }

    .status-svg {
        display: inline-block;
        vertical-align: middle;
    }

    .delivery-badge.success .status-svg {
        stroke: #16a34a;
    }

    .delivery-badge.failed .status-svg {
        stroke: #ef4444;
    }

    .empty-state {
        border: 1px dashed #cbd5e1;
    }
</style>

<script>
let notifModalObj = null;

function updateSidebarCount(count) {
    const badge = document.getElementById('sidebarNotifBadge');
    if (badge) {
        badge.innerText = count;
        if (parseInt(count) > 0) {
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }
}

function openNotifModal(data) {
    document.getElementById('modalNotifSubject').innerText = data.subject || '';
    document.getElementById('modalNotifMessage').innerText = data.message || '';

    // Show modal
    const modalEl = document.getElementById('notificationDetailModal');
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        notifModalObj = new bootstrap.Modal(modalEl);
        notifModalObj.show();
    }

    // Mark as read if unread
    if (!data.is_read) {
        fetch('/notifications/' + data.id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                data.is_read = true;
                const card = document.getElementById('notif-card-' + data.id);
                if (card) {
                    card.classList.remove('unread-card');
                }
                const unreadTag = document.getElementById('notif-badge-' + data.id);
                if (unreadTag) {
                    unreadTag.remove();
                }
                updateSidebarCount(res.unread_count);
                if (parseInt(res.unread_count) === 0) {
                    const markAllBtn = document.getElementById('markAllReadBtn');
                    if (markAllBtn) markAllBtn.remove();
                }
            }
        })
        .catch(err => console.error('Error marking notification read:', err));
    }
}

function markAllNotificationsAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            document.querySelectorAll('.unread-card').forEach(card => card.classList.remove('unread-card'));
            document.querySelectorAll('.unread-badge').forEach(badge => badge.remove());
            updateSidebarCount(0);
            const markAllBtn = document.getElementById('markAllReadBtn');
            if (markAllBtn) markAllBtn.remove();
        }
    })
    .catch(err => console.error('Error marking all notifications read:', err));
}
</script>
