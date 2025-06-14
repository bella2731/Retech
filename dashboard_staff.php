<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Staff Member';

// Updated SQL to get media type as well
$sql = "
 SELECT  r.report_id, r.title, r.description, r.location,
         r.urgency_level, r.status, r.report_date,
         ( SELECT file_path FROM MEDIA m
           WHERE m.report_id = r.report_id
           ORDER BY m.media_id ASC
           LIMIT 1
         ) AS evidence,
         ( SELECT media_type FROM MEDIA m
           WHERE m.report_id = r.report_id
           ORDER BY m.media_id ASC
           LIMIT 1
         ) AS media_type
 FROM REPORTS r
 WHERE r.user_id = ?
 ORDER BY r.report_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$countSql = "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed
    FROM REPORTS
    WHERE user_id = ?
";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countData = $countResult->fetch_assoc();

$active = 'dashboard';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #f5f7fa, #c3cfe2);
        }

        .sidebar {
            width: 250px;
            background-color: #008C9E;
            color: white;
            padding: 20px;
        }

        .sidebar h3 {
            border-bottom: 2px solid white;
            padding-bottom: 10px;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .sidebar a:hover,
        .sidebar .active {
            background-color: #00727e;
        }

        .content {
            flex: 1;
            padding: 40px;
        }

        .summary-cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .summary-card {
            background-color: white;
            border-left: 6px solid #008C9E;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 12px;
            min-width: 200px;
            flex: 1;
        }

        .summary-card h6 {
            font-size: 14px;
            font-weight: 600;
            color: #666;
        }

        .summary-card p {
            font-size: 24px;
            margin: 0;
            font-weight: bold;
            color: #333;
        }

        .btn-success {
            border-radius: 8px;
        }

        .report-image {
            max-width: 140px;
            max-height: 100px;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
        }

        .report-video {
            max-width: 140px;
            max-height: 100px;
            border-radius: 6px;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f1f3f5;
            z-index: 1;
        }

        .badge {
            font-size: 0.85rem;
            padding: 6px 12px;
        }

        .media-container {
            position: relative;
            display: inline-block;
        }

        .media-type-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            text-transform: uppercase;
        }

        .fullscreen-modal .modal-dialog {
            max-width: 90vw;
            max-height: 90vh;
        }

        .fullscreen-modal .modal-content {
            height: 80vh;
        }

        .fullscreen-modal img,
        .fullscreen-modal video {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
        }
    </style>
    <?php include 'styles.php'; ?>
</head>

<body class="d-flex">

    <?php include 'sidebar.php'; ?>

    <div class="flex-grow-1 p-4 content">
        <h3 class="mb-4">👋 Welcome, <?= htmlspecialchars($username) ?></h3>
        <h2 class="mb-4">Maintenance Report Summary </h2>

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

        <a href="report_form.php" class="btn btn-success mb-3">➕ Submit New Report</a>

        <?php if ($result->num_rows === 0): ?>
            <div class="alert alert-info">You have not submitted any reports yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
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
                                <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td><?= htmlspecialchars($row['urgency_level']) ?></td>
                                <td>
                                    <?php if ($row['evidence'] && file_exists($row['evidence'])): ?>
                                        <div class="media-container">
                                            <?php if ($row['media_type'] === 'video'): ?>
                                                <video class="report-video" controls preload="metadata" 
                                                       data-bs-toggle="modal" data-bs-target="#mediaModal<?= $row['report_id'] ?>"
                                                       style="cursor: pointer;">
                                                    <source src="<?= htmlspecialchars($row['evidence']) ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <span class="media-type-badge">VIDEO</span>
                                            <?php else: ?>
                                                <img src="<?= htmlspecialchars($row['evidence']) ?>" 
                                                     class="report-image" alt="Evidence"
                                                     data-bs-toggle="modal" data-bs-target="#mediaModal<?= $row['report_id'] ?>"
                                                     style="cursor: pointer;">
                                                <span class="media-type-badge">IMAGE</span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Modal for fullscreen view -->
                                        <div class="modal fade fullscreen-modal" id="mediaModal<?= $row['report_id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Evidence - <?= htmlspecialchars($row['title']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <?php if ($row['media_type'] === 'video'): ?>
                                                            <video controls class="w-100">
                                                                <source src="<?= htmlspecialchars($row['evidence']) ?>" type="video/mp4">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        <?php else: ?>
                                                            <img src="<?= htmlspecialchars($row['evidence']) ?>" 
                                                                 class="img-fluid" alt="Evidence">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="<?= htmlspecialchars($row['evidence']) ?>" 
                                                           class="btn btn-primary" download>
                                                            📥 Download
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No media</span>
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
                                    <a href="edit_form.php?id=<?= $row['report_id'] ?>" class="btn btn-sm btn-primary">
                                        ✏️ Edit Report
                                    </a>
                                    <a href="edit_status.php?id=<?= $row['report_id'] ?>" class="btn btn-sm btn-warning">🛠️ Update Status</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Pause all videos when modal is closed
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(function(modal) {
                modal.addEventListener('hidden.bs.modal', function() {
                    const videos = modal.querySelectorAll('video');
                    videos.forEach(function(video) {
                        video.pause();
                    });
                });
            });
        });
    </script>
</body>

</html>
