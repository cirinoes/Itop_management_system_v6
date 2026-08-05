<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Course extends Model
{
    private ?bool $academySchemaReady = null;

    private function academySchemaReady(): bool
    {
        if ($this->academySchemaReady !== null) {
            return $this->academySchemaReady;
        }

        try {
            $hasAcademiesTable = (bool) $this->db->query("SHOW TABLES LIKE 'academies'")->fetchColumn();
            $hasAcademyColumn = (bool) $this->db->query("SHOW COLUMNS FROM courses LIKE 'academy_id'")->fetchColumn();
            $this->academySchemaReady = $hasAcademiesTable && $hasAcademyColumn;
        } catch (\Throwable $exception) {
            $this->academySchemaReady = false;
        }

        return $this->academySchemaReady;
    }

    public function publicList(string $search = ''): array
    {
        $like = '%' . $search . '%';
        $query = $this->table('training_sessions ts')
            ->leftJoin('courses c', 'c.id', '=', 'ts.course_id')
            ->leftJoin('users', 'users.id', '=', 'ts.instructor_id')
            ->leftJoin('enrolments', 'enrolments.training_session_id', '=', 'ts.id AND enrolments.status IN ("active","completed")')
            ->whereIn('c.status', ['published', 'active'])
            ->whereRaw('(c.title LIKE ? OR c.category LIKE ?)', [$like, $like])
            ->groupBy('ts.id')
            ->orderBy('ts.start_date', 'ASC');

        if ($this->academySchemaReady()) {
            $query->select('ts.*', 'ts.id AS session_id', 'c.title', 'c.category', 'c.description', 'c.thumbnail_image', 'c.academy_id', 'c.status AS course_status', 'users.name AS instructor_name', 'academies.code AS academy_code', 'academies.name AS academy_name', 'COUNT(enrolments.id) AS participant_count')
                  ->leftJoin('academies', 'academies.id', '=', 'c.academy_id');
        } else {
            $query->select('ts.*', 'ts.id AS session_id', 'c.title', 'c.category', 'c.description', 'c.thumbnail_image', 'c.academy_id', 'c.status AS course_status', 'users.name AS instructor_name', 'NULL AS academy_code', 'NULL AS academy_name', 'COUNT(enrolments.id) AS participant_count');
        }

        return $query->get();
    }

    public function publicByAcademy(string $academyCode, string $search = ''): array
    {
        if (!$this->academySchemaReady()) {
            return [];
        }

        $like = '%' . $search . '%';
        return $this->table('training_sessions ts')
            ->select('ts.*', 'ts.id AS session_id', 'c.title', 'c.category', 'c.description', 'c.thumbnail_image', 'c.academy_id', 'c.status AS course_status', 'users.name AS instructor_name', 'academies.code AS academy_code', 'academies.name AS academy_name', 'COUNT(enrolments.id) AS participant_count')
            ->leftJoin('courses c', 'c.id', '=', 'ts.course_id')
            ->leftJoin('users', 'users.id', '=', 'ts.instructor_id')
            ->leftJoin('academies', 'academies.id', '=', 'c.academy_id')
            ->leftJoin('enrolments', 'enrolments.training_session_id', '=', 'ts.id AND enrolments.status IN ("active","completed")')
            ->whereIn('c.status', ['published', 'active'])
            ->where('academies.code', $academyCode)
            ->whereRaw('(c.title LIKE ? OR c.category LIKE ?)', [$like, $like])
            ->groupBy('ts.id')
            ->orderBy('c.category', 'ASC, c.title')
            ->get();
    }

    public function publicAcademies(): array
    {
        if (!$this->academySchemaReady()) {
            return [];
        }

        return $this->table('academies a')
            ->select(
                'a.*',
                '(SELECT COUNT(id) FROM courses c WHERE c.academy_id = a.id AND c.status IN ("published","active")) AS course_count',
                '(SELECT COALESCE(SUM(participants), 0) FROM training_statistics stat WHERE stat.academy_id = a.id) AS participant_count'
            )
            ->whereIn('a.code', ['ADGEA', 'IESGA'])
            ->orderBy('FIELD(a.code, "ADGEA", "IESGA")', '')
            ->get();
    }

    public function academyByCode(string $academyCode): ?array
    {
        if (!$this->academySchemaReady()) {
            return null;
        }

        return $this->table('academies')->where('code', $academyCode)->first();
    }

    public function all(string $search = '', string $status = '', string $category = ''): array
    {
        $like = '%' . $search . '%';
        $query = $this->table('training_sessions ts')
            ->leftJoin('courses c', 'c.id', '=', 'ts.course_id')
            ->leftJoin('users', 'users.id', '=', 'ts.instructor_id')
            ->leftJoin('enrolments', 'enrolments.training_session_id', '=', 'ts.id AND enrolments.status IN ("active","completed")')
            ->whereRaw('(c.title LIKE ? OR c.category LIKE ?)', [$like, $like])
            ->groupBy('ts.id')
            ->orderBy('ts.created_at', 'DESC');

        if ($this->academySchemaReady()) {
            $query->select('ts.*', 'ts.id AS session_id', 'c.title', 'c.category', 'c.description', 'c.thumbnail_image', 'c.academy_id', 'c.status AS course_status', 'users.name AS instructor_name', 'academies.code AS academy_code', 'academies.name AS academy_name', 'COUNT(enrolments.id) AS participant_count')
                  ->leftJoin('academies', 'academies.id', '=', 'c.academy_id');
        } else {
            $query->select('ts.*', 'ts.id AS session_id', 'c.title', 'c.category', 'c.description', 'c.thumbnail_image', 'c.academy_id', 'c.status AS course_status', 'users.name AS instructor_name', 'NULL AS academy_code', 'NULL AS academy_name', 'COUNT(enrolments.id) AS participant_count');
        }

        if ($status !== '') {
            $query->where('ts.status', $status);
        }
        if ($category !== '') {
            $query->where('c.category', $category);
        }

        return $query->get();
    }

    public function assignedTo(int $instructorId): array
    {
        $query = $this->table('training_sessions ts')
            ->leftJoin('courses c', 'c.id', '=', 'ts.course_id')
            ->leftJoin('enrolments', 'enrolments.training_session_id', '=', 'ts.id AND enrolments.status IN ("active","completed")')
            ->where('ts.instructor_id', $instructorId)
            ->groupBy('ts.id')
            ->orderBy('ts.start_date', 'DESC');

        if ($this->academySchemaReady()) {
            $query->select('ts.*', 'ts.id AS session_id', 'c.title', 'c.category', 'c.description', 'c.thumbnail_image', 'c.academy_id', 'c.status AS course_status', 'academies.code AS academy_code', 'academies.name AS academy_name', 'COUNT(enrolments.id) AS participant_count')
                  ->leftJoin('academies', 'academies.id', '=', 'c.academy_id');
        } else {
            $query->select('ts.*', 'ts.id AS session_id', 'c.title', 'c.category', 'c.description', 'c.thumbnail_image', 'c.academy_id', 'c.status AS course_status', 'NULL AS academy_code', 'NULL AS academy_name', 'COUNT(enrolments.id) AS participant_count');
        }

        return $query->get();
    }

    public function find(int $id): ?array
    {
        $query = $this->table('training_sessions ts')->where('ts.id', $id)->leftJoin('courses c', 'c.id', '=', 'ts.course_id')->leftJoin('users', 'users.id', '=', 'ts.instructor_id');
        if ($this->academySchemaReady()) {
            $query->select('ts.*', 'ts.id AS session_id', 'c.title', 'c.category', 'c.description', 'c.thumbnail_image', 'c.academy_id', 'c.status AS course_status', 'users.name AS instructor_name', 'academies.code AS academy_code', 'academies.name AS academy_name')
                  ->leftJoin('academies', 'academies.id', '=', 'c.academy_id');
        } else {
            $query->select('ts.*', 'ts.id AS session_id', 'c.title', 'c.category', 'c.description', 'c.thumbnail_image', 'c.academy_id', 'c.status AS course_status', 'users.name AS instructor_name', 'NULL AS academy_code', 'NULL AS academy_name');
        }
        return $query->first();
    }

    public function save(array $data): int
    {
        $supportsAcademySchema = $this->academySchemaReady();

        // Check if course exists, or create it
        // Actually, if we are saving from the old UI, it expects to create both course and session in one go if it's new.
        $courseData = [
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'],
            'status' => $data['status'] ?? 'active',
            'thumbnail_image' => $data['thumbnail_image'],
        ];
        
        if ($supportsAcademySchema) {
            $courseData['academy_id'] = $data['academy_id'] ?? null;
        }

        // We assume we always update the session and optionally the course.
        // Wait, the id passed from UI is the session_id!
        if (!empty($data['id'])) {
            $session = $this->table('training_sessions')->where('id', $data['id'])->first();
            if ($session) {
                $courseData['updated_at'] = date('Y-m-d H:i:s');
                $this->table('courses')->where('id', $session['course_id'])->update($courseData);

                $sessionData = [
                    'instructor_id' => $data['instructor_id'] ?: null,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'capacity' => $data['capacity'],
                    'max_participants' => $data['max_participants'],
                    'fee' => $data['fee'],
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                if (isset($data['course_status'])) {
                    $sessionData['status'] = $data['course_status'];
                }

                $this->table('training_sessions')->where('id', $data['id'])->update($sessionData);
                return (int) $data['id'];
            }
        }

        // Create new session, reusing course if it exists
        $existing = $this->table('courses')->where('title', $data['title'])->where('category', $data['category'])->first();
        if ($existing) {
            $courseId = (int) $existing['id'];
        } else {
            $courseData['created_by'] = $data['created_by'];
            $this->table('courses')->insert($courseData);
            $courseId = (int) $this->db->lastInsertId();
        }

        $sessionData = [
            'course_id' => $courseId,
            'instructor_id' => $data['instructor_id'] ?: null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'capacity' => $data['capacity'],
            'max_participants' => $data['max_participants'],
            'fee' => $data['fee'],
            'status' => $data['course_status'] ?? 'scheduled'
        ];
        
        $this->table('training_sessions')->insert($sessionData);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        // id is session_id
        $session = $this->table('training_sessions')->where('id', $id)->first();
        if ($session) {
            $this->table('training_sessions')->where('id', $id)->delete();
            // Optional: delete course if no more sessions
            $count = $this->table('training_sessions')->where('course_id', $session['course_id'])->count();
            if ($count === 0) {
                $this->table('courses')->where('id', $session['course_id'])->delete();
            }
        }
    }

    public function categories(): array
    {
        return $this->db->query('SELECT DISTINCT category FROM courses WHERE category <> "" ORDER BY category')->fetchAll();
    }

    public function instructors(): array
    {
        return $this->db->query('SELECT users.id, users.name FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "instructor" AND users.status = "active" ORDER BY users.name')->fetchAll();
    }

    /** Sessions with no instructor that an instructor can claim */
    public function availableToClaim(): array
    {
        return $this->table('training_sessions ts')
            ->select('ts.id AS session_id', 'ts.start_date', 'ts.end_date', 'ts.status AS session_status', 'c.title', 'c.category', 'c.status AS course_status')
            ->join('courses c', 'c.id', '=', 'ts.course_id')
            ->whereNull('ts.instructor_id')
            ->whereIn('ts.status', ['scheduled', 'active'])
            ->whereIn('c.status', ['published', 'active'])
            ->orderBy('c.title', 'ASC')
            ->get();
    }

    /** Claim a training session as instructor (only if currently unassigned) */
    public function claimSession(int $sessionId, int $instructorId): bool
    {
        $updated = $this->table('training_sessions')
            ->where('id', $sessionId)
            ->whereNull('instructor_id')
            ->update([
                'instructor_id' => $instructorId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        return $updated > 0;
    }

    /** Release a session the instructor currently teaches */
    public function unassignSession(int $sessionId, int $instructorId): bool
    {
        $updated = $this->table('training_sessions')
            ->where('id', $sessionId)
            ->where('instructor_id', $instructorId)
            ->update([
                'instructor_id' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        return $updated > 0;
    }
}
