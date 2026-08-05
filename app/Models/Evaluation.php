<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Evaluation extends Model
{
    public function save(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO evaluations (course_id, trainee_id, rating, course_rating, instructor_rating, feedback, comments, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE rating=VALUES(rating), course_rating=VALUES(course_rating), instructor_rating=VALUES(instructor_rating), feedback=VALUES(feedback), comments=VALUES(comments), completed_at=NOW()');
        $stmt->execute([$data['course_id'], $data['trainee_id'], $data['course_rating'], $data['course_rating'], $data['instructor_rating'], $data['feedback'], $data['comments']]);
    }

    public function reports(): array
    {
        return $this->table('evaluations e')
            ->select('e.*', 'u.name AS trainee_name', 'c.title AS course_title', 'i.name AS instructor_name')
            ->join('users u', 'u.id', '=', 'e.trainee_id')
            ->join('training_sessions ts', 'ts.id', '=', 'e.course_id')->join('courses c', 'c.id', '=', 'ts.course_id')
            ->leftJoin('users i', 'i.id', '=', 'ts.instructor_id')
            ->orderBy('COALESCE(e.completed_at, e.created_at)', 'DESC')
            ->get();
    }

    public function completedCoursesNeedingEvaluation(int $traineeId): array
    {
        return $this->table('enrolments e')
            ->select('e.training_session_id AS course_id', 'c.title')
            ->join('training_sessions ts', 'ts.id', '=', 'e.training_session_id')->join('courses c', 'c.id', '=', 'ts.course_id')
            ->leftJoin('evaluations ev', 'ev.course_id', '=', 'e.training_session_id AND ev.trainee_id = e.trainee_id')
            ->where('e.trainee_id', $traineeId)
            ->where('e.status', 'completed')
            ->whereNull('ev.id')
            ->orderBy('e.completed_at', 'DESC')
            ->get();
    }
}
