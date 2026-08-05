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
        $stmt = $this->db->prepare('SELECT e.*, ts.course_id, c.title, c.category, c.description, c.thumbnail_image, ts.start_date, ts.end_date, ts.status AS course_status, ts.capacity, ts.max_participants, users.name AS instructor_name, counts.participant_count FROM enrolments e JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id LEFT JOIN users ON users.id = ts.instructor_id LEFT JOIN (SELECT training_session_id, COUNT(*) AS participant_count FROM enrolments WHERE status IN ("active","completed") GROUP BY training_session_id) counts ON counts.training_session_id = ts.id WHERE e.trainee_id = ? ORDER BY e.created_at DESC');
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

    /** Whether the user already holds an active/completed enrolment for a session */
    public function hasEnrolment(int $sessionId, int $userId): bool
    {
        return $this->table('enrolments')
            ->where('training_session_id', $sessionId)
            ->where('trainee_id', $userId)
            ->whereIn('status', ['active', 'completed'])
            ->count() > 0;
    }

    /** Session ids this user is currently enrolled in (status active/completed) */
    public function enrolledSessionIds(int $userId, array $sessionIds = []): array
    {
        $query = $this->table('enrolments')
            ->select('training_session_id')
            ->where('trainee_id', $userId)
            ->whereIn('status', ['active', 'completed']);

        if (!empty($sessionIds)) {
            $query->whereIn('training_session_id', $sessionIds);
        }

        $rows = $query->get();
        return array_map(static fn (array $row): int => (int) $row['training_session_id'], $rows);
    }

    /** Instructor self-enrols into one of their own sessions (status active, no approval) */
    public function selfEnroll(int $sessionId, int $userId): void
    {
        $stmt = $this->db->prepare('INSERT INTO enrolments (training_session_id, trainee_id, status, created_at, updated_at)
            VALUES (?, ?, "active", NOW(), NOW())
            ON DUPLICATE KEY UPDATE status = "active", updated_at = NOW()');
        $stmt->execute([$sessionId, $userId]);
    }

    /** Withdraw an instructor's self-enrolment (does nothing if no enrolment exists) */
    public function selfWithdraw(int $sessionId, int $userId): void
    {
        $this->table('enrolments')
            ->where('training_session_id', $sessionId)
            ->where('trainee_id', $userId)
            ->where('status', 'active')
            ->update([
                'status' => 'withdrawn',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
