<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Security;
use App\Core\Controller;
use App\Models\Course;
use App\Models\Lms;

final class LmsController extends Controller
{
    public function courseRoom(): void
    {
        Auth::requireLogin();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        $lms = new Lms();
        $submissions = [];
        $traineeSubmissions = [];
        if (in_array(Auth::role(), ['admin', 'instructor'], true)) {
            $view = 'courses/instructor_room';
            $submissions = $lms->submissionsForCourse($courseId);
        } else {
            $view = 'courses/room';
            $traineeSubmissions = $lms->traineeSubmissions($courseId, Auth::id());
        }

        $this->render($view, [
            'course' => (new Course())->find($courseId),
            'materials' => $lms->materials($courseId),
            'assignments' => $lms->assignments($courseId),
            'quizzes' => $lms->quizzes($courseId),
            'submissions' => $submissions,
            'trainee_submissions' => $traineeSubmissions,
        ]);
    }

    public function addMaterial(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        Security::verifyCsrf();
        $filename = null;
        if (!empty($_FILES['material']['name'])) {
            $filename = Security::validateUpload($_FILES['material'], ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'mp4', 'zip']);
            move_uploaded_file($_FILES['material']['tmp_name'], UPLOAD_PATH . '/' . $filename);
        }

        (new Lms())->addMaterial([
            'course_id' => (int) $_POST['course_id'],
            'title' => Security::cleanString($_POST['title'] ?? ''),
            'type' => Security::cleanString($_POST['type'] ?? 'document'),
            'file_path' => $filename,
            'external_url' => filter_var($_POST['external_url'] ?? '', FILTER_VALIDATE_URL) ?: null,
            'uploaded_by' => Auth::id(),
        ]);
        Activity::log('Uploaded material');
        header('Location: index.php?page=course-room&course_id=' . (int) $_POST['course_id']);
    }

    public function addAssignment(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        Security::verifyCsrf();
        (new Lms())->addAssignment([
            'course_id' => (int) $_POST['course_id'],
            'title' => Security::cleanString($_POST['title'] ?? ''),
            'instructions' => Security::cleanString($_POST['instructions'] ?? '', 2000),
            'due_date' => $_POST['due_date'] ?: null,
            'max_score' => (float) ($_POST['max_score'] ?? 100),
            'created_by' => Auth::id(),
        ]);
        Activity::log('Created assignment');
        header('Location: index.php?page=course-room&course_id=' . (int) $_POST['course_id']);
    }

    public function submitAssignment(): void
    {
        Auth::requireRole(['trainee']);
        Security::verifyCsrf();
        $filename = Security::validateUpload($_FILES['submission'], ['pdf', 'doc', 'docx', 'zip', 'jpg', 'png']);
        if (!$filename) {
            exit('A submission file is required.');
        }
        move_uploaded_file($_FILES['submission']['tmp_name'], SUBMISSION_PATH . '/' . $filename);
        (new Lms())->submitAssignment([
            'assignment_id' => (int) $_POST['assignment_id'],
            'trainee_id' => Auth::id(),
            'file_path' => $filename,
            'notes' => Security::cleanString($_POST['notes'] ?? '', 1000),
        ]);
        Activity::log('Submitted assignment');
        $this->redirect('index.php?page=trainee-dashboard');
    }

    public function cancelSubmission(): void
    {
        Auth::requireRole(['trainee']);
        Security::verifyCsrf();
        $assignmentId = (int) $_POST['assignment_id'];
        $traineeId = (int) Auth::id();

        // Check if due date has passed
        $lms = new Lms();
        $db = \App\Core\Model::getDb();
        $stmt = $db->prepare('SELECT due_date FROM assignments WHERE id = ?');
        $stmt->execute([$assignmentId]);
        $assignment = $stmt->fetch();

        if ($assignment && $assignment['due_date']) {
            if (strtotime($assignment['due_date']) < time()) {
                exit('You cannot cancel a submission after the due date.');
            }
        }

        // Fetch submission to delete file (if needed)
        $stmt = $db->prepare('SELECT file_path FROM assignment_submissions WHERE assignment_id = ? AND trainee_id = ?');
        $stmt->execute([$assignmentId, $traineeId]);
        $submission = $stmt->fetch();

        if ($submission && $submission['file_path']) {
            $filePath = SUBMISSION_PATH . '/' . $submission['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $lms->removeSubmission($assignmentId, $traineeId);
        Activity::log('Cancelled assignment submission');
        $this->redirect('index.php?page=trainee-dashboard');
    }
}

