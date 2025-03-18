<?php
require '../../api/config.php';
require '../inc/config.php';

$query = "
    SELECT f.id, f.filename, f.displayname, u.username, 
           f.size, f.upload_date, f.views, f.is_nsfw
    FROM files f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.id DESC
";
$result = $mysqli->query($query);
?>

<link rel="stylesheet" href="../assets/css/table.css">
<table>
    <tr>
        <th>ID</th>
        <th>Имя файла</th>
        <th>Предпросмотр</th>
        <th>NSFW Тег</th>
        <th>Пользователь</th>
        <th>Размер</th>
        <th>Дата загрузки</th>
        <th>Просмотры</th>
        <th>Действия</th>
    </tr>
    <?php while($file = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $file['id'] ?></td>
        <td><?= htmlspecialchars($file['filename']) ?></td>
        <td><a href="../../file/?name=<?= $file['filename'] ?>" target="_blank">[ОТКРЫТЬ]</a></td>
        <td><?= $file['is_nsfw'] == 1 ? 'Да' : 'Нет' ?></td>
        <td><?= htmlspecialchars($file['username']) ?></td>
        <td><?= formatBytes($file['size']) ?></td>
        <td><?= date('d.m.Y H:i', $file['upload_date']) ?></td>
        <td><?= $file['views'] ?></td>
        <td>
            <form action="delete_file.php" method="post"
                    onsubmit="return confirm('Удалить файл?')">
                <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                <input type="hidden" name="filename" value="<?= $file['filename'] ?>">
                <button type="submit">Удалить</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<?php
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}
?>