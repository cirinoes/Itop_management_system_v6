<?php
$file = 'c:/xampp/htdocs/Itop_management_system/app/Controllers/ReportController.php';
$content = file_get_contents($file);

$replacements = [
    // 1. enrolments e ON e.course_id = c.id
    "LEFT JOIN enrolments e ON e.course_id = c.id" => "LEFT JOIN training_sessions ts_e ON ts_e.course_id = c.id LEFT JOIN enrolments e ON e.training_session_id = ts_e.id",

    // 2. assignments a ON a.course_id = c.id
    "LEFT JOIN assignments a ON a.course_id = c.id" => "LEFT JOIN training_sessions ts_a ON ts_a.course_id = c.id LEFT JOIN assignments a ON a.course_id = ts_a.id",

    // 3. evaluations e ON e.course_id = c.id
    "LEFT JOIN evaluations e ON e.course_id = c.id" => "LEFT JOIN training_sessions ts_ev ON ts_ev.course_id = c.id LEFT JOIN evaluations e ON e.course_id = ts_ev.id",

    // 4. certificates cert ON cert.course_id = c.id
    "LEFT JOIN certificates cert ON cert.course_id = c.id" => "LEFT JOIN training_sessions ts_cert ON ts_cert.course_id = c.id LEFT JOIN certificates cert ON cert.course_id = ts_cert.id",

    // 5. JOIN courses c ON c.id = cert.course_id
    "JOIN courses c ON c.id = cert.course_id" => "JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id",

    // 6. quiz_avg_score query
    "(SELECT ROUND(AVG(score), 0) FROM quiz_results qr JOIN quizzes q ON q.id = qr.quiz_id WHERE q.course_id = c.id AND qr.trainee_id = e.trainee_id)" => "(SELECT ROUND(AVG(score), 0) FROM quiz_results qr JOIN quizzes q ON q.id = qr.quiz_id JOIN training_sessions ts_q ON ts_q.id = q.course_id WHERE ts_q.course_id = c.id AND qr.trainee_id = e.trainee_id)",

    // 7. JOIN courses c ON c.id = e.course_id
    "JOIN courses c ON c.id = e.course_id" => "JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id",
    
    // 8. JOIN enrolments e ON e.course_id = c.id 
    "JOIN enrolments e ON e.course_id = c.id" => "JOIN training_sessions ts_e2 ON ts_e2.course_id = c.id JOIN enrolments e ON e.training_session_id = ts_e2.id",
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);

file_put_contents($file, $content);
echo "ReportController.php patched successfully.\n";
