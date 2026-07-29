<?php use App\Core\Auth; use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="section-label">Instructor Dashboard</span>
        <h1 class="section-title mb-0">Welcome back, <?= Security::e(Auth::user()['name']) ?></h1>
    </div>
    <!-- Quick Actions Toolbar -->
    <div class="d-flex gap-2">
        <a href="index.php?page=announcements-manage" class="btn btn-primary btn-sm"><i class="bi bi-megaphone"></i> Send Announcement</a>
        <a href="index.php?page=instructor-enrolments" class="btn btn-outline-secondary btn-sm"><i class="bi bi-people"></i> Manage Enrolments</a>
    </div>
</div>

<!-- ── Stat Cards ────────────────────────────────── -->
<div class="overview-stats mb-4">
    <div class="overview-stat-card animate-in">
        <div class="d-flex justify-content-between">
            <span class="overview-stat-label">Active Courses</span>
            <span class="text-primary" style="font-size: 1.25rem;">📚</span>
        </div>
        <strong class="overview-stat-value"><?= count($courses) ?></strong>
    </div>
    <div class="overview-stat-card animate-in">
        <div class="d-flex justify-content-between">
            <span class="overview-stat-label">Total Trainees</span>
            <span class="text-info" style="font-size: 1.25rem;">👥</span>
        </div>
        <strong class="overview-stat-value"><?php
            $traineeCount = 0;
            foreach ($courses as $c) { $traineeCount += (int) ($c['participant_count'] ?? 0); }
            echo $traineeCount;
        ?></strong>
    </div>
    <div class="overview-stat-card accent-orange animate-in">
        <div class="d-flex justify-content-between">
            <span class="overview-stat-label">Pending Grading</span>
            <span class="text-warning" style="font-size: 1.25rem;">✍️</span>
        </div>
        <strong class="overview-stat-value"><?= count(array_filter($submissions, fn($s) => ($s['status'] ?? '') === 'submitted')) ?></strong>
    </div>
    <div class="overview-stat-card accent-green animate-in">
        <div class="d-flex justify-content-between">
            <span class="overview-stat-label">Upcoming Deadlines</span>
            <span class="text-success" style="font-size: 1.25rem;">⏰</span>
        </div>
        <strong class="overview-stat-value">2</strong> <!-- Mocked for now -->
    </div>
</div>

<div class="row g-4">
    <!-- Main Content: Courses and Engagement -->
    <div class="col-lg-8">
        
        <!-- Recently Accessed Course Shortcut -->
        <?php if (!empty($courses)): ?>
            <div class="overview-panel mb-4 animate-in">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="section-label">Quick Jump</span>
                        <h2 class="overview-panel-title mb-0">Jump back in</h2>
                    </div>
                    <a href="index.php?page=instructor-courses" class="btn btn-sm btn-light text-primary">View All Courses</a>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <!-- We'll just show the first course as a 'resume' card -->
                        <?php $recent = $courses[0]; ?>
                        <div class="d-flex bg-white border rounded shadow-sm p-3 align-items-center justify-content-between hover-lift">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-book fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold"><?= Security::e($recent['title']) ?></h6>
                                    <div class="small text-muted"><?= Security::e($recent['category']) ?></div>
                                </div>
                            </div>
                            <a href="index.php?page=course-room&course_id=<?= (int)$recent['id'] ?>" class="btn btn-primary px-4 rounded-pill fw-semibold">Open Room</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Class Engagement Tracker (Mocked UI) -->
        <div class="overview-panel animate-in">
            <span class="section-label">Analytics</span>
            <h2 class="overview-panel-title">Class Engagement Tracker</h2>
            <div class="p-4 text-center border rounded mt-3" style="min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center; background: linear-gradient(145deg, #f8f9fa, #e9ecef);">
                <p class="text-muted mb-2"><span style="font-size: 2rem;">📈</span></p>
                <p class="fw-bold mb-0">Engagement graph data will appear here.</p>
                <p class="text-muted small">Tracking trainee activity, logins, and material downloads over time.</p>
            </div>
        </div>

    </div>

    <!-- Sidebar: Grading Queue & Schedule -->
    <div class="col-lg-4">
        
        <!-- Grading Queue -->
        <div class="overview-panel mb-4 animate-in" style="border-top: 4px solid var(--ims-warning)">
            <span class="section-label" style="color:#c0392b">High Priority</span>
            <h2 class="overview-panel-title">Grading Queue</h2>
            
            <div class="mt-3">
                <?php 
                $pendingSubmissions = array_filter($submissions, fn($s) => ($s['status'] ?? '') === 'submitted');
                foreach (array_slice($pendingSubmissions, 0, 5) as $sub): 
                ?>
                    <div class="p-3 border rounded mb-2 bg-white shadow-sm d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold small"><?= Security::e($sub['trainee_name']) ?></div>
                            <div class="text-muted small" style="font-size: 0.75rem;"><?= Security::e($sub['assignment_title']) ?></div>
                        </div>
                        <a href="index.php?page=course-room&course_id=<?= (int)$sub['course_id'] ?>#grading" class="btn btn-sm btn-outline-warning" style="font-size: 0.75rem;">Grade Now</a>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($pendingSubmissions)): ?>
                    <div class="text-center p-3">
                        <span class="text-success" style="font-size: 2rem;">✅</span>
                        <p class="text-muted small mt-2 mb-0">All caught up!</p>
                        <p class="text-muted" style="font-size: 0.75rem;">No pending submissions to grade.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Schedule (Mocked UI) -->
        <div class="overview-panel mb-4 animate-in">
            <span class="section-label">Calendar</span>
            <h2 class="overview-panel-title">Upcoming Schedule</h2>
            <div class="mt-3">
                <div class="d-flex gap-3 mb-3 border-bottom pb-3">
                    <div class="text-center" style="min-width: 50px;">
                        <div class="small fw-bold text-danger">OCT</div>
                        <div class="fs-4 fw-bold">12</div>
                    </div>
                    <div>
                        <div class="fw-bold small">Live Lecture: Chapter 1</div>
                        <div class="text-muted" style="font-size: 0.75rem;">10:00 AM - 11:30 AM</div>
                    </div>
                </div>
                <div class="d-flex gap-3 mb-3 border-bottom pb-3">
                    <div class="text-center" style="min-width: 50px;">
                        <div class="small fw-bold text-danger">OCT</div>
                        <div class="fs-4 fw-bold">15</div>
                    </div>
                    <div>
                        <div class="fw-bold small">Assignment 1 Due</div>
                        <div class="text-muted" style="font-size: 0.75rem;">11:59 PM • 25 Submissions expected</div>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <div class="text-center" style="min-width: 50px;">
                        <div class="small fw-bold text-danger">OCT</div>
                        <div class="fs-4 fw-bold">18</div>
                    </div>
                    <div>
                        <div class="fw-bold small">Office Hours</div>
                        <div class="text-muted" style="font-size: 0.75rem;">2:00 PM - 4:00 PM</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
