<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;
use App\Core\Controller;
use App\Models\Certificate as CertificateModel;

final class ReportController extends Controller
{
    public function reports(): void
    {
        Auth::requireRole(['admin']);
        $db = Database::connection();
        $academy = (int) ($_GET['academy_id'] ?? 0);
        $course = (int) ($_GET['course_id'] ?? 0);
        $where = [];
        if ($academy) {
            $where[] = 'c.academy_id = ' . $academy;
        }
        if ($course) {
            $where[] = 'c.id = ' . $course;
        }
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $this->render('reports/index', [
            'summary' => [
                'total_trainees' => (int) $db->query('SELECT COUNT(*) FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "trainee"')->fetchColumn(),
                'active_trainees' => (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE status = "active"')->fetchColumn(),
                'completed_trainees' => (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE status = "completed"')->fetchColumn(),
                'certificates_issued' => (int) $db->query('SELECT COUNT(*) FROM certificates WHERE approval_status = "approved"')->fetchColumn(),
            ],
            'academies' => $db->query('SELECT id, code, name FROM academies ORDER BY code')->fetchAll(),
            'courses' => $db->query('SELECT id, title FROM courses ORDER BY title')->fetchAll(),
            'completion' => $db->query('SELECT a.name AS academy_name, c.title, COUNT(e.id) AS total, SUM(e.status = "completed") AS completed, ROUND((SUM(e.status = "completed") / GREATEST(COUNT(e.id), 1)) * 100, 0) AS rate FROM courses c JOIN academies a ON a.id = c.academy_id LEFT JOIN training_sessions ts_e ON ts_e.course_id = c.id LEFT JOIN enrolments e ON e.training_session_id = ts_e.id' . $whereSql . ' GROUP BY c.id HAVING total > 0 ORDER BY a.name, c.title')->fetchAll(),
            'attendance' => $db->query('SELECT a.name AS academy_name, c.title, ROUND(AVG(att.status = "present") * 100, 0) AS attendance_rate, COUNT(att.id) AS records FROM courses c JOIN academies a ON a.id = c.academy_id LEFT JOIN training_sessions ts_e ON ts_e.course_id = c.id LEFT JOIN enrolments e ON e.training_session_id = ts_e.id LEFT JOIN attendance att ON att.enrolment_id = e.id' . $whereSql . ' GROUP BY c.id HAVING records > 0 ORDER BY a.name, c.title')->fetchAll(),
            'assignments' => $db->query('SELECT a.name AS academy_name, c.title, COUNT(assign.id) AS assignments, COUNT(s.id) AS submissions, SUM(s.status = "graded") AS graded FROM courses c JOIN academies a ON a.id = c.academy_id LEFT JOIN training_sessions ts_a ON ts_a.course_id = c.id LEFT JOIN assignments assign ON assign.course_id = ts_a.id LEFT JOIN assignment_submissions s ON s.assignment_id = assign.id' . $whereSql . ' GROUP BY c.id HAVING assignments > 0 ORDER BY a.name, c.title')->fetchAll(),
            'evaluations' => $db->query('SELECT a.name AS academy_name, c.title, ROUND(AVG(eval.rating), 2) AS avg_rating, COUNT(eval.id) AS responses FROM courses c JOIN academies a ON a.id = c.academy_id LEFT JOIN evaluations eval ON eval.course_id = c.id' . $whereSql . ' GROUP BY c.id HAVING responses > 0 ORDER BY a.name, c.title')->fetchAll(),
            'certificates' => $db->query('SELECT c.title, COUNT(cert.id) AS total, SUM(cert.approval_status = "approved") AS approved, SUM(cert.approval_status = "pending") AS pending FROM courses c LEFT JOIN training_sessions ts_cert ON ts_cert.course_id = c.id LEFT JOIN certificates cert ON cert.course_id = ts_cert.id' . $whereSql . ' GROUP BY c.id HAVING total > 0 ORDER BY c.title')->fetchAll(),
            'master_data_stats' => $db->query('SELECT ts.*, a.code AS academy_code, a.name AS academy_name, c.title AS course_title FROM training_statistics ts JOIN academies a ON a.id = ts.academy_id LEFT JOIN courses c ON c.id = ts.course_id ORDER BY a.code, ts.participants DESC')->fetchAll(),
            'detailed_log' => $db->query('SELECT u.name AS trainee_name, u.email, COALESCE(u.institution_company, "Unknown") AS background, c.title AS course_title, e.status, e.progress_percent, e.created_at AS enrolled_date FROM enrolments e JOIN users u ON u.id = e.trainee_id JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id ORDER BY e.created_at DESC')->fetchAll(),
            'revenue' => $db->query('SELECT c.title, COUNT(e.id) AS total_enrolments, ts.fee, (COUNT(e.id) * ts.fee) AS total_revenue FROM courses c JOIN training_sessions ts ON ts.course_id = c.id LEFT JOIN enrolments e ON e.training_session_id = ts.id GROUP BY ts.id HAVING total_enrolments > 0 ORDER BY total_revenue DESC')->fetchAll(),
            'instructor_performance' => $db->query('SELECT u.name AS instructor_name, COUNT(DISTINCT ts.id) AS assigned_courses, COUNT(e.id) AS total_students FROM users u JOIN training_sessions ts ON ts.instructor_id = u.id LEFT JOIN enrolments e ON e.training_session_id = ts.id WHERE u.role_id = (SELECT id FROM roles WHERE slug = "instructor") GROUP BY u.id HAVING total_students > 0 ORDER BY total_students DESC')->fetchAll(),
            'demographics' => $db->query('SELECT DATE_FORMAT(e.created_at, "%Y-%m") AS enrolment_month, c.title AS course_title, COALESCE(NULLIF(u.institution_company, ""), "Independent/Student") AS background, COUNT(e.id) AS total_enrolments FROM enrolments e JOIN users u ON u.id = e.trainee_id JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id GROUP BY enrolment_month, c.id, background ORDER BY enrolment_month DESC, total_enrolments DESC')->fetchAll(),
            'academyId' => $academy,
            'courseId' => $course,
        ]);
    }

    public function instructorReports(): void
    {
        Auth::requireRole(['instructor']);
        $db = Database::connection();
        $instructorId = Auth::id();

        $stmtOverview = $db->prepare('
            SELECT 
                COUNT(ts.id) AS total_courses,
                COUNT(DISTINCT e.trainee_id) AS total_students
            FROM training_sessions ts
            JOIN courses c ON c.id = ts.course_id
            LEFT JOIN enrolments e ON e.training_session_id = ts.id
            WHERE ts.instructor_id = ?
        ');
        $stmtOverview->execute([$instructorId]);
        $overview = $stmtOverview->fetch();
        
        $stmtBacklog = $db->prepare('
            SELECT a.title, c.title AS course_title, COUNT(s.id) AS pending_count
            FROM assignments a
            JOIN training_sessions ts ON ts.id = a.course_id
            JOIN courses c ON c.id = ts.course_id
            JOIN assignment_submissions s ON s.assignment_id = a.id
            WHERE ts.instructor_id = ? AND s.status = "pending"
            GROUP BY a.id
            ORDER BY pending_count DESC
        ');
        $stmtBacklog->execute([$instructorId]);
        $gradingBacklog = $stmtBacklog->fetchAll();

        $stmtPerformance = $db->prepare('
            SELECT 
                c.title,
                COUNT(e.id) AS enrollments,
                ROUND(AVG(att.status = "present") * 100, 0) AS attendance_rate,
                ROUND(AVG(sub.score), 1) AS avg_assignment_score,
                ROUND((SUM(e.status = "completed") / GREATEST(COUNT(e.id), 1)) * 100, 0) AS completion_rate
            FROM training_sessions ts
            JOIN courses c ON c.id = ts.course_id
            LEFT JOIN enrolments e ON e.training_session_id = ts.id
            LEFT JOIN attendance att ON att.enrolment_id = e.id
            LEFT JOIN assignments a ON a.course_id = ts.id
            LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id AND sub.status = "graded"
            WHERE ts.instructor_id = ?
            GROUP BY ts.id
            ORDER BY c.title
        ');
        $stmtPerformance->execute([$instructorId]);
        $coursePerformance = $stmtPerformance->fetchAll();

        $this->render('reports/instructor', [
            'overview' => $overview,
            'grading_backlog' => $gradingBacklog,
            'course_performance' => $coursePerformance,
        ]);
    }

    public function traineeReport(): void
    {
        Auth::requireRole(['trainee']);
        $db = Database::connection();
        $traineeId = Auth::id();

        $stmtEnrolled = $db->prepare('SELECT COUNT(*) FROM enrolments WHERE trainee_id = ?');
        $stmtEnrolled->execute([$traineeId]);

        $stmtCompleted = $db->prepare('SELECT COUNT(*) FROM enrolments WHERE trainee_id = ? AND status = "completed"');
        $stmtCompleted->execute([$traineeId]);
        
        $stmtHours = $db->prepare('
            SELECT SUM((DATEDIFF(IFNULL(ts_e2.end_date, ts_e2.start_date), ts_e2.start_date) + 1) * 8) 
            FROM courses c 
            JOIN training_sessions ts_e2 ON ts_e2.course_id = c.id JOIN enrolments e ON e.training_session_id = ts_e2.id 
            WHERE e.trainee_id = ? AND e.status = "completed"
        ');
        $stmtHours->execute([$traineeId]);

        $stmtCert = $db->prepare('
            SELECT COUNT(*) 
            FROM certificates 
            WHERE trainee_id = ? AND approval_status = "approved"
        ');
        $stmtCert->execute([$traineeId]);

        $stmtProgress = $db->prepare('
            SELECT 
                c.title, 
                e.progress_percent, 
                e.status,
                (SELECT ROUND(AVG(score), 0) FROM quiz_results qr JOIN quizzes q ON q.id = qr.quiz_id WHERE q.course_id = c.id AND qr.trainee_id = e.trainee_id) as quiz_avg_score
            FROM enrolments e
            JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id
            WHERE e.trainee_id = ?
            ORDER BY e.created_at DESC
        ');
        $stmtProgress->execute([$traineeId]);

        $stmtCertList = $db->prepare('
            SELECT c.title AS course_title, cert.issued_at, cert.certificate_no, cert.id
            FROM certificates cert
            JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id
            WHERE cert.trainee_id = ? AND cert.approval_status = "approved"
            ORDER BY cert.issued_at DESC
        ');
        $stmtCertList->execute([$traineeId]);

        $this->render('reports/trainee', [
            'overview' => [
                'enrolled_courses' => (int) $stmtEnrolled->fetchColumn(),
                'completed_courses' => (int) $stmtCompleted->fetchColumn(),
                'total_hours' => (int) $stmtHours->fetchColumn(),
            ],
            'cert_count' => (int) $stmtCert->fetchColumn(),
            'progress' => $stmtProgress->fetchAll(),
            'certificates' => $stmtCertList->fetchAll(),
        ]);
    }

    public function exportCsv(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        $db = Database::connection();
        $format = Security::cleanString($_GET['format'] ?? 'csv');
        $reportType = Security::cleanString($_GET['report_type'] ?? 'completion');

        $rows = [];
        $headers = [];
        $title = 'ITOP Training Report';

        switch ($reportType) {
            case 'attendance':
                $title = 'Attendance Report';
                $headers = ['Course', 'Attendance Rate (%)', 'Total Records'];
                $data = $db->query('SELECT c.title, ROUND(AVG(att.status = "present") * 100, 0) AS attendance_rate, COUNT(att.id) AS records FROM courses c LEFT JOIN training_sessions ts_e ON ts_e.course_id = c.id LEFT JOIN enrolments e ON e.training_session_id = ts_e.id LEFT JOIN attendance att ON att.enrolment_id = e.id GROUP BY c.id ORDER BY c.title')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['title'], $row['attendance_rate'], $row['records']];
                }
                break;
            case 'certificates':
                $title = 'Certificates Report';
                $headers = ['Course', 'Total Certificates', 'Approved', 'Pending'];
                $data = $db->query('SELECT c.title, COUNT(cert.id) AS total, SUM(cert.approval_status = "approved") AS approved, SUM(cert.approval_status = "pending") AS pending FROM courses c LEFT JOIN training_sessions ts_cert ON ts_cert.course_id = c.id LEFT JOIN certificates cert ON cert.course_id = ts_cert.id GROUP BY c.id ORDER BY c.title')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['title'], (int)$row['total'], (int)$row['approved'], (int)$row['pending']];
                }
                break;
            case 'master_data':
                $title = 'Master Data Statistics Report';
                $headers = ['Academy', 'Course Name', 'Participants'];
                $data = $db->query('SELECT a.code AS academy_code, ts.course_name, ts.participants FROM training_statistics ts JOIN academies a ON a.id = ts.academy_id ORDER BY a.code, ts.participants DESC')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['academy_code'], $row['course_name'], $row['participants']];
                }
                break;
            case 'completion':
            default:
                $title = 'Course Completion Report';
                $headers = ['Course', 'Total Participants', 'Completed', 'Completion Rate (%)'];
                $data = $db->query('SELECT c.title, COUNT(e.id) AS total, SUM(e.status = "completed") AS completed, ROUND((SUM(e.status = "completed") / GREATEST(COUNT(e.id), 1)) * 100, 0) AS rate FROM courses c LEFT JOIN training_sessions ts_e ON ts_e.course_id = c.id LEFT JOIN enrolments e ON e.training_session_id = ts_e.id GROUP BY c.id ORDER BY c.title')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['title'], (int)$row['total'], (int)$row['completed'], $row['rate']];
                }
                break;
            case 'detailed_log':
                $title = 'Detailed Trainee Enrolment Log';
                $headers = ['Trainee Name', 'Email', 'Institution/Company', 'Course Title', 'Status', 'Progress (%)', 'Enrolled Date'];
                $data = $db->query('SELECT u.name AS trainee_name, u.email, COALESCE(u.institution_company, "Unknown") AS background, c.title AS course_title, e.status, e.progress_percent, e.created_at AS enrolled_date FROM enrolments e JOIN users u ON u.id = e.trainee_id JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id ORDER BY e.created_at DESC')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['trainee_name'], $row['email'], $row['background'], $row['course_title'], ucfirst($row['status']), $row['progress_percent'], substr($row['enrolled_date'], 0, 10)];
                }
                break;
            case 'revenue':
                $title = 'Financial & Revenue Report';
                $headers = ['Course Title', 'Total Enrolments', 'Course Fee (RM)', 'Total Revenue (RM)'];
                $data = $db->query('SELECT c.title, COUNT(e.id) AS total_enrolments, ts.fee, (COUNT(e.id) * ts.fee) AS total_revenue FROM courses c JOIN training_sessions ts ON ts.course_id = c.id LEFT JOIN enrolments e ON e.training_session_id = ts.id GROUP BY ts.id ORDER BY total_revenue DESC')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['title'], (int)$row['total_enrolments'], number_format((float)$row['fee'], 2), number_format((float)$row['total_revenue'], 2)];
                }
                break;
            case 'instructor_performance':
                $title = 'Instructor Performance Report';
                $headers = ['Instructor Name', 'Assigned Courses', 'Total Students'];
                $data = $db->query('SELECT u.name AS instructor_name, COUNT(DISTINCT ts.id) AS assigned_courses, COUNT(e.id) AS total_students FROM users u JOIN training_sessions ts ON ts.instructor_id = u.id LEFT JOIN enrolments e ON e.training_session_id = ts.id WHERE u.role_id = (SELECT id FROM roles WHERE slug = "instructor") GROUP BY u.id ORDER BY total_students DESC')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['instructor_name'], (int)$row['assigned_courses'], (int)$row['total_students']];
                }
                break;
            case 'demographics':
                $title = 'Demographics & Trend Report';
                $headers = ['Enrolment Month', 'Course Title', 'Trainee Background (Institution/Company)', 'Total Enrolments'];
                $data = $db->query('SELECT DATE_FORMAT(e.created_at, "%Y-%m") AS enrolment_month, c.title AS course_title, COALESCE(NULLIF(u.institution_company, ""), "Independent/Student") AS background, COUNT(e.id) AS total_enrolments FROM enrolments e JOIN users u ON u.id = e.trainee_id JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id GROUP BY enrolment_month, c.id, background ORDER BY enrolment_month DESC, total_enrolments DESC')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['enrolment_month'], $row['course_title'], $row['background'], (int)$row['total_enrolments']];
                }
                break;
        }

        if ($format === 'pdf' && class_exists('Dompdf\\Dompdf')) {
            $html = '<h1>' . Security::e($title) . '</h1><table width="100%" border="1" cellspacing="0" cellpadding="6"><thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . Security::e($header) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($row as $col) {
                    $html .= '<td>' . Security::e((string)$col) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="itop-report.pdf"');
            echo $dompdf->output();
            return;
        }

        if ($format === 'excel') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="itop-report.xls"');
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head><body>';
            echo '<table border="1"><thead><tr>';
            foreach ($headers as $header) {
                echo '<th style="background-color:#f8f9fa; font-weight:bold;">' . Security::e($header) . '</th>';
            }
            echo '</tr></thead><tbody>';
            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $col) {
                    echo '<td>' . Security::e((string)$col) . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table></body></html>';
            return;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="itop-report.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
    }

    public function verifyCertificate(): void
    {
        $code = Security::cleanString($_GET['code'] ?? '');
        $stmt = Database::connection()->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id WHERE cert.verification_code = ?');
        $stmt->execute([$code]);
        $this->render('public/certificate-verify', ['certificate' => $stmt->fetch() ?: null, 'code' => $code]);
    }

    public function viewCertificate(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $code = Security::cleanString($_GET['code'] ?? '');
        $db = Database::connection();
        if ($id) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.id = ?');
            $stmt->execute([$id]);
        } elseif ($code) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.verification_code = ?');
            $stmt->execute([$code]);
        } else {
            http_response_code(400);
            exit('Missing certificate identifier.');
        }

        $certificate = $stmt->fetch() ?: null;
        if (!$certificate) {
            http_response_code(404);
            exit('Certificate not found.');
        }
        if (Auth::role() === 'trainee' && ((int) $certificate['trainee_id'] !== (int) Auth::id() || ($certificate['approval_status'] ?? '') !== 'approved')) {
            http_response_code(403);
            exit('Certificate is not available.');
        }

        $this->render('public/certificate-template', ['certificate' => $certificate, 'hide_actions' => !empty($_GET['hide_actions']), 'template' => [
            'background_image' => $certificate['background_image'] ?? null,
            'logo' => $certificate['logo'] ?? null,
            'signature' => $certificate['signature'] ?? null,
            'font_family' => $certificate['font_family'] ?? 'Arial',
            'font_size' => $certificate['font_size'] ?? 28,
            'text_color' => $certificate['text_color'] ?? '#182230',
            'layout_json' => $certificate['layout_json'] ?? '{}',
        ]], '');
    }

    public function downloadCertificate(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $code = Security::cleanString($_GET['code'] ?? '');
        $db = Database::connection();
        if ($id) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.id = ?');
            $stmt->execute([$id]);
        } elseif ($code) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.verification_code = ?');
            $stmt->execute([$code]);
        } else {
            http_response_code(400);
            exit('Missing certificate identifier.');
        }

        $certificate = $stmt->fetch() ?: null;
        if (!$certificate) {
            http_response_code(404);
            exit('Certificate not found.');
        }
        if (Auth::role() === 'trainee' && ((int) $certificate['trainee_id'] !== (int) Auth::id() || ($certificate['approval_status'] ?? '') !== 'approved')) {
            http_response_code(403);
            exit('Certificate is not available.');
        }

        $html = $this->certificatePdfHtml($certificate);

        // Try to render PDF with Dompdf if available
        if (class_exists('Dompdf\\Dompdf')) {
            $pdfPath = CERTIFICATE_PATH . '/certificate_' . ($certificate['id'] ?? $id) . '_' . time() . '.pdf';
            if (!is_dir(CERTIFICATE_PATH)) {
                @mkdir(CERTIFICATE_PATH, 0755, true);
            }
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->loadHtml($html);
            $dompdf->render();
            file_put_contents($pdfPath, $dompdf->output());

            // Update certificate record with pdf_path if model exists
            if (class_exists('\App\\Models\\Certificate')) {
                (new CertificateModel())->setPdfPath((int) ($certificate['id'] ?? $id), str_replace(dirname(__DIR__, 2) . '/', '', $pdfPath));
                if (Auth::check()) {
                    (new CertificateModel())->downloadLog((int) ($certificate['id'] ?? $id), (int) Auth::id());
                }
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($pdfPath) . '"');
            readfile($pdfPath);
            exit;
        }

        // Fallback: stream HTML with instructions
        header('Content-Type: text/html');
        echo $html;
        echo '<p style="text-align:center;">PDF generation requires <strong>dompdf/dompdf</strong>. Install via Composer: <code>composer require dompdf/dompdf</code></p>';
    }

    public function exportWord(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $code = Security::cleanString($_GET['code'] ?? '');
        $db = Database::connection();
        if ($id) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.id = ?');
            $stmt->execute([$id]);
        } elseif ($code) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.verification_code = ?');
            $stmt->execute([$code]);
        } else {
            http_response_code(400);
            exit('Missing certificate identifier.');
        }

        $certificate = $stmt->fetch() ?: null;
        if (!$certificate) {
            http_response_code(404);
            exit('Certificate not found.');
        }
        if (Auth::role() === 'trainee' && ((int) $certificate['trainee_id'] !== (int) Auth::id() || ($certificate['approval_status'] ?? '') !== 'approved')) {
            http_response_code(403);
            exit('Certificate is not available.');
        }

        $html = $this->certificateWordHtml($certificate);

        $filename = 'certificate_' . ($certificate['certificate_no'] ?? $certificate['id'] ?? time()) . '.doc';
        
        header("Content-Type: application/force-download");
        header("Content-Type: application/msword");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        // Outputting HTML structure, Word will interpret it as a document.
        echo $html;
        exit;
    }

    private function certificateWordHtml(array $certificate): string
    {
        $number = htmlspecialchars((string) (!empty($certificate['certificate_number']) ? $certificate['certificate_number'] : (!empty($certificate['certificate_no']) ? $certificate['certificate_no'] : '')), ENT_QUOTES, 'UTF-8');
        $rawName = !empty($certificate['trainee_name']) ? $certificate['trainee_name'] : 'Participant Name';
        $name = htmlspecialchars((string) $rawName, ENT_QUOTES, 'UTF-8');
        $course = htmlspecialchars((string) (!empty($certificate['course_title']) ? $certificate['course_title'] : 'Course Title'), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars((string) ($certificate['issue_date'] ?? $certificate['issued_at'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8');
        $formattedDate = strtoupper(date('d F Y', strtotime($date)));

        $layout = [];
        if (!empty($certificate['layout_json'])) {
            $layout = json_decode((string) $certificate['layout_json'], true) ?: [];
        }

        $title = htmlspecialchars((string) ($layout['title'] ?? 'CERTIFICATE'), ENT_QUOTES, 'UTF-8');
        $intro = htmlspecialchars((string) ($layout['intro'] ?? 'This is to certify that'), ENT_QUOTES, 'UTF-8');
        $introScript = htmlspecialchars((string) ($layout['intro_script'] ?? 'has successfully completed the training programme for :'), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars((string) ($layout['description'] ?? 'Congratulations on your active participation in this program...'), ENT_QUOTES, 'UTF-8');
        $signatoryName = htmlspecialchars((string) ($layout['signatory_name'] ?? 'Dato Haji Syeed Mohd Hussien Bin Wan Abd Rahman'), ENT_QUOTES, 'UTF-8');
        $signatoryTitle = htmlspecialchars((string) ($layout['signatory_title'] ?? 'Chief Executive Officer'), ENT_QUOTES, 'UTF-8');
        $organization = htmlspecialchars((string) ($layout['organization'] ?? 'Centre for Technology Excellence Sarawak'), ENT_QUOTES, 'UTF-8');
        $showWatermark = (bool) ($layout['show_watermark'] ?? true);
        $showQr = (bool) ($layout['show_qr'] ?? true);

        $textColor = htmlspecialchars((string) ($certificate['text_color'] ?? '#000000'), ENT_QUOTES, 'UTF-8');

        $logoUrl = !empty($certificate['logo']) ? APP_URL . '/storage/uploads/' . $certificate['logo'] : APP_URL . '/public/assets/img/centexs-logo-with-outline-1.png';
        $sigUrl = !empty($certificate['signature']) ? APP_URL . '/storage/uploads/' . $certificate['signature'] : '';
        $bgUrl = !empty($certificate['background_image']) ? APP_URL . '/storage/uploads/' . $certificate['background_image'] : APP_URL . '/public/assets/img/centexs-logo-with-outline-1.png';

        $verifyUrl = APP_URL . '/index.php?page=verify-certificate&code=' . urlencode((string) $certificate['verification_code']);
        $qrCodeSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($verifyUrl);

        // Get actual logo size to prevent stretching in Word
        $logoPath = !empty($certificate['logo']) ? dirname(__DIR__, 2) . '/storage/uploads/' . $certificate['logo'] : dirname(__DIR__, 2) . '/public/assets/img/centexs-logo-with-outline-1.png';
        $logoImg = '<img src="' . $logoUrl . '" style="height: 160px; width: auto; border: none; margin-bottom: 20px;">';
        if (file_exists($logoPath)) {
            $size = @getimagesize($logoPath);
            if ($size && $size[1] > 0) {
                $ratio = $size[0] / $size[1];
                $targetHeight = 160;
                $targetWidth = round($targetHeight * $ratio);
                $logoImg = '<img src="' . $logoUrl . '" width="' . $targetWidth . '" height="' . $targetHeight . '" style="width: ' . $targetWidth . 'px; height: ' . $targetHeight . 'px; border: none; margin-bottom: 20px;">';
            }
        }

        // Signature size
        $sigImgHtml = '<br><br><br>';
        if (!empty($sigUrl)) {
            $sigPath = dirname(__DIR__, 2) . '/storage/uploads/' . $certificate['signature'];
            if (file_exists($sigPath)) {
                $size = @getimagesize($sigPath);
                if ($size && $size[1] > 0) {
                    $ratio = $size[0] / $size[1];
                    $targetHeight = 90;
                    $targetWidth = round($targetHeight * $ratio);
                    $sigImgHtml = '<img src="' . $sigUrl . '" width="' . $targetWidth . '" height="' . $targetHeight . '" style="border: none;">';
                }
            } else {
                $sigImgHtml = '<img src="' . $sigUrl . '" height="90" style="border: none;">';
            }
        }

        $vmlWatermark = '';
        if ($showWatermark && !empty($bgUrl)) {
            $bgWidth = 450;
            $bgHeight = 450;
            $bgPath = !empty($certificate['background_image']) ? dirname(__DIR__, 2) . '/storage/uploads/' . $certificate['background_image'] : dirname(__DIR__, 2) . '/public/assets/img/centexs-logo-with-outline-1.png';
            if (file_exists($bgPath)) {
                $size = @getimagesize($bgPath);
                if ($size && $size[1] > 0) {
                    $ratio = $size[0] / $size[1];
                    $bgHeight = 450;
                    $bgWidth = round($bgHeight * $ratio);
                }
            }
            $vmlWatermark = '
            <!--[if gte vml 1]>
            <w:pict>
                <v:rect id="watermark"
                    style="position:absolute;z-index:-1;width:' . $bgWidth . 'pt;height:' . $bgHeight . 'pt;
                    mso-position-horizontal:center;mso-position-horizontal-relative:margin;
                    mso-position-vertical:center;mso-position-vertical-relative:margin;"
                    stroked="f">
                    <v:fill src="' . $bgUrl . '" type="frame" opacity="0.15"/>
                </v:rect>
            </w:pict>
            <![endif]-->';
        }

        return '
<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns:m="http://schemas-microsoft.com/office/2004/12/omml"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 15">
<meta name=Originator content="Microsoft Word 15">
<!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>100</w:Zoom>
  <w:DoNotOptimizeForBrowser/>
 </w:WordDocument>
</xml><![endif]-->
<style>
body { font-family: "Arial", sans-serif; color: ' . $textColor . '; text-align: center; background-color: white; }
p { margin: 0; padding: 0; }
</style>
</head>
<body style="text-align: center;">
    ' . $vmlWatermark . '
    <div style="text-align: center; margin-top: 20px;">
        ' . $logoImg . '
        
        <p style="font-size: 38pt; font-weight: bold; margin-top: 10px;">CERTIFICATE</p>
        <p style="font-size: 20pt; font-weight: bold; margin-bottom: 20px;">OF COMPLETION</p>
        
        <p style="font-size: 14pt; font-weight: bold; margin-bottom: 20px;">' . $intro . '</p>
        
        <p style="font-size: 22pt; font-weight: bold; text-transform: uppercase; margin-bottom: 20px;">' . $name . '</p>
        
        <p style="font-size: 16pt; font-family: \'Brush Script MT\', \'Monotype Corsiva\', cursive; font-style: italic; margin-bottom: 20px;">' . $introScript . '</p>
        
        <p style="font-size: 18pt; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;">' . $course . '</p>
        <p style="font-size: 12pt; font-weight: bold; margin-bottom: 20px;">(' . $formattedDate . ')</p>
        
        <div style="font-size: 11pt; line-height: 1.5; margin: 0 10%; margin-bottom: 40px;">' . $description . '</div>
        
        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-top: 40px;">
            <tr>
                <td width="25%" align="left" valign="bottom">
                    ' . ($showQr ? '
                    <img src="' . $qrCodeSrc . '" width="90" height="90" style="border: none;">
                    <p style="font-size: 8pt; font-weight: bold; margin-top: 5px;">Certificate No.</p>
                    <p style="font-size: 9pt; font-weight: bold;">' . $number . '</p>
                    ' : '') . '
                </td>
                <td width="50%" align="center" valign="bottom">
                    ' . $sigImgHtml . '
                    <div style="border-top: 1.5px solid black; width: 350px; margin: 5px auto 0 auto; padding-top: 5px;">
                        <p style="font-size: 11pt; font-weight: bold;">' . $signatoryName . '</p>
                        <p style="font-size: 10pt;">' . $signatoryTitle . '</p>
                        <p style="font-size: 9pt; color: #555;">' . $organization . '</p>
                    </div>
                </td>
                <td width="25%"></td>
            </tr>
        </table>
    </div>
</body>
</html>';
    }

    private function certificatePdfHtml(array $certificate): string
    {
        $number = htmlspecialchars((string) (!empty($certificate['certificate_number']) ? $certificate['certificate_number'] : (!empty($certificate['certificate_no']) ? $certificate['certificate_no'] : '')), ENT_QUOTES, 'UTF-8');
        $rawName = !empty($certificate['trainee_name']) ? $certificate['trainee_name'] : 'Participant Name';
        $name = htmlspecialchars((string) $rawName, ENT_QUOTES, 'UTF-8');
        $course = htmlspecialchars((string) (!empty($certificate['course_title']) ? $certificate['course_title'] : 'Course Title'), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars((string) ($certificate['issue_date'] ?? $certificate['issued_at'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8');
        $formattedDate = strtoupper(date('d F Y', strtotime($date)));

        $layout = [];
        if (!empty($certificate['layout_json'])) {
            $layout = json_decode((string) $certificate['layout_json'], true) ?: [];
        }

        $style = $layout['style'] ?? [];
        $title = htmlspecialchars((string) ($layout['title'] ?? 'CERTIFICATE'), ENT_QUOTES, 'UTF-8');
        $intro = htmlspecialchars((string) ($layout['intro'] ?? 'This is to certify that'), ENT_QUOTES, 'UTF-8');
        $introScript = htmlspecialchars((string) ($layout['intro_script'] ?? 'has successfully completed the training programme for :'), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars((string) ($layout['description'] ?? 'Congratulations on your active participation in this program which have equipped you with valuable knowledge and skills on Artificial Intelligence (AI), Ethical Use of AI, Instructional Design Planning, Educational Data Analytics, AI for Visuals and Audio, and AI-Based Tasks.'), ENT_QUOTES, 'UTF-8');
        $signatoryName = htmlspecialchars((string) ($layout['signatory_name'] ?? 'Dato Haji Syeed Mohd Hussien Bin Wan Abd Rahman'), ENT_QUOTES, 'UTF-8');
        $signatoryTitle = htmlspecialchars((string) ($layout['signatory_title'] ?? 'Chief Executive Officer'), ENT_QUOTES, 'UTF-8');
        $organization = htmlspecialchars((string) ($layout['organization'] ?? 'Centre for Technology Excellence Sarawak'), ENT_QUOTES, 'UTF-8');
        $showWatermark = (bool) ($layout['show_watermark'] ?? true);
        $showQr = (bool) ($layout['show_qr'] ?? true);

        $textColor = htmlspecialchars((string) ($certificate['text_color'] ?? '#000000'), ENT_QUOTES, 'UTF-8');
        $accentColor = htmlspecialchars((string) ($style['accent_color'] ?? '#aa3338'), ENT_QUOTES, 'UTF-8');
        $patternOpacity = (float) ($style['pattern_opacity'] ?? 0.15);

        // Map web URLs for Dompdf rendering to bypass local directory chroot restrictions
        $logoUrl = !empty($certificate['logo']) ? APP_URL . '/storage/uploads/' . $certificate['logo'] : APP_URL . '/public/assets/img/centexs-logo-with-outline-1.png';
        $sigUrl = !empty($certificate['signature']) ? APP_URL . '/storage/uploads/' . $certificate['signature'] : '';
        $bgUrl = !empty($certificate['background_image']) ? APP_URL . '/storage/uploads/' . $certificate['background_image'] : APP_URL . '/public/assets/img/centexs-logo-with-outline-1.png';

        $verifyUrl = APP_URL . '/index.php?page=verify-certificate&code=' . urlencode((string) $certificate['verification_code']);
        $qrCodeSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($verifyUrl);

        $sigImageHtml = '';
        if (!empty($sigUrl)) {
            $sigImageHtml = '<img src="' . $sigUrl . '" style="height: 90px; display: block; margin: 0 auto; border: none;">';
        } else {
            $sigImageHtml = '<div style="height: 60px;"></div>'; // spacer if no signature
        }

        return '<!doctype html><html><head><meta charset="utf-8"><style>
            @page { size: A4 portrait; margin: 0; }
            html, body { margin: 0; padding: 0; background: #fff; font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: ' . $textColor . '; }
            
            .watermark { 
                position: absolute; 
                top: 75mm; 
                left: 35mm; 
                width: 140mm; 
                height: 140mm; 
                opacity: ' . $patternOpacity . '; 
                text-align: center; 
                z-index: -1; 
            }
            .watermark img { width: 100%; height: 100%; object-fit: contain; }
            
            .content {
                padding-top: 30mm;
                text-align: center;
                position: relative;
                z-index: 1;
            }
            
            .footer {
                position: absolute;
                bottom: 25mm;
                left: 15mm;
                width: 180mm;
                z-index: 2;
            }
        </style></head><body>
            
            ' . ($showWatermark ? '<div class="watermark"><img src="' . $bgUrl . '"></div>' : '') . '
            
            <div class="content">
                <img src="' . $logoUrl . '" style="height: 160px; display: inline-block; border: none; margin-bottom: 20px;">
                
                <div style="font-size: 52px; font-weight: 900; line-height: 1.1; margin: 0;">CERTIFICATE</div>
                <div style="font-size: 28px; font-weight: bold; margin: 0 0 20px 0;">OF COMPLETION</div>
                
                <div style="font-size: 16px; margin: 0 0 25px 0; font-weight: bold;">' . $intro . '</div>
                
                <div style="font-size: 26px; font-weight: bold; text-transform: uppercase; margin: 0 0 25px 0;">' . $name . '</div>
                
                <div style="font-size: 24px; margin: 0 0 20px 0; font-family: \'Brush Script MT\', \'Lucida Handwriting\', \'Monotype Corsiva\', \'Times New Roman\', serif; font-style: italic;">' . $introScript . '</div>
                
                <div style="font-size: 22px; font-weight: bold; text-transform: uppercase; margin: 0 0 5px 0;">' . $course . '</div>
                <div style="font-size: 16px; font-weight: bold; margin: 0 0 25px 0;">(' . $formattedDate . ')</div>
                
                <div style="font-size: 14px; line-height: 1.5; max-width: 85%; margin: 0 auto;">' . $description . '</div>
            </div>
            
            <div class="footer">
                <table style="width: 100%; border: none; border-collapse: collapse; margin: 0;">
                    <tr>
                        ' . ($showQr ? '<td style="width: 25%; text-align: left; vertical-align: bottom; padding: 0; border: none;">
                            <img src="' . $qrCodeSrc . '" style="width: 90px; height: 90px; display: block; border: none; margin-bottom: 5px;">
                            <div style="font-size: 10px; color: ' . $accentColor . '; font-weight: bold; margin-bottom: 2px;">Certificate No.</div>
                            <strong style="font-size: 11px; display: block;">' . $number . '</strong>
                        </td>' : '<td style="width: 25%; border: none;"></td>') . '
                        
                        <td style="width: 50%; text-align: center; vertical-align: bottom; padding: 0; border: none;">
                            <table style="margin: 0 auto; border: none; border-collapse: collapse; width: 350px; text-align: center;">
                                <tr>
                                    <td style="text-align: center; padding: 0 0 5px 0; border: none;">
                                        ' . $sigImageHtml . '
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none; border-top: 1.5px solid #000; padding: 8px 0 0 0; text-align: center; line-height: 1.2;">
                                        <strong style="font-size: 14px; display: block; margin-bottom: 3px;">' . $signatoryName . '</strong>
                                        <span style="font-size: 12px; display: block; color: #333; margin-bottom: 2px;">' . $signatoryTitle . '</span>
                                        <span style="font-size: 11px; display: block; color: #555;">' . $organization . '</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        
                        ' . ($showQr ? '<td style="width: 25%; border: none;"></td>' : '') . '
                    </tr>
                </table>
            </div>
        </body></html>';
    }
}
