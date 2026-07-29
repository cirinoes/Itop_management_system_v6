<?php use App\Core\Security; use App\Core\View; ?>
<section class="container-fluid py-4">
    <?php View::partial('partials/role-nav'); ?>
    
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-semibold">Admin</span>
                <h1 class="h3 mb-0 fw-bold">Training Reports</h1>
            </div>
            <p class="text-muted mb-0">Platform-wide analytics for trainees, completion rates, and certificates.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary shadow-sm hover-lift rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-download me-2"></i> Export Data
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white">
        <div class="card-body p-4">
            <form class="row g-3 align-items-end" method="get">
                <input type="hidden" name="page" value="reports">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-semibold text-uppercase tracking-wider mb-2">Academy</label>
                    <select class="form-select form-select-lg bg-light border-0" name="academy_id">
                        <option value="">All academies</option>
                        <?php foreach ($academies as $academy): ?>
                            <option value="<?= (int) $academy['id'] ?>" <?= $academyId === (int) $academy['id'] ? 'selected' : '' ?>>
                                <?= Security::e($academy['code']) ?> - <?= Security::e($academy['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-semibold text-uppercase tracking-wider mb-2">Course</label>
                    <select class="form-select form-select-lg bg-light border-0" name="course_id">
                        <option value="">All courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= (int) $course['id'] ?>" <?= $courseId === (int) $course['id'] ? 'selected' : '' ?>>
                                <?= Security::e($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small fw-semibold text-uppercase tracking-wider mb-2">Date From</label>
                    <input class="form-control form-control-lg bg-light border-0" type="date" name="date_from">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm hover-lift fw-semibold"><i class="bi bi-funnel me-2"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <?php 
        $colors = ['primary', 'success', 'warning', 'info'];
        $icons = ['bi-people-fill', 'bi-person-check-fill', 'bi-mortarboard-fill', 'bi-award-fill'];
        $i = 0;
        foreach ($summary as $label => $value): 
            $color = $colors[$i % 4];
            $icon = $icons[$i % 4];
        ?>
            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm h-100 position-relative overflow-hidden hover-lift">
                    <div class="position-absolute end-0 top-0 p-3 opacity-10">
                        <i class="bi <?= $icon ?>" style="font-size: 4rem; color: var(--bs-<?= $color ?>);"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <span class="text-secondary small fw-bold text-uppercase tracking-wider d-block mb-2"><?= Security::e(ucwords(str_replace('_', ' ', $label))) ?></span>
                        <div class="display-5 fw-bold text-dark"><?= (int) $value ?></div>
                    </div>
                    <div class="card-footer bg-<?= $color ?> border-0 p-1"></div>
                </div>
            </div>
        <?php $i++; endforeach; ?>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0">Course Completion Rate</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="completionChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0">Certificate Issuance</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="certificatesChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Detailed Course Metrics</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-uppercase small fw-semibold text-secondary py-3 px-4">Course</th>
                                <th class="text-uppercase small fw-semibold text-secondary py-3">Completion</th>
                                <th class="text-uppercase small fw-semibold text-secondary py-3">Attendance</th>
                                <th class="text-uppercase small fw-semibold text-secondary py-3 text-center">Assignments</th>
                                <th class="text-uppercase small fw-semibold text-secondary py-3 text-center">Evaluations</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php foreach ($completion as $row): 
                                $att = current(array_filter($attendance, fn($a) => $a['title'] === $row['title'])) ?: ['attendance_rate' => 0];
                                $asn = current(array_filter($assignments, fn($a) => $a['title'] === $row['title'])) ?: ['assignments' => 0, 'submissions' => 0];
                                $eval = current(array_filter($evaluations, fn($e) => $e['title'] === $row['title'])) ?: ['avg_rating' => 0];
                            ?>
                                <tr>
                                    <td class="px-4 py-3 fw-medium"><?= Security::e($row['title']) ?></td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-success rounded-pill" style="width: <?= (int)$row['rate'] ?>%"></div>
                                            </div>
                                            <span class="small fw-semibold"><?= (int)$row['rate'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary rounded-pill" style="width: <?= (int)$att['attendance_rate'] ?>%"></div>
                                            </div>
                                            <span class="small fw-semibold"><?= (int)$att['attendance_rate'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1"><?= (int)$asn['submissions'] ?> / <?= (int)$asn['assignments'] ?></span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1 text-warning">
                                            <i class="bi bi-star-fill"></i> <span class="text-dark fw-semibold ms-1"><?= number_format((float)$eval['avg_rating'], 1) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($completion)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No course data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="get" action="index.php">
                <input type="hidden" name="page" value="export-report">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title h5 fw-bold">Export Report Data</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Report Type</label>
                        <select class="form-select form-select-lg bg-light border-0" name="report_type" required>
                            <option value="completion">Course Completion</option>
                            <option value="attendance">Attendance Records</option>
                            <option value="certificates">Certificate Issuance</option>
                            <option value="master_data">Master Data Statistics</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Format</label>
                        <select class="form-select form-select-lg bg-light border-0" name="format">
                            <option value="csv">CSV (Excel)</option>
                            <option value="pdf">PDF Document</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Download Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared Chart Options
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#6c757d';
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, grid: { borderDash: [4, 4], color: '#f0f0f0' } },
            x: { grid: { display: false } }
        },
        animation: { duration: 1000, easing: 'easeOutQuart' }
    };

    // Completion Chart
    const compCtx = document.getElementById('completionChart');
    if (compCtx) {
        new Chart(compCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($completion, 'title')) ?>,
                datasets: [{
                    label: 'Completion Rate (%)',
                    data: <?= json_encode(array_column($completion, 'rate')) ?>,
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderRadius: 6,
                    maxBarThickness: 40
                }]
            },
            options: commonOptions
        });
    }

    // Certificates Chart
    const certCtx = document.getElementById('certificatesChart');
    if (certCtx) {
        new Chart(certCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($certificates, 'title')) ?>,
                datasets: [{
                    label: 'Approved Certificates',
                    data: <?= json_encode(array_column($certificates, 'approved')) ?>,
                    backgroundColor: 'rgba(25, 135, 84, 0.8)',
                    borderRadius: 6,
                    maxBarThickness: 40
                }]
            },
            options: commonOptions
        });
    }
});
</script>

<style>
.tracking-wider { letter-spacing: 0.05em; }
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1) !important; }
.custom-table th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; }
.custom-table td { border-bottom: 1px solid #f1f3f5; color: #495057; }
</style>
