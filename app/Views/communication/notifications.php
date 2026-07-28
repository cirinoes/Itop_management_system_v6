<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>
    
    <style>
        .notification-card {
            transition: all 0.2s ease-in-out;
            border-left: 4px solid transparent;
        }
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
            background-color: #fff !important;
        }
        .notification-card.unread {
            border-left-color: var(--bs-primary);
            background-color: rgba(var(--bs-primary-rgb), 0.02);
        }
    </style>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <span class="section-label">Communication</span>
            <h1 class="section-title mb-0">Notifications</h1>
            <p class="text-muted small mb-0 mt-1">Track learning, certificate, message, and system updates.</p>
        </div>
        
        <div class="d-flex gap-2">
            <form method="post" action="index.php?page=mark-notification-read" data-ajax-form>
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <input type="hidden" name="all" value="1">
                <button class="btn btn-outline-primary bg-white shadow-sm h-100 d-flex align-items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Mark All as Read
                </button>
            </form>
            
            <form class="d-flex gap-2 bg-white shadow-sm rounded p-1" method="get">
                <input type="hidden" name="page" value="notifications">
                <select class="form-select border-0 bg-light" name="type" style="min-width: 150px;">
                    <option value="">All categories</option>
                    <?php foreach ($types as $item): ?>
                        <option value="<?= Security::e($item['notification_type']) ?>" <?= $type === $item['notification_type'] ? 'selected' : '' ?>>
                            <?= Security::e(ucwords(str_replace('_', ' ', $item['notification_type']))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control border-0 bg-light" name="q" value="<?= Security::e($q) ?>" placeholder="Search..." style="min-width: 150px;">
                <button class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php 
        $delay = 0;
        foreach ($notifications as $notification): 
            $typeStr = strtolower($notification['notification_type']);
            if (str_contains($typeStr, 'certificate')) {
                $icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>';
                $colorClass = 'success';
            } elseif (str_contains($typeStr, 'message')) {
                $icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
                $colorClass = 'primary';
            } elseif (str_contains($typeStr, 'course') || str_contains($typeStr, 'learning') || str_contains($typeStr, 'assignment')) {
                $icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>';
                $colorClass = 'warning';
            } else {
                $icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
                $colorClass = 'info';
            }
        ?>
            <div class="col-md-6 col-xl-4">
                <div class="trainee-card animate-in shadow-sm h-100 notification-card p-4 <?= $notification['read_at'] ? '' : 'unread' ?>" style="animation-delay: <?= $delay ?>s">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-<?= $colorClass ?> bg-opacity-10 text-<?= $colorClass ?> rounded p-2">
                            <?= $icon ?>
                        </div>
                        <span class="small text-muted fw-semibold" style="font-size: 0.75rem;">
                            <?= date('d M Y, H:i', strtotime($notification['created_at'])) ?>
                        </span>
                    </div>
                    
                    <h2 class="h6 fw-bold text-dark mb-2"><?= Security::e($notification['title']) ?></h2>
                    <p class="text-muted small mb-3" style="line-height: 1.5;"><?= Security::e($notification['description'] ?? '') ?></p>
                    
                    <div class="mt-auto d-flex align-items-center justify-content-between pt-3 border-top">
                        <div class="small text-muted" style="font-size: 0.75rem;">
                            From: <span class="fw-semibold text-dark"><?= Security::e($notification['sender_name'] ?? 'System') ?></span>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <?php if ($notification['related_url']): ?>
                                <a href="<?= Security::e($notification['related_url']) ?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" title="Open Link">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    Open
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!$notification['read_at']): ?>
                                <form method="post" action="index.php?page=mark-notification-read" data-ajax-form>
                                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $notification['id'] ?>">
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Mark as Read">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="20 6 9 17 4 12"/></svg>
                                        Mark Read
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="post" action="index.php?page=delete-notification" data-ajax-form>
                                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $notification['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php 
            $delay += 0.05;
        endforeach; 
        ?>
        
        <?php if (!$notifications): ?>
            <div class="col-12">
                <div class="trainee-card animate-in shadow-sm p-5 text-center bg-light">
                    <div class="text-muted bg-white p-4 rounded-circle shadow-sm mb-4 d-inline-block">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                    <h3 class="h5 fw-bold text-dark">All Caught Up!</h3>
                    <p class="text-muted small">You don't have any notifications right now.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
