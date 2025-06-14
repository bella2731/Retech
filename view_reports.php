<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$q = "SELECT r.*, u.username FROM reports r
      JOIN users u ON r.user_id = u.id
      ORDER BY r.created_at DESC";
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
    <td><?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['username']) ?></td>
    <td><?= htmlspecialchars($r['title']) ?></td>
    <td><?= nl2br(htmlspecialchars($r['description'])) ?></td>
    <td><?= htmlspecialchars($r['location']) ?></td>
    <td><?= $r['status'] ?></td>
    <td><?= nl2br(htmlspecialchars($r['status_details'])) ?></td>
    <td>
      <?php if ($r['image_path'] && file_exists($r['image_path'])): ?>
        <img src="<?= $r['image_path'] ?>" width=100>
      <?php else: ?>
        No image
      <?php endif; ?>
    </td>
    <td><?= $r['created_at'] ?></td>
  </tr>
<?php endwhile; ?>
</table>
