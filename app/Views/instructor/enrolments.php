<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="section-label">Instructor Enrolments</span>
        <h1 class="section-title mb-0">Course Roster</h1>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Manual Enroll</button>
        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Export CSV</button>
    </div>
</div>

<!-- ── Data Controls ───────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Status Tabs -->
    <?php $statusFilter = Security::cleanString($_GET['status'] ?? ''); ?>
    <div class="status-tabs mb-0">
        <a class="status-tab <?= $statusFilter === '' ? 'active' : '' ?>" href="index.php?page=instructor-enrolments">All Enrolments</a>
        <a class="status-tab <?= $statusFilter === 'pending' ? 'active' : '' ?>" href="index.php?page=instructor-enrolments&status=pending">Pending <span class="badge bg-warning text-dark ms-1">New</span></a>
        <a class="status-tab <?= $statusFilter === 'active' ? 'active' : '' ?>" href="index.php?page=instructor-enrolments&status=active">Active</a>
        <a class="status-tab <?= $statusFilter === 'completed' ? 'active' : '' ?>" href="index.php?page=instructor-enrolments&status=completed">Completed</a>
    </div>

    <!-- Search & Filter -->
    <div class="d-flex gap-2">
        <div class="input-group input-group-sm" style="width: 250px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" placeholder="Search trainee name...">
        </div>
        <select class="form-select form-select-sm w-auto">
            <option>All Courses</option>
            <option>Course 1</option>
            <option>Course 2</option>
        </select>
    </div>
</div>

<div class="overview-panel animate-in">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;"><input class="form-check-input" type="checkbox" title="Select all trainees for bulk actions"></th>
                    <th>Trainee Details</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Progress / Performance</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $filtered = $enrolments;
                if ($statusFilter) {
                    $filtered = array_filter($enrolments, fn($r) => ($r['status'] ?? '') === $statusFilter);
                }
                ?>
                <?php foreach ($filtered as $index => $row): ?>
                    <!-- Mocking At-Risk and Scores for presentation -->
                    <?php 
                        $isAtRisk = ($index % 4 === 0) && ($row['status'] === 'active'); 
                        $progress = ($row['status'] === 'completed') ? 100 : rand(20, 80);
                    ?>
                    <tr>
                        <td><input class="form-check-input" type="checkbox" title="Select trainee for bulk actions"></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                    <?= substr(Security::e($row['trainee_name']), 0, 1) ?>
                                </div>
                                <div>
                                    <strong class="text-primary" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#traineeModal<?= $index ?>"><?= Security::e($row['trainee_name']) ?></strong>
                                    <?php if ($isAtRisk): ?>
                                        <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">At Risk</span>
                                    <?php endif; ?>
                                    <div class="small text-muted"><?= Security::e($row['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="fw-semibold text-dark"><?= Security::e($row['course_title']) ?></span></td>
                        <td>
                            <?php if (($row['status'] ?? 'pending') === 'active'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Active</span>
                            <?php elseif (($row['status'] ?? 'pending') === 'pending'): ?>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Pending</span>
                            <?php elseif (($row['status'] ?? 'pending') === 'completed'): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Completed</span>
                            <?php endif; ?>
                        </td>
                        <td style="min-width: 150px;">
                            <?php if (($row['status'] ?? 'pending') !== 'pending'): ?>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span><?= $progress ?>% Complete</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar <?= $isAtRisk ? 'bg-danger' : 'bg-primary' ?>" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small">Awaiting Approval</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2 align-items-center">
                                <?php if (($row['status'] ?? '') === 'pending'): ?>
                                    <form class="m-0" method="post" action="index.php?page=instructor-enrolment-status">
                                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                        <button name="status" value="active" class="btn btn-sm btn-success" title="Approve">
                                            <i class="bi bi-check-lg me-1"></i> Approve
                                        </button>
                                        <button name="status" value="rejected" class="btn btn-sm btn-outline-danger" title="Reject">
                                            <i class="bi bi-x-lg me-1"></i> Reject
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="index.php?page=messages" class="btn btn-sm btn-outline-primary" title="Direct Message"><i class="bi bi-chat-dots"></i> Message</a>
                                    <button class="btn btn-sm btn-outline-secondary" title="View Profile" data-bs-toggle="modal" data-bs-target="#traineeModal<?= $index ?>"><i class="bi bi-person-lines-fill"></i> Profile</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>


                <?php endforeach; ?>
                <?php if (empty($filtered)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-people fs-1 d-block mb-3"></i>
                        No enrolments found for your courses.
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination Mock -->
    <?php if (!empty($filtered)): ?>
    <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light rounded-bottom">
        <span class="text-muted small">Showing 1 to <?= count($filtered) ?> of <?= count($filtered) ?> entries</span>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
    </div>
    <?php endif; ?>
</div>

<!-- Modals -->
<?php foreach ($filtered as $index => $row): ?>
    <?php $progress = ($row['status'] === 'completed') ? 100 : rand(20, 80); ?>
    <div class="modal fade" id="traineeModal<?= $index ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-0">
                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        <?= substr(Security::e($row['trainee_name']), 0, 1) ?>
                    </div>
                    <h4 class="mb-1"><?= Security::e($row['trainee_name']) ?></h4>
                    <p class="text-muted small mb-3"><?= Security::e($row['email']) ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <a href="index.php?page=messages" class="btn btn-primary btn-sm"><i class="bi bi-chat-dots"></i> Message</a>
                        <a href="index.php?page=instructor-reports" class="btn btn-outline-secondary btn-sm">Full Report</a>
                    </div>
                    
                    <div class="row g-2 text-center border-top pt-3">
                        <div class="col-6">
                            <div class="text-muted small">Course</div>
                            <div class="fw-semibold text-truncate"><?= Security::e($row['course_title']) ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Enrollment Date</div>
                            <div class="fw-semibold"><?= date('M j, Y', strtotime($row['created_at'])) ?></div>
                        </div>
                        <div class="col-6 mt-3">
                            <div class="text-muted small">Avg Quiz Score</div>
                            <div class="fw-semibold">82%</div> <!-- Mock -->
                        </div>
                        <div class="col-6 mt-3">
                            <div class="text-muted small">Completion</div>
                            <div class="fw-semibold"><?= $progress ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<style>
@media (min-width: 992px) {
    /* Offset modals by sidebar width so they center perfectly in the main content area */
    [id^="traineeModal"] .modal-dialog {
        margin-left: calc(50% - 250px + 260px/2); /* Adjust based on sidebar */
        transform: translateX(-50%);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id^="traineeModal"]').forEach(function(modal) {
        document.body.appendChild(modal);
    });
});
</script>
