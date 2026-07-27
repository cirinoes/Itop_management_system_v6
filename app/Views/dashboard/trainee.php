<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

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
                <div class="bg-primary text-white p-4">
                    <span class="badge bg-white text-primary mb-2">Current Course</span>
                    <h2 class="h4 fw-bold mb-1"><?= Security::e($current['title']) ?></h2>
                    <p class="mb-0 opacity-75 small">Module progress: <?= (int) $current['progress_percent'] ?>%</p>
                </div>
                
                <div class="p-4">
                    <h3 class="h5 fw-bold mb-3">Learning Materials</h3>
                    
                    <?php if (!empty($current['materials'])): ?>
                        <?php foreach ($current['materials'] as $mat): ?>
                            <div class="material-card">
                                <div class="material-icon">
                                    <?php if ($mat['type'] === 'video'): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                    <?php elseif ($mat['type'] === 'pdf'): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="material-details">
                                    <div class="material-title"><?= Security::e($mat['title']) ?></div>
                                    <div class="material-meta">
                                        <span><?= strtoupper(Security::e($mat['type'])) ?></span>
                                        <span>Uploaded: <?= date('d M Y', strtotime($mat['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="material-actions">
                                    <?php if ($mat['file_path']): ?>
                                        <a href="<?= APP_URL ?>/storage/uploads/<?= Security::e($mat['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Preview</a>
                                        <a href="<?= APP_URL ?>/storage/uploads/<?= Security::e($mat['file_path']) ?>" download class="btn btn-sm btn-primary">Download</a>
                                    <?php elseif ($mat['external_url']): ?>
                                        <a href="<?= Security::e($mat['external_url']) ?>" target="_blank" class="btn btn-sm btn-primary">Open Link</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state py-4">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                            <h4 class="h6 mt-2">No Materials Yet</h4>
                            <p class="small">The instructor hasn't uploaded materials for this course.</p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($current['assignments'])): ?>
                        <h3 class="h5 fw-bold mb-3 mt-4">Assignments</h3>
                        <?php foreach ($current['assignments'] as $assignment): ?>
                            <div class="material-card">
                                <div class="material-icon text-warning" style="background: rgba(255,193,7,0.1)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                </div>
                                <div class="material-details">
                                    <div class="material-title"><?= Security::e($assignment['title']) ?></div>
                                    <div class="material-meta">
                                        <span>Due: <?= $assignment['due_date'] ? date('d M Y', strtotime($assignment['due_date'])) : 'No due date' ?></span>
                                    </div>
                                </div>
                                <div class="material-actions">
                                    <a href="index.php?page=course-room&id=<?= (int) $current['course_id'] ?>" class="btn btn-sm btn-primary">Submit</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
        <?php else: ?>
            <div class="trainee-card animate-in">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    <h3>No Active Courses</h3>
                    <p>You are not enrolled in any courses yet.</p>
                    <a href="index.php?page=courses" class="btn btn-primary mt-2">Browse courses</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Other enrolled courses -->
        <?php if (count($enrolments) > 1): ?>
            <h3 class="h5 fw-bold mb-3 mt-4">Other Enrolled Courses</h3>
            <div class="accordion" id="otherCoursesAccordion">
                <?php foreach (array_slice($enrolments, 1) as $index => $row): ?>
                    <div class="accordion-item mb-3 border rounded overflow-hidden">
                        <h2 class="accordion-header" id="heading<?= $index ?>">
                            <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>">
                                <div class="d-flex flex-column w-100 me-3">
                                    <span class="fw-bold text-dark"><?= Security::e($row['title']) ?></span>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="progress flex-grow-1" style="height: 4px;">
                                            <div class="progress-bar <?= (int) $row['progress_percent'] === 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= (int) $row['progress_percent'] ?>%"></div>
                                        </div>
                                        <span class="small text-muted"><?= (int) $row['progress_percent'] ?>%</span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index ?>" data-bs-parent="#otherCoursesAccordion">
                            <div class="accordion-body bg-white">
                                <?php if (!empty($row['materials'])): ?>
                                    <?php foreach ($row['materials'] as $mat): ?>
                                        <div class="material-card mb-2">
                                            <div class="material-details">
                                                <div class="material-title" style="font-size:0.9rem"><?= Security::e($mat['title']) ?></div>
                                                <div class="material-meta" style="font-size:0.75rem">
                                                    <span><?= strtoupper(Security::e($mat['type'])) ?></span>
                                                </div>
                                            </div>
                                            <div class="material-actions">
                                                <?php if ($mat['file_path']): ?>
                                                    <a href="<?= APP_URL ?>/storage/uploads/<?= Security::e($mat['file_path']) ?>" download class="btn btn-sm btn-outline-primary" style="padding: 0.15rem 0.5rem; font-size: 0.75rem">Download</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted small mb-0">No materials available for this course.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Sidebar ───────────────────────────────── -->
    <div class="col-lg-4">
        <?php if ($pendingEvaluations): ?>
            <div class="trainee-card border-warning mb-4 animate-in" style="background: rgba(255,193,7,0.05)">
                <div class="d-flex gap-3">
                    <div class="text-warning">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div>
                        <h3 class="h6 fw-bold mb-1">Pending Evaluations</h3>
                        <p class="small text-muted mb-2">You have <?= count($pendingEvaluations) ?> completed course<?= count($pendingEvaluations) === 1 ? '' : 's' ?> needing feedback.</p>
                        <a href="index.php?page=trainee-evaluations" class="btn btn-sm btn-warning">Evaluate Now</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="trainee-card animate-in mb-4" style="animation-delay: 0.1s">
            <h2 class="h5 fw-bold mb-4">Overall Progress</h2>
            <div class="text-center mb-3">
                <div class="display-4 fw-bold text-primary"><?= $avgProgress ?>%</div>
                <div class="text-muted small text-uppercase fw-semibold tracking-wide">Completion Rate</div>
            </div>
            <div class="progress mb-3" style="height: 8px;">
                <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" style="width: <?= $avgProgress ?>%"></div>
            </div>
            
            <ul class="list-unstyled mb-0">
                <?php foreach ($enrolments as $index => $row): ?>
                    <li class="d-flex justify-content-between align-items-center mb-2 pb-2 <?= $index < count($enrolments)-1 ? 'border-bottom' : '' ?>">
                        <span class="text-truncate me-3 small" title="<?= Security::e($row['title']) ?>"><?= Security::e($row['title']) ?></span>
                        <span class="badge <?= (int) $row['progress_percent'] === 100 ? 'bg-success' : 'bg-light text-dark border' ?>">
                            <?= (int) $row['progress_percent'] ?>%
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Announcements -->
        <div class="trainee-card animate-in mb-4" style="animation-delay: 0.2s">
            <h2 class="h5 fw-bold mb-4">Latest Updates</h2>
            <?php foreach (array_slice($announcements, 0, 4) as $item): ?>
                <div class="mb-3 pb-3 border-bottom last-border-0 last-mb-0 last-pb-0">
                    <div class="fw-semibold small mb-1"><?= Security::e($item['title']) ?></div>
                    <div class="text-muted" style="font-size: 0.8rem"><?= Security::e(Security::excerpt($item['body'], 80)) ?></div>
                    <div class="text-muted mt-1" style="font-size: 0.7rem"><?= date('d M Y', strtotime($item['created_at'])) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($announcements)): ?>
                <div class="text-center text-muted py-3">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="mb-2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <p class="small mb-0">No new announcements</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

