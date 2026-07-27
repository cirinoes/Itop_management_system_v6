<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="section-label">Training Evaluation</span>
            <h1 class="section-title mb-0">Post-Course Feedback</h1>
        </div>
    </div>

    <div class="row g-4">
        <!-- ── Feedback Form ─────────────────────────── -->
        <div class="col-lg-8">
            <?php if (!$courses): ?>
                <div class="trainee-card animate-in text-center py-5">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-muted mb-3"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <h3 class="h5 fw-bold">You're All Caught Up!</h3>
                    <p class="text-muted mb-0">No completed courses are waiting for evaluation.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($courses as $courseIndex => $course): ?>
                <div class="trainee-card mb-4 animate-in" style="animation-delay: <?= $courseIndex * 0.1 ?>s">
                    <h2 class="h4 fw-bold border-bottom pb-3 mb-4">Evaluation: <?= Security::e($course['title']) ?></h2>

                    <form method="post" action="index.php?page=save-evaluation">
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        <input type="hidden" name="course_id" value="<?= (int) $course['course_id'] ?>">

                        <div class="mb-5">
                            <h3 class="h6 fw-bold mb-3 text-uppercase text-muted" style="letter-spacing:0.5px">Section 1: Detailed Feedback</h3>
                            <div class="table-responsive">
                                <table class="likert-table">
                                    <thead>
                                        <tr>
                                            <th>Statement</th>
                                            <th>Strongly<br>Disagree</th>
                                            <th>Disagree</th>
                                            <th>Neutral</th>
                                            <th>Agree</th>
                                            <th>Strongly<br>Agree</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $questions = [
                                                'content_met_expectations' => 'Course content met expectations.',
                                                'trainer_explained_clearly' => 'Trainer explained concepts clearly.',
                                                'materials_useful' => 'Learning materials were useful.',
                                                'environment_comfortable' => 'Training environment was comfortable.',
                                                'activities_engaging' => 'Activities were engaging.',
                                                'practical_sessions_effective' => 'Practical sessions were effective.',
                                                'gained_knowledge' => 'I gained useful knowledge.'
                                            ];
                                            foreach ($questions as $key => $label):
                                        ?>
                                        <tr>
                                            <td class="fw-medium text-dark"><?= $label ?></td>
                                            <?php for ($i=1; $i<=5; $i++): ?>
                                                <td><input type="radio" name="<?= $key ?>" value="<?= $i ?>" class="likert-radio" required></td>
                                            <?php endfor; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h3 class="h6 fw-bold mb-3 text-uppercase text-muted" style="letter-spacing:0.5px">Section 2: Open Feedback</h3>
                            
                            <div class="mb-4">
                                <label class="form-label fw-medium text-dark">What did you like most?</label>
                                <textarea class="form-control bg-light" name="liked_most" rows="3" placeholder="Share your positive experiences..."></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-medium text-dark">What can be improved?</label>
                                <textarea class="form-control bg-light" name="needs_improvement" rows="3" placeholder="Share suggestions for improvement..."></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-medium text-dark">Additional Comments (Optional)</label>
                                <textarea class="form-control bg-light" name="feedback" rows="2" placeholder="Any other thoughts..."></textarea>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h3 class="h6 fw-bold mb-3 text-uppercase text-muted" style="letter-spacing:0.5px">Section 3: Overall Satisfaction</h3>
                            
                            <div class="row align-items-center mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium text-dark mb-0">Course Overall Rating</label>
                                </div>
                                <div class="col-md-6">
                                    <div class="star-rating">
                                        <input type="radio" id="cr5_<?= $courseIndex ?>" name="course_rating" value="5" required><label for="cr5_<?= $courseIndex ?>">★</label>
                                        <input type="radio" id="cr4_<?= $courseIndex ?>" name="course_rating" value="4"><label for="cr4_<?= $courseIndex ?>">★</label>
                                        <input type="radio" id="cr3_<?= $courseIndex ?>" name="course_rating" value="3"><label for="cr3_<?= $courseIndex ?>">★</label>
                                        <input type="radio" id="cr2_<?= $courseIndex ?>" name="course_rating" value="2"><label for="cr2_<?= $courseIndex ?>">★</label>
                                        <input type="radio" id="cr1_<?= $courseIndex ?>" name="course_rating" value="1"><label for="cr1_<?= $courseIndex ?>">★</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium text-dark mb-0">Trainer Overall Rating</label>
                                </div>
                                <div class="col-md-6">
                                    <div class="star-rating">
                                        <input type="radio" id="ir5_<?= $courseIndex ?>" name="instructor_rating" value="5" required><label for="ir5_<?= $courseIndex ?>">★</label>
                                        <input type="radio" id="ir4_<?= $courseIndex ?>" name="instructor_rating" value="4"><label for="ir4_<?= $courseIndex ?>">★</label>
                                        <input type="radio" id="ir3_<?= $courseIndex ?>" name="instructor_rating" value="3"><label for="ir3_<?= $courseIndex ?>">★</label>
                                        <input type="radio" id="ir2_<?= $courseIndex ?>" name="instructor_rating" value="2"><label for="ir2_<?= $courseIndex ?>">★</label>
                                        <input type="radio" id="ir1_<?= $courseIndex ?>" name="instructor_rating" value="1"><label for="ir1_<?= $courseIndex ?>">★</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-medium text-dark">Would you recommend this course?</label>
                                <div class="d-flex gap-4 mt-2">
                                    <label class="d-flex align-items-center gap-2"><input type="radio" name="recommend_course" value="Yes" class="likert-radio" required> Yes</label>
                                    <label class="d-flex align-items-center gap-2"><input type="radio" name="recommend_course" value="Maybe" class="likert-radio"> Maybe</label>
                                    <label class="d-flex align-items-center gap-2"><input type="radio" name="recommend_course" value="No" class="likert-radio"> No</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-medium text-dark d-block">How likely are you to recommend this course to a colleague? (0-10)</label>
                                <div class="nps-group mt-2">
                                    <?php for($i=0; $i<=10; $i++): ?>
                                        <input type="radio" name="nps" id="nps_<?= $courseIndex ?>_<?= $i ?>" value="<?= $i ?>" class="nps-input" required>
                                        <label for="nps_<?= $courseIndex ?>_<?= $i ?>" class="nps-btn"><?= $i ?></label>
                                    <?php endfor; ?>
                                </div>
                                <div class="d-flex justify-content-between mt-1 small text-muted">
                                    <span>Not likely at all</span>
                                    <span>Extremely likely</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-end border-top pt-4">
                            <button class="btn btn-primary px-5 py-2 fw-bold" type="submit">Submit Feedback</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Evaluation Summary Sidebar ────────────── -->
        <div class="col-lg-4">
            <div class="trainee-card animate-in mb-4">
                <h2 class="h5 fw-bold mb-3">Feedback Guidelines</h2>
                <div class="d-flex flex-column gap-3 mt-4">
                    <div class="d-flex gap-3">
                        <div class="text-primary mt-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div>
                            <div class="fw-bold mb-1">Be honest & constructive</div>
                            <div class="small text-muted">Your feedback is anonymous and helps improve future training sessions.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="text-primary mt-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <div>
                            <div class="fw-bold mb-1">Rate all categories</div>
                            <div class="small text-muted">Please provide answers for all required sections so we can gather complete data.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="text-primary mt-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        </div>
                        <div>
                            <div class="fw-bold mb-1">Share suggestions</div>
                            <div class="small text-muted">Use the text areas to provide detailed thoughts on your learning experience.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
