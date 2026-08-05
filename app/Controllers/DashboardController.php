<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Security;
use App\Models\Certificate;
use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Lms;
use App\Models\Notification;
use App\Models\TraineeProfile;
use App\Models\User;

final class DashboardController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireLogin();
        match (Auth::role()) {
            'admin' => $this->admin(),
            'instructor' => $this->instructor(),
            default => $this->traineeOverview(),
        };
    }

    public function admin(): void
    {
        Auth::requireRole(['admin']);
        $content = new Content();
        $db = \App\Core\Model::getDb();
        $pendingUsersCount = (int) $db->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
        $this->render('dashboard/admin', [
            'stats' => $content->stats(),
            'trends' => $content->trends(),
            'analytics' => $content->dashboardAnalytics(),
            'activity' => $content->recentActivity(),
            'announcements' => $content->announcements(),
            'pendingUsersCount' => $pendingUsersCount,
        ]);
    }

    public function instructor(): void
    {
        Auth::requireRole(['instructor']);
        $courseModel = new Course();
        $userId = (int) Auth::id();

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editingCourse = null;
        if ($editingId) {
            $course = $courseModel->find($editingId);
            if ($course && (int) $course['instructor_id'] === $userId) {
                $editingCourse = $course;
            }
        }

        $courses = $courseModel->assignedTo($userId);

        $enrollmentModel = new Enrollment();
        $sessionIds = array_map(static fn (array $c): int => (int) $c['id'], $courses);
        $enrolledSessionIds = $enrollmentModel->enrolledSessionIds($userId, $sessionIds);
        foreach ($courses as &$course) {
            $course['self_enrolled'] = in_array((int) $course['id'], $enrolledSessionIds, true);
        }
        unset($course);

        $this->render('dashboard/instructor', [
            'courses' => $courses,
            'submissions' => (new Lms())->submissionsForInstructor($userId),
            'editing' => $editingCourse,
            'availableSessions' => $courseModel->availableToClaim(),
        ]);
    }

    /** Instructor self-enrols into one of their own assigned sessions */
    public function selfEnroll(): void
    {
        Auth::requireRole(['instructor']);
        Security::verifyCsrf();
        $userId = (int) Auth::id();
        $sessionId = (int) ($_POST['session_id'] ?? 0);

        $course = (new Course())->find($sessionId);
        if (!$course || (int) $course['instructor_id'] !== $userId) {
            http_response_code(403);
            exit('You can only enrol in your own assigned courses.');
        }

        (new Enrollment())->selfEnroll($sessionId, $userId);
        Activity::log('Self-enrolled in assigned course', $userId);
        $this->redirect('index.php?page=instructor-dashboard');
    }

    /** Instructor withdraws their own self-enrolment (blocked once the session has ended) */
    public function selfWithdraw(): void
    {
        Auth::requireRole(['instructor']);
        Security::verifyCsrf();
        $userId = (int) Auth::id();
        $sessionId = (int) ($_POST['session_id'] ?? 0);

        $course = (new Course())->find($sessionId);
        if (!$course || (int) $course['instructor_id'] !== $userId) {
            http_response_code(403);
            exit('You do not have access to this course.');
        }

        $endDate = $course['end_date'] ?? null;
        if ($endDate && strtotime($endDate) < time()) {
            $this->redirect('index.php?page=instructor-dashboard');
        }

        (new Enrollment())->selfWithdraw($sessionId, $userId);
        Activity::log('Withdrew self-enrolment from assigned course', $userId);
        $this->redirect('index.php?page=instructor-dashboard');
    }

    public function instructorCourses(): void
    {
        Auth::requireRole(['instructor']);
        $userId = (int) Auth::id();
        $courseModel = new Course();
        $this->render('instructor/courses', [
            'courses' => $courseModel->assignedTo($userId),
            'availableSessions' => $courseModel->availableToClaim(),
        ]);
    }

    /** Trainee Overview — distinct from "My Learning" */
    public function traineeOverview(): void
    {
        Auth::requireRole(['trainee']);
        $userId = (int) Auth::id();
        $enrolments = (new Enrollment())->forTrainee($userId);
        $certificates = (new Certificate())->forTrainee($userId);
        $notificationCount = (new Notification())->unreadCount($userId);
        $profile = (new TraineeProfile())->findByUser($userId);
        $user = (new User())->find($userId);
        $announcements = (new Content())->announcements(false, 'trainee', $userId);

        $this->render('dashboard/trainee-overview', [
            'enrolments' => $enrolments,
            'certificates' => $certificates,
            'notificationCount' => $notificationCount,
            'profile' => $profile,
            'user' => $user,
            'announcements' => $announcements,
        ]);
    }

    /** Trainee My Learning page */
    public function trainee(): void
    {
        Auth::requireRole(['trainee']);
        $userId = (int) Auth::id();
        $enrolments = (new Enrollment())->forTrainee($userId);
        
        $lms = new Lms();
        $coursesWithMaterials = [];
        
        foreach ($enrolments as $enrolment) {
            $courseId = (int) $enrolment['course_id'];
            $materials = $lms->materials($courseId);
            $assignments = $lms->assignments($courseId);
            $quizzes = $lms->quizzes($courseId);
            
            $submissions = $lms->traineeSubmissions($courseId, $userId);
            foreach ($assignments as &$assignment) {
                $assignment['submission'] = $submissions[$assignment['id']] ?? null;
            }
            unset($assignment);
            
            $enrolment['materials'] = $materials;
            $enrolment['assignments'] = $assignments;
            $enrolment['quizzes'] = $quizzes;
            $coursesWithMaterials[] = $enrolment;
        }

        $this->render('dashboard/trainee', [
            'enrolments' => $coursesWithMaterials,
            'pendingEvaluations' => (new Evaluation())->completedCoursesNeedingEvaluation($userId),
            'announcements' => (new Content())->announcements(false, 'trainee', $userId),
        ]);
    }

    public function apiAnnouncements(): void
    {
        Auth::requireRole(['trainee']);
        $userId = (int) Auth::id();
        $announcements = (new Content())->announcements(false, 'trainee', $userId);
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $announcements]);
        exit;
    }
}
