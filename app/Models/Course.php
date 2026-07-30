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
        $query = $this->table('courses')
            ->leftJoin('users', 'users.id', '=', 'courses.instructor_id')
            ->leftJoin('enrolments', 'enrolments.course_id', '=', 'courses.id AND enrolments.status IN ("active","completed")')
            ->whereIn('courses.status', ['published', 'active'])
            ->whereRaw('(courses.title LIKE ? OR courses.category LIKE ?)', [$like, $like])
            ->groupBy('courses.id')
            ->orderBy('courses.start_date', 'ASC');

        if ($this->academySchemaReady()) {
            $query->select('courses.*', 'users.name AS instructor_name', 'academies.code AS academy_code', 'academies.name AS academy_name', 'COUNT(enrolments.id) AS participant_count')
                  ->leftJoin('academies', 'academies.id', '=', 'courses.academy_id');
        } else {
            $query->select('courses.*', 'users.name AS instructor_name', 'NULL AS academy_code', 'NULL AS academy_name', 'COUNT(enrolments.id) AS participant_count');
        }

        return $query->get();
    }

    public function publicByAcademy(string $academyCode, string $search = ''): array
    {
        if (!$this->academySchemaReady()) {
            return [];
        }

        $like = '%' . $search . '%';
        return $this->table('courses')
            ->select('courses.*', 'users.name AS instructor_name', 'academies.code AS academy_code', 'academies.name AS academy_name', 'COUNT(enrolments.id) AS participant_count')
            ->leftJoin('users', 'users.id', '=', 'courses.instructor_id')
            ->leftJoin('academies', 'academies.id', '=', 'courses.academy_id')
            ->leftJoin('enrolments', 'enrolments.course_id', '=', 'courses.id AND enrolments.status IN ("active","completed")')
            ->whereIn('courses.status', ['published', 'active'])
            ->where('academies.code', $academyCode)
            ->whereRaw('(courses.title LIKE ? OR courses.category LIKE ?)', [$like, $like])
            ->groupBy('courses.id')
            ->orderBy('courses.category', 'ASC, courses.title')
            ->get();
    }

    public function publicAcademies(): array
    {
        if (!$this->academySchemaReady()) {
            return [];
        }

        return $this->table('academies a')
            ->select('a.*', 'COUNT(c.id) AS course_count', 'COALESCE(SUM(ts.participants), 0) AS participant_count')
            ->leftJoin('courses c', 'c.academy_id', '=', 'a.id AND c.status IN ("published","active")')
            ->leftJoin('training_statistics ts', 'ts.academy_id', '=', 'a.id')
            ->whereIn('a.code', ['ADGEA', 'IESGA'])
            ->groupBy('a.id')
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
        $query = $this->table('courses')
            ->leftJoin('users', 'users.id', '=', 'courses.instructor_id')
            ->leftJoin('enrolments', 'enrolments.course_id', '=', 'courses.id AND enrolments.status IN ("active","completed")')
            ->whereRaw('(courses.title LIKE ? OR courses.category LIKE ?)', [$like, $like])
            ->groupBy('courses.id')
            ->orderBy('courses.created_at', 'DESC');

        if ($this->academySchemaReady()) {
            $query->select('courses.*', 'users.name AS instructor_name', 'academies.code AS academy_code', 'academies.name AS academy_name', 'COUNT(enrolments.id) AS participant_count')
                  ->leftJoin('academies', 'academies.id', '=', 'courses.academy_id');
        } else {
            $query->select('courses.*', 'users.name AS instructor_name', 'NULL AS academy_code', 'NULL AS academy_name', 'COUNT(enrolments.id) AS participant_count');
        }

        if ($status !== '') {
            $query->where('courses.status', $status);
        }
        if ($category !== '') {
            $query->where('courses.category', $category);
        }

        return $query->get();
    }

    public function assignedTo(int $instructorId): array
    {
        $query = $this->table('courses')
            ->leftJoin('enrolments', 'enrolments.course_id', '=', 'courses.id AND enrolments.status IN ("active","completed")')
            ->where('courses.instructor_id', $instructorId)
            ->groupBy('courses.id')
            ->orderBy('courses.start_date', 'DESC');

        if ($this->academySchemaReady()) {
            $query->select('courses.*', 'academies.code AS academy_code', 'academies.name AS academy_name', 'COUNT(enrolments.id) AS participant_count')
                  ->leftJoin('academies', 'academies.id', '=', 'courses.academy_id');
        } else {
            $query->select('courses.*', 'NULL AS academy_code', 'NULL AS academy_name', 'COUNT(enrolments.id) AS participant_count');
        }

        return $query->get();
    }

    public function find(int $id): ?array
    {
        $query = $this->table('courses')->where('courses.id', $id)->leftJoin('users', 'users.id', '=', 'courses.instructor_id');
        if ($this->academySchemaReady()) {
            $query->select('courses.*', 'users.name AS instructor_name', 'academies.code AS academy_code', 'academies.name AS academy_name')
                  ->leftJoin('academies', 'academies.id', '=', 'courses.academy_id');
        } else {
            $query->select('courses.*', 'users.name AS instructor_name', 'NULL AS academy_code', 'NULL AS academy_name');
        }
        return $query->first();
    }

    public function save(array $data): int
    {
        $supportsAcademySchema = $this->academySchemaReady();

        $saveData = [
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'capacity' => $data['capacity'],
            'max_participants' => $data['max_participants'],
            'status' => $data['status'],
            'course_status' => $data['course_status'],
            'thumbnail_image' => $data['thumbnail_image'],
            'instructor_id' => $data['instructor_id'] ?: null,
            'fee' => $data['fee']
        ];
        
        if ($supportsAcademySchema) {
            $saveData['academy_id'] = $data['academy_id'] ?? null;
        }

        if (!empty($data['id'])) {
            $saveData['updated_at'] = date('Y-m-d H:i:s');
            // 'created_by=COALESCE(created_by, ?)' logic is hard to replicate exactly with just standard array keys
            // But we can just avoid updating created_by if it's an update, which is usually correct anyway.
            $this->table('courses')->where('id', $data['id'])->update($saveData);
            return (int) $data['id'];
        }

        $saveData['created_by'] = $data['created_by'];
        $this->table('courses')->insert($saveData);
        return (int) $this->db->lastInsertId(); // insert doesn't return ID yet, so fallback to raw PDO
    }

    public function delete(int $id): void
    {
        $this->table('courses')->where('id', $id)->delete();
    }

    public function categories(): array
    {
        return $this->db->query('SELECT DISTINCT category FROM courses WHERE category <> "" ORDER BY category')->fetchAll();
    }

    public function instructors(): array
    {
        return $this->db->query('SELECT users.id, users.name FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "instructor" AND users.status = "active" ORDER BY users.name')->fetchAll();
    }
}
