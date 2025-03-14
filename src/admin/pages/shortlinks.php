<?php
require '../../api/config.php';
require '../inc/config.php';

$result = $mysqli->query("SELECT * FROM short");
?>

<link rel="stylesheet" href="../assets/css/table.css">
<table>
    <tr>
        <th>ID</th>
        <th>Код</th>
        <th>Файл</th>
        <th>Истекает</th>
        <th>Действия</th>
    </tr>
    <?php while($link = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $link['id'] ?></td>
        <td><?= htmlspecialchars($link['code']) ?></td>
        <td><?= htmlspecialchars($link['filename']) ?></td>
        <td><?= date('d.m.Y H:i', $link['expire']) ?></td>
        <td>
            <form action="delete_short.php" method="post"
                  onsubmit="return confirm('Удалить ссылку?')">
                <input type="hidden" name="short_id" value="<?= $link['id'] ?>">
                <button type="submit">Удалить</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>