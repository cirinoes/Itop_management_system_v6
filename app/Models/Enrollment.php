<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Enrollment extends Model
{
    public function request(int $sessionId, int $traineeId): void
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO enrolments (training_session_id, trainee_id, status) VALUES (?, ?, "pending")');
        $stmt->execute([$sessionId, $traineeId]);
    }

    public function forTrainee(int $traineeId): array
    {
        $stmt = $this->db->prepare('SELECT e.*, c.title, c.category, c.description, c.thumbnail_image, ts.start_date, ts.end_date, ts.status AS course_status, ts.capacity, ts.max_participants, users.name AS instructor_name, counts.participant_count FROM enrolments e JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id LEFT JOIN users ON users.id = ts.instructor_id LEFT JOIN (SELECT training_session_id, COUNT(*) AS participant_count FROM enrolments WHERE status IN ("active","completed") GROUP BY training_session_id) counts ON counts.training_session_id = ts.id WHERE e.trainee_id = ? ORDER BY e.created_at DESC');
        $stmt->execute([$traineeId]);
        return $stmt->fetchAll();
    }

    public function pending(): array
    {
        return $this->table('enrolments e')
            ->select('e.*', 'c.title AS course_title', 'u.name AS trainee_name', 'u.email', 'ts.start_date', 'ts.end_date')
            ->join('training_sessions ts', 'ts.id', '=', 'e.training_session_id')
            ->join('courses c', 'c.id', '=', 'ts.course_id')
            ->join('users u', 'u.id', '=', 'e.trainee_id')
            ->where('e.status', 'pending')
            ->orderBy('e.created_at', 'DESC')
            ->get();
    }

    /** All enrolments for admin view (all statuses) */
    public function allEnrolments(): array
    {
        return $this->table('enrolments e')
            ->select('e.*', 'c.title AS course_title', 'u.name AS trainee_name', 'u.email', 'instr.name AS instructor_name', 'ts.start_date', 'ts.end_date')
            ->join('training_sessions ts', 'ts.id', '=', 'e.training_session_id')
            ->join('courses c', 'c.id', '=', 'ts.course_id')
            ->join('users u', 'u.id', '=', 'e.trainee_id')
            ->leftJoin('users instr', 'instr.id', '=', 'ts.instructor_id')
            ->orderBy('e.created_at', 'DESC')
            ->get();
    }

    /** Enrolments for courses assigned to a specific instructor */
    public function forInstructor(int $instructorId): array
    {
        return $this->table('enrolments e')
            ->select('e.*', 'c.title AS course_title', 'u.name AS trainee_name', 'u.email', 'ts.start_date', 'ts.end_date')
            ->join('training_sessions ts', 'ts.id', '=', 'e.training_session_id')
            ->join('courses c', 'c.id', '=', 'ts.course_id')
            ->join('users u', 'u.id', '=', 'e.trainee_id')
            ->where('ts.instructor_id', $instructorId)
            ->orderBy('e.created_at', 'DESC')
            ->get();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->table('enrolments')->where('id', $id)->update([
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /** Verify that an enrolment belongs to a course taught by the given instructor */
    public function belongsToInstructor(int $enrolmentId, int $instructorId): bool
    {
        return $this->table('enrolments e')
            ->join('training_sessions ts', 'ts.id', '=', 'e.training_session_id')
            ->where('e.id', $enrolmentId)
            ->where('ts.instructor_id', $instructorId)
            ->count() > 0;
    }
}
