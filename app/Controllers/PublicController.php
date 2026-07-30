<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Security;
use App\Core\Controller;
use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;

final class PublicController extends Controller
{
    public function home(): void
    {
        $db = \App\Core\Model::getDb();
        $settings = [];
        try {
            foreach ($db->query('SELECT setting_key, setting_value FROM website_settings')->fetchAll() as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Exception $e) {}

        $intakes = [];
        try {
            $intakes = $db->query('SELECT i.*, a.code AS academy_code, a.name AS academy_name FROM upcoming_intakes i LEFT JOIN academies a ON a.id = i.academy_id WHERE i.is_active = 1 ORDER BY i.intake_date LIMIT 3')->fetchAll();
        } catch (\Exception $e) {}

        $stories = [];
        try {
            $stories = $db->query('SELECT * FROM success_stories WHERE is_active = 1 ORDER BY sort_order, id LIMIT 3')->fetchAll();
        } catch (\Exception $e) {}

        $this->render('public/home', [
            'courses' => (new Course())->publicList(),
            'announcements' => (new Content())->announcements(true),
            'settings' => $settings,
            'intakes' => $intakes,
            'stories' => $stories,
        ]);
    }

    public function about(): void
    {
        $this->render('public/about');
    }

    public function courses(): void
    {
        $courseModel = new Course();
        $academyCode = strtoupper(Security::cleanString($_GET['academy'] ?? ''));
        $this->render('public/courses', [
            'academies' => $courseModel->publicAcademies(),
            'selectedAcademy' => in_array($academyCode, ['ADGEA', 'IESGA'], true) ? $courseModel->academyByCode($academyCode) : null,
            'courses' => in_array($academyCode, ['ADGEA', 'IESGA'], true) ? $courseModel->publicByAcademy($academyCode, Security::cleanString($_GET['q'] ?? '')) : [],
            'q' => Security::cleanString($_GET['q'] ?? ''),
        ]);
    }

    public function course(): void
    {
        $course = (new Course())->find((int) ($_GET['id'] ?? 0));
        if (!$course) {
            http_response_code(404);
            exit('Course not found.');
        }
        $this->render('public/course-detail', ['course' => $course]);
    }

    public function enroll(): void
    {
        Auth::requireRole(['trainee']);
        Security::verifyCsrf();
        (new Enrollment())->request((int) $_POST['course_id'], (int) Auth::id());
        $this->redirect('index.php?page=trainee-dashboard');
    }

    public function news(): void
    {
        $this->render('public/news', ['announcements' => (new Content())->announcements(true)]);
    }

    public function contact(): void
    {
        $this->render('public/contact');
    }
}
