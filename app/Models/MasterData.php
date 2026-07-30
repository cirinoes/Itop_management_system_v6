<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class MasterData extends Model
{
    private const TABLES = [
        'academies' => ['id' => 'id', 'label' => 'name', 'fields' => ['code', 'name', 'description', 'status']],
        'training_categories' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'description', 'status']],
        'companies' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'location_id', 'status']],
        'institutions' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'location_id', 'status']],
        'locations' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'status']],
        'professions' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'status']],
    ];

    public function tables(): array
    {
        return self::TABLES;
    }

    public function list(string $table): array
    {
        $this->assertTable($table);
        return $this->table($table)->orderBy(self::TABLES[$table]['label'])->get();
    }

    public function find(string $table, int $id): ?array
    {
        $this->assertTable($table);
        return $this->table($table)->where(self::TABLES[$table]['id'], $id)->first();
    }

    public function save(string $table, array $data): int
    {
        $this->assertTable($table);
        $pk = self::TABLES[$table]['id'];
        $fields = self::TABLES[$table]['fields'];
        $values = [];
        foreach ($fields as $field) {
            $values[$field] = $data[$field] ?? null;
        }

        if (!empty($data[$pk])) {
            $values['updated_at'] = date('Y-m-d H:i:s');
            $this->table($table)->where($pk, $data[$pk])->update($values);
            return (int) $data[$pk];
        }

        $this->table($table)->insert($values);
        return (int) $this->db->lastInsertId();
    }

    public function delete(string $table, int $id): void
    {
        $this->assertTable($table);
        $this->table($table)->where(self::TABLES[$table]['id'], $id)->delete();
    }

    public function statistics(): array
    {
        return [
            'training' => $this->table('training_statistics ts')
                ->select('ts.*', 'a.code AS academy_code', 'a.name AS academy_name', 'c.title AS course_title')
                ->join('academies a', 'a.id', '=', 'ts.academy_id')
                ->leftJoin('courses c', 'c.id', '=', 'ts.course_id')
                ->orderBy('a.code', 'ASC, ts.participants DESC')
                ->get(),
            'participant' => $this->table('participant_statistics ps')
                ->select('ps.*', 'a.code AS academy_code', 'tc.name AS category_name', 'c.title AS course_title', 'co.name AS company_name', 'p.name AS profession_name')
                ->leftJoin('academies a', 'a.id', '=', 'ps.academy_id')
                ->leftJoin('training_categories tc', 'tc.id', '=', 'ps.category_id')
                ->leftJoin('courses c', 'c.id', '=', 'ps.course_id')
                ->leftJoin('companies co', 'co.id', '=', 'ps.company_id')
                ->leftJoin('professions p', 'p.id', '=', 'ps.profession_id')
                ->orderBy('ps.report_year', 'ASC, ps.participant_count DESC')
                ->get(),
            'yearly' => $this->table('yearly_reports')->orderBy('report_year')->get(),
            'summary' => $this->table('dashboard_summary')->orderBy('metric_label')->get(),
        ];
    }

    public function saveTrainingStatistic(array $data): int
    {
        if (!empty($data['id'])) {
            $this->table('training_statistics')->where('id', $data['id'])->update([
                'academy_id' => $data['academy_id'],
                'course_id' => $data['course_id'] ?: null,
                'course_name' => $data['course_name'],
                'participants' => $data['participants'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return (int) $data['id'];
        }
        
        $this->table('training_statistics')->insert([
            'academy_id' => $data['academy_id'],
            'course_id' => $data['course_id'] ?: null,
            'course_name' => $data['course_name'],
            'participants' => $data['participants'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteTrainingStatistic(int $id): void
    {
        $this->table('training_statistics')->where('id', $id)->delete();
    }

    private function assertTable(string $table): void
    {
        if (!isset(self::TABLES[$table])) {
            throw new \InvalidArgumentException('Unsupported master data table.');
        }
    }
}
