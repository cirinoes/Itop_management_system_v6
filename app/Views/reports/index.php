<?php use App\Core\Security; use App\Core\View; ?>
<section class="container-fluid py-4 report-page">
    <?php View::partial('partials/role-nav'); ?>
    <?php View::partial('reports/_report-styles'); ?>
    
    <!-- Report Header -->
    <div class="report-header d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-semibold">Admin</span>
                <h1 class="h3 mb-0 fw-bold">Training Reports</h1>
            </div>
            <p class="text-muted mb-0">Platform-wide analytics for trainees, completion rates, and certificates.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 hover-lift d-none d-md-inline-block" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Print Report
            </button>
            <button type="button" class="btn btn-primary shadow-sm hover-lift rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-download me-2"></i> Export Data
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card filter-bar border-0 rounded-4 shadow-sm mb-4 bg-white">
        <div class="card-body p-4">
            <form class="row g-3 align-items-end" method="get">
                <input type="hidden" name="page" value="reports">
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold text-uppercase tracking-wider mb-2">Academy</label>
                    <select class="form-select bg-light border-0" name="academy_id">
                        <option value="">All academies</option>
                        <?php foreach ($academies as $academy): ?>
                            <option value="<?= (int) $academy['id'] ?>" <?= $academyId === (int) $academy['id'] ? 'selected' : '' ?>>
                                <?= Security::e($academy['code']) ?> - <?= Security::e($academy['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold text-uppercase tracking-wider mb-2">Course</label>
                    <select class="form-select bg-light border-0" name="course_id">
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
                    <input class="form-control bg-light border-0" type="date" name="date_from" value="<?= Security::e($_GET['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small fw-semibold text-uppercase tracking-wider mb-2">Date To</label>
                    <input class="form-control bg-light border-0" type="date" name="date_to" value="<?= Security::e($_GET['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 shadow-sm hover-lift fw-semibold"><i class="bi bi-funnel"></i></button>
                    <a href="index.php?page=reports" class="btn btn-light w-100 rounded-3 hover-lift fw-semibold text-muted" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Strip -->
    <div class="row g-4 mb-4">
        <?php 
        $kpiData = [
            ['label' => 'Total Trainees', 'value' => $summary['total_trainees'], 'icon' => 'bi-people', 'color' => 'primary', 'trend' => '+5% this month'],
            ['label' => 'Active Enrolments', 'value' => $summary['active_trainees'], 'icon' => 'bi-journal-check', 'color' => 'success', 'trend' => 'Steady'],
            ['label' => 'Completed', 'value' => $summary['completed_trainees'], 'icon' => 'bi-check-circle', 'color' => 'warning', 'trend' => '+12% this month'],
            ['label' => 'Certificates Issued', 'value' => $summary['certificates_issued'], 'icon' => 'bi-award', 'color' => 'info', 'trend' => 'Recently issued 4'],
        ];
        foreach ($kpiData as $kpi): 
        ?>
            <div class="col-6 col-lg-3">
                <div class="stat-card p-4 hover-lift">
                    <div class="icon-circle bg-<?= $kpi['color'] ?> bg-opacity-10 text-<?= $kpi['color'] ?>">
                        <i class="bi <?= $kpi['icon'] ?>"></i>
                    </div>
                    <div class="value counter" data-target="<?= (int) $kpi['value'] ?>">0</div>
                    <div class="label"><?= Security::e($kpi['label']) ?></div>
                    <div class="trend-indicator text-muted"><i class="bi bi-graph-up text-<?= $kpi['color'] ?> me-1"></i> <?= $kpi['trend'] ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 4px; background: var(--bs-<?= $kpi['color'] ?>);"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Completion Rate -->
        <div class="col-lg-4">
            <div class="chart-card d-flex flex-column bg-white">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">Completion Rate</h5>
                </div>
                <div class="chart-card-body flex-grow-1">
                    <canvas id="completionChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Evaluation Ratings -->
        <div class="col-lg-4">
            <div class="chart-card d-flex flex-column bg-white">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">Evaluation Ratings</h5>
                </div>
                <div class="chart-card-body flex-grow-1">
                    <canvas id="evaluationsChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Certificate Pipeline -->
        <div class="col-lg-4">
            <div class="chart-card d-flex flex-column bg-white">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">Certificate Pipeline</h5>
                </div>
                <div class="chart-card-body flex-grow-1">
                    <canvas id="certificatesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card border-0 rounded-4 shadow-sm bg-white">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="fw-bold mb-0">Detailed Course Metrics</h5>
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-0" id="tableSearch" placeholder="Search courses...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table custom-table align-middle" id="metricsTable">
                        <thead>
                            <tr>
                                <th class="px-4 cursor-pointer" onclick="sortTable(0)">Course <i class="bi bi-arrow-down-up ms-1 text-muted"></i></th>
                                <th class="cursor-pointer" onclick="sortTable(1)">Completion <i class="bi bi-arrow-down-up ms-1 text-muted"></i></th>
                                <th class="cursor-pointer" onclick="sortTable(2)">Attendance <i class="bi bi-arrow-down-up ms-1 text-muted"></i></th>
                                <th class="text-center cursor-pointer" onclick="sortTable(3)">Assignments <i class="bi bi-arrow-down-up ms-1 text-muted"></i></th>
                                <th class="text-center cursor-pointer" onclick="sortTable(4)">Evaluations <i class="bi bi-arrow-down-up ms-1 text-muted"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completion as $row): 
                                $att = current(array_filter($attendance, fn($a) => $a['title'] === $row['title'])) ?: ['attendance_rate' => 0];
                                $asn = current(array_filter($assignments, fn($a) => $a['title'] === $row['title'])) ?: ['assignments' => 0, 'submissions' => 0];
                                $eval = current(array_filter($evaluations, fn($e) => $e['title'] === $row['title'])) ?: ['avg_rating' => 0];
                                $compRate = (int)$row['rate'];
                                $attRate = (int)$att['attendance_rate'];
                                $evalScore = (float)$eval['avg_rating'];
                            ?>
                                <tr>
                                    <td class="px-4 fw-medium text-dark"><?= Security::e($row['title']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-<?= $compRate >= 80 ? 'success' : ($compRate >= 50 ? 'warning' : 'danger') ?> rounded-pill" style="width: <?= $compRate ?>%"></div>
                                            </div>
                                            <span class="small fw-bold" style="width: 35px;"><?= $compRate ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary rounded-pill" style="width: <?= $attRate ?>%"></div>
                                            </div>
                                            <span class="small fw-bold" style="width: 35px;"><?= $attRate ?>%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1"><?= (int)$asn['submissions'] ?> / <?= (int)$asn['assignments'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <?php 
                                            for ($s = 1; $s <= 5; $s++) {
                                                if ($s <= $evalScore) echo '<i class="bi bi-star-fill text-warning"></i>';
                                                elseif ($s - 0.5 <= $evalScore) echo '<i class="bi bi-star-half text-warning"></i>';
                                                else echo '<i class="bi bi-star text-muted opacity-25"></i>';
                                            }
                                            ?>
                                            <span class="text-dark fw-bold ms-2"><?= number_format($evalScore, 1) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($completion)): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state text-center py-5">
                                            <i class="bi bi-bar-chart text-muted opacity-25" style="font-size: 3rem;"></i>
                                            <h6 class="fw-bold mt-3">No Data Available</h6>
                                            <p class="text-muted small">Try adjusting your filters to see more results.</p>
                                        </div>
                                    </td>
                                </tr>
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
                    <h5 class="modal-title h5 fw-bold">Export Analytics Data</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Report Type</label>
                        <select class="form-select bg-light border-0" name="report_type" required>
                            <option value="completion">Course Completion Metrics</option>
                            <option value="attendance">Attendance Records</option>
                            <option value="certificates">Certificate Issuance Pipeline</option>
                            <option value="master_data">Master Data Overview</option>
                        </select>
                    </div>
                    <div class="mb-4 row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Date From (Optional)</label>
                            <input type="date" class="form-control bg-light border-0" name="export_from">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Date To (Optional)</label>
                            <input type="date" class="form-control bg-light border-0" name="export_to">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Format</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="fmtCsv" value="csv" checked>
                                <label class="form-check-label" for="fmtCsv">CSV (Excel)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="fmtPdf" value="pdf">
                                <label class="form-check-label" for="fmtPdf">PDF Document</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-download me-2"></i> Download Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Number counter animation
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const duration = 1500;
        const step = target / (duration / 16);
        let current = 0;
        const update = () => {
            current += step;
            if (current < target) {
                counter.innerText = Math.ceil(current).toLocaleString();
                requestAnimationFrame(update);
            } else {
                counter.innerText = target.toLocaleString();
            }
        };
        update();
    });

    // Table search filter
    const searchInput = document.getElementById('tableSearch');
    const tableBody = document.querySelector('#metricsTable tbody');
    if(searchInput && tableBody) {
        searchInput.addEventListener('keyup', function() {
            const val = this.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach(row => {
                if (row.querySelector('.empty-state')) return;
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(val) ? '' : 'none';
            });
        });
    }

    // Shared Chart Options
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#64748b';
    
    // 1. Completion Rate (Horizontal Bar)
    const compCtx = document.getElementById('completionChart');
    if (compCtx) {
        new Chart(compCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($completion, 'title')) ?>,
                datasets: [{
                    label: 'Completion (%)',
                    data: <?= json_encode(array_column($completion, 'rate')) ?>,
                    backgroundColor: 'rgba(13, 110, 253, 0.85)',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, max: 100, grid: { borderDash: [4, 4] } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // 2. Evaluation Ratings (Doughnut / Polar)
    const evalCtx = document.getElementById('evaluationsChart');
    if (evalCtx) {
        const evalLabels = <?= json_encode(array_column($evaluations, 'title')) ?>;
        const evalData = <?= json_encode(array_column($evaluations, 'avg_rating')) ?>;
        new Chart(evalCtx, {
            type: 'polarArea',
            data: {
                labels: evalLabels,
                datasets: [{
                    data: evalData,
                    backgroundColor: [
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12 } }
                },
                scales: {
                    r: { min: 0, max: 5, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // 3. Certificate Pipeline (Stacked Bar)
    const certCtx = document.getElementById('certificatesChart');
    if (certCtx) {
        new Chart(certCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($certificates, 'title')) ?>,
                datasets: [
                    {
                        label: 'Approved',
                        data: <?= json_encode(array_column($certificates, 'approved')) ?>,
                        backgroundColor: 'rgba(22, 163, 74, 0.85)',
                        borderRadius: {topLeft: 4, topRight: 4, bottomLeft: 4, bottomRight: 4},
                        borderSkipped: false
                    },
                    {
                        label: 'Pending',
                        data: <?= json_encode(array_column($certificates, 'pending')) ?>,
                        backgroundColor: 'rgba(245, 158, 11, 0.85)',
                        borderRadius: {topLeft: 4, topRight: 4, bottomLeft: 4, bottomRight: 4},
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { boxWidth: 12, usePointStyle: true } }
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, grid: { borderDash: [4, 4] } }
                }
            }
        });
    }
});

// Simple table sorter
let sortDirection = false;
function sortTable(columnIndex) {
    const table = document.getElementById("metricsTable");
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.querySelectorAll("tr"));
    
    if (rows[0].querySelector('.empty-state')) return;

    sortDirection = !sortDirection;
    
    rows.sort((a, b) => {
        let aVal = a.cells[columnIndex].innerText.trim();
        let bVal = b.cells[columnIndex].innerText.trim();
        
        // Remove % for number comparison
        if (aVal.includes('%')) aVal = aVal.replace('%', '');
        if (bVal.includes('%')) bVal = bVal.replace('%', '');
        
        let aNum = parseFloat(aVal);
        let bNum = parseFloat(bVal);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return sortDirection ? aNum - bNum : bNum - aNum;
        }
        return sortDirection ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}
</script>
<style>
.cursor-pointer { cursor: pointer; }
.cursor-pointer:hover { background-color: var(--ims-border-light) !important; }
</style>
