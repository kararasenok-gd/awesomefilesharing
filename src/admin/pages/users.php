<?php 
require '../../api/config.php';
require '../inc/config.php';

$query = "
    SELECT u.id, u.username, u.email, u.active, 
           (SELECT COUNT(*) FROM files WHERE user_id = u.id) AS file_count
    FROM users u
";
$result = $mysqli->query($query);
?>

<link rel="stylesheet" href="../assets/css/table.css">
<table>
    <tr>
        <th>ID</th>
        <th>Логин</th>
        <th>Email</th>
        <th>Активен</th>
        <th>Файлов</th>
        <th>Действия</th>
    </tr>
    <?php while($user = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $user['id'] ?></td>
        <td><?= htmlspecialchars($user['username']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= $user['active'] ? 'Да' : 'Нет' ?></td>
        <td><?= $user['file_count'] ?></td>
        <td>
            <form action="delete_user.php" method="post" 
                  onsubmit="return confirm('Удалить пользователя и все файлы?')">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <button type="submit">Удалить</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>