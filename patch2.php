<?php
$c = file_get_contents('c:/xampp/htdocs/Itop_management_system/app/Controllers/AdminController.php'); 
$c = str_replace('WHERE course_id = ', 'WHERE training_session_id = ', $c); 
$c = str_replace('e.course_id =', 'e.training_session_id =', $c); 
$c = str_replace('course_id = ?', 'training_session_id = ?', $c); 
$c = str_replace('JOIN courses c ON c.id = e.course_id', 'JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id', $c); 
$c = str_replace('JOIN courses c ON c.id = cert.course_id', 'JOIN training_sessions ts ON ts.id = cert.course_id JOIN courses c ON c.id = ts.course_id', $c); 
file_put_contents('c:/xampp/htdocs/Itop_management_system/app/Controllers/AdminController.php', $c);
echo "AdminController.php patched.\n";

$c = file_get_contents('c:/xampp/htdocs/Itop_management_system/app/Controllers/LmsController.php'); 
$c = str_replace('JOIN courses c ON c.id = e.course_id', 'JOIN training_sessions ts ON ts.id = e.training_session_id JOIN courses c ON c.id = ts.course_id', $c); 
file_put_contents('c:/xampp/htdocs/Itop_management_system/app/Controllers/LmsController.php', $c);
echo "LmsController.php patched.\n";
