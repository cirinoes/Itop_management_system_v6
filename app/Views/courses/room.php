<?php use App\Core\Auth; use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="index.php?page=trainee-dashboard" class="btn btn-light rounded-pill px-3 fw-medium text-secondary hover-lift"><i class="bi bi-arrow-left me-2"></i> Back to Dashboard</a>
    </div>

    <!-- Course Hero -->
    <div class="course-hero rounded-4 p-5 mb-4 position-relative overflow-hidden bg-primary text-white shadow">
        <div class="position-relative z-index-1">
            <div class="badge bg-white text-primary rounded-pill px-3 py-2 mb-3 fw-bold tracking-wider text-uppercase small">
                <?= Security::e($course['category'] ?? 'Course') ?>
            </div>
            <h1 class="display-5 fw-bold mb-3"><?= Security::e($course['title'] ?? 'Course Room') ?></h1>
            <p class="lead opacity-75 mb-4" style="max-width: 600px;">
                <?= Security::e($course['description'] ?? 'Continue your learning journey here.') ?>
            </p>
        </div>
        <!-- Decorative bg shape -->
        <div class="position-absolute end-0 top-0 h-100 w-50 opacity-10" style="background: radial-gradient(circle at center, #ffffff 0%, transparent 70%); transform: translate(20%, -20%);"></div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 2rem;">
                <div class="card-body p-3">
                    <div class="nav flex-column nav-pills course-sidebar-nav" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active d-flex align-items-center py-3 mb-2 fw-semibold" id="v-pills-overview-tab" data-bs-toggle="pill" data-bs-target="#v-pills-overview" type="button" role="tab" aria-controls="v-pills-overview" aria-selected="true">
                            <i class="bi bi-grid-1x2 me-3 fs-5"></i> Overview
                        </button>
                        <button class="nav-link d-flex align-items-center py-3 mb-2 fw-semibold" id="v-pills-materials-tab" data-bs-toggle="pill" data-bs-target="#v-pills-materials" type="button" role="tab" aria-controls="v-pills-materials" aria-selected="false">
                            <i class="bi bi-journal-text me-3 fs-5"></i> Materials
                        </button>
                        <button class="nav-link d-flex align-items-center py-3 mb-2 fw-semibold" id="v-pills-assignments-tab" data-bs-toggle="pill" data-bs-target="#v-pills-assignments" type="button" role="tab" aria-controls="v-pills-assignments" aria-selected="false">
                            <i class="bi bi-file-earmark-check me-3 fs-5"></i> Assignments
                        </button>
                        <button class="nav-link d-flex align-items-center py-3 mb-2 fw-semibold" id="v-pills-quizzes-tab" data-bs-toggle="pill" data-bs-target="#v-pills-quizzes" type="button" role="tab" aria-controls="v-pills-quizzes" aria-selected="false">
                            <i class="bi bi-patch-question me-3 fs-5"></i> Quizzes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-9">
            <div class="tab-content" id="v-pills-tabContent">
                
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="v-pills-overview" role="tabpanel" aria-labelledby="v-pills-overview-tab">
                    <div class="card border-0 rounded-4 shadow-sm mb-4">
                        <div class="card-body p-4 p-md-5">
                            <h3 class="fw-bold mb-4">About This Course</h3>
                            <div class="text-secondary lh-lg fs-5">
                                <?= nl2br(Security::e($course['description'] ?? 'No description provided.')) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Materials Tab -->
                <div class="tab-pane fade" id="v-pills-materials" role="tabpanel" aria-labelledby="v-pills-materials-tab">
                    <div class="card border-0 rounded-4 shadow-sm mb-4">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="fw-bold mb-0">Learning Modules</h3>
                            </div>
                            
                            <?php if (empty($materials)): ?>
                                <div class="text-center p-5 bg-light rounded-4 border border-dashed">
                                    <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                    <h5 class="mt-3 fw-bold text-secondary">No materials yet</h5>
                                    <p class="text-muted">Your instructor hasn't added any materials to this course.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush mb-4">
                                    <?php foreach ($materials as $m): ?>
                                        <div class="list-group-item bg-transparent border-bottom d-flex justify-content-between align-items-center py-4 px-0 hover-lift">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-4" style="width: 56px; height: 56px;">
                                                    <?php if ($m['type'] === 'video'): ?><i class="bi bi-play-fill fs-3"></i>
                                                    <?php elseif ($m['type'] === 'link'): ?><i class="bi bi-link-45deg fs-3"></i>
                                                    <?php else: ?><i class="bi bi-file-earmark-pdf-fill fs-4"></i><?php endif; ?>
                                                </div>
                                                <div>
                                                    <h5 class="mb-1 fw-bold text-dark"><?= Security::e($m['title']) ?></h5>
                                                    <small class="text-secondary fw-medium text-uppercase tracking-wider" style="font-size: 0.75rem;"><i class="bi bi-book me-1"></i> <?= Security::e(ucfirst($m['type'])) ?></small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <?php if ($m['external_url']): ?>
                                                    <a href="<?= Security::e($m['external_url']) ?>" target="_blank" class="btn btn-outline-secondary rounded-pill fw-medium px-4 py-2"><i class="bi bi-box-arrow-up-right me-2"></i> Open Link</a>
                                                <?php else: ?>
                                                    <a href="<?= Security::e('storage/uploads/' . $m['file_path']) ?>" download class="btn btn-primary rounded-pill fw-medium px-4 py-2 shadow-sm"><i class="bi bi-cloud-download me-2"></i> Download</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Assignments Tab -->
                <div class="tab-pane fade" id="v-pills-assignments" role="tabpanel" aria-labelledby="v-pills-assignments-tab">
                    <div class="card border-0 rounded-4 shadow-sm mb-4">
                        <div class="card-body p-4 p-md-5">
                            <h3 class="fw-bold mb-4">Assignments</h3>
                            
                            <?php if (empty($assignments)): ?>
                                <div class="text-center p-5 bg-light rounded-4 border border-dashed">
                                    <i class="bi bi-file-check fs-1 text-secondary opacity-50"></i>
                                    <h5 class="mt-3 fw-bold text-secondary">No assignments</h5>
                                    <p class="text-muted">You have no pending assignments for this course.</p>
                                </div>
                            <?php else: ?>
                                <div class="accordion accordion-flush custom-accordion" id="assignmentsAccordion">
                                    <?php foreach ($assignments as $index => $a): ?>
                                        <?php 
                                        $sub = $trainee_submissions[$a['id']] ?? null;
                                        $status = $sub ? $sub['status'] : 'not_submitted';
                                        $isGraded = ($status === 'graded');
                                        ?>
                                        <div class="accordion-item border-0 mb-3 bg-white rounded-4 shadow-sm overflow-hidden">
                                            <h2 class="accordion-header" id="heading<?= $a['id'] ?>">
                                                <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?> bg-white p-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $a['id'] ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $a['id'] ?>">
                                                    <div class="d-flex align-items-center w-100 pe-3">
                                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-4 flex-shrink-0" style="width: 48px; height: 48px;">
                                                            <i class="bi bi-journal-text fs-4 text-primary"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h5 class="mb-1 fw-bold"><?= Security::e($a['title']) ?></h5>
                                                            <div class="text-muted small">
                                                                <i class="bi bi-calendar-event me-1"></i> Due: <?= date('M j, Y', strtotime($a['due_date'])) ?>
                                                                <span class="mx-2">•</span>
                                                                <i class="bi bi-award me-1"></i> Max Score: <?= (int)$a['max_score'] ?>
                                                            </div>
                                                        </div>
                                                        <div class="ms-auto flex-shrink-0">
                                                            <?php if ($status === 'not_submitted'): ?>
                                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3 py-2 fw-semibold">Not Submitted</span>
                                                            <?php elseif ($status === 'pending'): ?>
                                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3 py-2 fw-semibold">Pending Review</span>
                                                            <?php elseif ($status === 'graded'): ?>
                                                                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-2 fw-semibold">Graded</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapse<?= $a['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $a['id'] ?>" data-bs-parent="#assignmentsAccordion">
                                                <div class="accordion-body p-4 p-md-5 border-top bg-light">
                                                    
                                                    <!-- Instructions -->
                                                    <div class="mb-5">
                                                        <h6 class="fw-bold text-uppercase tracking-wider text-secondary small mb-3">Instructions</h6>
                                                        <div class="bg-white p-4 rounded-4 shadow-sm border lh-lg">
                                                            <?= nl2br(Security::e($a['instructions'])) ?>
                                                        </div>
                                                    </div>

                                                    <?php if ($status === 'not_submitted'): ?>
                                                        <!-- Submission Form -->
                                                        <h6 class="fw-bold text-uppercase tracking-wider text-secondary small mb-3">Submit Your Work</h6>
                                                        <form method="post" enctype="multipart/form-data" action="index.php?page=submit-assignment" class="bg-white p-4 rounded-4 shadow-sm border">
                                                            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                                            <input type="hidden" name="assignment_id" value="<?= (int) $a['id'] ?>">
                                                            
                                                            <div class="mb-4">
                                                                <label class="form-label fw-semibold">Upload File (PDF, DOCX, ZIP)</label>
                                                                <input class="form-control form-control-lg bg-light" type="file" name="submission" required>
                                                            </div>
                                                            <div class="mb-4">
                                                                <label class="form-label fw-semibold">Optional Notes to Instructor</label>
                                                                <textarea class="form-control bg-light" name="notes" rows="3" placeholder="Any comments about your submission..."></textarea>
                                                            </div>
                                                            <button class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm hover-lift"><i class="bi bi-cloud-arrow-up me-2"></i> Submit Assignment</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <!-- Submitted Details -->
                                                        <div class="row g-4">
                                                            <div class="col-md-6">
                                                                <h6 class="fw-bold text-uppercase tracking-wider text-secondary small mb-3">Your Submission</h6>
                                                                <div class="bg-white p-4 rounded-4 shadow-sm border h-100 d-flex flex-column justify-content-center align-items-center text-center">
                                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                                                        <i class="bi bi-file-earmark-check fs-2"></i>
                                                                    </div>
                                                                    <h5 class="fw-bold text-dark mb-1"><?= Security::e($sub['file_path']) ?></h5>
                                                                    <p class="text-muted small mb-3">Submitted on <?= date('M j, Y \a\t H:i', strtotime($sub['submitted_at'])) ?></p>
                                                                    <a href="storage/uploads/<?= Security::e($sub['file_path']) ?>" download class="btn btn-sm btn-outline-secondary rounded-pill px-4 fw-medium"><i class="bi bi-download me-2"></i> Download Copy</a>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="fw-bold text-uppercase tracking-wider text-secondary small mb-3">Instructor Evaluation</h6>
                                                                <div class="bg-white p-4 rounded-4 shadow-sm border h-100 position-relative overflow-hidden">
                                                                    <?php if ($isGraded): ?>
                                                                        <div class="position-absolute top-0 end-0 p-3 opacity-10">
                                                                            <i class="bi bi-award-fill" style="font-size: 5rem;"></i>
                                                                        </div>
                                                                        <div class="position-relative z-index-1">
                                                                            <div class="mb-4">
                                                                                <span class="text-secondary small fw-bold text-uppercase tracking-wider d-block mb-1">Final Score</span>
                                                                                <div class="display-4 fw-bold text-success"><?= (float)$sub['score'] ?> <span class="fs-4 text-muted fw-normal">/ <?= (int)$a['max_score'] ?></span></div>
                                                                            </div>
                                                                            <div>
                                                                                <span class="text-secondary small fw-bold text-uppercase tracking-wider d-block mb-2">Feedback</span>
                                                                                <div class="p-3 bg-light rounded-3 text-dark lh-base border">
                                                                                    <?= nl2br(Security::e($sub['feedback'] ?? 'No feedback provided.')) ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <div class="d-flex flex-column justify-content-center align-items-center text-center h-100 text-muted">
                                                                            <i class="bi bi-hourglass-split fs-1 mb-3 opacity-50"></i>
                                                                            <h5 class="fw-bold">Pending Review</h5>
                                                                            <p class="small">Your instructor has not graded this yet.</p>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quizzes Tab -->
                <div class="tab-pane fade" id="v-pills-quizzes" role="tabpanel" aria-labelledby="v-pills-quizzes-tab">
                    <div class="card border-0 rounded-4 shadow-sm mb-4">
                        <div class="card-body p-4 p-md-5">
                            <h3 class="fw-bold mb-4">Quizzes</h3>
                            
                            <?php if (empty($quizzes)): ?>
                                <div class="text-center p-5 bg-light rounded-4 border border-dashed">
                                    <i class="bi bi-patch-question fs-1 text-secondary opacity-50"></i>
                                    <h5 class="mt-3 fw-bold text-secondary">No quizzes</h5>
                                    <p class="text-muted">No quizzes have been published for this course yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach ($quizzes as $q): ?>
                                        <div class="col-md-6">
                                            <div class="card h-100 border rounded-4 shadow-sm hover-lift">
                                                <div class="card-body p-4">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                            <i class="bi bi-patch-question fs-4"></i>
                                                        </div>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3 py-1">Quiz</span>
                                                    </div>
                                                    <h5 class="fw-bold mb-2"><?= Security::e($q['title']) ?></h5>
                                                    <p class="text-muted small mb-4">Total Marks: <?= (int)$q['total_marks'] ?></p>
                                                    <button class="btn btn-outline-primary w-100 rounded-pill fw-semibold disabled">Take Quiz (Mock)</button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* Custom Styles for Trainee Room Layout */
.course-sidebar-nav .nav-link {
    color: var(--bs-secondary);
    transition: all 0.2s ease;
    border-radius: 0.5rem;
}
.course-sidebar-nav .nav-link:hover {
    background-color: var(--bs-light);
    color: var(--ims-primary);
}
.course-sidebar-nav .nav-link.active {
    background-color: var(--bs-primary);
    color: #fff;
    box-shadow: 0 4px 6px -1px rgba(var(--ims-primary-rgb), 0.2);
}
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1) !important;
}
.tracking-wider {
    letter-spacing: 0.05em;
}
.custom-accordion .accordion-button:not(.collapsed) {
    background-color: transparent;
    color: var(--bs-dark);
    box-shadow: none;
}
.custom-accordion .accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0,0,0,0.125);
}
.custom-accordion .accordion-button::after {
    background-size: 1rem;
}
</style>
