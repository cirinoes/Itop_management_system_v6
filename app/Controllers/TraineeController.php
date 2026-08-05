<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Security;
use App\Core\Controller;
use App\Models\Certificate;
use App\Models\Evaluation;
use App\Models\TraineeProfile;
use App\Models\User;

final class TraineeController extends Controller
{
    public function userProfile(): void
    {
        Auth::requireLogin();
        $role = Auth::role();
        
        if ($role === 'admin') {
            (new AdminController())->profile();
            return;
        }

        if ($role === 'instructor') {
            $this->instructorProfile();
            return;
        }

        $this->profile();
    }

    public function profile(): void
    {
        Auth::requireLogin();
        $userId = (int) Auth::id();
        $profileModel = new TraineeProfile();
        $this->render('profile/trainee', [
            'user' => (new User())->find($userId),
            'profile' => $profileModel->findByUser($userId),
            'documents' => $profileModel->documents($userId),
            'stats' => $this->traineeStats($userId),
            'learning' => $this->learningSummary($userId),
        ]);
    }

    public function instructorProfile(): void
    {
        Auth::requireLogin();
        $userId = (int) Auth::id();
        $this->render('profile/instructor', [
            'user' => (new User())->find($userId),
            'stats' => $this->instructorStats($userId),
        ]);
    }

    private function traineeStats(int $userId): array
    {
        $db = \App\Core\Model::getDb();
        $stmt1 = $db->prepare('SELECT COUNT(*) FROM enrolments WHERE trainee_id = ?');
        $stmt1->execute([$userId]);
        $enrolled = (int) $stmt1->fetchColumn();

        $stmt2 = $db->prepare('SELECT COUNT(*) FROM enrolments WHERE trainee_id = ? AND status = "completed"');
        $stmt2->execute([$userId]);
        $completed = (int) $stmt2->fetchColumn();

        $stmt3 = $db->prepare('SELECT COUNT(*) FROM certificates WHERE trainee_id = ? AND status = "issued"');
        $stmt3->execute([$userId]);
        $certificates = (int) $stmt3->fetchColumn();

        return [
            'enrolled_courses' => $enrolled,
            'completed_courses' => $completed,
            'certificates_earned' => $certificates,
        ];
    }

    private function instructorStats(int $userId): array
    {
        $db = \App\Core\Model::getDb();
        $stmt1 = $db->prepare('SELECT COUNT(*) FROM training_sessions WHERE instructor_id = ?');
        $stmt1->execute([$userId]);
        $courses = (int) $stmt1->fetchColumn();

        $stmt2 = $db->prepare('SELECT COUNT(DISTINCT trainee_id) FROM enrolments e JOIN training_sessions ts ON ts.id = e.training_session_id WHERE ts.instructor_id = ?');
        $stmt2->execute([$userId]);
        $students = (int) $stmt2->fetchColumn();

        $stmt3 = $db->prepare('SELECT COUNT(*) FROM certificates cert JOIN training_sessions ts ON ts.id = cert.course_id WHERE ts.instructor_id = ? AND cert.status = "issued"');
        $stmt3->execute([$userId]);
        $certs = (int) $stmt3->fetchColumn();

        $stmt4 = $db->prepare('SELECT AVG(instructor_rating) FROM evaluations ev JOIN training_sessions ts ON ts.id = ev.course_id WHERE ts.instructor_id = ?');
        $stmt4->execute([$userId]);
        $avgRating = $stmt4->fetchColumn();

        return [
            'assigned_courses' => $courses,
            'total_students' => $students,
            'certificates_issued' => $certs,
            'avg_rating' => $avgRating ? round((float)$avgRating, 1) : 4.9,
        ];
    }

    public function saveProfile(): void
    {
        Auth::requireLogin();
        Security::verifyCsrf();
        $userId = (int) Auth::id();
        $profilePicture = null;
        if (!empty($_FILES['profile_picture']['name'])) {
            $profilePicture = Security::validateUpload($_FILES['profile_picture'], ['jpg', 'jpeg', 'png', 'webp']);
            if ($profilePicture) {
                move_uploaded_file($_FILES['profile_picture']['tmp_name'], UPLOAD_PATH . '/' . $profilePicture);
            }
        }

        // Update standard user columns on users table
        $db = \App\Core\Model::getDb();
        $stmt = $db->prepare('UPDATE users SET name = ?, phone = ?, address = ?, identity_number = ?, gender = ?, date_of_birth = ?, institution_company = ?, time_zone = ?, profile_picture = COALESCE(?, profile_picture), updated_at = NOW() WHERE id = ?');
        $stmt->execute([
            Security::cleanString($_POST['name'] ?? ''),
            Security::cleanString($_POST['phone'] ?? ''),
            Security::cleanString($_POST['address'] ?? '', 1000),
            Security::cleanString($_POST['identity_number'] ?? ''),
            Security::cleanString($_POST['gender'] ?? ''),
            !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
            Security::cleanString($_POST['institution_company'] ?? ''),
            Security::cleanString($_POST['time_zone'] ?? 'Asia/Kuala_Lumpur'),
            $profilePicture,
            $userId
        ]);

        // If trainee, also update trainee_profiles table and handle document uploads
        if (Auth::role() === 'trainee') {
            $profileModel = new TraineeProfile();
            $profileModel->save($userId, [
                'identity_number' => Security::cleanString($_POST['identity_number'] ?? ''),
                'phone' => Security::cleanString($_POST['phone'] ?? ''),
                'address' => Security::cleanString($_POST['address'] ?? '', 1000),
                'education' => Security::cleanString($_POST['education'] ?? '', 2000),
                'employment' => Security::cleanString($_POST['employment'] ?? '', 2000),
                'emergency_contact' => Security::cleanString($_POST['emergency_contact'] ?? '', 1000),
                'profile_picture' => $profilePicture,
            ]);

            if (!empty($_FILES['document']['name'])) {
                $document = Security::validateUpload($_FILES['document'], ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);
                if ($document) {
                    move_uploaded_file($_FILES['document']['tmp_name'], UPLOAD_PATH . '/' . $document);
                    $profileModel->addDocument($userId, Security::cleanString($_POST['document_type'] ?? 'Supporting Document'), $_FILES['document']['name'], $document);
                }
            }
        }

        Activity::log('Updated profile');
        $this->redirect('index.php?page=profile');
    }

    public function evaluations(): void
    {
        Auth::requireRole(['trainee']);
        $this->render('trainee/evaluations', [
            'courses' => (new Evaluation())->completedCoursesNeedingEvaluation((int) Auth::id()),
        ]);
    }

    public function saveEvaluation(): void
    {
        Auth::requireRole(['trainee']);
        Security::verifyCsrf();
        
        $likertData = [
            'content_met_expectations' => (int) ($_POST['content_met_expectations'] ?? 0),
            'trainer_explained_clearly' => (int) ($_POST['trainer_explained_clearly'] ?? 0),
            'materials_useful' => (int) ($_POST['materials_useful'] ?? 0),
            'environment_comfortable' => (int) ($_POST['environment_comfortable'] ?? 0),
            'activities_engaging' => (int) ($_POST['activities_engaging'] ?? 0),
            'practical_sessions_effective' => (int) ($_POST['practical_sessions_effective'] ?? 0),
            'gained_knowledge' => (int) ($_POST['gained_knowledge'] ?? 0),
            'overall_satisfaction' => (int) ($_POST['overall_satisfaction'] ?? 0),
            'recommend_course' => Security::cleanString($_POST['recommend_course'] ?? ''),
            'nps' => (int) ($_POST['nps'] ?? 0),
            'liked_most' => Security::cleanString($_POST['liked_most'] ?? '', 1000),
            'needs_improvement' => Security::cleanString($_POST['needs_improvement'] ?? '', 1000),
        ];

        (new Evaluation())->save([
            'course_id' => (int) ($_POST['course_id'] ?? 0),
            'trainee_id' => Auth::id(),
            'course_rating' => max(1, min(5, (int) ($_POST['course_rating'] ?? 1))),
            'instructor_rating' => max(1, min(5, (int) ($_POST['instructor_rating'] ?? 1))),
            'feedback' => Security::cleanString($_POST['feedback'] ?? '', 3000),
            'comments' => json_encode($likertData),
        ]);
        Activity::log('Submitted course evaluation');
        $this->redirect('index.php?page=trainee-evaluations');
    }

    public function certificates(): void
    {
        Auth::requireRole(['trainee']);
        $this->render('trainee/certificates', [
            'certificates' => (new Certificate())->forTrainee((int) Auth::id(), Security::cleanString($_GET['q'] ?? '')),
            'q' => Security::cleanString($_GET['q'] ?? ''),
        ]);
    }

    private function learningSummary(int $userId): array
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT e.*, c.title AS course_title, ts.start_date, ts.end_date, i.name AS instructor_name FROM enrolments e JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id LEFT JOIN users i ON i.id = ts.instructor_id WHERE e.trainee_id = ? ORDER BY e.created_at DESC LIMIT 5');
        $stmt->execute([$userId]);
        $courses = $stmt->fetchAll();
        $activity = $db->prepare('SELECT * FROM login_activity WHERE user_id = ? ORDER BY created_at DESC LIMIT 8');
        $activity->execute([$userId]);
        return [
            'courses' => $courses,
            'login_activity' => $activity->fetchAll(),
        ];
    }
}
