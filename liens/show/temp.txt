<?php
session_start();

// Détecter le nom du dossier contenant ce fichier
$linkName = basename(dirname(__FILE__));

// Connexion à la base de données
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=liens;charset=utf8',
        'admin',
        'qMH9ymK@zY51Fj',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('<div style="color: #ff5252; text-align: center; margin-top: 2em;">Erreur de connexion : ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Récupération des infos du lien
$stmt = $pdo->prepare('SELECT visited, created_at, del_password FROM liens WHERE nom = :nom');
$stmt->execute(['nom' => $linkName]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die('<div style="color: #ff5252; text-align: center; margin-top: 2em;">Lien introuvable</div>');
}

$visited   = (int)$result['visited'];
$createdAt = !empty($result['created_at']) ? date('d/m/Y', $result['created_at']) : null;
$dbPassword = $result['del_password'];
$adminPassword = 'y0JlCHH2&*jV&ynoh1xUYTk##Rj@LKumhJ!WdmtbXBuwO^gEdu$4mBf7RfhHIU^&e2OdMQ6Hq$X$d$Uep7xODZrHCdk%qsLBA5@h';

// Vérifier l'authentification
if (!isset($_SESSION['auth_'.$linkName])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $dbPassword || $_POST['password'] === $adminPassword) {
            $_SESSION['auth_'.$linkName] = true;
            header("Location: index.php");
            exit;
        } else {
            $error_message = "Code incorrect";
        }
    }

    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="https://teamgeek.fr/lien/assets/css/main.css" />
        <noscript><link rel="stylesheet" href="https://teamgeek.fr/lien/assets/css/noscript.css" /></noscript>
        <link rel="shortcut icon" href="assets/images/paw.png" type="image/x-icon">
        <title>Accès protégé - lien.cat</title>
    </head>
    <body>
        <div style="text-align: center; display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column;">
            <h2>Page protégée</h2>
            <p><em>Entrez ici votre code unique obtenu lors de la création du lien</em></p>
            <?php if (!empty($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Code" required>
                <br>
                <button type="submit">Se connecter</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Visites de https://lien.cat/<?= htmlspecialchars($linkName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <style>
        body {
            background: #181c23;
            color: #f2f2f2;
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Fira Mono', 'Consolas', monospace;
            padding: 1rem;
            box-sizing: border-box;
        }
        .box {
            background: #232834;
            border-radius: 14px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.25);
            padding: 40px 32px;
            min-width: 280px;
            max-width: 90vw;
            text-align: center;
            word-wrap: break-word;
        }
        .label {
            font-size: 1.1em;
            letter-spacing: 0.03em;
            color: #8ab4f8;
            margin-bottom: 18px;
            display: block;
        }
        .value {
            font-size: 2.3em;
            font-weight: bold;
            color: #00e676; /* couleur initiale */
            letter-spacing: 0.04em;
            transition: font-size 0.2s;
            display: inline-block;
            user-select: none;
        }
        .created-at {
            display: block;
            margin-top: 18px;
            color: #b0b8c1;
            font-size: 0.98em;
            letter-spacing: 0.01em;
        }
        @media (max-width: 480px) {
            .value {
                font-size: 1.8em;
            }
            .box {
                padding: 30px 20px;
                min-width: 220px;
            }
            .label {
                font-size: 1em;
                margin-bottom: 14px;
            }
            .created-at {
                font-size: 0.93em;
                margin-top: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="box" role="main" aria-label="Nombre de visites de https://lien.cat/<?= htmlspecialchars($linkName) ?>">
        <span class="label">Nombre de visites de <b>https://lien.cat/<?= htmlspecialchars($linkName) ?></b> :</span>
        <span class="value" id="animated-number">0</span>
        <?php if ($createdAt): ?>
            <span class="created-at">Créé le <?= htmlspecialchars($createdAt) ?></span>
        <?php endif; ?>
    </div>
    <script>
        const realValue = <?= $visited ?>;
        const maxValue = (realValue === 0)
            ? Math.floor(Math.random() * (1234 - 1000 + 1)) + 1000
            : realValue * (Math.floor(Math.random() * (1234 - 1000 + 1)) + 1000);
        const startFontSize = 2.3; // em
        const maxFontSize = 10;    // em
        const upDuration = 2000;   // ms
        const idleDuration = 1000; // ms
        const downDuration = 3000; // ms

        const valueElem = document.getElementById('animated-number');
        let animationFrameId = null;
        let animationSkipped = false;
        let animationPhase = 'up';
        let idleTimeoutId = null;

        function easeInExpo(t) {
            return t === 0 ? 0 : Math.pow(2, 10 * (t - 1));
        }

        function interpolateColor(t) {
            const start = {r: 0, g: 230, b: 118};
            const end = {r: 255, g: 59, b: 59};
            const r = Math.round(start.r + (end.r - start.r) * t);
            const g = Math.round(start.g + (end.g - start.g) * t);
            const b = Math.round(start.b + (end.b - start.b) * t);
            return `rgb(${r},${g},${b})`;
        }

        function animate({from, to, duration, onUpdate, onComplete, easing}) {
            const start = performance.now();
            function frame(now) {
                if (animationSkipped) return;
                let t = Math.min(1, (now - start) / duration);
                if (easing) t = easing(t);
                const current = from + (to - from) * t;
                onUpdate(current, t);
                if (t < 1) {
                    animationFrameId = requestAnimationFrame(frame);
                } else if (onComplete) {
                    onComplete();
                }
            }
            animationFrameId = requestAnimationFrame(frame);
        }

        function showFinalValue() {
            animationSkipped = true;
            if (animationFrameId) cancelAnimationFrame(animationFrameId);
            if (idleTimeoutId) clearTimeout(idleTimeoutId);
            valueElem.textContent = realValue;
            valueElem.style.fontSize = startFontSize + 'em';
            valueElem.style.color = 'rgb(0,230,118)';
            animationPhase = 'done';
        }

        function startAnimation() {
            animationPhase = 'up';
            animate({
                from: 0,
                to: maxValue,
                duration: upDuration,
                easing: easeInExpo,
                onUpdate: (val, t) => {
                    valueElem.textContent = Math.round(val);
                    valueElem.style.fontSize = (startFontSize + (maxFontSize - startFontSize) * t).toFixed(2) + 'em';
                    valueElem.style.color = interpolateColor(t);
                },
                onComplete: () => {
                    if (animationSkipped) return;
                    animationPhase = 'idle';
                    idleTimeoutId = setTimeout(() => {
                        if (animationSkipped) return;
                        animationPhase = 'down';
                        animate({
                            from: maxValue,
                            to: realValue,
                            duration: downDuration,
                            easing: easeInExpo,
                            onUpdate: (val, t) => {
                                valueElem.textContent = Math.round(val);
                                valueElem.style.fontSize = (maxFontSize - (maxFontSize - startFontSize) * t).toFixed(2) + 'em';
                                valueElem.style.color = interpolateColor(1 - t);
                            },
                            onComplete: () => {
                                showFinalValue();
                            }
                        });
                    }, idleDuration);
                }
            });
        }

        valueElem.addEventListener('click', function () {
            if (animationPhase !== 'done') {
                showFinalValue();
            }
        });

        startAnimation();
    </script>
</body>
</html>
