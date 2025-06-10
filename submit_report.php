<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit;
}

// 1. GET FORM INPUT
$title       = $_POST['title'] ?? '';
$desc        = $_POST['description'] ?? '';
$location    = $_POST['location'] ?? '';
$urgency     = $_POST['urgency_level'] ?? '';
$user_id     = $_SESSION['user_id'];

// 2. VALIDATE BASIC
if (empty($title) || empty($desc) || empty($location) || empty($urgency)) {
    die("All fields are required.");
}

// 3. INSERT INTO REPORTS TABLE
$report_stmt = $conn->prepare("
    INSERT INTO reports (user_id, title, description, location, urgency_level, status, report_date)
    VALUES (?, ?, ?, ?, ?, 'Pending', CURDATE())
");
$report_stmt->bind_param("issss", $user_id, $title, $desc, $location, $urgency);
$report_stmt->execute();
$report_id = $report_stmt->insert_id;

// 4. HANDLE FILE UPLOAD (OPTIONAL)
if (!empty($_FILES['evidence']['name'])) {
    $file      = $_FILES['evidence'];
    $filename  = basename($file['name']);
    $ext       = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed   = ['jpg','jpeg','png','gif','webp'];

    if (in_array($ext, $allowed)) {
        $uploadDir  = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $newName   = 'report_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $filePath  = $uploadDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Insert into MEDIA table
            $media_stmt = $conn->prepare("
                INSERT INTO media (report_id, media_type, file_path, upload_date)
                VALUES (?, 'image', ?, CURDATE())
            ");
            $media_stmt->bind_param("is", $report_id, $filePath);
            $media_stmt->execute();
        } else {
            echo "❌ Error uploading file.";
        }
    } else {
        echo "❌ Invalid file type.";
    }
}

// 5. REDIRECT
header("Location: dashboard_staff.php");
exit;
?>
