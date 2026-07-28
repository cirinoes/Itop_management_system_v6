<?php use App\Core\Security;
use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="section-label">Certification Module</span>
            <h1 class="section-title mb-0">Digital Certificates</h1>
        </div>
    </div>

    <?php
    $featured = $certificates[0] ?? null;
    $others = array_slice($certificates, 1);
    ?>

    <div class="row g-4">
        <!-- ── Certificate Display ───────────────────── -->
        <div class="col-lg-8">
            <?php if ($featured): ?>
                <div class="trainee-card p-0 overflow-hidden mb-4 animate-in">
                    <div class="bg-dark text-white p-4">
                        <span class="badge bg-primary mb-2">Latest Certificate</span>
                        <h2 class="h4 fw-bold mb-1"><?= Security::e($featured['course_title']) ?></h2>
                        <p class="mb-0 text-white-50 small">Certificate No: <?= Security::e($featured['display_number']) ?>
                        </p>
                    </div>

                    <div class="p-4 bg-light text-center border-bottom">
                        <!-- Actual mini certificate preview -->
                        <div id="cert-preview-wrapper" class="bg-white border rounded mx-auto shadow-sm overflow-hidden"
                            style="max-width: 400px; width: 100%; aspect-ratio: 1 / 1.414; position: relative; background: #f8f9fa;">
                            <iframe id="cert-preview-iframe"
                                src="index.php?page=view-certificate&id=<?= (int) $featured['id'] ?>&hide_actions=1"
                                style="position: absolute; top: 0; left: 0; width: 800px; height: 1131px; transform-origin: top left; border: none; overflow: hidden; pointer-events: none;"
                                scrolling="no" tabindex="-1">
                            </iframe>
                        </div>
                        <script>
                            function resizeCertPreview() {
                                const wrapper = document.getElementById('cert-preview-wrapper');
                                const iframe = document.getElementById('cert-preview-iframe');
                                if (wrapper && iframe) {
                                    const scale = wrapper.clientWidth / 800;
                                    iframe.style.transform = `scale(${scale})`;
                                }
                            }
                            window.addEventListener('resize', resizeCertPreview);
                            // Initial scale right after element is rendered
                            requestAnimationFrame(resizeCertPreview);
                            // Backup scale after full load in case of layout shifts
                            window.addEventListener('load', resizeCertPreview);
                        </script>
                    </div>

                    <div class="p-3 d-flex gap-2 justify-content-center bg-white">
                        <a class="btn btn-primary"
                            href="index.php?page=download-certificate&id=<?= (int) $featured['id'] ?>">Download PDF</a>
                        <a class="btn btn-outline-secondary"
                            href="index.php?page=verify-certificate&code=<?= urlencode((string) $featured['verification_code']) ?>">Verify
                            Certificate</a>
                        <a class="btn btn-outline-primary"
                            href="index.php?page=view-certificate&id=<?= (int) $featured['id'] ?>" target="_blank">
                            Preview Full Size
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <div class="trainee-card animate-in text-center py-5">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        class="text-muted mb-3">
                        <circle cx="12" cy="8" r="7" />
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" />
                    </svg>
                    <h3 class="h5 fw-bold">No Certificates Yet</h3>
                    <p class="text-muted mb-0">Complete your courses and submit evaluations to earn certificates.</p>
                </div>
            <?php endif; ?>

            <!-- Additional Certificate Cards -->
            <?php if ($others): ?>
                <h3 class="h5 fw-bold mb-3 mt-5">Other Certificates</h3>
                <div class="row g-3">
                    <?php foreach ($others as $index => $cert): ?>
                        <div class="col-md-6">
                            <div class="trainee-card h-100 d-flex flex-column animate-in"
                                style="animation-delay: <?= ($index + 1) * 0.1 ?>s">
                                <div class="d-flex align-items-start mb-3 border-bottom pb-3">
                                    <div class="bg-light rounded p-2 text-primary me-3">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <circle cx="12" cy="8" r="7" />
                                            <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="h6 fw-bold mb-1" style="font-size: 0.95rem">
                                            <?= Security::e($cert['course_title']) ?>
                                        </h4>
                                        <div class="small text-muted mb-0">No: <?= Security::e($cert['display_number']) ?></div>
                                    </div>
                                </div>
                                <div class="mt-auto d-flex flex-wrap gap-2 pt-2">
                                    <a class="btn btn-sm btn-primary flex-grow-1"
                                        href="index.php?page=download-certificate&id=<?= (int) $cert['id'] ?>">Download</a>
                                    <a class="btn btn-sm btn-outline-secondary flex-grow-1"
                                        href="index.php?page=view-certificate&id=<?= (int) $cert['id'] ?>" target="_blank">
                                        Preview
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Completion History Sidebar ─────────────── -->
        <div class="col-lg-4">
            <!-- Search -->
            <div class="trainee-card animate-in mb-4">
                <h2 class="h5 fw-bold mb-3">Find a Certificate</h2>
                <form method="get">
                    <input type="hidden" name="page" value="trainee-certificates">
                    <div class="input-group mb-2">
                        <input class="form-control bg-light" name="q" value="<?= Security::e($q ?? '') ?>"
                            placeholder="Search by course or no.">
                        <button class="btn btn-primary" type="submit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="trainee-card animate-in mb-4" style="animation-delay: 0.1s">
                <h2 class="h5 fw-bold mb-3">Completion History</h2>
                <?php if ($certificates): ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($certificates as $index => $cert): ?>
                            <li
                                class="d-flex justify-content-between align-items-center mb-3 pb-3 <?= $index < count($certificates) - 1 ? 'border-bottom' : '' ?> last-mb-0 last-pb-0 last-border-0">
                                <div>
                                    <div class="fw-semibold small" style="line-height:1.2; margin-bottom: 0.2rem">
                                        <?= Security::e($cert['course_title']) ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem">
                                        <?php
                                        $issueDate = $cert['issue_date'] ?? $cert['issued_at'] ?? '';
                                        if ($issueDate) {
                                            $ts = strtotime($issueDate);
                                            echo 'Issued ' . ($ts ? date('d M Y', $ts) : Security::e($issueDate));
                                        }
                                        ?>
                                    </div>
                                </div>
                                <span
                                    class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Completed</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small mb-0">No history available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>