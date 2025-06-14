<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Staff';

// 1. Get report ID from URL
$report_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$report_id) {
    die('Invalid report ID');
}

// 2. Check if report belongs to logged-in staff
$stmt = $conn->prepare("SELECT * FROM REPORTS WHERE report_id = ? AND user_id = ?");
$stmt->bind_param("ii", $report_id, $user_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    die("Report not found or not authorized.");
}

// 3. Fetch related logs
$logs_stmt = $conn->prepare("SELECT * FROM LOGS WHERE report_id = ? ORDER BY created_at ASC");
$logs_stmt->bind_param("i", $report_id);
$logs_stmt->execute();
$logs_result = $logs_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Status History - Report #<?= $report_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <a href="dashboard_staff.php" class="btn btn-secondary mb-4">&larr; Back to Dashboard</a>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">🕒 Status History for Report #<?= $report_id ?></h4>
        </div>
        <div class="card-body">

            <h5 class="text-secondary">📝 Report Information</h5>
            <ul class="list-group mb-4">
                <li class="list-group-item"><strong>Title:</strong> <?= htmlspecialchars($report['title']) ?></li>
                <li class="list-group-item"><strong>Description:</strong> <?= nl2br(htmlspecialchars($report['description'])) ?></li>
                <li class="list-group-item"><strong>Current Status:</strong> <?= htmlspecialchars($report['status']) ?></li>
                <li class="list-group-item"><strong>Created At:</strong> <?= date("d M Y, h:i A", strtotime($report['report_date'])) ?></li>
            </ul>

            <h5 class="text-secondary">📜 Status Logs</h5>
            <?php if ($logs_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Status Details</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($log = $logs_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($log['status_details']) ?></td>
                                    <td><?= date("d M Y, h:i A", strtotime($log['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No log entries found for this report.</div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
