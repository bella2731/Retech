<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'] ?? 'Admin';

// Ambil semua laporan
$sql = "
 SELECT  r.report_id, r.title, r.description, r.location,
         r.urgency_level, r.status, r.report_date,
         u.username,
         ( SELECT file_path FROM MEDIA m
           WHERE m.report_id = r.report_id
           ORDER BY m.media_id ASC
           LIMIT 1
         ) AS evidence
 FROM REPORTS r
 JOIN USERS u ON r.user_id = u.user_id
 ORDER BY r.report_date DESC
";
$result = $conn->query($sql);

// Kira jumlah laporan
$countSql = "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed
    FROM REPORTS
";
$countResult = $conn->query($countSql);
$countData = $countResult->fetch_assoc();

$active = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* [Same CSS as your original version] */
    </style>
    <?php include 'styles.php'; ?>
</head>
<body class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="flex-grow-1 p-4 content">
        <h3 class="mb-4">👋 Welcome, <?= htmlspecialchars($username) ?></h3>
        <h2 class="mb-4">All Maintenance Reports</h2>

        <!-- Summary cards -->
        <div class="summary-cards">
            <div class="summary-card border-start border-primary">
                <h6>Total Reports</h6>
                <p><?= $countData['total'] ?></p>
            </div>
            <div class="summary-card border-start border-warning">
                <h6>Pending</h6>
                <p><?= $countData['pending'] ?></p>
            </div>
            <div class="summary-card border-start border-info">
                <h6>In Progress</h6>
                <p><?= $countData['in_progress'] ?></p>
            </div>
            <div class="summary-card border-start border-success">
                <h6>Completed</h6>
                <p><?= $countData['completed'] ?></p>
            </div>
        </div>

        <?php if ($result->num_rows === 0): ?>
            <div class="alert alert-info">No reports submitted yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>User</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th>Urgency</th>
                            <th>Evidence</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['report_id'] ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td><?= htmlspecialchars($row['urgency_level']) ?></td>
                                <td>
                                    <?php if ($row['evidence'] && file_exists($row['evidence'])): ?>
                                        <img src="<?= htmlspecialchars($row['evidence']) ?>" class="report-image" alt="Evidence">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badge = [
                                        'Pending' => 'warning text-dark',
                                        'In Progress' => 'info text-dark',
                                        'Completed' => 'success'
                                    ][$row['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= $row['status'] ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($row['report_date'])) ?></td>
                                <td>
                                    <a href="edit_status.php?id=<?= $row['report_id'] ?>" class="btn btn-sm btn-warning">🛠️ Update Status</a>
                                    <a href="delete_report.php?id=<?= $row['report_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
