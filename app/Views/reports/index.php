<?php use App\Core\Security; use App\Core\View; ?>
<section class="container-fluid py-4 report-page">
    <?php View::partial('partials/role-nav'); ?>
    <?php View::partial('reports/_report-styles'); ?>
    
    <!-- Report Header -->
    <div class="report-header d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-dark text-white rounded-pill px-3 py-2 fw-semibold shadow-sm fs-6"><i class="bi bi-shield-lock me-1"></i> Admin View</span>
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
                <div class="col-md-2 d-flex gap-2 pb-1">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 shadow-sm hover-lift fw-bold">Filter</button>
                    <a href="index.php?page=reports" class="btn btn-white border w-100 rounded-3 shadow-sm hover-lift fw-bold text-dark text-center py-2" style="text-decoration: none;">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Strip -->
    <div class="row g-4 mb-4">
        <?php 
        $kpiData = [
            ['label' => 'Total Trainees', 'value' => $summary['total_trainees'], 'icon' => 'bi-people', 'bg' => 'linear-gradient(135deg, #0ea5e9, #2563eb)', 'color' => 'primary', 'trend' => '+5% this month'],
            ['label' => 'Active Enrolments', 'value' => $summary['active_trainees'], 'icon' => 'bi-journal-check', 'bg' => 'linear-gradient(135deg, #10b981, #059669)', 'color' => 'success', 'trend' => 'Steady'],
            ['label' => 'Completed', 'value' => $summary['completed_trainees'], 'icon' => 'bi-check-circle', 'bg' => 'linear-gradient(135deg, #f59e0b, #d97706)', 'color' => 'warning', 'trend' => '+12% this month'],
            ['label' => 'Certificates Issued', 'value' => $summary['certificates_issued'], 'icon' => 'bi-award', 'bg' => 'linear-gradient(135deg, #8b5cf6, #6d28d9)', 'color' => 'info', 'trend' => 'Recently issued 4'],
        ];
        foreach ($kpiData as $kpi): 
        ?>
            <div class="col-6 col-lg-3">
                <div class="stat-card p-4 hover-lift">
                    <div class="icon-circle text-white shadow-sm" style="background: <?= $kpi['bg'] ?>;">
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
        <div class="col-lg-6">
            <div class="chart-card d-flex flex-column bg-white h-100">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">Completion Rate</h5>
                </div>
                <div class="chart-card-body flex-grow-1">
                    <canvas id="completionChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Evaluation Ratings -->
        <div class="col-lg-6">
            <div class="chart-card d-flex flex-column bg-white h-100">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">Evaluation Ratings</h5>
                </div>
                <div class="chart-card-body flex-grow-1">
                    <canvas id="evaluationsChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Certificate Pipeline -->
        <div class="col-12">
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
            <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
                <div class="card-header bg-transparent border-0 p-4 pb-0 text-center">
                    <ul class="nav nav-pills glass-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="metrics-tab" data-bs-toggle="pill" data-bs-target="#metrics-pane" type="button" role="tab">Course Metrics</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="demographics-tab" data-bs-toggle="pill" data-bs-target="#demographics-pane" type="button" role="tab">Demographics</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="revenue-tab" data-bs-toggle="pill" data-bs-target="#revenue-pane" type="button" role="tab">Financials</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="instructor-tab" data-bs-toggle="pill" data-bs-target="#instructor-pane" type="button" role="tab">Instructors</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="trainee-tab" data-bs-toggle="pill" data-bs-target="#trainee-pane" type="button" role="tab">Trainee Log</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="p-3 d-flex justify-content-end border-bottom">
                        <div class="input-group shadow-sm" style="max-width: 300px; border-radius: 50px; overflow: hidden;">
                            <span class="input-group-text bg-light border-0 px-3"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-0" id="tableSearch" placeholder="Search data...">
                        </div>
                    </div>
                    <div class="tab-content" id="reportTabsContent">
                        
                        <!-- TAB 1: Course Metrics -->
                        <div class="tab-pane fade show active" id="metrics-pane" role="tabpanel" tabindex="0">
                            <?php $activeAcademies = array_unique(array_column($completion, 'academy_name')); ?>
                            <div class="px-4 py-3 d-flex flex-wrap gap-2 border-bottom bg-light" style="background: linear-gradient(to right, #f8fafc, #ffffff);">
                                <button class="btn btn-primary text-white border border-primary rounded-pill px-3 py-1 btn-sm academy-filter-btn fw-medium shadow-sm transition-all" data-academy="all">
                                    <i class="bi bi-grid-fill me-1 opacity-75"></i> All Academies
                                </button>
                                <?php foreach($activeAcademies as $ac): ?>
                                    <button class="btn btn-light bg-white text-secondary border rounded-pill px-3 py-1 btn-sm academy-filter-btn fw-medium shadow-sm transition-all" data-academy="<?= Security::e($ac) ?>">
                                        <i class="bi bi-building me-1 opacity-50"></i> <?= Security::e($ac) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <div class="table-responsive">
                    <table class="table custom-table align-middle" id="metricsTable">
                        <thead>
                            <tr>
                                <th class="px-4 cursor-pointer transition-all" onclick="sortTable(0, this)">Course <i class="bi bi-arrow-down-up ms-1 text-muted opacity-50"></i></th>
                                <th class="cursor-pointer transition-all" onclick="sortTable(1, this)">Completion <i class="bi bi-arrow-down-up ms-1 text-muted opacity-50"></i></th>
                                <th class="cursor-pointer transition-all" onclick="sortTable(2, this)">Attendance <i class="bi bi-arrow-down-up ms-1 text-muted opacity-50"></i></th>
                                <th class="text-center cursor-pointer transition-all" onclick="sortTable(3, this)">Assignments <i class="bi bi-arrow-down-up ms-1 text-muted opacity-50"></i></th>
                                <th class="text-center cursor-pointer transition-all" onclick="sortTable(4, this)">Evaluations <i class="bi bi-arrow-down-up ms-1 text-muted opacity-50"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($completion as $row): 
                                $att = current(array_filter($attendance, fn($a) => $a['title'] === $row['title'])) ?: ['attendance_rate' => 0];
                                $asn = current(array_filter($assignments, fn($a) => $a['title'] === $row['title'])) ?: ['assignments' => 0, 'submissions' => 0];
                                $eval = current(array_filter($evaluations, fn($e) => $e['title'] === $row['title'])) ?: ['avg_rating' => 0];
                                $compRate = (int)$row['rate'];
                                $attRate = (int)$att['attendance_rate'];
                                $evalScore = (float)$eval['avg_rating'];
                            ?>
                                <tr class="academy-row" data-academy="<?= Security::e($row['academy_name']) ?>">
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 36px; height: 36px;">
                                                <i class="bi bi-journal-richtext"></i>
                                            </div>
                                            <span class="fw-medium text-dark"><?= Security::e($row['title']) ?></span>
                                        </div>
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
                                                <div class="progress-bar bg-primary rounded-pill" style="width: <?= $attRate ?>%"></div>
                                            </div>
                                            <span class="small fw-bold" style="width: 35px;"><?= $attRate ?>%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1 shadow-sm fs-6"><?= (int)$asn['submissions'] ?> / <?= (int)$asn['assignments'] ?></span>
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

                        <!-- TAB 2: Demographics -->
                        <div class="tab-pane fade" id="demographics-pane" role="tabpanel" tabindex="0">
                            <div class="table-responsive">
                                <table class="table custom-table align-middle search-target-table">
                                    <thead>
                                        <tr>
                                            <th class="px-4">Month</th>
                                            <th>Course Title</th>
                                            <th>Trainee Background</th>
                                            <th class="text-center">Enrolments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($demographics ?? [] as $row): 
                                            $dateObj = DateTime::createFromFormat('Y-m', $row['enrolment_month']);
                                        ?>
                                            <tr class="align-middle">
                                                <td class="px-4">
                                                    <div class="bg-light rounded p-2 text-center border shadow-sm" style="width: 60px;">
                                                        <div class="text-uppercase small fw-bold text-primary mb-1" style="font-size: 0.7rem; letter-spacing: 1px; line-height: 1;"><?= $dateObj ? $dateObj->format('M') : '' ?></div>
                                                        <div class="fw-bolder text-dark" style="font-size: 1.1rem; line-height: 1;"><?= $dateObj ? $dateObj->format('Y') : '' ?></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                                                            <i class="bi bi-journal-richtext"></i>
                                                        </div>
                                                        <span class="fw-medium text-dark"><?= Security::e($row['course_title']) ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-2 fw-medium shadow-sm">
                                                        <i class="bi bi-person-workspace me-1"></i> <?= Security::e($row['background']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary text-white rounded-pill px-3 py-2 fs-6 shadow-sm" style="min-width: 40px;"><?= (int)$row['total_enrolments'] ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; if (empty($demographics)): ?>
                                            <tr><td colspan="4" class="text-center py-5 text-muted">No demographic data available.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 3: Revenue -->
                        <div class="tab-pane fade" id="revenue-pane" role="tabpanel" tabindex="0">
                            <div class="table-responsive">
                                <table class="table custom-table align-middle search-target-table">
                                    <thead>
                                        <tr>
                                            <th class="px-4">Course Title</th>
                                            <th class="text-center">Enrolments</th>
                                            <th class="text-end">Fee per Trainee</th>
                                            <th class="text-end px-4">Total Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($revenue ?? [] as $row): ?>
                                            <tr class="align-middle">
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                                                            <i class="bi bi-wallet2"></i>
                                                        </div>
                                                        <span class="fw-medium text-dark"><?= Security::e($row['title']) ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fs-6"><?= (int)$row['total_enrolments'] ?></span>
                                                </td>
                                                <td class="text-end text-muted fw-medium">RM <?= number_format((float)$row['fee'], 2) ?></td>
                                                <td class="text-end px-4">
                                                    <span class="badge bg-success text-white rounded-pill px-3 py-2 fs-6 shadow-sm">RM <?= number_format((float)$row['total_revenue'], 2) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; if (empty($revenue)): ?>
                                            <tr><td colspan="4" class="text-center py-5 text-muted">No revenue data available.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 4: Instructors -->
                        <div class="tab-pane fade" id="instructor-pane" role="tabpanel" tabindex="0">
                            <div class="p-4 bg-light">
                                <div class="row g-4">
                                    <?php foreach ($instructor_performance ?? [] as $row): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="instructor-card p-4 text-center">
                                                <div class="instructor-avatar mx-auto mb-3 shadow-sm">
                                                    <?= strtoupper(substr(Security::e($row['instructor_name']), 0, 1)) ?>
                                                </div>
                                                <h5 class="fw-bold text-dark mb-1"><?= Security::e($row['instructor_name']) ?></h5>
                                                <p class="text-muted small mb-3">Senior Instructor</p>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <div class="px-3 py-2 bg-light rounded-3">
                                                        <div class="fw-bold text-primary h5 mb-0"><?= (int)$row['assigned_courses'] ?></div>
                                                        <div class="small text-muted" style="font-size: 11px; text-transform: uppercase;">Courses</div>
                                                    </div>
                                                    <div class="px-3 py-2 bg-light rounded-3">
                                                        <div class="fw-bold text-success h5 mb-0"><?= (int)$row['total_students'] ?></div>
                                                        <div class="small text-muted" style="font-size: 11px; text-transform: uppercase;">Students</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; if (empty($instructor_performance)): ?>
                                        <div class="col-12"><div class="text-center py-5 text-muted">No instructor data available.</div></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: Trainee Log -->
                        <div class="tab-pane fade" id="trainee-pane" role="tabpanel" tabindex="0">
                            <div class="table-responsive">
                                <table class="table custom-table align-middle search-target-table" style="min-width: 800px;">
                                    <thead>
                                        <tr>
                                            <th class="px-4">Trainee</th>
                                            <th>Course</th>
                                            <th>Status</th>
                                            <th class="text-center">Progress</th>
                                            <th class="text-end px-4">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detailed_log ?? [] as $row): 
                                            $dateObj = new DateTime($row['enrolled_date']);
                                        ?>
                                            <tr class="align-middle">
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary me-3 border shadow-sm" style="width: 40px; height: 40px; font-weight: 700; font-size: 1.2rem;">
                                                            <?= strtoupper(substr(Security::e($row['trainee_name']), 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark mb-1" style="line-height: 1.2;"><?= Security::e($row['trainee_name']) ?></div>
                                                            <div class="small text-muted" style="line-height: 1;"><i class="bi bi-building me-1"></i> <?= Security::e($row['background']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-journal-richtext text-muted me-2 opacity-50"></i>
                                                        <span class="text-dark fw-medium"><?= Security::e($row['course_title']) ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $bg = $row['status'] === 'completed' ? 'success' : ($row['status'] === 'active' ? 'primary' : 'secondary');
                                                    ?>
                                                    <span class="badge bg-<?= $bg ?> text-white px-3 py-2 rounded-pill shadow-sm fw-medium"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;"></i> <?= ucfirst(Security::e($row['status'])) ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <div class="small fw-bold text-<?= $bg ?> mb-1"><?= (int)$row['progress_percent'] ?>%</div>
                                                        <div class="progress shadow-sm w-100 bg-light border" style="height: 6px; max-width: 80px;">
                                                            <div class="progress-bar bg-<?= $bg ?> rounded-pill" style="width: <?= (int)$row['progress_percent'] ?>%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end px-4">
                                                    <div class="d-inline-flex bg-light rounded p-2 text-center border shadow-sm" style="width: 60px;">
                                                        <div class="w-100">
                                                            <div class="text-uppercase small fw-bold text-primary mb-1" style="font-size: 0.7rem; letter-spacing: 1px; line-height: 1;"><?= $dateObj->format('M') ?></div>
                                                            <div class="fw-bolder text-dark" style="font-size: 1.1rem; line-height: 1;"><?= $dateObj->format('d') ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; if (empty($detailed_log)): ?>
                                            <tr><td colspan="5" class="text-center py-5 text-muted">No enrolments available.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
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
                            <option value="detailed_log">Detailed Trainee Enrolment Log</option>
                            <option value="revenue">Financial & Revenue Report</option>
                            <option value="instructor_performance">Instructor Performance Report</option>
                            <option value="demographics">Demographics & Trend Report</option>
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
                                <input class="form-check-input" type="radio" name="format" id="fmtExcel" value="excel" checked>
                                <label class="form-check-label" for="fmtExcel">Native Excel (.xls)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="fmtCsv" value="csv">
                                <label class="form-check-label" for="fmtCsv">Standard CSV</label>
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
<?php
// Aggregate data by Academy for the charts to prevent clutter
$chartDataByAcademy = [];
foreach ($completion as $row) {
    $ac = $row['academy_name'];
    if (!isset($chartDataByAcademy[$ac])) {
        $chartDataByAcademy[$ac] = ['comp_total' => 0, 'comp_count' => 0, 'eval_total' => 0, 'eval_count' => 0];
    }
    $chartDataByAcademy[$ac]['comp_total'] += $row['rate'];
    $chartDataByAcademy[$ac]['comp_count']++;
}
foreach ($evaluations as $row) {
    $ac = $row['academy_name'];
    if (!isset($chartDataByAcademy[$ac])) {
        $chartDataByAcademy[$ac] = ['comp_total' => 0, 'comp_count' => 0, 'eval_total' => 0, 'eval_count' => 0];
    }
    $chartDataByAcademy[$ac]['eval_total'] += $row['avg_rating'];
    $chartDataByAcademy[$ac]['eval_count']++;
}

$chartLabels = array_keys($chartDataByAcademy);
$chartCompData = [];
$chartEvalData = [];
foreach ($chartDataByAcademy as $ac => $data) {
    $chartCompData[] = $data['comp_count'] > 0 ? round($data['comp_total'] / $data['comp_count']) : 0;
    $chartEvalData[] = $data['eval_count'] > 0 ? round($data['eval_total'] / $data['eval_count'], 1) : 0;
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fix modal backdrop z-index issue caused by sidebar layout stacking context
    const exportModal = document.getElementById('exportModal');
    if (exportModal) document.body.appendChild(exportModal);

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

    // Table search filter (works across all tabs now)
    const searchInput = document.getElementById('tableSearch');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            const val = this.value.toLowerCase();
            const activeTable = document.querySelector('.tab-pane.active table');
            if (!activeTable) return;
            const rows = activeTable.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.querySelector('.empty-state') || row.querySelector('.text-muted[colspan]')) return;
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(val) ? '' : 'none';
            });
        });
    }

    // Tab active styling logic is handled by standard Bootstrap .nav-pills 
    // We only need to clear search on switch
    const tabButtons = document.querySelectorAll('#reportTabs .nav-link');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            if(searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('keyup'));
            }
        });
    });

    // Shared Chart Options
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#64748b';
    
    // 1. Completion Rate (Bar)
    const compCtx = document.getElementById('completionChart');
    if (compCtx) {
        new Chart(compCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Avg Completion (%)',
                    data: <?= json_encode($chartCompData) ?>,
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
        new Chart(evalCtx, {
            type: 'polarArea',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    data: <?= json_encode($chartEvalData) ?>,
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

// Academy Filter Buttons Logic
document.querySelectorAll('.academy-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Toggle active states
        document.querySelectorAll('.academy-filter-btn').forEach(b => {
            b.classList.remove('btn-primary', 'text-white', 'border-primary');
            b.classList.add('btn-light', 'bg-white', 'text-secondary', 'border');
        });
        this.classList.remove('btn-light', 'bg-white', 'text-secondary', 'border');
        this.classList.add('btn-primary', 'text-white', 'border-primary');
        
        const target = this.dataset.academy;
        const rows = document.querySelectorAll('#metricsTable tbody tr.academy-row');
        
        rows.forEach(row => {
            if (target === 'all' || row.dataset.academy === target) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// Simple table sorter
let sortDirection = false;
let currentSortColumn = -1;

function sortTable(columnIndex, thElement) {
    const table = document.getElementById("metricsTable");
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.querySelectorAll("tr"));
    
    if (rows[0] && rows[0].querySelector('.empty-state')) return;

    if (currentSortColumn === columnIndex) {
        sortDirection = !sortDirection;
    } else {
        sortDirection = false; // default to descending (highest first)
        currentSortColumn = columnIndex;
    }
    
    // Reset all headers
    const headers = table.querySelectorAll('th.cursor-pointer');
    headers.forEach(th => {
        th.classList.remove('bg-primary', 'bg-opacity-10', 'text-primary', 'text-white');
        const icon = th.querySelector('i');
        if (icon) {
            icon.className = 'bi bi-arrow-down-up ms-1 text-muted opacity-50';
        }
    });
    
    // Set active header
    if (thElement) {
        thElement.classList.add('bg-primary', 'text-white');
        const icon = thElement.querySelector('i');
        if (icon) {
            icon.className = sortDirection ? 'bi bi-arrow-up ms-1 text-white fw-bold' : 'bi bi-arrow-down ms-1 text-white fw-bold';
        }
    }
    
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
.transition-all { transition: all 0.2s ease-in-out; }
.academy-filter-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; }
</style>
