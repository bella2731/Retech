<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$q = "SELECT r.*, u.username FROM reports r
      JOIN users u ON r.user_id = u.id
      ORDER BY r.report_date DESC"; // 🔧 FIXED LINE

$res = $conn->query($q);
?>
<!doctype html>
<title>All Reports</title>
<a href="logout.php">Logout</a>
<table border=1>
  <tr><th>ID</th><th>By</th><th>Title</th><th>Description</th><th>Location</th>
      <th>Status</th><th>Status Details</th><th>Image</th><th>Submitted</th></tr>

<?php while($r = $res->fetch_assoc()): ?>
  <tr>
    <td><?= $r['report_id'] ?></td>
    <td><?= htmlspecialchars($r['username']) ?></td>
    <td><?= htmlspecialchars($r['title']) ?></td>
    <td><?= nl2br(htmlspecialchars($r['description'])) ?></td>
    <td><?= htmlspecialchars($r['location']) ?></td>
    <td><?= $r['status'] ?></td>
    <td><?= nl2br(htmlspecialchars($r['action'])) ?></td>
    <td>
      <?php if (!empty($r['image_path']) && file_exists($r['image_path'])): ?>
        <img src="<?= $r['image_path'] ?>" width=100>
      <?php else: ?>
        No image
      <?php endif; ?>
    </td>
    <td><?= $r['report_date'] ?></td>
  </tr>
<?php endwhile; ?>
</table>
