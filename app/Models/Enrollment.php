<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Enrollment extends Model
{
    public function request(int $courseId, int $traineeId): void
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO enrolments (course_id, trainee_id, status) VALUES (?, ?, "pending")');
        $stmt->execute([$courseId, $traineeId]);
    }

    public function forTrainee(int $traineeId): array
    {
        $stmt = $this->db->prepare('SELECT e.*, c.title, c.category, c.description, c.start_date, c.end_date, c.status AS course_status, c.thumbnail_image, c.capacity, c.max_participants, users.name AS instructor_name, counts.participant_count FROM enrolments e JOIN courses c ON c.id = e.course_id LEFT JOIN users ON users.id = c.instructor_id LEFT JOIN (SELECT course_id, COUNT(*) AS participant_count FROM enrolments WHERE status IN ("active","completed") GROUP BY course_id) counts ON counts.course_id = c.id WHERE e.trainee_id = ? ORDER BY e.created_at DESC');
        $stmt->execute([$traineeId]);
        return $stmt->fetchAll();
    }

    public function pending(): array
    {
        return $this->table('enrolments e')
            ->select('e.*', 'c.title AS course_title', 'u.name AS trainee_name', 'u.email')
            ->join('courses c', 'c.id', '=', 'e.course_id')
            ->join('users u', 'u.id', '=', 'e.trainee_id')
            ->where('e.status', 'pending')
            ->orderBy('e.created_at', 'DESC')
            ->get();
    }

    /** All enrolments for admin view (all statuses) */
    public function allEnrolments(): array
    {
        return $this->table('enrolments e')
            ->select('e.*', 'c.title AS course_title', 'u.name AS trainee_name', 'u.email', 'instr.name AS instructor_name')
            ->join('courses c', 'c.id', '=', 'e.course_id')
            ->join('users u', 'u.id', '=', 'e.trainee_id')
            ->leftJoin('users instr', 'instr.id', '=', 'c.instructor_id')
            ->orderBy('e.created_at', 'DESC')
            ->get();
    }

    /** Enrolments for courses assigned to a specific instructor */
    public function forInstructor(int $instructorId): array
    {
        return $this->table('enrolments e')
            ->select('e.*', 'c.title AS course_title', 'u.name AS trainee_name', 'u.email')
            ->join('courses c', 'c.id', '=', 'e.course_id')
            ->join('users u', 'u.id', '=', 'e.trainee_id')
            ->where('c.instructor_id', $instructorId)
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
            ->join('courses c', 'c.id', '=', 'e.course_id')
            ->where('e.id', $enrolmentId)
            ->where('c.instructor_id', $instructorId)
            ->count() > 0;
    }
}
