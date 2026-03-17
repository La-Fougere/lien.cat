<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
	<link rel="stylesheet" href="https://teamgeek.fr/lien/assets/css/main.css" />
	<noscript><link rel="stylesheet" href="https://teamgeek.fr/lien/assets/css/noscript.css" /></noscript>
    <link rel="shortcut icon" href="assets/images/paw.png" type="image/x-icon">
    <title>Admin - lien.cat</title>
</head>
<?php
session_start();

// Connexion à la BDD
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=liens;charset=utf8',
        'admin',
        'supersecretpassword',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die("Erreur de connexion à la base de données");
}

// Vérifier si l'utilisateur est déjà authentifié
if (!isset($_SESSION['admin_authenticated'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === 'supersecretpassword2') {
            $_SESSION['admin_authenticated'] = true;
            header('Location: index.php');
            exit;
        } else {
            $stmt = $pdo->prepare("UPDATE liens SET visited = COALESCE(visited, 0) + 1 WHERE nom = ?");
            $stmt->execute(['admin']);

			header('Location: https://www.youtube.com/watch?v=dQw4w9WgXcQ');
            exit;
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin - Connexion</title>
    </head>

	<style>
	.edit-mode {
		width: 100%;
		max-width: 200px;
	}
	</style>

	<!-- Ajout de la détection de fuseau horaire -->
	<script>
	document.addEventListener('DOMContentLoaded', () => {
		const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
		console.log('Détection JS :', timezone); // Vérifiez dans la console
		
		fetch('set_timezone.php', {
			method: 'POST',
			credentials: 'same-origin', // Important pour les sessions
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: 'timezone=' + encodeURIComponent(timezone)
		})
		.then(r => r.text())
		.then(console.log) // Affiche la réponse du serveur
		.catch(console.error);
	});
	</script>

    <body>
        <div style="text-align: center; display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column;">
            <h2>Connexion Admin</h2>
            <?php if (!empty($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Mot de passe" required>
                <br>
                <button type="submit">Se connecter</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Suppression d'un lien
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $linkName = $_POST['delete'];

    // Supprimer de la base de données
    $stmt = $pdo->prepare("DELETE FROM liens WHERE nom = ?");
    $stmt->execute([$linkName]);

    // Supprimer le dossier
    $folderPath = "../$linkName";
    if (is_dir($folderPath)) {
        array_map('unlink', glob("$folderPath/*"));
        rmdir($folderPath);
    }

    header("Location: index.php");
    exit;
}

// Édition d'un lien
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $oldName = $_POST['old_name'];
    $newName = $_POST['nom'];
    $url = $_POST['url'];
    $visited = (int)$_POST['visited'];
    $createdAt = strtotime($_POST['created_at']);

    // Validation basique
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $newName)) {
        die("Nom invalide : uniquement lettres, chiffres, tirets et underscores");
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        die("URL invalide");
    }

    // Vérifier que le nouveau nom n'existe pas déjà (sauf si c'est le même)
    if ($oldName !== $newName) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM liens WHERE nom = ?");
        $stmt->execute([$newName]);
        if ($stmt->fetchColumn() > 0) {
            die("Ce nom de lien existe déjà !");
        }
    }

    // Mise à jour de la BDD
    $stmt = $pdo->prepare("UPDATE liens 
                          SET nom = ?, url = ?, visited = ?, created_at = ?
                          WHERE nom = ?");
    $stmt->execute([$newName, $url, $visited, $createdAt, $oldName]);

    // Renommer le dossier si le nom a changé
    if ($oldName !== $newName) {
        $oldPath = "../$oldName";
        $newPath = "../$newName";
        if (is_dir($oldPath)) {
            // Renommer le dossier
            rename($oldPath, $newPath);

            // Mettre à jour le fichier index.php à l'intérieur du dossier renommé
            $indexPath = "$newPath/index.php";
            if (file_exists($indexPath)) {
                // Générer le nouveau contenu du fichier index.php
                $indexContent = "<?php
try {
    \$pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=liens;charset=utf8',
        'admin',
        'qMH9ymK@zY51Fj',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    \$linkName = basename(dirname(__FILE__));
    \$stmt = \$pdo->prepare('UPDATE liens SET visited = COALESCE(visited, 0) + 1 WHERE nom = ?');
    \$stmt->execute([\$linkName]);

    header('Location: ' . htmlspecialchars(\"$url\", ENT_QUOTES) . '');
    exit;
} catch (Exception \$e) {
    header('Location: ' . htmlspecialchars(\"$url\", ENT_QUOTES) . '');
    exit;
}
?>";
                file_put_contents($indexPath, $indexContent);
            }
        }
    } else {
        // Si le nom ne change pas mais l'URL oui, mettre à jour le fichier index.php
        $indexPath = "../$newName/index.php";
        if (file_exists($indexPath)) {
            $indexContent = "<?php
try {
    \$pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=liens;charset=utf8',
        'admin',
        'qMH9ymK@zY51Fj',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    \$linkName = basename(dirname(__FILE__));
    \$stmt = \$pdo->prepare('UPDATE liens SET visited = COALESCE(visited, 0) + 1 WHERE nom = ?');
    \$stmt->execute([\$linkName]);

    header('Location: ' . htmlspecialchars(\"$url\", ENT_QUOTES) . '');
    exit;
} catch (Exception \$e) {
    header('Location: ' . htmlspecialchars(\"$url\", ENT_QUOTES) . '');
    exit;
}
?>";
            file_put_contents($indexPath, $indexContent);
        }
    }

    header("Location: index.php");
    exit;
}

// Récupérer les liens
$stmt = $pdo->query("SELECT * FROM liens ORDER BY created_at DESC");
$liens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<body class="is-preload" cz-shortcut-listen="true">

		<!-- Page Wrapper -->
		<div id="page-wrapper">
			<!-- Wrapper -->
			<section id="wrapper">

				<!-- Content -->
				<div class="wrapper">
					<div class="inner">
					<a href="https://lien.cat/">Retourner à lien.cat</a>
					<br><br>
                        <style>
                        /* Centrage du tableau */
                        .table-container {
                            display: flex;
                            justify-content: center;
                            align-items: flex-start;
                            width: 100%;
                            margin-top: 20px;
                        }
                        #liens-table {
                            margin: 0 auto;
                            border-collapse: collapse;
                            background: #1e1f23;
                            min-width: 800px;
                            max-width: 100%;
                        }
                        #liens-table th, #liens-table td {
                            padding: 8px 12px;
                            text-align: left;
                            white-space: nowrap;
                        }
                        /* Colonne URL scrollable */
                        #liens-table td.url-cell {
                            max-width: 350px;
                            min-width: 120px;
                            overflow-x: auto;
                            white-space: nowrap;
                            display: block;
                        }

                        .url-cell-wrapper {
                            -webkit-user-select: none;
                            user-select: none;
                        }
                        .url-text {
                            -webkit-user-select: text;
                            user-select: text;
                            display: inline-block;
                            max-width: 300px;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            white-space: nowrap;
                            vertical-align: middle;
                        }

                        /* width */
                        ::-webkit-scrollbar {
                        width: 3px;
                        }

                        /* Track */
                        ::-webkit-scrollbar-track {
                        background: #0d0d0fff;
                        }

                        /* Handle */
                        ::-webkit-scrollbar-thumb {
                        background: #888;
                        }

                        /* Handle on hover */
                        ::-webkit-scrollbar-thumb:hover {
                        background: #555;
                        }
                        </style>
                        <div class="table-container">
                        <table border="1" id="liens-table">
                            <thead>
                            <tr>
                                <th><button type="button" onclick="sortTable('nom')" style="all:unset;cursor:pointer;">Nom</button></th>
                                <th><button type="button" onclick="sortTable('url')" style="all:unset;cursor:pointer;">URL</button></th>
                                <th><button type="button" onclick="sortTable('date')" style="all:unset;cursor:pointer;">Date de création</button></th>
                                <th><button type="button" onclick="sortTable('visited')" style="all:unset;cursor:pointer;">Visites</button></th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="liens-tbody">
                            <?php foreach ($liens as $lien) : ?>
                            <tr>
                                <form method="POST">
                                    <td data-nom="<?= htmlspecialchars($lien['nom']) ?>">
                                        <span class="view-mode"><?= htmlspecialchars($lien['nom']) ?></span>
                                        <input type="text" name="nom" value="<?= htmlspecialchars($lien['nom']) ?>" 
                                            class="edit-mode" style="display:none;">
                                    </td>
                                    <td data-url-len="<?= strlen($lien['url']) ?>" class="url-cell">
                                        <span class="view-mode url-cell-wrapper" style="display:block;overflow-x:auto;white-space:nowrap;max-width:340px;-webkit-user-select:none;user-select:none;">
                                            <button type="button" class="copy-link-btn" title="Copier le lien" style="margin-right:6px;cursor:pointer;-webkit-user-select:none;user-select:none;">📋</button>
                                            <span class="url-text" style="user-select:text;-webkit-user-select:text;display:inline;"><?= htmlspecialchars($lien['url']) ?></span>
                                        </span>
                                        <input type="url" name="url" value="<?= htmlspecialchars($lien['url']) ?>" 
                                            class="edit-mode" style="display:none;" required>
                                    </td>
                                    <td data-date="<?= $lien['created_at'] ?>">
                                        <span class="view-mode">
                                            <?php
                                            $timezone = $_SESSION['user_timezone'] ?? 'UTC';
                                            $date = new DateTime('@' . $lien['created_at']);
                                            $date->setTimezone(new DateTimeZone($timezone));
                                            echo $date->format('d/m/Y H:i:s');
                                            ?>
                                        </span>
                                        <input type="datetime-local" name="created_at" 
                                            value="<?= date('Y-m-d\TH:i', $lien['created_at']) ?>" 
                                            class="edit-mode" style="display:none;">
                                    </td>
                                    <td data-visited="<?= $lien['visited'] ?>">
                                        <span class="view-mode"><?= $lien['visited'] ?></span>
                                        <input type="number" name="visited" value="<?= $lien['visited'] ?>" 
                                            class="edit-mode" style="display:none; width: 150px;" min="0">
                                    </td>
                                    <td>
                                        <input type="hidden" name="old_name" value="<?= $lien['nom'] ?>">
                                        <!-- Bouton Éditer/Annuler -->
                                        <button type="button" class="view-mode edit-btn" 
                                        onclick="toggleEdit(this)">✏️</button>
                                        <!-- Bouton Supprimer -->
                                        <?php if ($lien['url'] !== 'nope'): ?>
                                        <button type="submit" name="delete" value="<?= $lien['nom'] ?>" 
                                                class="view-mode" onclick="return confirm('Supprimer le lien ?')" 
                                                title="Supprimer le lien">🗑️</button>
                                        <?php endif; ?>
                                        <!-- Bouton Sauvegarder -->
                                        <button type="submit" name="update" value="1" 
                                                class="edit-mode" style="display:none;">💾</button>
                                        <!-- Bouton Annuler -->
                                        <button type="button" class="edit-mode" style="display:none;" 
                                                onclick="toggleEdit(this)">❌</button>
                                    </td>
                                </form>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                        <br>
                        <a href="https://lien.cat/">Retourner à lien.cat</a>
                    </div>
                </div>
            </section>
        </div>
        <script>

            document.querySelectorAll('.url-cell-wrapper').forEach(function(wrapper) {
                const urlSpan = wrapper.querySelector('.url-text');
                if (urlSpan) {
                    wrapper.addEventListener('mousedown', function(e) {
                        if (e.target !== urlSpan) e.preventDefault();
                    });
                }
            });

            function toggleEdit(btn) {
                const row = btn.closest('tr');
                const isEditing = row.classList.toggle('editing');
                row.querySelectorAll('.view-mode').forEach(el => 
                    el.style.display = isEditing ? 'none' : 'inline-block');
                row.querySelectorAll('.edit-mode').forEach(el => 
                    el.style.display = isEditing ? 'inline-block' : 'none');
            }

            // Tri dynamique du tableau
            let sortState = {
                nom: false,
                url: false,
                date: false,
                visited: false
            };

            function sortTable(col) {
                const tbody = document.getElementById('liens-tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                let compareFn;
                let asc = !sortState[col];
                sortState = { nom: false, url: false, date: false, visited: false };
                sortState[col] = asc;

                if (col === 'nom') {
                    compareFn = (a, b) => {
                        const an = a.querySelector('td[data-nom]').getAttribute('data-nom').toLowerCase();
                        const bn = b.querySelector('td[data-nom]').getAttribute('data-nom').toLowerCase();
                        return asc ? an.localeCompare(bn) : bn.localeCompare(an);
                    };
                } else if (col === 'url') {
                    compareFn = (a, b) => {
                        const al = parseInt(a.querySelector('td[data-url-len]').getAttribute('data-url-len'));
                        const bl = parseInt(b.querySelector('td[data-url-len]').getAttribute('data-url-len'));
                        return asc ? al - bl : bl - al;
                    };
                } else if (col === 'date') {
                    compareFn = (a, b) => {
                        const ad = parseInt(a.querySelector('td[data-date]').getAttribute('data-date'));
                        const bd = parseInt(b.querySelector('td[data-date]').getAttribute('data-date'));
                        return asc ? bd - ad : ad - bd; // plus récent d'abord si asc
                    };
                } else if (col === 'visited') {
                    compareFn = (a, b) => {
                        const av = parseInt(a.querySelector('td[data-visited]').getAttribute('data-visited'));
                        const bv = parseInt(b.querySelector('td[data-visited]').getAttribute('data-visited'));
                        return asc ? bv - av : av - bv; // plus vu d'abord si asc
                    };
                }
                rows.sort(compareFn);
                rows.forEach(row => tbody.appendChild(row));
            }

        // Gestion du bouton copier le lien (fix + sélection facile)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.copy-link-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    // Récupère le texte de l'URL dans le span.url-text
                    const urlSpan = btn.parentElement.querySelector('.url-text');
                    const url = urlSpan ? urlSpan.textContent.trim() : '';
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(url).then(function() {
                            btn.textContent = '✅';
                            setTimeout(() => { btn.textContent = '📋'; }, 1200);
                        });
                    } else {
                        // fallback
                        const textarea = document.createElement('textarea');
                        textarea.value = url;
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                        btn.textContent = '✅';
                        setTimeout(() => { btn.textContent = '📋'; }, 1200);
                    }
                });
            });
        });
        </script>

</body></html>
