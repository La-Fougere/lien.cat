<?php
try {
	$pdo = new PDO(
		'mysql:host=127.0.0.1;dbname=liens;charset=utf8',
		'admin',
		'qMH9ymK@zY51Fj',
		[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
	);

	$linkName = basename(dirname(__FILE__));
	$stmt = $pdo->prepare('UPDATE liens SET visited = COALESCE(visited, 0) + 1 WHERE nom = ?');
	$stmt->execute([$linkName]);

	header('Location: https://youtu.be/dQw4w9WgXcQ');
	exit;
} catch (Exception $e) {
	header('Location: https://youtu.be/dQw4w9WgXcQ');
	exit;
}
?>