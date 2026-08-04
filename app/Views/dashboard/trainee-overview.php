<?php
use App\Core\Auth;
use App\Core\Security;
use App\Core\View;

$enrolled = count($enrolments);
$totalProgress = 0;
foreach ($enrolments as $row) {
    $totalProgress += (int) ($row['progress_percent'] ?? 0);
}
$avgProgress = $enrolled ? (int) round($totalProgress / $enrolled) : 0;
$certCount = count($certificates);

// Calculate profile completion
$profileFields = ['phone', 'address', 'education', 'employment', 'emergency_contact', 'profile_picture'];
$filledFields = 0;
foreach ($profileFields as $field) {
    if (!empty($profile[$field])) $filledFields++;
}
// Add user fields
if (!empty($user['email'])) $filledFields++;
if (!empty($user['name'])) $filledFields++;
$profileCompletion = (int) round(($filledFields / 8) * 100);
?>
<?php View::partial('partials/role-nav'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="section-label">Trainee Dashboard</span>
        <h1 class="section-title mb-0">Welcome back, <?= Security::e(Auth::user()['name']) ?> 👋</h1>
    </div>
    <div class="d-none d-md-block text-end">
        <span class="text-muted small d-block mb-1">Profile Completion</span>
        <div class="d-flex align-items-center gap-2">
            <div class="progress" style="width: 150px; height: 6px;">
                <div class="progress-bar bg-success" style="width: <?= $profileCompletion ?>%"></div>
            </div>
            <span class="small fw-bold text-success"><?= $profileCompletion ?>%</span>
        </div>
    </div>
</div>

<!-- ── Stat Cards Row ────────────────────────────── -->
<div class="overview-stats">
    <div class="overview-stat-card animate-in">
        <div class="d-flex justify-content-between align-items-start">
            <span class="overview-stat-label">Enrolled Courses</span>
            <span class="overview-stat-icon" style="background: rgba(5, 77, 158, .1); color: var(--ims-primary);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            </span>
        </div>
        <strong class="overview-stat-value"><?= $enrolled ?></strong>
    </div>
    <div class="overview-stat-card accent-green animate-in" style="animation-delay: 0.1s">
        <div class="d-flex justify-content-between align-items-start">
            <span class="overview-stat-label">Learning Progress</span>
            <span class="overview-stat-icon" style="background: rgba(24, 169, 153, .1); color: var(--ims-accent);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </span>
        </div>
        <strong class="overview-stat-value"><?= $avgProgress ?>%</strong>
    </div>
    <div class="overview-stat-card animate-in" style="animation-delay: 0.2s">
        <div class="d-flex justify-content-between align-items-start">
            <span class="overview-stat-label">Certificates</span>
            <span class="overview-stat-icon" style="background: rgba(5, 77, 158, .1); color: var(--ims-primary);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
            </span>
        </div>
        <strong class="overview-stat-value"><?= $certCount ?></strong>
    </div>
    <div class="overview-stat-card accent-orange animate-in" style="animation-delay: 0.3s">
        <div class="d-flex justify-content-between align-items-start">
            <span class="overview-stat-label">Notifications</span>
            <span class="overview-stat-icon" style="background: rgba(234, 88, 12, .1); color: var(--ims-warning);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </span>
        </div>
        <strong class="overview-stat-value"><?= (int) $notificationCount ?></strong>
    </div>
</div>

<!-- ── Two-Column Content ────────────────────────── -->
<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Current Courses -->
        <div class="bg-white border rounded-4 shadow-sm p-4 animate-in mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h5 fw-bold mb-0">Learning Progress</h2>
                <a href="index.php?page=trainee-dashboard" class="btn btn-sm btn-outline-primary">View All</a>
            </div>

            <?php if ($enrolments): ?>
                <?php foreach (array_slice($enrolments, 0, 3) as $row): ?>
                    <div class="card border border-light shadow-sm mb-3">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="bg-tint-primary rounded p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold text-dark"><?= Security::e($row['title']) ?></span>
                                    <span class="fw-bold <?= (int) ($row['progress_percent'] ?? 0) === 100 ? 'text-success' : 'text-primary' ?>"><?= (int) ($row['progress_percent'] ?? 0) ?>%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar <?= (int) ($row['progress_percent'] ?? 0) === 100 ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= (int) ($row['progress_percent'] ?? 0) ?>%" aria-valuenow="<?= (int) ($row['progress_percent'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3" style="width: 48px; height: 48px; opacity: 0.5;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    <h3 class="h6 fw-bold text-dark mb-2">No Active Courses</h3>
                    <p class="small mb-3">You haven't enrolled in any courses yet.</p>
                    <a href="index.php?page=courses" class="btn btn-primary btn-sm">Browse Courses</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Timeline -->
        <div class="bg-white border rounded-4 shadow-sm p-4 animate-in mb-4" style="animation-delay: 0.1s">
            <h2 class="h5 fw-bold mb-4">Upcoming Schedule & Announcements</h2>
            
            <?php
                $scheduleItems = [];
                foreach ($enrolments as $row) {
                    if (!empty($row['start_date']) && strtotime($row['start_date']) >= strtotime('today')) {
                        $scheduleItems[] = ['date' => $row['start_date'], 'title' => $row['title'], 'meta' => 'Course begins'];
                    }
                    if (!empty($row['end_date']) && strtotime($row['end_date']) >= strtotime('today')) {
                        $scheduleItems[] = ['date' => $row['end_date'], 'title' => $row['title'], 'meta' => 'Course deadline'];
                    }
                }
                foreach (array_slice($announcements, 0, 3) as $ann) {
                    $scheduleItems[] = ['date' => $ann['created_at'] ?? date('Y-m-d'), 'title' => $ann['title'], 'meta' => 'Announcement'];
                }
                usort($scheduleItems, fn($a, $b) => strtotime($a['date']) - strtotime($b['date']));
                $scheduleItems = array_slice($scheduleItems, 0, 5);
            ?>

            <?php if ($scheduleItems): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($scheduleItems as $item): ?>
                        <?php
                            $badgeTint = 'bg-tint-primary';
                            if ($item['meta'] === 'Course begins') $badgeTint = 'bg-tint-success';
                            if ($item['meta'] === 'Course deadline') $badgeTint = 'bg-tint-danger';
                            if ($item['meta'] === 'Announcement') $badgeTint = 'bg-tint-purple';
                        ?>
                        <div class="card border border-light shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge <?= $badgeTint ?> rounded-pill px-3 py-2">
                                        <?= Security::e($item['meta']) ?>
                                    </span>
                                    <small class="text-muted fw-bold"><?= date('j M Y', strtotime($item['date'])) ?></small>
                                </div>
                                <h3 class="h6 mb-0 fw-bold text-dark"><?= Security::e($item['title']) ?></h3>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3" style="width: 48px; height: 48px; opacity: 0.5;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <h3 class="h6 fw-bold text-dark mb-2">Clear Schedule</h3>
                    <p class="small mb-0">No upcoming events or deadlines right now.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="trainee-card animate-in mb-4" style="animation-delay: 0.2s">
            <h2 class="h5 fw-bold mb-4">Quick Actions</h2>
            <div class="d-grid gap-2">
                <a href="index.php?page=trainee-dashboard" class="btn btn-outline-secondary text-start d-flex align-items-center gap-3">
                    <div class="bg-light rounded p-2 text-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                    Continue Learning
                </a>
                <a href="index.php?page=trainee-evaluations" class="btn btn-outline-secondary text-start d-flex align-items-center gap-3">
                    <div class="bg-light rounded p-2 text-warning">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </div>
                    Pending Evaluations
                </a>
                <a href="index.php?page=trainee-certificates" class="btn btn-outline-secondary text-start d-flex align-items-center gap-3">
                    <div class="bg-light rounded p-2 text-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                    </div>
                    View Certificates
                </a>
                <a href="index.php?page=trainee-profile" class="btn btn-outline-secondary text-start d-flex align-items-center gap-3">
                    <div class="bg-light rounded p-2 text-info">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    Update Profile
                </a>
            </div>
        </div>
    </div>
</div>
