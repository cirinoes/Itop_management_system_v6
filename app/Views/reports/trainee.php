<?php use App\Core\Security; use App\Core\View; ?>
<section class="container-fluid py-4">
    <?php View::partial('partials/role-nav'); ?>
    
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-1 fw-semibold">Trainee</span>
                <h1 class="h3 mb-0 fw-bold">My Learning Report</h1>
            </div>
            <p class="text-muted mb-0">Your personal progress, skill development, and achievements.</p>
        </div>
    </div>

    <!-- Personal Overview Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white hover-lift">
                <div class="card-body p-4 text-center position-relative overflow-hidden">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-book fs-2"></i>
                    </div>
                    <div class="display-4 fw-bold text-dark mb-1"><?= (int) ($metrics['completed_courses'] ?? 0) ?></div>
                    <span class="text-secondary small fw-bold text-uppercase tracking-wider">Courses Completed</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white hover-lift">
                <div class="card-body p-4 text-center position-relative overflow-hidden">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-award fs-2"></i>
                    </div>
                    <div class="display-4 fw-bold text-dark mb-1"><?= (int) ($metrics['certificates'] ?? 0) ?></div>
                    <span class="text-secondary small fw-bold text-uppercase tracking-wider">Certificates Earned</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white hover-lift">
                <div class="card-body p-4 text-center position-relative overflow-hidden">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-clock-history fs-2"></i>
                    </div>
                    <div class="display-4 fw-bold text-dark mb-1"><?= (int) ($metrics['total_hours'] ?? 0) ?></div>
                    <span class="text-secondary small fw-bold text-uppercase tracking-wider">Learning Hours</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Course Progress -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Course Progression</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($course_progress)): ?>
                        <div class="text-center p-5 text-muted">
                            <i class="bi bi-journal-x fs-1 mb-3 opacity-50 d-block"></i>
                            <h6 class="fw-bold">No enrolled courses</h6>
                            <p class="small">Enroll in a course to see your progress here.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($course_progress as $course): 
                                $percent = (int) $course['progress_percent'];
                                $isCompleted = $course['status'] === 'completed';
                                $barColor = $isCompleted ? 'success' : 'primary';
                            ?>
                                <div class="list-group-item bg-transparent border-bottom px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0 text-dark"><?= Security::e($course['title']) ?></h6>
                                        <span class="badge bg-<?= $isCompleted ? 'success' : 'warning' ?> bg-opacity-10 text-<?= $isCompleted ? 'success' : 'warning' ?> rounded-pill fw-semibold">
                                            <?= $isCompleted ? 'Completed' : 'In Progress' ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-<?= $barColor ?> rounded-pill" style="width: <?= $percent ?>%"></div>
                                        </div>
                                        <span class="small fw-bold text-muted" style="width: 40px; text-align: right;"><?= $percent ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Certificate Timeline -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Achievement Timeline</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($certificates_list)): ?>
                        <div class="text-center p-5 text-muted">
                            <i class="bi bi-stars fs-1 mb-3 opacity-50 d-block"></i>
                            <h6 class="fw-bold">No certificates yet</h6>
                            <p class="small">Complete courses to unlock certificates and build your timeline.</p>
                        </div>
                    <?php else: ?>
                        <div class="position-relative">
                            <!-- Timeline line -->
                            <div class="position-absolute h-100" style="left: 19px; top: 0; width: 2px; background-color: #e9ecef;"></div>
                            
                            <?php foreach ($certificates_list as $cert): ?>
                                <div class="position-relative ps-5 mb-4 hover-lift">
                                    <!-- Timeline dot -->
                                    <div class="position-absolute bg-success rounded-circle border border-4 border-white shadow-sm" style="left: 11px; top: 0; width: 18px; height: 18px;"></div>
                                    
                                    <div class="bg-light rounded-4 p-3 border">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="fw-bold mb-0"><?= Security::e($cert['title']) ?></h6>
                                        </div>
                                        <p class="text-muted small mb-2"><i class="bi bi-calendar-check me-1"></i> <?= date('M j, Y', strtotime($cert['issued_at'])) ?></p>
                                        <span class="badge bg-white text-secondary border px-2 py-1"><i class="bi bi-upc-scan me-1"></i> <?= Security::e($cert['certificate_no']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.tracking-wider { letter-spacing: 0.05em; }
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1) !important; }
</style>
