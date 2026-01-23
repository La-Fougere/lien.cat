<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="assets/images/paw.png" type="image/x-icon">
        <link rel="stylesheet" href="assets/css/style.css">
        <title>Lien.cat</title>
    </head>
    <?php
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['delete']) && isset($_POST['delPassword'])) {
		try {
			$pdo = new PDO(
				'mysql:host=127.0.0.1;dbname=liens;charset=utf8',
				'admin',
				'qMH9ymK@zY51Fj',
				[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
			);

			$delPassword = $_POST['delPassword'];
			
			// Find the link with this deletion password
			$stmt = $pdo->prepare('SELECT nom FROM liens WHERE del_password = ?');
			$stmt->execute([$delPassword]);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$result) {
				throw new Exception('Clé de suppression invalide ou lien déjà supprimé');
			}

			$linkName = $result['nom'];
			
			// Delete the directory and its contents
			if (is_dir($linkName)) {
				unlink("$linkName/index.php");
				rmdir($linkName);
			}

            if (is_dir(__DIR__ . "/show/$linkName")) {
                unlink(__DIR__ . "/show/$linkName/index.php");
                rmdir(__DIR__ . "/show/$linkName");
            }

			// Delete from database
			$stmt = $pdo->prepare('DELETE FROM liens WHERE del_password = ?');
			$stmt->execute([$delPassword]);

			$success_message = "Le lien: <span style=\"color: rgb(255 128 78);\">lien.cat/$linkName</span> a été supprimé avec succès !";

		} catch (Exception $e) {
			$error_message = htmlspecialchars($e->getMessage());
		}
	} else {
		try {
			$pdo = new PDO(
				'mysql:host=127.0.0.1;dbname=liens;charset=utf8',
				'admin',
				'qMH9ymK@zY51Fj',
				[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
			);

			$linkName = $_POST['linkName'] ?? '';
			$link = $_POST['link'] ?? '';

			if (!filter_var($link, FILTER_VALIDATE_URL)) {
				throw new Exception('Le lien fourni n\'est pas une URL valide');
			}

			if (empty($linkName)) {
				$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$linkName = '';
				for ($i = 0; $i < 4; $i++) {
					$linkName .= $characters[rand(0, strlen($characters) - 1)];
				}
			}

			if (strlen($linkName) > 250) {
				throw new Exception('Le nom du lien est trop long');
			}

			if (!preg_match('/^[a-zA-Z0-9_-]+$/', $linkName)) {
				throw new Exception('Le nom du lien contient des caractères invalides');
			}

			$stmt = $pdo->prepare('SELECT COUNT(*) FROM liens WHERE nom = ?');
			$stmt->execute([$linkName]);
			if ($stmt->fetchColumn() > 0) {
				throw new Exception('Ce nom de lien existe déjà');
			}

			// Generate unique deletion password
			do {
				$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				$delPassword = '';
				for ($i = 0; $i < 20; $i++) {
					$delPassword .= $characters[rand(0, strlen($characters) - 1)];
				}
				$stmt = $pdo->prepare('SELECT COUNT(*) FROM liens WHERE del_password = ?');
				$stmt->execute([$delPassword]);
			} while ($stmt->fetchColumn() > 0);

			if (!mkdir($linkName, 0755) && !is_dir($linkName)) {
				throw new Exception('Impossible de créer le dossier');
			}

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

                header('Location: ' . htmlspecialchars(\"$link\", ENT_QUOTES) . '');
				exit;
			} catch (Exception \$e) {
				// echo 'Error: ' . \$e->getMessage();
                header('Location: ' . htmlspecialchars(\"$link\", ENT_QUOTES) . '');
				exit;
			}
			?>";
			if (!file_put_contents("$linkName/index.php", $indexContent)) {
				throw new Exception('Impossible de créer le fichier index.php');
			}

			$currentTimestamp = time();
			$stmt = $pdo->prepare('INSERT INTO liens (nom, url, created_at, visited, del_password) VALUES (?, ?, ?, ?, ?)');
			$stmt->execute([$linkName, $link, $currentTimestamp, 0, $delPassword]);

            // Créer le dossier dans "show"
            if (!is_dir(__DIR__ . "/show/$linkName")) {
                if (!mkdir(__DIR__ . "/show/$linkName", 0755, true)) {
                    throw new Exception('Impossible de créer le dossier dans show/');
                }
            }

            // Copier le fichier template
            if (!copy(__DIR__ . "/show/test/index.php", __DIR__ . "/show/$linkName/index.php")) {
                throw new Exception('Impossible de copier le fichier template');
            }

			$success_message = "Lien créé avec succès ! Voici ton lien (clique pour le copier): <a href='https://lien.cat/$linkName' onclick='navigator.clipboard.writeText(this.href); return false;'>lien.cat/$linkName</a>
			<br>Garde le précieusement, je ne vais pas te le redonner :)
            <br>Pour traquer les visites de ton lien, rends-toi sur: <a href='https://lien.cat/show/$linkName' onclick='navigator.clipboard.writeText(this.href); return false;'>lien.cat/show/$linkName</a> avec ta clé
			<br>Voici ta clé unique: <span onclick='navigator.clipboard.writeText(this.textContent); return false;' style='cursor: pointer; text-decoration: underline; color: rgb(255 128 78);'>$delPassword</span> (pour accéder au nombre de visites ou supprimer ce lien)";

		} catch (Exception $e) {
			$error_message = htmlspecialchars($e->getMessage());
		}
	}
}
?>
    <body>

        <nav class="navigation">
            <div class="mobile-logo">
                <a href="#">
                    <img src="assets/images/cat.png" id="logo" alt="Kitten Studio">
                </a>
            </div>

            <div class="mobile-menu">Menu</div>

            <div class="wrapper">
                <ul>
                    <li><a href="https://teamgeek.fr/" target="_blank">TeamGeek.fr</a></li>
                    <li><a href="https://guns.lol/lafougere" target="_blank">Moi et mes liens</a></li>
                    <!-- <li><a href="https://teamgeek.fr/mp3/" target="_blank">Commande Boombox</a></li> -->
                </ul>
                <div class="logo">
                    <a href="">
                        <img src="assets/images/paw.png" id="logo" alt="Kitten Studio">
                    </a>
                </div>
                <ul>
                    <li><a href="https://lien.cat/discordcat" target="_blank">Discord</a></li>
                    <li><a href="https://www.youtube.com/@thefougere" target="_blank">Youtube</a></li>
                    <li><a href="https://tipeee.com/lafougere" target="_blank">Un petit don ?</a></li>
                </ul>
            </div>
        </nav>

        <main>
            <div class="welcome center">
                <a href="https://lien.cat/" style="color: inherit;"><h1>Lien.<img src="assets/images/cat-logo.png" alt="cat" style="height: 2em; vertical-align: -20px;">
                <style>
                    @media screen and (max-width: 768px) {
                        img[alt="cat"] {
                            vertical-align: -15px !important;
                        }
                    }
                </style>
                </h1></a>
                <p style="text-transform: none;">
                    Le racourcisseur de liens le plus mignon du monde !
                </p>
                <!-- Wrapper -->
                <section id="wrapper">

                    <!-- Content -->
                    <div class="wrapper">
                        <div class="inner">
                        <h2 style="padding-bottom: 10px;">Créer un lien</h2>
                            <form method="POST" action="">
                                <table style="display: flex; flex-direction: column; align-items: center;">
                                    <tr>
                                        <td style="padding-right: 10px;">
                                            <label for="link" style="font-size: 20px;">Lien de destination</label>
                                        </td>
                                        <td>
                                            <input type="text" name="link" id="link" required class="btn btn-secondary" style="font-family: arial; border-radius: 5px; margin: 0; height: fit-content; width: 130%; padding: 5px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-right: 10px; margin-top: 10px;">
                                            <label for="linkName" style="font-size: 20px;">Nom du lien.cat</label>
                                        </td>
                                        <td>
                                            <div style="position: relative; width: 130%; margin-top: 10px;">
                                                <span style="position: absolute; left: 5px; top: 50%; transform: translateY(-50%); z-index: 1; font-family: arial; font-weight: bold;">lien.cat/</span>
                                                <input type="text" name="linkName" id="linkName" class="btn btn-secondary" style="font-family: arial; border-radius: 5px; margin: 0; height: fit-content; width: 100%; padding: 5px; padding-left: 65px;">
                                            </div>
                                            <style>
                                                @media screen and (max-width: 768px) {
                                                    input[name="link"] {
                                                        width: 100% !important;
                                                    }
                                                    div[style*="width: 130%"] {
                                                        width: 100% !important;
                                                    }
                                                }
                                            </style>
                                        </td>
                                    </tr>
                                </table>
                                <strong style="color: red; font-family: arial; font-size: medium; text-transform: none;"><?php echo $error_message; ?></strong>
                                <strong style="color: green; font-family: arial; font-size: medium; text-transform: none;"><?php echo $success_message; ?></strong>
                                <div class="form-group" style="padding-top: 20px; display: flex; gap: 10px; justify-content: center;">
                                    <input type="submit" value="Donne mon lien !" class="btn btn-secondary">
                                </form>
                                    <button onclick="deleteLinkPrompt()" class="btn btn-primary" style="width: fit-content;">Supprimer un lien</button>
                                </div>
                                <script>
                                function deleteLinkPrompt() {
                                    const delKey = prompt("Entrez la clé de suppression:");
                                    if (delKey) {
                                        if (confirm('Êtes-vous sûr de vouloir supprimer le lien lié à cette clé ?')) {
                                            const form = document.createElement('form');
                                            form.method = 'POST';
                                            form.innerHTML = `
                                                <input type="hidden" name="delPassword" value="${delKey}">
                                                <input type="hidden" name="delete" value="1">
                                            `;
                                            document.body.appendChild(form);
                                            form.submit();
                                        }
                                    }
                                }
                                </script>
                            </div>
                        </div>
                    </div>

                    </section>
            </div>

            <div class="kitties">
                <img src="assets/images/kittens.png" alt="#Kittens" draggable="false">
            </div>
        </main>


        <script src="assets/js/app.js"></script>
    </body>
</html>