<?php
require 'c:/xampp/htdocs/Itop_management_system/config/config.php';
require 'c:/xampp/htdocs/Itop_management_system/app/Core/Model.php';
require 'c:/xampp/htdocs/Itop_management_system/app/Models/Content.php';

try {
    $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $model = new \App\Models\Content();
    $model->setDb($db);
    $model->recentActivity();
    $model->filteredAnalytics();
    echo "Dashboard methods tested successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
