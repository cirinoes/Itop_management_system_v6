<?php
use App\Core\Security;
?>
<style>
    #notificationsOffcanvas {
        width: 400px;
        border-radius: 0 16px 16px 0;
        box-shadow: 10px 0 30px rgba(0,0,0,0.05);
    }
    
    @media (min-width: 992px) {
        #notificationsOffcanvas {
            left: var(--sidenav-width); /* Appear beside sidebar */
        }
    }
    
    .notification-item {
        transition: all 0.2s;
        border-bottom: 1px solid #f1f5f9;
    }
    .notification-item:hover {
        background-color: #f8fafc;
    }
    .notification-item.unread {
        background-color: #f0f9ff;
    }
    .notification-avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>

<div class="offcanvas offcanvas-start border-0" tabindex="-1" id="notificationsOffcanvas" aria-labelledby="notificationsOffcanvasLabel" data-bs-backdrop="true">
    <div class="offcanvas-header border-bottom py-3">
        <h5 class="offcanvas-title fw-bold" id="notificationsOffcanvasLabel">Notification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body p-0 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom bg-light">
            <div class="d-flex gap-3 fw-semibold small text-muted">
                <span class="text-dark border-bottom border-2 border-dark pb-1 cursor-pointer">All notification <span class="badge bg-danger rounded-pill ms-1"><?= (int) ($notificationCount ?? 0) ?></span></span>
            </div>
            
            <?php if (($notificationCount ?? 0) > 0): ?>
                <form method="post" action="index.php?page=mark-notification-read" data-ajax-form class="m-0">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <input type="hidden" name="all" value="1">
                    <button class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold">Mark all read</button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="notification-list flex-grow-1 overflow-y-auto">
            <?php if (empty($recentNotifications)): ?>
                <div class="text-center p-5 text-muted">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3 opacity-50"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <p class="small mb-0">No notifications yet.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($recentNotifications ?? [] as $notification): 
                $typeStr = strtolower($notification['notification_type']);
                if (str_contains($typeStr, 'certificate')) {
                    $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
                    $colorClass = 'success';
                } elseif (str_contains($typeStr, 'message')) {
                    $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
                    $colorClass = 'primary';
                } elseif (str_contains($typeStr, 'course') || str_contains($typeStr, 'learning')) {
                    $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>';
                    $colorClass = 'warning';
                } else {
                    $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                    $colorClass = 'danger';
                }
            ?>
                <div class="notification-item p-4 <?= $notification['read_at'] ? '' : 'unread' ?>">
                    <div class="d-flex gap-3">
                        <div class="notification-avatar bg-<?= $colorClass ?> bg-opacity-10 text-<?= $colorClass ?>">
                            <?= $icon ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <h6 class="mb-0 fw-bold fs-6 text-dark lh-sm pe-2"><?= Security::e($notification['title']) ?></h6>
                                <span class="small text-muted flex-shrink-0" style="font-size: 0.75rem;">
                                    <?php
                                        $time = strtotime($notification['created_at']);
                                        $diff = time() - $time;
                                        if ($diff < 60) echo 'Just now';
                                        elseif ($diff < 3600) echo floor($diff/60) . 'm ago';
                                        elseif ($diff < 86400) echo floor($diff/3600) . 'h ago';
                                        else echo date('M d', $time);
                                    ?>
                                    <?php if (!$notification['read_at']): ?>
                                        <span class="d-inline-block bg-danger rounded-circle ms-1" style="width: 6px; height: 6px;"></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <p class="small text-muted mb-2 lh-sm"><?= Security::e($notification['description'] ?? '') ?></p>
                            
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <span class="small text-muted" style="font-size: 0.75rem;">
                                    By <span class="fw-semibold text-dark"><?= Security::e($notification['sender_name'] ?? 'System') ?></span>
                                </span>
                                
                                <div class="d-flex gap-2">
                                    <?php if ($notification['related_url']): ?>
                                        <a href="<?= Security::e($notification['related_url']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 0.75rem;">View</a>
                                    <?php endif; ?>
                                    
                                    <?php if (!$notification['read_at']): ?>
                                        <form method="post" action="index.php?page=mark-notification-read" data-ajax-form class="m-0">
                                            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $notification['id'] ?>">
                                            <button class="btn btn-sm btn-light rounded-pill py-0 px-2 border" style="font-size: 0.75rem;" title="Mark read">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="p-3 bg-white text-center border-top">
            <a href="index.php?page=notifications" class="text-decoration-none fw-semibold small text-primary">See all notifications in Dashboard</a>
        </div>
    </div>
</div>
