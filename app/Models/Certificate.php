<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Certificate extends Model
{
    public function templates(): array
    {
        return $this->table('certificate_templates')->orderBy('created_at', 'DESC')->get();
    }

    public function activeTemplates(): array
    {
        return $this->table('certificate_templates')->where('status', 'active')->orderBy('template_name')->get();
    }

    public function template(int $id): ?array
    {
        return $this->table('certificate_templates')->where('template_id', $id)->first();
    }

    public function saveTemplate(array $data): int
    {
        if (!empty($data['template_id'])) {
            $this->table('certificate_templates')->where('template_id', $data['template_id'])->update([
                'template_name' => $data['template_name'],
                'background_image' => $data['background_image'],
                'logo' => $data['logo'],
                'signature' => $data['signature'],
                'font_family' => $data['font_family'],
                'font_size' => $data['font_size'],
                'text_color' => $data['text_color'],
                'layout_json' => $data['layout_json'],
                'status' => $data['status'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return (int) $data['template_id'];
        }

        return $this->table('certificate_templates')->insert([
            'template_name' => $data['template_name'],
            'background_image' => $data['background_image'],
            'logo' => $data['logo'],
            'signature' => $data['signature'],
            'font_family' => $data['font_family'],
            'font_size' => $data['font_size'],
            'text_color' => $data['text_color'],
            'layout_json' => $data['layout_json'],
            'status' => $data['status'],
        ]);
    }

    public function deleteTemplate(int $id): void
    {
        $this->table('certificate_templates')->where('template_id', $id)->delete();
    }

    public function records(string $search = ''): array
    {
        $like = '%' . $search . '%';
        return $this->table('certificates cert')
            ->select('cert.*', 'COALESCE(cert.certificate_number, cert.certificate_no) AS display_number', 'u.name AS trainee_name', 'c.title AS course_title', 'i.name AS instructor_name', 't.template_name')
            ->join('users u', 'u.id', '=', 'cert.trainee_id')
            ->join('training_sessions ts', 'ts.id', '=', 'cert.course_id')->join('courses c', 'c.id', '=', 'ts.course_id')
            ->leftJoin('users i', 'i.id', '=', 'ts.instructor_id')
            ->leftJoin('certificate_templates t', 't.template_id', '=', 'cert.template_id')
            ->whereRaw('(u.name LIKE ? OR c.title LIKE ? OR cert.certificate_no LIKE ? OR cert.certificate_number LIKE ?)', [$like, $like, $like, $like])
            ->orderBy('COALESCE(cert.issue_date, cert.issued_at)', 'DESC')
            ->get();
    }

    public function forTrainee(int $traineeId, string $search = ''): array
    {
        $like = '%' . $search . '%';
        return $this->table('certificates cert')
            ->select('cert.*', 'COALESCE(cert.certificate_number, cert.certificate_no) AS display_number', 'c.title AS course_title', 'i.name AS instructor_name', 't.template_name', 't.logo', 't.signature', 't.font_family', 't.font_size', 't.text_color', 't.layout_json')
            ->join('training_sessions ts', 'ts.id', '=', 'cert.course_id')->join('courses c', 'c.id', '=', 'ts.course_id')
            ->leftJoin('enrolments e', 'e.training_session_id', '=', 'cert.course_id AND e.trainee_id = cert.trainee_id')
            ->leftJoin('users i', 'i.id', '=', 'ts.instructor_id')
            ->leftJoin('certificate_templates t', 't.template_id', '=', 'cert.template_id')
            ->where('cert.trainee_id', $traineeId)
            ->where('cert.approval_status', 'approved')
            ->whereRaw('(c.title LIKE ? OR cert.certificate_no LIKE ? OR cert.certificate_number LIKE ?)', [$like, $like, $like])
            ->orderBy('COALESCE(cert.issue_date, cert.issued_at)', 'DESC')
            ->get();
    }

    public function issue(array $data): int
    {
        $number = $data['certificate_number'] ?: 'ITOP-' . date('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        $verification = $data['verification_code'] ?: 'CENTEXS-' . bin2hex(random_bytes(5));
        $stmt = $this->db->prepare('INSERT INTO certificates (course_id, trainee_id, template_id, certificate_no, certificate_number, verification_code, file_path, pdf_path, issued_at, issue_date, issued_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE template_id=VALUES(template_id), file_path=VALUES(file_path), pdf_path=VALUES(pdf_path), issue_date=VALUES(issue_date), issued_by=VALUES(issued_by), status=VALUES(status)');
        $stmt->execute([
            $data['course_id'],
            $data['trainee_id'],
            $data['template_id'] ?: null,
            $number,
            $number,
            $verification,
            $data['pdf_path'],
            $data['pdf_path'],
            $data['issue_date'],
            $data['issue_date'],
            $data['issued_by'],
            $data['status'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function setPdfPath(int $id, string $path): void
    {
        $this->table('certificates')->where('id', $id)->update(['pdf_path' => $path]);
    }

    public function approve(int $id, int $reviewerId, string $status, string $remarks = ''): void
    {
        $approvedAt = $status === 'approved' ? 'NOW()' : 'NULL';
        $stmt = $this->db->prepare('UPDATE certificates SET approval_status = ?, approved_by = ?, approved_at = ' . $approvedAt . ', rejection_reason = ? WHERE id = ?');
        $stmt->execute([$status, $reviewerId, $status === 'rejected' ? $remarks : null, $id]);

        $this->table('certificate_approvals')->insert([
            'certificate_id' => $id,
            'reviewer_id' => $reviewerId,
            'status' => $status,
            'remarks' => $remarks
        ]);
    }

    public function downloadLog(int $id, int $userId): void
    {
        $this->table('certificate_download_logs')->insert([
            'certificate_id' => $id,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }

    public function getDownloadLogs(int $id): array
    {
        return $this->table('certificate_download_logs l')
            ->select('l.*', 'u.name AS user_name', 'u.email AS user_email')
            ->join('users u', 'u.id', '=', 'l.user_id')
            ->where('l.certificate_id', $id)
            ->orderBy('l.downloaded_at', 'DESC')
            ->get();
    }

    public function revoke(int $id): void
    {
        $this->table('certificates')->where('id', $id)->update(['status' => 'revoked']);
    }
}
