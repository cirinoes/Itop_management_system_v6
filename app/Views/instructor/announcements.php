<?php use App\Core\Auth; use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="section-label">Communications</span>
        <h1 class="section-title mb-0">Announcements</h1>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?page=instructor-dashboard" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<div class="row g-4">
    
    <!-- Left Column: Create Announcement -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-4"><i class="bi bi-megaphone"></i> Broadcast New Announcement</h5>
                
                <form method="post" action="index.php?page=save-announcement">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Target Audience</label>
                        <select class="form-select form-select-sm" name="course_id">
                            <option value="">All My Courses</option>
                            <option value="1">Introduction to Programming</option> <!-- Mock options -->
                            <option value="2">Advanced Data Structures</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Subject</label>
                        <input class="form-control" name="title" placeholder="e.g. Midterm Exam Schedule Updates" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Message</label>
                        <!-- Mocking a rich text editor toolbar -->
                        <div class="border rounded border-bottom-0 rounded-bottom-0 bg-light p-2 d-flex gap-2 text-muted">
                            <button type="button" class="btn btn-sm btn-light"><i class="bi bi-type-bold"></i></button>
                            <button type="button" class="btn btn-sm btn-light"><i class="bi bi-type-italic"></i></button>
                            <button type="button" class="btn btn-sm btn-light"><i class="bi bi-type-underline"></i></button>
                            <span class="border-end mx-1"></span>
                            <button type="button" class="btn btn-sm btn-light"><i class="bi bi-list-ul"></i></button>
                            <button type="button" class="btn btn-sm btn-light"><i class="bi bi-list-ol"></i></button>
                            <span class="border-end mx-1"></span>
                            <button type="button" class="btn btn-sm btn-light"><i class="bi bi-link-45deg"></i></button>
                            <button type="button" class="btn btn-sm btn-light"><i class="bi bi-image"></i></button>
                        </div>
                        <textarea class="form-control border-top-0 rounded-top-0 border shadow-none" name="content" rows="6" placeholder="Write your announcement here..." required></textarea>
                    </div>

                    <!-- Publishing Options (Mocked functionality) -->
                    <div class="row g-3 mb-4 border-top pt-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold d-block">Scheduled Publishing</label>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="schedulePublish">
                                <label class="form-check-label small" for="schedulePublish">Publish later</label>
                            </div>
                            <input type="datetime-local" class="form-control form-control-sm text-muted bg-light" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold d-block">Multi-Channel Push</label>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" id="pushDashboard" checked>
                                <label class="form-check-label small" for="pushDashboard">Dashboard Banner</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" id="pushEmail" checked>
                                <label class="form-check-label small" for="pushEmail">Email Notification</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light text-muted px-4">Save Draft</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send"></i> Broadcast Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: History Feed -->
    <div class="col-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">Recent Announcements</h5>
            <div class="input-group input-group-sm w-auto">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-start-0 ps-0" placeholder="Search...">
            </div>
        </div>

        <div class="d-flex flex-column gap-3">
            <?php if (empty($announcements)): ?>
                <div class="text-center p-5 bg-white rounded shadow-sm border text-muted">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-2 mb-0 small">No announcements sent yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($announcements as $a): ?>
                    <div class="card shadow-sm border-0 announcement-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-secondary border"><i class="bi bi-bookmark"></i> All Courses</span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0 shadow-none" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item small" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                        <li><a class="dropdown-item small text-danger" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold mb-1"><?= Security::e($a['title']) ?></h6>
                            <p class="text-muted small mb-3 text-truncate" style="max-height: 2.8em; overflow: hidden; white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?= Security::e(strip_tags($a['content'])) ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                <span class="text-muted small" style="font-size: 0.75rem;"><i class="bi bi-clock"></i> <?= date('M j, Y g:i A', strtotime($a['created_at'])) ?></span>
                                <!-- Engagement Tracker Mock -->
                                <span class="badge bg-success bg-opacity-10 text-success border border-success" title="Viewed by 85% of trainees">
                                    <i class="bi bi-eye"></i> 85% Read
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .announcement-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .announcement-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
    }
</style>
