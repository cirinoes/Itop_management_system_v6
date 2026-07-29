<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="section-label">Instructor Hub</span>
        <h1 class="section-title mb-0">My Courses</h1>
    </div>
    <div class="d-flex gap-2">
        <!-- Optional: A button to create a new course if they have permissions -->
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <!-- Filters -->
    <div class="status-tabs mb-0">
        <a class="status-tab active" href="#">Active Courses</a>
        <a class="status-tab" href="#">Past Courses</a>
        <a class="status-tab" href="#">Drafts</a>
    </div>

    <div class="input-group input-group-sm" style="width: 250px;">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control border-start-0 ps-0" placeholder="Search courses...">
    </div>
</div>

<!-- Courses Grid -->
<div class="row g-4">
    <?php foreach ($courses as $course): ?>
        <div class="col-md-6 col-lg-4">
            <?php View::partial('partials/course-card', ['course' => $course, 'actions' => 'instructor']); ?>
        </div>
    <?php endforeach; ?>
    <?php if (!$courses): ?>
        <div class="col-12">
            <div class="empty-state bg-white border rounded shadow-sm py-5">
                <div class="empty-state-icon fs-1 mb-3">📚</div>
                <div class="empty-state-title fw-bold">No assigned courses</div>
                <p class="text-muted small">You are not assigned to any courses yet.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Claim/Teach Course Panel -->
<?php
$unassignedCourses = [];
try {
    $db = \App\Core\Model::getDb();
    $unassignedCourses = $db->query('SELECT * FROM courses WHERE instructor_id IS NULL AND status IN ("published", "active") ORDER BY title')->fetchAll();
} catch (\Exception $e) {}
?>
<?php if (!empty($unassignedCourses)): ?>
    <div class="mt-5 p-4 border rounded bg-white shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">Available to Teach</h5>
                <p class="text-muted small mb-0">Select a course to claim it and become the instructor.</p>
            </div>
            <form method="post" action="index.php?page=instructor-claim-course" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <select class="form-select form-select-sm" name="course_id" required style="min-width: 250px;">
                    <option value="">Choose available course...</option>
                    <?php foreach ($unassignedCourses as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= Security::e($c['title']) ?> (<?= Security::e($c['category']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary btn-sm flex-shrink-0 px-3">Claim Course</button>
            </form>
        </div>
    </div>
<?php endif; ?>
