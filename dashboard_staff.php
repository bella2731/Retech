<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit;
}

require 'db.php';

$username = $_SESSION['username'] ?? 'Staff Member';

// --- Summary counts ---
$countSql = "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed
    FROM reports
";
$countStmt = $conn->prepare($countSql);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countData = $countResult->fetch_assoc();

// --- Chart: Urgency level ---
$urgencySql = "SELECT urgency_level, COUNT(*) as total FROM reports GROUP BY urgency_level";
$urgencyStmt = $conn->prepare($urgencySql);
$urgencyStmt->execute();
$urgencyRes = $urgencyStmt->get_result();
$urgencyData = [];
while ($row = $urgencyRes->fetch_assoc()) {
    $urgencyData[$row['urgency_level']] = $row['total'];
}

// --- Chart: Monthly trends ---
$monthSql = "
    SELECT DATE_FORMAT(report_date, '%b %Y') as month, COUNT(*) as total
    FROM reports
    GROUP BY month
    ORDER BY MIN(report_date)
";
$monthStmt = $conn->prepare($monthSql);
$monthStmt->execute();
$monthRes = $monthStmt->get_result();
$monthlyLabels = $monthlyCounts = [];
while ($row = $monthRes->fetch_assoc()) {
    $monthlyLabels[] = $row['month'];
    $monthlyCounts[] = $row['total'];
}

$active = 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'styles.php'; ?>
    <style>
        .summary-cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .summary-card {
            flex: 1;
            min-width: 200px;
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
        }
        .summary-card h6 {
            font-size: 1rem;
            color: #666;
        }
        .summary-card p {
            font-size: 1.4rem;
            font-weight: bold;
        }
    </style>
</head>

<body class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="content p-4">
        <h3>👋 Welcome <?= htmlspecialchars($username) ?>!</h3>
        <h2 class="mb-4">📊 Dashboard Summary</h2>

        <!-- 🔢 Quick Summary Cards -->
        <div class="summary-cards mb-4">
            <div class="summary-card border-start border-primary border-4">
                <h6>Total Reports</h6>
                <p><?= $countData['total'] ?? 0 ?></p>
            </div>
            <div class="summary-card border-start border-warning border-4">
                <h6>Pending</h6>
                <p><?= $countData['pending'] ?? 0 ?></p>
            </div>
            <div class="summary-card border-start border-info border-4">
                <h6>In Progress</h6>
                <p><?= $countData['in_progress'] ?? 0 ?></p>
            </div>
            <div class="summary-card border-start border-success border-4">
                <h6>Completed</h6>
                <p><?= $countData['completed'] ?? 0 ?></p>
            </div>
        </div>

        <!-- 📈 Charts Section -->
        <div class="row">
            <div class="col-md-6 mb-4 text-center">
                <h5>📌 Urgency Breakdown</h5>
                <div style="max-width: 350px; margin: auto;">
                    <canvas id="urgencyChart"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <h5>📅 Monthly Report Trends</h5>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const urgencyChart = new Chart(document.getElementById('urgencyChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_keys($urgencyData)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($urgencyData)) ?>,
                    backgroundColor: ['#dc3545', '#ffc107', '#198754', '#0d6efd']
                }]
            }
        });

        const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($monthlyLabels) ?>,
                datasets: [{
                    label: 'Reports per Month',
                    data: <?= json_encode($monthlyCounts) ?>,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    </script>
</body>

</html>
