<?php use App\Core\Security; use App\Core\View; ?>
<section class="container-fluid py-4">
    <?php View::partial('partials/role-nav'); ?>
    
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 fw-semibold">Instructor</span>
                <h1 class="h3 mb-0 fw-bold">My Analytics</h1>
            </div>
            <p class="text-muted mb-0">Track engagement, performance, and grading backlogs across your courses.</p>
        </div>
    </div>

    <!-- Instructor Overview Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-primary bg-gradient text-white hover-lift">
                <div class="card-body p-4 position-relative overflow-hidden">
                    <i class="bi bi-journal-album position-absolute end-0 top-0 opacity-25 mt-3 me-3" style="font-size: 5rem; transform: rotate(15deg);"></i>
                    <h6 class="fw-semibold text-uppercase tracking-wider opacity-75 mb-1">Courses Taught</h6>
                    <div class="display-4 fw-bold mb-0"><?= (int) ($overview['total_courses'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-success bg-gradient text-white hover-lift">
                <div class="card-body p-4 position-relative overflow-hidden">
                    <i class="bi bi-people-fill position-absolute end-0 top-0 opacity-25 mt-3 me-3" style="font-size: 5rem; transform: rotate(15deg);"></i>
                    <h6 class="fw-semibold text-uppercase tracking-wider opacity-75 mb-1">Total Students</h6>
                    <div class="display-4 fw-bold mb-0"><?= (int) ($overview['total_students'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Grading Backlog -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Action Items</h5>
                    <span class="badge bg-danger rounded-pill"><?= array_sum(array_column($grading_backlog, 'pending_count')) ?> Pending</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($grading_backlog)): ?>
                        <div class="text-center p-5">
                            <i class="bi bi-check-circle text-success fs-1 mb-3 d-block"></i>
                            <h6 class="fw-bold">All caught up!</h6>
                            <p class="text-muted small">You have no pending assignments to grade.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($grading_backlog as $backlog): ?>
                                <div class="list-group-item bg-transparent border-bottom p-4 hover-lift">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0"><?= Security::e($backlog['title']) ?></h6>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill fw-semibold"><?= (int) $backlog['pending_count'] ?> to grade</span>
                                    </div>
                                    <p class="text-muted small mb-0"><i class="bi bi-book me-1"></i> <?= Security::e($backlog['course_title']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Course Performance Matrix -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Course Performance Matrix</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-uppercase small fw-semibold text-secondary py-3 px-4">Course</th>
                                <th class="text-uppercase small fw-semibold text-secondary py-3 text-center">Enrollments</th>
                                <th class="text-uppercase small fw-semibold text-secondary py-3">Attendance Rate</th>
                                <th class="text-uppercase small fw-semibold text-secondary py-3">Avg Assignment Score</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php foreach ($course_performance as $course): 
                                $avgScore = (float) $course['avg_assignment_score'];
                                $scoreColor = $avgScore >= 80 ? 'success' : ($avgScore >= 60 ? 'warning' : 'danger');
                            ?>
                                <tr>
                                    <td class="px-4 py-3 fw-medium text-dark"><?= Security::e($course['title']) ?></td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-light text-dark border px-3 py-1 rounded-pill"><?= (int)$course['enrollments'] ?></span>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary rounded-pill" style="width: <?= (int)$course['attendance_rate'] ?>%"></div>
                                            </div>
                                            <span class="small fw-semibold"><?= (int)$course['attendance_rate'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-<?= $scoreColor ?>"><?= $avgScore > 0 ? $avgScore : '-' ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($course_performance)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">No course performance data available yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.tracking-wider { letter-spacing: 0.05em; }
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1) !important; }
.custom-table th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; }
.custom-table td { border-bottom: 1px solid #f1f3f5; color: #495057; }
</style>
