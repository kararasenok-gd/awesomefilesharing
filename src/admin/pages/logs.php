<?php 
require '../../api/config.php';
require '../inc/config.php';

$query = "
    SELECT l.*, u.username 
    FROM logs l 
    JOIN users u ON l.user_id = u.id 
    ORDER BY l.timestamp DESC
";
$result = $mysqli->query($query);
?>

<link rel="stylesheet" href="../assets/css/table.css">
<table>
    <tr>
        <th>ID</th>
        <th>Пользователь</th>
        <th>Действие</th>
        <th>Время</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['action']) ?></td>
        <td><?= date('d.m.Y H:i', $row['timestamp']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>