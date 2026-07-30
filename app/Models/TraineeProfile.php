<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class TraineeProfile extends Model
{
    public function findByUser(int $userId): ?array
    {
        return $this->table('trainee_profiles')->where('user_id', $userId)->first();
    }

    public function save(int $userId, array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO trainee_profiles (user_id, identity_number, phone, address, education, employment, emergency_contact, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE identity_number=VALUES(identity_number), phone=VALUES(phone), address=VALUES(address), education=VALUES(education), employment=VALUES(employment), emergency_contact=VALUES(emergency_contact), profile_picture=COALESCE(VALUES(profile_picture), profile_picture), updated_at=NOW()');
        $stmt->execute([$userId, $data['identity_number'], $data['phone'], $data['address'], $data['education'], $data['employment'], $data['emergency_contact'], $data['profile_picture']]);

        $userStmt = $this->db->prepare('UPDATE users SET phone = ?, address = ?, profile_picture = COALESCE(?, profile_picture), updated_at = NOW() WHERE id = ?');
        $userStmt->execute([$data['phone'], $data['address'], $data['profile_picture'], $userId]);
    }

    public function documents(int $userId): array
    {
        return $this->table('trainee_documents')->where('user_id', $userId)->orderBy('uploaded_at', 'DESC')->get();
    }

    public function addDocument(int $userId, string $type, string $fileName, string $filePath): void
    {
        $this->table('trainee_documents')->insert([
            'user_id' => $userId,
            'document_type' => $type,
            'file_name' => $fileName,
            'file_path' => $filePath
        ]);
    }

    public function adminList(string $search = ''): array
    {
        $like = '%' . $search . '%';
        return $this->table('users u')
            ->select('u.id', 'u.name', 'u.email', 'u.phone', 'u.status', 'p.identity_number', 'p.education', 'p.employment', 'p.emergency_contact', 'p.updated_at', 'COUNT(d.document_id) AS document_count')
            ->join('roles r', 'r.id', '=', 'u.role_id AND r.slug = "trainee"')
            ->leftJoin('trainee_profiles p', 'p.user_id', '=', 'u.id')
            ->leftJoin('trainee_documents d', 'd.user_id', '=', 'u.id')
            ->whereRaw('u.name LIKE ? OR u.email LIKE ? OR p.identity_number LIKE ? OR p.education LIKE ?', [$like, $like, $like, $like])
            ->groupBy('u.id')
            ->orderBy('u.name', 'ASC')
            ->get();
    }

    public function adminDetail(int $userId): ?array
    {
        return $this->table('users u')
            ->select('u.*', 'p.*')
            ->leftJoin('trainee_profiles p', 'p.user_id', '=', 'u.id')
            ->where('u.id', $userId)
            ->first();
    }
}
