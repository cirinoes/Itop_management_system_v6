<?php use App\Core\Security; use App\Core\View; ?>
<section class="container-fluid py-4 report-page">
    <?php View::partial('partials/role-nav'); ?>
    <?php View::partial('reports/_report-styles'); ?>

    <!-- Report Header -->
    <div class="report-header d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-1 fw-semibold">Trainee</span>
                <h1 class="h3 mb-0 fw-bold">My Learning Report</h1>
            </div>
            <p class="text-muted mb-0">Track your progress, achievements, and certificate timeline.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary shadow-sm hover-lift rounded-pill px-4" onclick="window.print()">
                <i class="bi bi-file-earmark-pdf me-2"></i> Download PDF
            </button>
        </div>
    </div>

    <!-- KPI Strip -->
    <div class="row g-4 mb-4">
        <?php 
        $enrolled = (int)($overview['enrolled_courses'] ?? 0);
        $completed = (int)($overview['completed_courses'] ?? 0);
        $certs = (int)($cert_count ?? 0);
        
        $kpiData = [
            ['label' => 'Courses Enrolled', 'value' => $enrolled, 'icon' => 'bi-journal-bookmark', 'color' => 'primary', 'trend' => 'Active & Completed'],
            ['label' => 'Courses Completed', 'value' => $completed, 'icon' => 'bi-check-circle', 'color' => 'success', 'trend' => $enrolled > 0 ? round(($completed/$enrolled)*100) . '% of total' : '0% of total'],
            ['label' => 'Certificates Earned', 'value' => $certs, 'icon' => 'bi-award', 'color' => 'warning', 'trend' => 'Ready to download'],
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
                    <div class="trend-indicator text-muted"><i class="bi bi-graph-up text-<?= $kpi['color'] ?> me-1"></i> <?= $kpi['trend'] ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 4px; background: var(--bs-<?= $kpi['color'] ?>);"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Progress Overview Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="chart-card d-flex flex-column bg-white">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">Course Progression Overview</h5>
                </div>
                <div class="chart-card-body">
                    <div style="height: 250px;">
                        <canvas id="progressChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Two-Column Bottom -->
    <div class="row g-4">
        <!-- Course Progression Details -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm bg-white h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Detailed Course Progression</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($progress)): ?>
                        <div class="empty-state text-center py-5">
                            <i class="bi bi-journal-x text-muted opacity-25" style="font-size: 3rem;"></i>
                            <h6 class="fw-bold mt-3">No Courses Found</h6>
                            <p class="text-muted small">You haven't enrolled in any courses yet.</p>
                            <a href="index.php?page=courses" class="btn btn-outline-primary rounded-pill mt-3 px-4">Browse Courses</a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($progress as $course): 
                                $percent = (int)$course['progress_percent'];
                                $isCompleted = $course['status'] === 'completed';
                                $quizScore = $course['quiz_avg_score'] !== null ? (int)$course['quiz_avg_score'] : null;
                            ?>
                                <div class="list-group-item p-4 border-bottom">
                                    <div class="d-flex align-items-center gap-4">
                                        <!-- KPI Ring -->
                                        <div class="kpi-ring text-<?= $isCompleted ? 'success' : 'primary' ?> mb-0 flex-shrink-0" style="--progress: <?= $percent ?>%; width: 56px; height: 56px; font-size: 1.25rem;">
                                            <span class="fw-bold text-dark" style="font-size: 0.9rem;"><?= $percent ?>%</span>
                                        </div>
                                        
                                        <!-- Course Info -->
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-bold text-dark"><?= Security::e($course['title']) ?></h6>
                                                <?php if ($isCompleted): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">In Progress</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex gap-3 text-muted small mt-2">
                                                <?php if ($quizScore !== null): ?>
                                                    <span><i class="bi bi-ui-checks me-1"></i> Quiz Avg: <?= $quizScore ?>%</span>
                                                <?php else: ?>
                                                    <span><i class="bi bi-ui-checks me-1"></i> No Quizzes Yet</span>
                                                <?php endif; ?>
                                                <span><i class="bi bi-calendar2-check me-1"></i> <?= ucfirst(Security::e($course['status'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Achievement Timeline -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm bg-white h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Achievement Timeline</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($certificates)): ?>
                        <div class="empty-state text-center py-4">
                            <i class="bi bi-award text-muted opacity-25" style="font-size: 3rem;"></i>
                            <h6 class="fw-bold mt-3">No Certificates Yet</h6>
                            <p class="text-muted small">Complete courses to earn certificates.</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline position-relative border-start border-2 border-info ms-3 ps-4 py-2">
                            <?php foreach ($certificates as $cert): ?>
                                <div class="timeline-item position-relative mb-4">
                                    <div class="timeline-marker position-absolute bg-white border border-2 border-info rounded-circle" style="width: 16px; height: 16px; left: -1.8rem; top: 0.2rem;"></div>
                                    <div class="text-muted small fw-semibold mb-1"><?= date('F j, Y', strtotime($cert['issued_at'])) ?></div>
                                    <h6 class="fw-bold text-dark mb-1"><?= Security::e($cert['course_title']) ?></h6>
                                    <p class="text-muted small mb-2">Certificate #<?= Security::e($cert['certificate_no']) ?></p>
                                    <a href="index.php?page=view-certificate&id=<?= (int)$cert['id'] ?>" class="btn btn-sm btn-outline-info rounded-pill hover-lift px-3">
                                        <i class="bi bi-eye me-1"></i> View Certificate
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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

    const progCtx = document.getElementById('progressChart');
    if (progCtx) {
        const labels = <?= json_encode(array_column($progress, 'title')) ?>;
        const data = <?= json_encode(array_column($progress, 'progress_percent')) ?>;
        
        // Color coding: green for 100%, blue otherwise
        const bgColors = data.map(val => val >= 100 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(59, 130, 246, 0.8)');

        new Chart(progCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Progress (%)',
                    data: data,
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
