<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CommunicationController;
use App\Controllers\DashboardController;
use App\Controllers\LmsController;
use App\Controllers\PublicController;
use App\Controllers\ReportController;
use App\Controllers\TraineeController;
use App\Core\Router;

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$router = new Router();

// --- PUBLIC ROUTES ---
$router->get('home', [PublicController::class, 'home']);
$router->get('about', [PublicController::class, 'about']);
$router->get('courses', [PublicController::class, 'courses']);
$router->get('course', [PublicController::class, 'course']);
$router->get('news', [PublicController::class, 'news']);
$router->get('contact', [PublicController::class, 'contact']);
$router->get('login', [AuthController::class, 'loginForm']);
$router->get('register', [AuthController::class, 'registerForm']);
$router->get('logout', [AuthController::class, 'logout']);
$router->get('verify-certificate', [ReportController::class, 'verifyCertificate']); // usually public verification
$router->get('view-certificate', [ReportController::class, 'viewCertificate']);

$router->post('login', [AuthController::class, 'login']);
$router->post('register', [AuthController::class, 'register']);
$router->post('enroll', [PublicController::class, 'enroll']);

// --- AUTHENTICATED ROUTES ---
$authRoutesGet = [
    'dashboard' => [DashboardController::class, 'dashboard'],
    'instructor-dashboard' => [DashboardController::class, 'instructor'],
    'instructor-courses' => [DashboardController::class, 'instructorCourses'],
    'trainee-dashboard' => [DashboardController::class, 'trainee'],
    'profile' => [TraineeController::class, 'userProfile'],
    'trainee-profile' => [TraineeController::class, 'profile'],
    'trainee-evaluations' => [TraineeController::class, 'evaluations'],
    'trainee-certificates' => [TraineeController::class, 'certificates'],
    'messages' => [CommunicationController::class, 'messages'],
    'messages-feed' => [CommunicationController::class, 'messagesFeed'],
    'notifications' => [CommunicationController::class, 'notifications'],
    'badge-counts' => [CommunicationController::class, 'badgeCounts'],
    'course-room' => [LmsController::class, 'courseRoom'],
    'reports' => [ReportController::class, 'reports'],
    'instructor-reports' => [ReportController::class, 'instructorReports'],
    'my-learning-report' => [ReportController::class, 'traineeReport'],
    'export-report' => [ReportController::class, 'exportCsv'],
    'download-certificate' => [ReportController::class, 'downloadCertificate'],
    'download-certificate-word' => [ReportController::class, 'exportWord'],
    'api-trainee-updates' => [DashboardController::class, 'apiAnnouncements'],
    'announcements-manage' => [AdminController::class, 'announcements'],
    'instructor-enrolments' => [AdminController::class, 'instructorEnrolments'],
];

foreach ($authRoutesGet as $path => $handler) {
    $router->get($path, $handler)->middleware('auth');
}

$authRoutesPost = [
    'save-trainee-profile' => [TraineeController::class, 'saveProfile'],
    'save-evaluation' => [TraineeController::class, 'saveEvaluation'],
    'send-message' => [CommunicationController::class, 'sendMessage'],
    'delete-message' => [CommunicationController::class, 'deleteMessage'],
    'mark-notification-read' => [CommunicationController::class, 'markNotificationRead'],
    'delete-notification' => [CommunicationController::class, 'deleteNotification'],
    'add-material' => [LmsController::class, 'addMaterial'],
    'add-assignment' => [LmsController::class, 'addAssignment'],
    'submit-assignment' => [LmsController::class, 'submitAssignment'],
    'cancel-submission' => [LmsController::class, 'cancelSubmission'],
    'instructor-claim-course' => [AdminController::class, 'claimCourse'],
    'instructor-unassign-course' => [AdminController::class, 'unassignCourse'],
    'instructor-enrolment-status' => [AdminController::class, 'setInstructorEnrolmentStatus'],
    'save-course' => [AdminController::class, 'saveCourse'],
    'save-announcement' => [AdminController::class, 'saveAnnouncement'],
];

foreach ($authRoutesPost as $path => $handler) {
    $router->post($path, $handler)->middleware('auth');
}


// --- ADMIN ROUTES ---
$adminRoutesGet = [
    'admin-dashboard' => [DashboardController::class, 'admin'],
    'admin-users' => [AdminController::class, 'users'],
    'admin-user-detail' => [AdminController::class, 'userDetail'],
    'admin-courses' => [AdminController::class, 'courses'],
    'admin-course-detail' => [AdminController::class, 'courseDetail'],
    'admin-enrolments' => [AdminController::class, 'enrolments'],
    'admin-enrolment-detail' => [AdminController::class, 'enrolmentDetail'],
    'admin-certificates' => [AdminController::class, 'certificates'],
    'admin-certificate-logs' => [AdminController::class, 'certificateLogs'],
    'admin-documentation' => [AdminController::class, 'documentation'],
    'admin-documentation-export' => [AdminController::class, 'exportDocumentation'],
    'admin-evaluations' => [AdminController::class, 'evaluations'],
    'admin-master-data' => [AdminController::class, 'masterData'],
    'admin-website-settings' => [AdminController::class, 'websiteSettings'],
    'admin-fetch-analytics-details' => [AdminController::class, 'fetchAnalyticsDetails'],
    'admin-analytics' => [AdminController::class, 'analytics'],
    'admin-analytics-detail' => [AdminController::class, 'analyticsDetail'],
    'admin-participants' => [AdminController::class, 'participants'],
    'admin-participant-detail' => [AdminController::class, 'participantDetail'],
    'admin-system-settings' => [AdminController::class, 'systemSettings'],
    'admin-backup-database' => [AdminController::class, 'backupDatabase'],
    'admin-profile' => [AdminController::class, 'profile'],
];

foreach ($adminRoutesGet as $path => $handler) {
    $router->get($path, $handler)->middleware('admin');
}

$adminRoutesPost = [
    'admin-update-profile' => [AdminController::class, 'updateProfile'],
    'admin-change-password' => [AdminController::class, 'changePassword'],
    'admin-update-email' => [AdminController::class, 'updateEmail'],
    'admin-save-preferences' => [AdminController::class, 'savePreferences'],
    'admin-revoke-session' => [AdminController::class, 'revokeSession'],
    'admin-logout-all-devices' => [AdminController::class, 'logoutAllDevices'],
    'admin-participant-update' => [AdminController::class, 'updateParticipant'],
    'admin-save-analytics-details' => [AdminController::class, 'saveAnalyticsDetails'],
    'admin-create-custom-chart' => [AdminController::class, 'createCustomChart'],
    'admin-delete-custom-chart' => [AdminController::class, 'deleteCustomChart'],
    'admin-restore-database' => [AdminController::class, 'restoreDatabase'],
    'admin-save-system-settings' => [AdminController::class, 'saveSystemSettings'],
    'set-user-status' => [AdminController::class, 'setUserStatus'],
    'save-user' => [AdminController::class, 'saveUser'],
    'delete-user' => [AdminController::class, 'deleteUser'],
    'delete-course' => [AdminController::class, 'deleteCourse'],
    'set-enrolment-status' => [AdminController::class, 'setEnrolmentStatus'],
    'save-certificate-template' => [AdminController::class, 'saveCertificateTemplate'],
    'delete-certificate-template' => [AdminController::class, 'deleteCertificateTemplate'],
    'admin-bulk-issue-certificates' => [AdminController::class, 'bulkIssueCertificates'],
    'admin-revoke-certificate' => [AdminController::class, 'revokeCertificate'],
    'issue-certificate' => [AdminController::class, 'issueCertificate'],
    'review-certificate' => [AdminController::class, 'reviewCertificate'],
    'save-master-data' => [AdminController::class, 'saveMasterData'],
    'delete-master-data' => [AdminController::class, 'deleteMasterData'],
    'save-training-statistic' => [AdminController::class, 'saveTrainingStatistic'],
    'delete-training-statistic' => [AdminController::class, 'deleteTrainingStatistic'],
    'save-website-settings' => [AdminController::class, 'saveWebsiteSettings'],
    'save-success-story' => [AdminController::class, 'saveSuccessStory'],
    'delete-success-story' => [AdminController::class, 'deleteSuccessStory'],
    'save-intake' => [AdminController::class, 'saveIntake'],
    'delete-intake' => [AdminController::class, 'deleteIntake'],
    'save-profile-picture' => [AdminController::class, 'saveProfilePicture'],
    'admin-verify-document' => [AdminController::class, 'verifyTraineeDocument'],
    'admin-reject-document' => [AdminController::class, 'rejectTraineeDocument'],
];

foreach ($adminRoutesPost as $path => $handler) {
    $router->post($path, $handler)->middleware('admin');
}

$router->dispatch($method, $uri);
