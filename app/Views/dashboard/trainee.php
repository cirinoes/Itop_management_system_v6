<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<style>
/* Scoped modern styling */
.nav-pills-modern .nav-link {
    border-radius: 0.5rem;
    color: var(--bs-body-color);
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.2s;
}
.nav-pills-modern .nav-link.active {
    background-color: var(--bs-primary);
    color: #fff;
    box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);
}
.material-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.material-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.quiz-card {
    border-left: 4px solid var(--bs-info);
}
.upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #f8f9fa;
}
.upload-zone.dragover {
    border-color: var(--bs-primary);
    background: rgba(var(--bs-primary-rgb), 0.05);
}
.announcement-item {
    transition: opacity 0.3s ease-in-out;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="section-label">Learning Management System</span>
        <h1 class="section-title mb-0">My Learning</h1>
    </div>
</div>

<?php
    $current = $enrolments[0] ?? null;
    $totalProgress = 0;
    $enrolled = count($enrolments);
    foreach ($enrolments as $row) {
        $totalProgress += (int) $row['progress_percent'];
    }
    $avgProgress = $enrolled ? (int) round($totalProgress / $enrolled) : 0;
?>

<div class="row g-4">
    <!-- ── Main Content ──────────────────────────── -->
    <div class="col-lg-8">
        <?php if ($current): ?>
            <div class="trainee-card p-0 overflow-hidden mb-4 animate-in">
                <div class="bg-primary text-white p-4" style="background: linear-gradient(135deg, var(--bs-primary) 0%, #2a52be 100%);">
                    <span class="badge bg-white text-primary mb-2 shadow-sm">Current Course</span>
                    <h2 class="h3 fw-bold mb-2"><?= Security::e($current['title']) ?></h2>
                    <div class="d-flex align-items-center gap-3">
                        <div class="progress flex-grow-1 bg-white bg-opacity-25" style="height: 6px;">
                            <div class="progress-bar bg-white" style="width: <?= (int) $current['progress_percent'] ?>%"></div>
                        </div>
                        <span class="small fw-semibold"><?= (int) $current['progress_percent'] ?>% Completed</span>
                    </div>
                </div>
                
                <div class="p-4 bg-white">
                    <ul class="nav nav-pills nav-pills-modern mb-4 gap-2" id="courseTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials" type="button" role="tab">Materials</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="assignments-tab" data-bs-toggle="tab" data-bs-target="#assignments" type="button" role="tab">
                                Assignments
                                <?php if (!empty($current['assignments'])): ?>
                                    <span class="badge bg-danger ms-1 rounded-pill"><?= count($current['assignments']) ?></span>
                                <?php endif; ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="quizzes-tab" data-bs-toggle="tab" data-bs-target="#quizzes" type="button" role="tab">Quizzes & Exams</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="courseTabsContent">
                        <!-- Materials Tab -->
                        <div class="tab-pane fade show active" id="materials" role="tabpanel">
                            <?php if (!empty($current['materials'])): ?>
                                <div class="row g-3">
                                    <?php foreach ($current['materials'] as $mat): ?>
                                        <div class="col-md-6">
                                            <div class="material-card border rounded p-3 h-100 d-flex flex-column">
                                                <div class="d-flex gap-3 mb-3">
                                                    <div class="material-icon flex-shrink-0 text-primary" style="width: 40px; height: 40px;">
                                                        <?php if ($mat['type'] === 'video'): ?>
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                                        <?php elseif ($mat['type'] === 'pdf'): ?>
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                        <?php else: ?>
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h4 class="h6 mb-1 fw-bold text-dark"><?= Security::e($mat['title']) ?></h4>
                                                        <div class="small text-muted">
                                                            <span class="badge bg-light text-dark border me-1"><?= strtoupper(Security::e($mat['type'])) ?></span>
                                                            <?= date('d M Y', strtotime($mat['created_at'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-auto pt-2 border-top d-flex gap-2">
                                                    <?php if ($mat['file_path']): ?>
                                                        <a href="<?= APP_URL ?>/storage/uploads/<?= Security::e($mat['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary w-50">Preview</a>
                                                        <a href="<?= APP_URL ?>/storage/uploads/<?= Security::e($mat['file_path']) ?>" download class="btn btn-sm btn-primary w-50">Download</a>
                                                    <?php elseif ($mat['external_url']): ?>
                                                        <a href="<?= Security::e($mat['external_url']) ?>" target="_blank" class="btn btn-sm btn-primary w-100">Open Link</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state py-5 text-center">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="text-muted mb-3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                                    <h4 class="h5">No Materials Yet</h4>
                                    <p class="text-muted">The instructor hasn't uploaded materials for this course.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Assignments Tab -->
                        <div class="tab-pane fade" id="assignments" role="tabpanel">
                            <?php if (!empty($current['assignments'])): ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($current['assignments'] as $assignment): ?>
                                        <?php
                                        $hasSubmission = isset($assignment['submission']) && $assignment['submission'];
                                        $submission = $assignment['submission'] ?? null;
                                        $dueTimestamp = $assignment['due_date'] ? strtotime($assignment['due_date']) : null;
                                        $isPastDue = $dueTimestamp ? time() > $dueTimestamp : false;
                                        ?>
                                        <div class="p-3 border rounded-3 bg-light hover-bg-white transition-colors position-relative overflow-hidden group">
                                            <div class="d-flex gap-3">
                                                <div class="bg-warning-subtle text-warning-emphasis rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width: 48px; height: 48px;">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h4 class="h6 fw-bold mb-1"><?= Security::e($assignment['title']) ?></h4>
                                                    <div class="text-muted small mb-2"><?= nl2br(Security::e($assignment['instructions'])) ?></div>
                                                    
                                                    <div class="d-flex gap-2 flex-wrap mb-2">
                                                        <span class="badge bg-white text-dark border">
                                                            <i class="far fa-calendar me-1"></i> Due: <?= $assignment['due_date'] ? date('d M Y, H:i', strtotime($assignment['due_date'])) : 'Anytime' ?>
                                                        </span>
                                                        <span class="badge bg-white text-dark border">Max Score: <?= (int)$assignment['max_score'] ?></span>
                                                    </div>

                                                    <?php if ($hasSubmission): ?>
                                                        <!-- Submission Status Table -->
                                                        <div class="mt-3 border rounded bg-white overflow-hidden shadow-sm">
                                                            <h5 class="h6 px-3 py-2 m-0 bg-light border-bottom text-primary fw-bold">Submission status</h5>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered m-0" style="font-size: 0.85rem;">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th class="bg-light w-25 text-muted px-3 py-2 align-middle">Submission status</th>
                                                                            <td class="px-3 py-2 bg-success-subtle text-success fw-semibold align-middle">Submitted for grading</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="bg-light text-muted px-3 py-2 align-middle">Grading status</th>
                                                                            <?php if ($submission['status'] === 'graded'): ?>
                                                                                <td class="px-3 py-2 bg-success-subtle text-success fw-semibold align-middle">Graded</td>
                                                                            <?php else: ?>
                                                                                <td class="px-3 py-2 align-middle">Not graded</td>
                                                                            <?php endif; ?>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="bg-light text-muted px-3 py-2 align-middle">Time remaining</th>
                                                                            <td class="px-3 py-2 align-middle">
                                                                                <?php
                                                                                if ($dueTimestamp) {
                                                                                    $subTime = strtotime($submission['submitted_at']);
                                                                                    if ($subTime <= $dueTimestamp) {
                                                                                        $diff = $dueTimestamp - $subTime;
                                                                                        $days = floor($diff / 86400);
                                                                                        $hours = floor(($diff % 86400) / 3600);
                                                                                        $mins = floor(($diff % 3600) / 60);
                                                                                        $parts = [];
                                                                                        if ($days > 0) $parts[] = $days . ' days';
                                                                                        if ($hours > 0) $parts[] = $hours . ' hours';
                                                                                        if ($mins > 0) $parts[] = $mins . ' mins';
                                                                                        $timeStr = empty($parts) ? 'less than a minute' : implode(' ', $parts);
                                                                                        echo 'Assignment was submitted ' . $timeStr . ' early';
                                                                                    } else {
                                                                                        $diff = $subTime - $dueTimestamp;
                                                                                        $days = floor($diff / 86400);
                                                                                        $hours = floor(($diff % 86400) / 3600);
                                                                                        $mins = floor(($diff % 3600) / 60);
                                                                                        $parts = [];
                                                                                        if ($days > 0) $parts[] = $days . ' days';
                                                                                        if ($hours > 0) $parts[] = $hours . ' hours';
                                                                                        if ($mins > 0) $parts[] = $mins . ' mins';
                                                                                        $timeStr = empty($parts) ? 'less than a minute' : implode(' ', $parts);
                                                                                        echo '<span class="text-danger fw-semibold">Assignment was submitted ' . $timeStr . ' late</span>';
                                                                                    }
                                                                                } else {
                                                                                    echo 'No due date';
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="bg-light text-muted px-3 py-2 align-middle">Last modified</th>
                                                                            <td class="px-3 py-2 align-middle"><?= date('l, d F Y, h:i A', strtotime($submission['submitted_at'])) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="bg-light text-muted px-3 py-2 align-middle">File submissions</th>
                                                                            <td class="px-3 py-2 align-middle">
                                                                                <a href="<?= APP_URL ?>/storage/submissions/<?= Security::e($submission['file_path']) ?>" target="_blank" class="text-decoration-none">
                                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                                                    <?= Security::e($submission['file_path']) ?>
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                        <?php if (!empty($submission['notes'])): ?>
                                                                        <tr>
                                                                            <th class="bg-light text-muted px-3 py-2 align-middle">Submission comments</th>
                                                                            <td class="px-3 py-2 text-muted fst-italic align-middle">
                                                                                <?= nl2br(Security::e($submission['notes'])) ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endif; ?>
                                                                        <?php if ($submission['status'] === 'graded'): ?>
                                                                        <tr>
                                                                            <th class="bg-light text-muted px-3 py-2 align-middle">Score</th>
                                                                            <td class="px-3 py-2 fw-bold text-primary align-middle"><?= (float) $submission['score'] ?> / <?= (float) $assignment['max_score'] ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="bg-light text-muted px-3 py-2 align-middle">Feedback</th>
                                                                            <td class="px-3 py-2 align-middle"><?= nl2br(Security::e($submission['feedback'])) ?></td>
                                                                        </tr>
                                                                        <?php endif; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <div class="mt-3 d-flex gap-2">
                                                            <?php if (!$isPastDue): ?>
                                                                <button class="btn btn-outline-primary btn-sm submit-assignment-btn fw-semibold" data-bs-toggle="modal" data-bs-target="#submitAssignmentModal" data-id="<?= (int)$assignment['id'] ?>" data-title="<?= Security::e($assignment['title']) ?>">Edit submission</button>
                                                                <form action="index.php?page=cancel-submission" method="POST" onsubmit="return confirm('Are you sure you want to remove your submission? This will delete the file completely.');">
                                                                    <input type="hidden" name="_csrf" value="<?= Security::csrfToken() ?>">
                                                                    <input type="hidden" name="assignment_id" value="<?= (int)$assignment['id'] ?>">
                                                                    <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold">Remove submission</button>
                                                                </form>
                                                            <?php else: ?>
                                                                <span class="text-danger small fw-bold mt-1">Due date has passed. You cannot remove or edit this submission.</span>
                                                            <?php endif; ?>
                                                        </div>

                                                    <?php else: ?>
                                                        <div class="d-flex align-items-center justify-content-end mt-3">
                                                            <?php if (!$isPastDue): ?>
                                                                <button class="btn btn-primary btn-sm submit-assignment-btn fw-semibold" data-bs-toggle="modal" data-bs-target="#submitAssignmentModal" data-id="<?= (int)$assignment['id'] ?>" data-title="<?= Security::e($assignment['title']) ?>">
                                                                    Submit Assignment
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="text-danger small fw-bold">Overdue</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state py-5 text-center">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="text-muted mb-3"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                    <h4 class="h5">No Pending Assignments</h4>
                                    <p class="text-muted">You have no pending assignments for this course.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Quizzes & Exams Tab -->
                        <div class="tab-pane fade" id="quizzes" role="tabpanel">
                            <?php if (!empty($current['quizzes'])): ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($current['quizzes'] as $quiz): ?>
                                        <div class="card shadow-sm border-0 quiz-card bg-light">
                                            <div class="card-body d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                                                <div class="flex-shrink-0 text-info bg-info bg-opacity-10 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h4 class="h6 fw-bold mb-1"><?= Security::e($quiz['title']) ?></h4>
                                                    <div class="text-muted small mb-2"><?= nl2br(Security::e($quiz['description'] ?? 'No description provided.')) ?></div>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <span class="badge bg-white text-dark border">
                                                            <i class="far fa-clock me-1"></i> <?= (int)($quiz['time_limit'] ?? 0) ?> mins
                                                        </span>
                                                        <span class="badge bg-white text-dark border">
                                                            Due: <?= !empty($quiz['due_date']) ? date('d M Y', strtotime($quiz['due_date'])) : 'Anytime' ?>
                                                        </span>
                                                        <span class="badge bg-secondary">Not Started</span>
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0 mt-3 mt-sm-0">
                                                    <a href="index.php?page=take-quiz&id=<?= (int)$quiz['id'] ?>" class="btn btn-outline-info shadow-sm px-4">Start Quiz</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state py-5 text-center">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="text-muted mb-3"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    <h4 class="h5">No Quizzes Scheduled</h4>
                                    <p class="text-muted">There are no quizzes or exams available right now.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="trainee-card animate-in text-center py-5">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted mb-3"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                <h3 class="fw-bold">No Active Courses</h3>
                <p class="text-muted">You are not enrolled in any courses yet.</p>
                <a href="index.php?page=courses" class="btn btn-primary mt-2 px-4 py-2">Browse Courses</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Sidebar ───────────────────────────────── -->
    <div class="col-lg-4">
        <?php if ($pendingEvaluations): ?>
            <div class="trainee-card border-warning mb-4 animate-in shadow-sm" style="background: rgba(255,193,7,0.05)">
                <div class="d-flex gap-3">
                    <div class="text-warning mt-1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div>
                        <h3 class="h6 fw-bold mb-1">Pending Evaluations</h3>
                        <p class="small text-muted mb-2">You have <?= count($pendingEvaluations) ?> completed course<?= count($pendingEvaluations) === 1 ? '' : 's' ?> needing feedback.</p>
                        <a href="index.php?page=trainee-evaluations" class="btn btn-sm btn-warning fw-semibold px-3">Evaluate Now</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="trainee-card animate-in mb-4 shadow-sm" style="animation-delay: 0.1s">
            <h2 class="h6 fw-bold mb-4 text-uppercase tracking-wide text-muted">Overall Progress</h2>
            <div class="text-center mb-3">
                <div class="display-4 fw-bold text-primary"><?= $avgProgress ?>%</div>
            </div>
            <div class="progress mb-4 rounded-pill" style="height: 10px;">
                <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" style="width: <?= $avgProgress ?>%"></div>
            </div>
            
            <?php if (count($enrolments) > 1): ?>
                <h3 class="h6 fw-bold mb-3">Other Courses</h3>
                <div class="accordion accordion-flush" id="otherCoursesAccordion">
                    <?php foreach (array_slice($enrolments, 1) as $index => $row): ?>
                        <div class="accordion-item bg-transparent border-bottom">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button collapsed bg-transparent px-0 py-3 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                                    <div class="d-flex flex-column w-100 me-2">
                                        <span class="fw-bold text-dark small text-truncate" style="max-width:200px" title="<?= Security::e($row['title']) ?>"><?= Security::e($row['title']) ?></span>
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <div class="progress flex-grow-1" style="height: 4px;">
                                                <div class="progress-bar <?= (int) $row['progress_percent'] === 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= (int) $row['progress_percent'] ?>%"></div>
                                            </div>
                                            <span class="small fw-semibold" style="font-size:0.7rem"><?= (int) $row['progress_percent'] ?>%</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#otherCoursesAccordion">
                                <div class="accordion-body px-0 py-2">
                                    <?php if (!empty($row['materials'])): ?>
                                        <div class="d-flex flex-column gap-2">
                                        <?php foreach ($row['materials'] as $mat): ?>
                                            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                                                <span class="small text-truncate me-2" style="max-width:140px"><?= Security::e($mat['title']) ?></span>
                                                <?php if ($mat['file_path']): ?>
                                                    <a href="<?= APP_URL ?>/storage/uploads/<?= Security::e($mat['file_path']) ?>" download class="btn btn-sm btn-outline-primary py-0" style="font-size: 0.7rem"><i class="fas fa-download"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small mb-0">No materials available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Announcements / Latest Updates -->
        <div class="trainee-card animate-in mb-4 shadow-sm" style="animation-delay: 0.2s">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h6 fw-bold mb-0 text-uppercase tracking-wide text-muted">Latest Updates</h2>
                <div class="spinner-border spinner-border-sm text-primary d-none" id="updates-spinner" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <div id="announcements-container">
                <?php foreach (array_slice($announcements, 0, 4) as $item): ?>
                    <div class="announcement-item mb-3 pb-3 border-bottom last-border-0 last-mb-0 last-pb-0">
                        <div class="fw-bold small mb-1 text-dark"><?= Security::e($item['title']) ?></div>
                        <div class="text-muted mb-2" style="font-size: 0.85rem; line-height: 1.4;"><?= nl2br(Security::e(Security::excerpt($item['body'], 80))) ?></div>
                        <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.7rem">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?= date('d M Y, H:i', strtotime($item['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($announcements)): ?>
                    <div class="text-center text-muted py-4">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <p class="small mb-0">No new announcements</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Submission Modal -->
<div class="modal fade" id="submitAssignmentModal" tabindex="-1" aria-labelledby="submitAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="submitAssignmentModalLabel">Submit Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4" id="modal-assignment-title">Upload your work for evaluation.</p>
                
                <form action="index.php?page=submit-assignment" method="POST" enctype="multipart/form-data" id="assignmentForm">
                    <input type="hidden" name="_csrf" value="<?= Security::csrfToken() ?>">
                    <input type="hidden" name="assignment_id" id="modal_assignment_id" value="">
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Upload File <span class="text-danger">*</span></label>
                        <div class="upload-zone" id="uploadZone">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-primary mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p class="mb-1 fw-semibold text-dark">Click to browse or drag file here</p>
                            <p class="small text-muted mb-0">Supported formats: PDF, DOC, DOCX, ZIP, JPG, PNG (Max 10MB)</p>
                            <input type="file" name="submission" id="submissionFile" style="display:none;" accept=".pdf,.doc,.docx,.zip,.jpg,.png">
                        </div>
                        <div id="file-name-display" class="mt-2 small text-primary fw-semibold d-none"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold small">Optional Notes for Instructor</label>
                        <textarea class="form-control bg-light border-0" id="notes" name="notes" rows="3" placeholder="Add any comments or clarifications here..."></textarea>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">Submit Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Drag and Drop File Upload Logic
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('submissionFile');
    const fileNameDisplay = document.getElementById('file-name-display');

    if(uploadZone) {
        uploadZone.addEventListener('click', () => fileInput.click());

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileName();
            }
        });

        fileInput.addEventListener('change', updateFileName);
    }

    function updateFileName() {
        if (fileInput.files.length > 0) {
            fileNameDisplay.textContent = 'Selected file: ' + fileInput.files[0].name;
            fileNameDisplay.classList.remove('d-none');
            uploadZone.style.borderColor = '';
            const errorMsg = document.getElementById('file-error-msg');
            if (errorMsg) errorMsg.remove();
        } else {
            fileNameDisplay.classList.add('d-none');
        }
    }

    // 2. Set Modal Data dynamically
    const submitBtns = document.querySelectorAll('.submit-assignment-btn');
    const assignmentIdInput = document.getElementById('modal_assignment_id');
    const modalTitle = document.getElementById('modal-assignment-title');

    submitBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            assignmentIdInput.value = this.getAttribute('data-id');
            modalTitle.textContent = 'Upload your work for: ' + this.getAttribute('data-title');
            
            // Reset form
            fileInput.value = '';
            fileNameDisplay.classList.add('d-none');
            document.getElementById('notes').value = '';
        });
    });

    // Move modal to body to avoid z-index/backdrop issues
    const modalEl = document.getElementById('submitAssignmentModal');
    if(modalEl) {
        document.body.appendChild(modalEl);
    }

    // Handle form submission validation
    const assignmentForm = document.getElementById('assignmentForm');
    if(assignmentForm) {
        assignmentForm.addEventListener('submit', function(e) {
            const errorMsg = document.getElementById('file-error-msg');
            if (fileInput.files.length === 0) {
                e.preventDefault();
                uploadZone.style.borderColor = 'var(--bs-danger)';
                if(!errorMsg) {
                    const msg = document.createElement('div');
                    msg.id = 'file-error-msg';
                    msg.className = 'text-danger small fw-bold mt-2';
                    msg.innerText = '⚠️ Please select a file to upload before submitting.';
                    uploadZone.parentNode.insertBefore(msg, fileNameDisplay);
                }
            } else {
                if(errorMsg) errorMsg.remove();
                // Use setTimeout to avoid canceling the form submission in some browsers
                setTimeout(() => {
                    const btn = assignmentForm.querySelector('button[type="submit"]');
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Submitting...';
                    btn.disabled = true;
                }, 10);
            }
        });
    }

    // 3. AJAX Polling for Latest Updates
    const announcementsContainer = document.getElementById('announcements-container');
    const updatesSpinner = document.getElementById('updates-spinner');
    
    function fetchUpdates() {
        if (!announcementsContainer) return;
        updatesSpinner.classList.remove('d-none');
        fetch('index.php?page=api-trainee-updates')
            .then(response => response.json())
            .then(res => {
                updatesSpinner.classList.add('d-none');
                if (res.status === 'success') {
                    renderAnnouncements(res.data);
                }
            })
            .catch(error => {
                updatesSpinner.classList.add('d-none');
                console.error('Error fetching updates:', error);
            });
    }

    function renderAnnouncements(data) {
        if (!data || data.length === 0) {
            announcementsContainer.innerHTML = `
                <div class="text-center text-muted py-4">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <p class="small mb-0">No new announcements</p>
                </div>
            `;
            return;
        }

        let html = '';
        // limit to 4
        data.slice(0, 4).forEach(item => {
            // formatting date
            const d = new Date(item.created_at.replace(/-/g, '/')); // cross browser compat
            
            let dateStr = item.created_at; // fallback
            if (!isNaN(d)) {
                // Get month names
                const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                dateStr = String(d.getDate()).padStart(2, '0') + ' ' + monthNames[d.getMonth()] + ' ' + d.getFullYear() + ', ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
            }
            
            // excerpt
            let body = item.body;
            if (body.length > 80) body = body.substring(0, 80) + '...';
            
            // html entities encode
            const escapeHTML = str => {
                if (!str) return '';
                return str.replace(/[&<>'"]/g, 
                    tag => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        "'": '&#39;',
                        '"': '&quot;'
                    }[tag])
                );
            };

            html += `
                <div class="announcement-item mb-3 pb-3 border-bottom last-border-0 last-mb-0 last-pb-0">
                    <div class="fw-bold small mb-1 text-dark">${escapeHTML(item.title)}</div>
                    <div class="text-muted mb-2" style="font-size: 0.85rem; line-height: 1.4;">${escapeHTML(body)}</div>
                    <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.7rem">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        ${dateStr}
                    </div>
                </div>
            `;
        });
        
        if (announcementsContainer.innerHTML.trim() !== html.trim()) {
            announcementsContainer.style.opacity = 0;
            setTimeout(() => {
                announcementsContainer.innerHTML = html;
                announcementsContainer.style.opacity = 1;
            }, 200);
        }
    }

    // Poll every 30 seconds
    setInterval(fetchUpdates, 30000);
});
</script>
