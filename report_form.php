
<?php 
session_start(); 
$active = 'report_form'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report a Maintenance Issue</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            box-shadow: 0 0 4px rgba(0,0,0,0.1);
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
    </style>
</head>
<body>

  <?php include 'sidebar.php'; ?>

  <div class="content">
    <h2 class="mb-4">Report a Maintenance Issue</h2>

    <!-- ✅ FORM STARTS HERE -->
    <form action="submit_report.php" method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Report Title:</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Issue Description:</label>
        <textarea name="description" class="form-control" rows="4" required></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Urgency Level:</label>
        <select name="urgency_level" class="form-select" required>
          <option value="">-- Select Urgency --</option>
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Location:</label>
        <input type="text" name="location" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Evidence (Image only):</label>
        <input type="file" name="evidence" class="form-control" accept="image/*" required>
      </div>

      <button type="submit" class="btn btn-success">Submit Report</button>
    </form>
    <!-- ✅ FORM ENDS HERE -->

  </div>

</body>
</html>
