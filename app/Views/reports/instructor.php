<?php use App\Core\Security; use App\Core\View; ?>
<section class="container-fluid py-4 report-page">
    <?php View::partial('partials/role-nav'); ?>
    <?php View::partial('reports/_report-styles'); ?>

    <!-- Report Header -->
    <div class="report-header d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold">Instructor</span>
                <h1 class="h3 mb-0 fw-bold">My Teaching Analytics</h1>
            </div>
            <p class="text-muted mb-0">Overview of your courses, student performance, and pending tasks.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 hover-lift d-none d-md-inline-block" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Print Report
            </button>
        </div>
    </div>

    <!-- KPI Strip -->
    <div class="row g-4 mb-4">
        <?php 
        $pendingSum = array_sum(array_column($grading_backlog, 'pending_count'));
        $kpiData = [
            ['label' => 'Courses Taught', 'value' => $overview['total_courses'] ?? 0, 'icon' => 'bi-book', 'color' => 'primary', 'trend' => 'Active classes'],
            ['label' => 'Total Students', 'value' => $overview['total_students'] ?? 0, 'icon' => 'bi-people', 'color' => 'success', 'trend' => 'Across all courses'],
            ['label' => 'Pending Grading', 'value' => $pendingSum, 'icon' => 'bi-inbox', 'color' => 'warning', 'trend' => $pendingSum > 0 ? 'Requires attention' : 'All caught up!'],
        ];
        foreach ($kpiData as $kpi): 
        ?>
            <div class="col-12 col-md-4">
                <div class="stat-card p-4 hover-lift">
                    <div class="icon-circle bg-<?= $kpi['color'] ?> bg-opacity-10 text-<?= $kpi['color'] ?>">
                        <i class="bi <?= $kpi['icon'] ?>"></i>
                    </div>
                    <div class="value counter" data-target="<?= (int) $kpi['value'] ?>">0</div>
                    <div class="label"><?= Security::e($kpi['label']) ?></div>
                    <div class="trend-indicator text-muted"><i class="bi bi-info-circle text-<?= $kpi['color'] ?> me-1"></i> <?= $kpi['trend'] ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 4px; background: var(--bs-<?= $kpi['color'] ?>);"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Student Engagement -->
        <div class="col-lg-6">
            <div class="chart-card d-flex flex-column bg-white">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">Student Engagement</h5>
                </div>
                <div class="chart-card-body flex-grow-1" style="min-height: 300px;">
                    <canvas id="engagementChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Assignment Scores -->
        <div class="col-lg-6">
            <div class="chart-card d-flex flex-column bg-white">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">Average Assignment Scores</h5>
                </div>
                <div class="chart-card-body flex-grow-1" style="min-height: 300px;">
                    <canvas id="scoresChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Two-Column Bottom -->
    <div class="row g-4">
        <!-- Action Items (Grading Backlog) -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm bg-white h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Action Items <span class="badge bg-danger ms-2 rounded-pill"><?= $pendingSum ?></span></h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($grading_backlog)): ?>
                        <div class="empty-state text-center py-5">
                            <i class="bi bi-check2-circle text-success opacity-50" style="font-size: 3rem;"></i>
                            <h6 class="fw-bold mt-3">All Caught Up!</h6>
                            <p class="text-muted small">You have no pending assignments to grade.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($grading_backlog as $item): ?>
                                <div class="list-group-item p-4 border-bottom">
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 fw-bold text-dark"><?= Security::e($item['title']) ?></h6>
                                        <span class="badge bg-warning text-dark rounded-pill"><?= (int)$item['pending_count'] ?> pending</span>
                                    </div>
                                    <p class="mb-3 text-muted small"><i class="bi bi-book me-1"></i> <?= Security::e($item['course_title']) ?></p>
                                    <a href="index.php?page=instructor-evaluations" class="btn btn-sm btn-light border fw-semibold hover-lift text-primary rounded-pill px-3">
                                        Grade Now <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Course Performance Matrix -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm bg-white h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Course Performance Matrix</h5>
                </div>
                <div class="table-responsive">
                    <table class="table custom-table align-middle mb-0" id="performanceTable">
                        <thead>
                            <tr>
                                <th class="px-4">Course</th>
                                <th class="text-center">Enrolled</th>
                                <th>Completion</th>
                                <th>Attendance</th>
                                <th class="text-center">Avg Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($course_performance as $course): 
                                $compRate = (int)($course['completion_rate'] ?? 0);
                                $attRate = (int)$course['attendance_rate'];
                            ?>
                                <tr>
                                    <td class="px-4 fw-medium text-dark"><?= Security::e($course['title']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1"><?= (int)$course['enrollments'] ?></span>
                                    </td>
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
                                                <div class="progress-bar bg-info rounded-pill" style="width: <?= $attRate ?>%"></div>
                                            </div>
                                            <span class="small fw-bold" style="width: 35px;"><?= $attRate ?>%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-dark"><?= number_format((float)$course['avg_assignment_score'], 1) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($course_performance)): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state text-center py-5">
                                            <i class="bi bi-journal-x text-muted opacity-25" style="font-size: 3rem;"></i>
                                            <h6 class="fw-bold mt-3">No Data Available</h6>
                                            <p class="text-muted small">You are not assigned to any courses yet.</p>
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

    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#64748b';

    const labels = <?= json_encode(array_column($course_performance, 'title')) ?>;
    
    // 1. Student Engagement (Bar + Line for Attendance)
    const engCtx = document.getElementById('engagementChart');
    if (engCtx) {
        new Chart(engCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Enrollments',
                        data: <?= json_encode(array_column($course_performance, 'enrollments')) ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        type: 'line',
                        label: 'Attendance Rate (%)',
                        data: <?= json_encode(array_column($course_performance, 'attendance_rate')) ?>,
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 3,
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true } }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Enrollments' },
                        grid: { borderDash: [4, 4] }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Attendance Rate (%)' },
                        grid: { drawOnChartArea: false },
                        min: 0,
                        max: 100
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 2. Average Assignment Scores (Horizontal Bar)
    const scoreCtx = document.getElementById('scoresChart');
    if (scoreCtx) {
        const scores = <?= json_encode(array_column($course_performance, 'avg_assignment_score')) ?>;
        // Color code based on score threshold
        const bgColors = scores.map(score => {
            if (score >= 80) return 'rgba(16, 185, 129, 0.8)'; // Green
            if (score >= 60) return 'rgba(245, 158, 11, 0.8)'; // Yellow
            return 'rgba(239, 68, 68, 0.8)'; // Red
        });

        new Chart(scoreCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Avg Score',
                    data: scores,
                    backgroundColor: bgColors,
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
});
</script>
