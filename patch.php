<?php
$c = file_get_contents('c:/xampp/htdocs/Itop_management_system/app/Models/User.php'); 
$c = str_replace('JOIN courses co ON co.id = e.course_id', 'JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses co ON co.id = ts.course_id', $c); 
file_put_contents('c:/xampp/htdocs/Itop_management_system/app/Models/User.php', $c);
echo "User.php patched.\n";

$c = file_get_contents('c:/xampp/htdocs/Itop_management_system/app/Models/MasterData.php');
$c = str_replace('leftJoin(\'courses c\', \'c.id\', \'=\', \'ts.course_id\')', 'leftJoin(\'courses c\', \'c.id\', \'=\', \'ts.course_id\')', $c); // Already joined properly? Wait, let's look at MasterData.php.
// Actually I'll do the replacements manually using PHP script for everything.

// Lms.php
$c = file_get_contents('c:/xampp/htdocs/Itop_management_system/app/Models/Lms.php');
$c = str_replace('join(\'courses c\', \'c.id\', \'=\', \'a.course_id\')', 'join(\'training_sessions ts\', \'ts.id\', \'=\', \'a.course_id\')->join(\'courses c\', \'c.id\', \'=\', \'ts.course_id\')', $c); 
file_put_contents('c:/xampp/htdocs/Itop_management_system/app/Models/Lms.php', $c);
echo "Lms.php patched.\n";

// Evaluation.php
$c = file_get_contents('c:/xampp/htdocs/Itop_management_system/app/Models/Evaluation.php');
$c = str_replace('join(\'courses c\', \'c.id\', \'=\', \'e.course_id\')', 'join(\'training_sessions ts\', \'ts.id\', \'=\', \'e.training_session_id\')->join(\'courses c\', \'c.id\', \'=\', \'ts.course_id\')', $c);
file_put_contents('c:/xampp/htdocs/Itop_management_system/app/Models/Evaluation.php', $c);
echo "Evaluation.php patched.\n";

// Certificate.php
$c = file_get_contents('c:/xampp/htdocs/Itop_management_system/app/Models/Certificate.php');
$c = str_replace('join(\'courses c\', \'c.id\', \'=\', \'c.course_id\')', 'join(\'training_sessions ts\', \'ts.id\', \'=\', \'c.course_id\')->join(\'courses crs\', \'crs.id\', \'=\', \'ts.course_id\')', $c); 
$c = str_replace('join(\'courses c\', \'c.id\', \'=\', \'cert.course_id\')', 'join(\'training_sessions ts\', \'ts.id\', \'=\', \'cert.course_id\')->join(\'courses c\', \'c.id\', \'=\', \'ts.course_id\')', $c); 
file_put_contents('c:/xampp/htdocs/Itop_management_system/app/Models/Certificate.php', $c);
echo "Certificate.php patched.\n";

// Content.php
$c = file_get_contents('c:/xampp/htdocs/Itop_management_system/app/Models/Content.php');
$c = str_replace('JOIN courses ON courses.id = enrolments.course_id', 'JOIN training_sessions ON training_sessions.id = enrolments.training_session_id JOIN courses ON courses.id = training_sessions.course_id', $c);
$c = str_replace('SUM(courses.fee)', 'SUM(training_sessions.fee)', $c);
$c = str_replace('JOIN courses c ON c.id = e.course_id', 'JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id', $c);
$c = str_replace('JOIN courses c ON c.id = cert.course_id', 'JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id', $c);
file_put_contents('c:/xampp/htdocs/Itop_management_system/app/Models/Content.php', $c);
echo "Content.php patched.\n";
