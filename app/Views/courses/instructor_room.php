<?php use App\Core\Auth; use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<!-- Option 1: Immersive Hero Banner -->
<?php 
$thumbnail = $course['thumbnail_image'] ?? '';
$bgImage = $thumbnail ? 'storage/uploads/' . $thumbnail : 'public/assets/img/course-placeholder.svg';
?>
<div class="course-hero-banner position-relative overflow-hidden" style="background: linear-gradient(to right, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.7)), url('<?= Security::e($bgImage) ?>') no-repeat center center/cover; padding: 3rem 0; color: white; margin-top: -1.5rem; margin-left: -1.5rem; margin-right: -1.5rem; margin-bottom: 2rem;">
    <div class="container-fluid px-4">
        <a href="index.php?page=instructor-dashboard" class="text-decoration-none text-light opacity-75 mb-4 d-inline-block transition-opacity hover-opacity-100">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <span class="badge bg-primary text-uppercase tracking-wider mb-3 px-3 py-2 rounded-pill"><?= Security::e($course['category'] ?? 'Category') ?></span>
                <h1 class="display-6 fw-bold mb-2 text-white"><?= Security::e($course['title'] ?? 'Course Room') ?></h1>
                <p class="mb-0 text-light opacity-75 d-flex align-items-center gap-3">
                    <span><i class="bi bi-person-circle"></i> <?= Security::e($course['instructor_name'] ?? 'You') ?></span>
                    <span>|</span>
                    <span><i class="bi bi-calendar3"></i> <?= Security::e($course['start_date'] ?? 'N/A') ?> to <?= Security::e($course['end_date'] ?? 'N/A') ?></span>
                </p>
            </div>
            <div class="text-md-end bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 backdrop-blur">
                <div class="text-light opacity-75 small text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Enrolled Trainees</div>
                <div class="fs-2 fw-bold text-white lh-1">
                    <?= (int)($course['participant_count'] ?? 0) ?> 
                    <span class="fs-5 opacity-50 fw-normal">/ <?= (int)($course['capacity'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-0">
    <div class="row g-4">
        
        <!-- Option 2: Vertical Sidebar Navigation -->
        <div class="col-md-3 col-lg-2">
            <div class="nav flex-column nav-pills course-sidebar-nav gap-2 sticky-top" style="top: 2rem;" id="courseRoomTabs" role="tablist" aria-orientation="vertical">
                
                <button class="nav-link active text-start py-3 px-4 rounded-3 d-flex align-items-center fw-medium" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                    <i class="bi bi-grid-1x2 me-3 fs-5"></i> Overview
                </button>
                
                <button class="nav-link text-start py-3 px-4 rounded-3 d-flex align-items-center fw-medium" id="materials-tab" data-bs-toggle="pill" data-bs-target="#materials" type="button" role="tab">
                    <i class="bi bi-journal-richtext me-3 fs-5"></i> Materials
                </button>
                
                <button class="nav-link text-start py-3 px-4 rounded-3 d-flex align-items-center fw-medium" id="assignments-tab" data-bs-toggle="pill" data-bs-target="#assignments" type="button" role="tab">
                    <i class="bi bi-clipboard-check me-3 fs-5"></i> Assignments
                </button>
                
                <button class="nav-link text-start py-3 px-4 rounded-3 d-flex align-items-center fw-medium" id="quizzes-tab" data-bs-toggle="pill" data-bs-target="#quizzes" type="button" role="tab">
                    <i class="bi bi-ui-checks-grid me-3 fs-5"></i> Quizzes
                </button>
                
                <button class="nav-link text-start py-3 px-4 rounded-3 d-flex justify-content-between align-items-center fw-medium" id="grading-tab" data-bs-toggle="pill" data-bs-target="#grading" type="button" role="tab">
                    <div><i class="bi bi-spellcheck me-3 fs-5"></i> Grading</div>
                    <span class="badge bg-danger rounded-pill">New</span>
                </button>

                <hr class="my-3 text-muted opacity-25">

                <div class="d-grid gap-2 px-2">
                    <button class="btn btn-light text-start text-secondary fw-semibold border-0 bg-transparent hover-bg-light"><i class="bi bi-gear-fill me-2"></i> Settings</button>
                    <a href="index.php?page=course&id=<?= (int)$course['id'] ?>" class="btn btn-light text-start text-primary fw-semibold border-0 bg-transparent hover-bg-light" target="_blank"><i class="bi bi-eye-fill me-2"></i> View as Trainee</a>
                </div>
            </div>
        </div>

        <!-- Tabs Content -->
        <div class="col-md-9 col-lg-10">
            <div class="tab-content bg-white p-4 rounded-4 shadow-sm border border-light" id="courseRoomTabsContent" style="min-height: 600px;">
                
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card shadow-none border bg-light rounded-4 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-bold mb-3">Course Description</h5>
                                    <p class="card-text text-secondary lh-lg mb-0"><?= nl2br(Security::e($course['description'] ?? 'No description provided.')) ?></p>
                                </div>
                            </div>
                            <div class="card shadow-none border rounded-4">
                                <div class="card-body p-4">
                                    <h5 class="card-title d-flex justify-content-between fw-bold mb-4">
                                        Recent Activity
                                        <button class="btn btn-sm btn-link text-decoration-none">View All</button>
                                    </h5>
                                    <div class="text-muted small text-center py-5">
                                        <i class="bi bi-activity fs-1 text-light opacity-50"></i>
                                        <p class="mt-3 mb-0 fw-medium">Activity feed will be populated here.</p>
                                        <p class="text-muted">Tracking recent submissions and updates.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card shadow-none border border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-4">
                                <div class="card-body p-4">
                                    <h5 class="card-title text-primary fw-bold mb-4">Quick Actions</h5>
                                    <div class="d-grid gap-3">
                                        <button class="btn btn-white bg-white text-primary fw-semibold text-start shadow-sm border-0 py-2 px-3 rounded-3" onclick="document.getElementById('materials-tab').click();"><i class="bi bi-cloud-upload fs-5 me-2 align-middle"></i> Upload Material</button>
                                        <button class="btn btn-white bg-white text-primary fw-semibold text-start shadow-sm border-0 py-2 px-3 rounded-3" onclick="document.getElementById('assignments-tab').click();"><i class="bi bi-file-earmark-plus fs-5 me-2 align-middle"></i> Create Assignment</button>
                                        <a href="index.php?page=announcements-manage" class="btn btn-white bg-white text-primary fw-semibold text-start shadow-sm border-0 py-2 px-3 rounded-3"><i class="bi bi-megaphone fs-5 me-2 align-middle"></i> Broadcast</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Materials Tab -->
                <div class="tab-pane fade" id="materials" role="tabpanel" aria-labelledby="materials-tab">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold mb-0">Learning Modules</h4>
                                <button class="btn btn-sm btn-outline-primary fw-semibold rounded-pill px-3"><i class="bi bi-folder-plus"></i> New Module</button>
                            </div>
                            <?php if (empty($materials)): ?>
                                <div class="text-center p-5 bg-light rounded-4 border border-dashed">
                                    <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                    <h5 class="mt-3 fw-bold text-secondary">No materials yet</h5>
                                    <p class="text-muted">Upload documents, videos, or share external links to build your course.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush mb-4">
                                    <?php foreach ($materials as $m): ?>
                                        <div class="list-group-item bg-transparent border-bottom d-flex justify-content-between align-items-center py-4 px-0">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                                    <?php if ($m['type'] === 'video'): ?><i class="bi bi-play-fill fs-4"></i>
                                                    <?php elseif ($m['type'] === 'link'): ?><i class="bi bi-link-45deg fs-4"></i>
                                                    <?php else: ?><i class="bi bi-file-earmark-pdf-fill fs-5"></i><?php endif; ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold"><?= Security::e($m['title']) ?></h6>
                                                    <small class="text-secondary fw-medium text-uppercase tracking-wider" style="font-size: 0.7rem;"><?= Security::e(ucfirst($m['type'])) ?></small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <?php if ($m['external_url']): ?>
                                                    <a href="<?= Security::e($m['external_url']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill fw-medium px-3"><i class="bi bi-box-arrow-up-right me-1"></i> Open Link</a>
                                                <?php else: ?>
                                                    <a href="<?= Security::e('storage/uploads/' . $m['file_path']) ?>" download class="btn btn-sm btn-outline-primary rounded-pill fw-medium px-3"><i class="bi bi-download me-1"></i> Download</a>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-outline-danger rounded-pill fw-medium px-3"><i class="bi bi-trash me-1"></i> Delete</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 2rem;">
                                <div class="card-body p-4 bg-light rounded-4 border">
                                    <h5 class="card-title fw-bold mb-4">Upload Material</h5>
                                    
                                    <!-- Drag & Drop Uploader UI (Mock) -->
                                    <div class="border rounded-4 p-4 text-center mb-4 transition-colors hover-border-primary" style="border-style: dashed !important; border-width: 2px !important; border-color: #cbd5e1; background: #f8fafc; cursor: pointer;">
                                        <i class="bi bi-cloud-arrow-up fs-1 text-primary mb-2"></i>
                                        <p class="fw-bold mb-1">Drag & Drop files here</p>
                                        <p class="text-muted small mb-0">PDF, MP4, ZIP up to 50MB</p>
                                    </div>

                                    <!-- Standard Form -->
                                    <form method="post" enctype="multipart/form-data" action="index.php?page=add-material">
                                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                        <input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0) ?>">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">Material Title</label>
                                            <input class="form-control bg-white" name="title" placeholder="e.g. Chapter 1 Slides" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">Type</label>
                                            <select class="form-select bg-white" name="type">
                                                <option value="document">Document / PDF</option>
                                                <option value="video">Video</option>
                                                <option value="link">External Link</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">File Attachment</label>
                                            <input class="form-control bg-white form-control-sm" type="file" name="material">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label small fw-semibold text-secondary">External URL (Optional)</label>
                                            <input class="form-control bg-white" name="external_url" placeholder="https://...">
                                        </div>
                                        <button class="btn btn-primary w-100 fw-semibold py-2 rounded-3">Add to Course</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignments Tab -->
                <div class="tab-pane fade" id="assignments" role="tabpanel" aria-labelledby="assignments-tab">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <h4 class="fw-bold mb-4">Course Assignments</h4>
                            <?php if (empty($assignments)): ?>
                                <div class="text-center p-5 bg-light rounded-4 border border-dashed">
                                    <i class="bi bi-journal-x fs-1 text-secondary opacity-50"></i>
                                    <h5 class="mt-3 fw-bold text-secondary">No assignments yet</h5>
                                    <p class="text-muted">Create assignments to assess your trainees.</p>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($assignments as $a): ?>
                                        <div class="card shadow-sm border border-light rounded-4 overflow-hidden">
                                            <div class="row g-0">
                                                <div class="col-auto bg-primary bg-opacity-10 d-flex flex-column justify-content-center align-items-center px-4 py-3 border-end border-light">
                                                    <span class="fs-4 fw-bold text-primary"><?= (int)$a['max_score'] ?></span>
                                                    <span class="small text-primary fw-semibold text-uppercase tracking-wider">Pts</span>
                                                </div>
                                                <div class="col">
                                                    <div class="card-body p-4">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h5 class="card-title fw-bold mb-0 text-dark"><?= Security::e($a['title']) ?></h5>
                                                        </div>
                                                        <p class="card-text text-secondary mb-3 lh-base" style="font-size: 0.95rem;"><?= nl2br(Security::e($a['instructions'])) ?></p>
                                                        <div class="d-flex justify-content-between align-items-center pt-2">
                                                            <span class="badge bg-light text-secondary border border-secondary border-opacity-25 px-3 py-2 fw-medium rounded-pill"><i class="bi bi-calendar-event me-2"></i> Due: <?= Security::e($a['due_date'] ?? 'No Due Date') ?></span>
                                                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-semibold hover-lift" onclick="document.getElementById('grading-tab').click();">Grade Submissions</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-4">
                            <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 2rem;">
                                <div class="card-body p-4 bg-light rounded-4 border">
                                    <h5 class="card-title fw-bold mb-4">Create Assignment</h5>
                                    <form method="post" action="index.php?page=add-assignment">
                                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                        <input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0) ?>">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">Assignment Title</label>
                                            <input class="form-control bg-white" name="title" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">Instructions / Prompt</label>
                                            <textarea class="form-control bg-white" name="instructions" rows="4" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">Due Date & Time</label>
                                            <input class="form-control bg-white" type="datetime-local" name="due_date">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label small fw-semibold text-secondary">Total Points</label>
                                            <input class="form-control bg-white" type="number" step="0.5" name="max_score" value="100">
                                        </div>
                                        
                                        <button class="btn btn-primary w-100 fw-semibold py-2 rounded-3">Publish Assignment</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quizzes Tab -->
                <div class="tab-pane fade" id="quizzes" role="tabpanel" aria-labelledby="quizzes-tab">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <h4 class="fw-bold mb-4">Quizzes & Exams</h4>
                            <?php if (empty($quizzes)): ?>
                                <div class="text-center p-5 bg-light rounded-4 border border-dashed">
                                    <i class="bi bi-ui-checks-grid fs-1 text-secondary opacity-50"></i>
                                    <h5 class="mt-3 fw-bold text-secondary">No assessments found</h5>
                                    <p class="text-muted">Use the quiz builder to create interactive exams.</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($quizzes as $q): ?>
                                        <div class="col-md-6">
                                            <div class="card shadow-sm border border-light rounded-4 h-100 hover-lift">
                                                <div class="card-body p-4">
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 d-inline-block">
                                                            <i class="bi bi-list-check fs-5"></i>
                                                        </div>
                                                        <span class="badge bg-light text-dark border"><?= (int) $q['total_marks'] ?> Pts</span>
                                                    </div>
                                                    <h6 class="fw-bold fs-5 mb-2"><?= Security::e($q['title']) ?></h6>
                                                    <div class="text-secondary small fw-medium mb-4">
                                                        <i class="bi bi-clock me-1"></i> 30 Mins Duration
                                                    </div>
                                                    <button class="btn btn-light w-100 fw-semibold text-primary">Manage Questions</button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-4">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-bold mb-3">New Quiz</h5>
                                    <p class="text-secondary small mb-4">Choose how you want to create your assessment.</p>
                                    
                                    <div class="d-grid gap-3">
                                        <button class="btn border text-start p-3 rounded-4 d-flex align-items-center gap-3 hover-border-primary bg-white transition-all">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-magic fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">Interactive Builder</div>
                                                <div class="small text-secondary mt-1">Create MCQs & forms manually.</div>
                                            </div>
                                        </button>
                                        
                                        <button class="btn border text-start p-3 rounded-4 d-flex align-items-center gap-3 hover-border-primary bg-white transition-all">
                                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-file-earmark-spreadsheet fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">CSV Upload</div>
                                                <div class="small text-secondary mt-1">Bulk upload from template.</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grading Tab -->
                <div class="tab-pane fade" id="grading" role="tabpanel" aria-labelledby="grading-tab">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                        <h4 class="fw-bold mb-0">Submissions Pipeline</h4>
                        <div class="d-flex gap-2">
                            <select class="form-select bg-light border-0 fw-medium shadow-sm rounded-pill px-4">
                                <option>All Assignments</option>
                                <?php foreach($assignments as $a): ?>
                                    <option value="<?= (int)$a['id'] ?>"><?= Security::e($a['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm"><i class="bi bi-lightning-charge-fill text-warning me-2"></i> Quick-Grade</button>
                        </div>
                    </div>
                    
                    <div class="table-responsive rounded-4 border overflow-hidden">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3 px-4 fw-semibold text-secondary">Trainee</th>
                                    <th class="py-3 px-4 fw-semibold text-secondary">Assignment</th>
                                    <th class="py-3 px-4 fw-semibold text-secondary">Status</th>
                                    <th class="py-3 px-4 fw-semibold text-secondary">Submitted</th>
                                    <th class="py-3 px-4 fw-semibold text-secondary">Score</th>
                                    <th class="py-3 px-4 fw-semibold text-secondary text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($submissions)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-5">No submissions yet for this course.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($submissions as $index => $sub): ?>
                                        <?php 
                                        $isGraded = ($sub['status'] === 'graded');
                                        $panelId = 'gradePanel' . $index;
                                        $initial = strtoupper(substr($sub['trainee_name'], 0, 1));
                                        ?>
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-<?= $isGraded ? 'primary' : 'secondary' ?> text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;"><?= Security::e($initial) ?></div>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?= Security::e($sub['trainee_name']) ?></div>
                                                        <div class="text-muted small"><?= Security::e($sub['trainee_email'] ?? '') ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 fw-medium"><?= Security::e($sub['assignment_title']) ?></td>
                                            <td class="px-4 py-3">
                                                <?php if ($isGraded): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-2">Graded</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3 py-2">Needs Grading</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-secondary small"><?= date('M j, Y H:i', strtotime($sub['submitted_at'])) ?></td>
                                            <td class="px-4 py-3">
                                                <?php if ($isGraded): ?>
                                                    <strong class="text-success fs-6"><?= (float)$sub['score'] ?> / 100</strong>
                                                <?php else: ?>
                                                    <span class="text-muted fw-semibold">-- / 100</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-end">
                                                <?php if ($isGraded): ?>
                                                    <button class="btn btn-sm btn-light text-secondary rounded-pill px-3 fw-medium" data-bs-toggle="collapse" data-bs-target="#<?= $panelId ?>">Edit Grade</button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-primary fw-semibold rounded-pill px-3" data-bs-toggle="collapse" data-bs-target="#<?= $panelId ?>">Review</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        
                                        <!-- In-line Grading Panel Collapse -->
                                        <tr class="collapse" id="<?= $panelId ?>">
                                            <td colspan="6" class="p-0 border-bottom">
                                                <div class="bg-light p-4 p-md-5 border-top shadow-inner">
                                                    <div class="row g-5">
                                                        <div class="col-md-7">
                                                            <h6 class="fw-bold mb-4 text-uppercase tracking-wider text-secondary small"><i class="bi bi-file-earmark-pdf me-2"></i> Trainee Submission Document</h6>
                                                            <!-- Embedded Document Viewer Mock -->
                                                            <div class="border rounded-4 bg-white p-5 text-center text-muted shadow-sm d-flex align-items-center justify-content-center flex-column" style="height: 400px;">
                                                                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                                                    <i class="bi bi-filetype-pdf fs-2"></i>
                                                                </div>
                                                                <h5 class="fw-bold text-dark"><?= Security::e($sub['file_path']) ?></h5>
                                                                <a href="storage/uploads/<?= Security::e($sub['file_path']) ?>" download class="btn btn-outline-secondary rounded-pill px-4 fw-medium mt-4"><i class="bi bi-cloud-download me-2"></i> Download File</a>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="bg-white p-4 rounded-4 shadow-sm border h-100">
                                                                <h6 class="fw-bold mb-4 text-uppercase tracking-wider text-secondary small"><i class="bi bi-check2-circle me-2"></i> Evaluation Panel</h6>
                                                                <form method="post" action="index.php?page=grade-submission">
                                                                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                                                    <input type="hidden" name="submission_id" value="<?= (int)$sub['id'] ?>">
                                                                    <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
                                                                    <div class="mb-4">
                                                                        <label class="form-label fw-semibold">Score (out of 100)</label>
                                                                        <div class="input-group input-group-lg">
                                                                            <input type="number" step="0.5" class="form-control fw-bold fs-4 text-primary bg-light" name="score" value="<?= $isGraded ? (float)$sub['score'] : '' ?>" placeholder="0" required>
                                                                            <span class="input-group-text bg-light text-muted fw-bold">/ 100</span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="mb-4 p-3 border rounded-3 bg-light">
                                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                                            <label class="form-label small fw-bold mb-0">Originality Report</label>
                                                                            <span class="badge bg-success">Safe</span>
                                                                        </div>
                                                                        <div class="progress bg-white" style="height: 8px;">
                                                                            <div class="progress-bar bg-success" role="progressbar" style="width: 12%;"></div>
                                                                        </div>
                                                                        <div class="text-secondary small mt-2 fw-medium">12% Similarity found</div>
                                                                    </div>
                                                                    
                                                                    <div class="mb-4">
                                                                        <label class="form-label fw-semibold">Instructor Feedback</label>
                                                                        <textarea class="form-control bg-light" name="feedback" rows="4" placeholder="Great job on the introduction, but..."><?= Security::e($sub['feedback'] ?? '') ?></textarea>
                                                                    </div>
                                                                    <button class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm hover-lift"><i class="bi bi-send-check me-2"></i> Submit Final Grade</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* Custom Styles for the new Course Room Layout */
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
    background-color: var(--ims-primary) !important;
    color: white !important;
    box-shadow: 0 4px 6px -1px rgba(var(--ims-primary-rgb), 0.2), 0 2px 4px -1px rgba(var(--ims-primary-rgb), 0.1);
}
.tracking-wider {
    letter-spacing: 0.05em;
}
.hover-opacity-100:hover {
    opacity: 1 !important;
}
.backdrop-blur {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}
.border-dashed {
    border-style: dashed !important;
    border-width: 2px !important;
    border-color: #cbd5e1;
}
.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}
</style>

<script>
    // To handle URL hashes to open specific tabs
    document.addEventListener("DOMContentLoaded", function() {
        var hash = window.location.hash;
        if (hash) {
            var triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]');
            if (triggerEl) {
                triggerEl.click();
            }
        }
    });
</script>
