<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Lms extends Model
{
    public function materials(int $courseId): array
    {
        return $this->table('learning_materials')->where('course_id', $courseId)->orderBy('created_at', 'DESC')->get();
    }

    public function assignments(int $courseId): array
    {
        return $this->table('assignments')->where('course_id', $courseId)->orderBy('due_date', 'ASC')->get();
    }

    public function quizzes(int $courseId): array
    {
        return $this->table('quizzes')->where('course_id', $courseId)->orderBy('created_at', 'DESC')->get();
    }

    public function addMaterial(array $data): void
    {
        $this->table('learning_materials')->insert([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'type' => $data['type'],
            'file_path' => $data['file_path'],
            'external_url' => $data['external_url'],
            'uploaded_by' => $data['uploaded_by']
        ]);
    }

    public function addAssignment(array $data): void
    {
        $this->table('assignments')->insert([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'instructions' => $data['instructions'],
            'due_date' => $data['due_date'],
            'max_score' => $data['max_score'],
            'created_by' => $data['created_by']
        ]);
    }

    public function submitAssignment(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO assignment_submissions (assignment_id, trainee_id, file_path, notes) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), notes = VALUES(notes), submitted_at = NOW(), status = "submitted"');
        $stmt->execute([$data['assignment_id'], $data['trainee_id'], $data['file_path'], $data['notes']]);
    }

    public function gradeSubmission(int $submissionId, float $score, string $feedback): void
    {
        $this->table('assignment_submissions')->where('id', $submissionId)->update([
            'score' => $score,
            'feedback' => $feedback,
            'status' => 'graded',
            'graded_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function submissionsForInstructor(int $instructorId): array
    {
        return $this->table('assignment_submissions s')
            ->select('s.*', 'a.title AS assignment_title', 'c.title AS course_title', 'c.id AS course_id', 'u.name AS trainee_name', 'u.email AS trainee_email')
            ->join('assignments a', 'a.id', '=', 's.assignment_id')
            ->join('training_sessions ts', 'ts.id', '=', 'a.course_id')->join('courses c', 'c.id', '=', 'ts.course_id')
            ->join('users u', 'u.id', '=', 's.trainee_id')
            ->where('ts.instructor_id', $instructorId)
            ->orderBy('s.submitted_at', 'DESC')
            ->get();
    }

    public function submissionsForCourse(int $courseId): array
    {
        return $this->table('assignment_submissions s')
            ->select('s.*', 'a.title AS assignment_title', 'u.name AS trainee_name', 'u.email AS trainee_email')
            ->join('assignments a', 'a.id', '=', 's.assignment_id')
            ->join('users u', 'u.id', '=', 's.trainee_id')
            ->where('a.course_id', $courseId)
            ->orderBy('s.submitted_at', 'DESC')
            ->get();
    }

    public function traineeSubmissions(int $courseId, int $traineeId): array
    {
        $submissions = $this->table('assignment_submissions s')
            ->select('s.*')
            ->join('assignments a', 'a.id', '=', 's.assignment_id')
            ->where('a.course_id', $courseId)
            ->where('s.trainee_id', $traineeId)
            ->get();
            
        $result = [];
        foreach ($submissions as $sub) {
            $result[$sub['assignment_id']] = $sub;
        }
        return $result;
    }

    public function removeSubmission(int $assignmentId, int $traineeId): void
    {
        $this->table('assignment_submissions')
            ->where('assignment_id', $assignmentId)
            ->where('trainee_id', $traineeId)
            ->delete();
    }
}

