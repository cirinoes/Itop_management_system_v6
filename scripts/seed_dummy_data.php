<?php
declare(strict_types=1);

$host = 'localhost';
$db   = 'centexs_itop_ims';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

echo "Seeding dummy data...\n";

// 1. Get Trainee Role ID
$stmt = $pdo->query("SELECT id FROM roles WHERE slug = 'trainee' LIMIT 1");
$role = $stmt->fetch();
$traineeRoleId = $role ? $role['id'] : 3;

$stmt = $pdo->query("SELECT id FROM academies WHERE code = 'ADGEA' LIMIT 1");
$academy = $stmt->fetch();
$academyId = $academy ? $academy['id'] : 1;

$passwordHash = password_hash('password123', PASSWORD_DEFAULT);

// Delete old dummy data if running multiple times
// Just to be safe, we won't delete, we'll just insert new ones with specific emails.
$pdo->exec("DELETE FROM enrolments WHERE trainee_id IN (SELECT id FROM users WHERE email LIKE 'dummy_trainee_%@example.com')");
$pdo->exec("DELETE FROM trainee_profiles WHERE user_id IN (SELECT id FROM users WHERE email LIKE 'dummy_trainee_%@example.com')");
$pdo->exec("DELETE FROM users WHERE email LIKE 'dummy_trainee_%@example.com'");
$pdo->exec("DELETE FROM training_sessions WHERE course_id IN (SELECT id FROM courses WHERE title LIKE '%Dummy%')");
$pdo->exec("DELETE FROM courses WHERE title LIKE '%Dummy%'");

// 2. Insert 3 Courses and their sessions
// Course 1: Introduction To Telecommunication Infrastructure
$pdo->exec("INSERT INTO courses (academy_id, title, category, status) VALUES ($academyId, 'Dummy: Introduction To Telecommunication Infrastructure', 'IT & Tech', 'published')");
$course1Id = $pdo->lastInsertId();

// Session 1 for Course 1
$pdo->exec("INSERT INTO training_sessions (course_id, venue, start_date, end_date, duration_days, capacity, fee, status) VALUES ($course1Id, 'CENTEXS Kuching', '2018-11-26', '2018-11-26', 1, 25, 100.00, 'completed')");
$session1Id = $pdo->lastInsertId();

// Course 2: Wireless Base Station And Antenna Subsystem Training
$pdo->exec("INSERT INTO courses (academy_id, title, category, status) VALUES ($academyId, 'Dummy: Wireless Base Station And Antenna Subsystem Training', 'IT & Tech', 'published')");
$course2Id = $pdo->lastInsertId();

// Session 1 for Course 2
$pdo->exec("INSERT INTO training_sessions (course_id, venue, start_date, end_date, duration_days, capacity, fee, status) VALUES ($course2Id, 'CENTEXS Kuching', '2018-11-27', '2018-11-28', 2, 25, 200.00, 'completed')");
$session2Id = $pdo->lastInsertId();

// Course 3: Fiber Optic Splicing
$pdo->exec("INSERT INTO courses (academy_id, title, category, status) VALUES ($academyId, 'Dummy: Fiber Optic Splicing', 'IT & Tech', 'published')");
$course3Id = $pdo->lastInsertId();

// Session 1 for Course 3
$pdo->exec("INSERT INTO training_sessions (course_id, venue, start_date, end_date, duration_days, capacity, fee, status) VALUES ($course3Id, 'CENTEXS Kuching', '2018-11-29', '2018-11-29', 1, 25, 150.00, 'completed')");
$session3Id = $pdo->lastInsertId();


// 3. Insert 30 Trainees
$traineeIds = [];
$firstNames = ['Ahmad', 'Siti', 'John', 'Jane', 'Ali', 'Fatima', 'Michael', 'Sarah', 'David', 'Emma', 'Daniel', 'Olivia', 'James', 'Grace', 'William', 'Sophia', 'Benjamin', 'Chloe', 'Lucas', 'Mia', 'Henry', 'Lily', 'Alexander', 'Zoey', 'Matthew', 'Avery', 'Joseph', 'Evelyn', 'Samuel', 'Ella'];
$lastNames = ['Bin Abdullah', 'Binti Sulaiman', 'Smith', 'Doe', 'Bin Mutalib', 'Binti Hassan', 'Johnson', 'Brown', 'Williams', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris'];

for ($i = 0; $i < 30; $i++) {
    $firstName = $firstNames[$i];
    $lastName = $lastNames[$i];
    $fullName = $firstName . ' ' . $lastName;
    $email = "dummy_trainee_" . ($i + 1) . "@example.com";
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role_id, identity_number, status, email_verified_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
    $stmt->execute([$fullName, $email, $passwordHash, $traineeRoleId, 'ID' . (100000 + $i)]);
    $userId = $pdo->lastInsertId();
    $traineeIds[] = $userId;
    
    $stmt = $pdo->prepare("INSERT INTO trainee_profiles (user_id, phone, is_verified) VALUES (?, ?, 1)");
    $stmt->execute([$userId, '01234567' . sprintf('%02d', $i)]);
}

// 4. Enroll trainees
// Course 1: 10 trainees (6 Completed, 4 Ongoing)
for ($i = 0; $i < 10; $i++) {
    $userId = $traineeIds[$i];
    $status = ($i < 6) ? 'completed' : 'active';
    $progress = ($i < 6) ? 100 : rand(10, 90);
    $learningStatus = ($i < 6) ? 'completed' : 'in_progress';
    $completedAt = ($i < 6) ? '2018-11-26 17:00:00' : null;
    
    $stmt = $pdo->prepare("INSERT INTO enrolments (training_session_id, trainee_id, status, progress_percent, attendance_requirement_met, assessments_completed, evaluation_submitted, certificate_available, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $isComplete = ($i < 6) ? 1 : 0;
    $stmt->execute([$session1Id, $userId, $status, $progress, $isComplete, $isComplete, $isComplete, $isComplete, $completedAt]);
    $enrolmentId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO course_progress (enrolment_id, progress_percent, learning_status) VALUES (?, ?, ?)");
    $stmt->execute([$enrolmentId, $progress, $learningStatus]);
}

// Course 2: 10 trainees (7 Completed, 3 Ongoing)
for ($i = 10; $i < 20; $i++) {
    $userId = $traineeIds[$i];
    $status = ($i < 17) ? 'completed' : 'active';
    $progress = ($i < 17) ? 100 : rand(10, 90);
    $learningStatus = ($i < 17) ? 'completed' : 'in_progress';
    $completedAt = ($i < 17) ? '2018-11-28 17:00:00' : null;
    
    $stmt = $pdo->prepare("INSERT INTO enrolments (training_session_id, trainee_id, status, progress_percent, attendance_requirement_met, assessments_completed, evaluation_submitted, certificate_available, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $isComplete = ($i < 17) ? 1 : 0;
    $stmt->execute([$session2Id, $userId, $status, $progress, $isComplete, $isComplete, $isComplete, $isComplete, $completedAt]);
    $enrolmentId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO course_progress (enrolment_id, progress_percent, learning_status) VALUES (?, ?, ?)");
    $stmt->execute([$enrolmentId, $progress, $learningStatus]);
}

// Course 3: 10 trainees (5 Completed, 5 Ongoing)
for ($i = 20; $i < 30; $i++) {
    $userId = $traineeIds[$i];
    $status = ($i < 25) ? 'completed' : 'active';
    $progress = ($i < 25) ? 100 : rand(10, 90);
    $learningStatus = ($i < 25) ? 'completed' : 'in_progress';
    $completedAt = ($i < 25) ? '2018-11-29 17:00:00' : null;
    
    $stmt = $pdo->prepare("INSERT INTO enrolments (training_session_id, trainee_id, status, progress_percent, attendance_requirement_met, assessments_completed, evaluation_submitted, certificate_available, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $isComplete = ($i < 25) ? 1 : 0;
    $stmt->execute([$session3Id, $userId, $status, $progress, $isComplete, $isComplete, $isComplete, $isComplete, $completedAt]);
    $enrolmentId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO course_progress (enrolment_id, progress_percent, learning_status) VALUES (?, ?, ?)");
    $stmt->execute([$enrolmentId, $progress, $learningStatus]);
}

echo "Seeding completed successfully!\n";
