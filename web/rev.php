<?php
require_once 'auth.php';

$is_logged_in = isset($_SESSION['user_id']);

// Fetch all reviews from the database
$stmt = $pdo->prepare("SELECT Atsauksme.*, Lietotajs.vards, Lietotajs.uzvards FROM Atsauksme JOIN Lietotajs ON Atsauksme.viesisID = Lietotajs.ID");
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    $tekst = trim($_POST['tekst'] ?? '');
    $vertejums = intval($_POST['vertejums'] ?? 0);
    $attels = $_FILES['attels']['name'] ?? '';

    if (empty($tekst) || $vertejums < 1 || $vertejums > 10) {
        $_SESSION['error'] = 'Atsauksmei jābūt ar tekstu un vērtējumu 1-10 robežās.';
        header('Location: rev.php');
        exit();
    }

    // Handle image upload
    $imagePath = null;
    if (!empty($attels)) {
        $targetDir = "media/atsauksmes/";
        $imagePath = $targetDir . basename($attels);
        if (!move_uploaded_file($_FILES['attels']['tmp_name'], $imagePath)) {
            $_SESSION['error'] = 'Attēla augšupielāde neizdevās.';
            header('Location: rev.php');
            exit();
        }
    }

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO Atsauksme (viesisID, teksts, vertejums, attels) VALUES (:viesisID, :teksts, :vertejums, :attels)");
    $stmt->bindParam(':viesisID', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':teksts', $tekst);
    $stmt->bindParam(':vertejums', $vertejums, PDO::PARAM_INT);
    $stmt->bindParam(':attels', $imagePath);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Atsauksme pievienota veiksmīgi!';
        header('Location: rev.php');
        exit();
    } else {
        $_SESSION['error'] = 'Atsauksmes pievienošana neizdevās.';
        header('Location: rev.php');
        exit();
    }
}

?>


<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atsauksmes</title>
    <link rel="stylesheet" href="media/style.css">
</head>
<body>

<div class="container">
    <?php
    include 'nav.php';
    ?>

    <h1 style="display: flex; justify-content: center;">Atsauksmes</h1>
    <h2 style="display: flex; justify-content: center;"><a href="#" onclick="openReviewModal()">Pievienot atsauksmi</a></h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="reviews">
        <?php foreach ($reviews as $review): ?>
            <div class="review">
                <h3><?= htmlspecialchars($review['vards'] . ' ' . $review['uzvards']) ?></h3>
                <p><?= htmlspecialchars($review['teksts']) ?></p>
                <p>Vērtējums: <?= htmlspecialchars($review['vertejums']) ?>/10</p>
                <?php if (!empty($review['attels'])): ?>
                    <img src="<?= htmlspecialchars($review['attels']) ?>" alt="Atsauksmes attēls" class="review-image">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $is_logged_in = isset($_SESSION['user_id']);
    ?>
    <div id="review-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>

            <?php if ($is_logged_in): ?>
                <!-- Review Form -->
                <form id="review-form" method="POST" action="rev.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="submit_review">
                    <label for="tekst">Atsauksmes teksts:</label>
                    <textarea name="tekst" id="tekst" required></textarea>

                    <label for="vertejums">Vērtējums (1-10):</label>
                    <input type="number" name="vertejums" id="vertejums" min="1" max="10" required>

                    <label for="attels">Pievienot attēlu (nav obligāti):</label>
                    <input type="file" name="attels" id="attels">

                    <button type="submit">Pievienot atsauksmi</button>
                </form>
            <?php else: ?>
                <p>Lai pievienotu atsauksmi, lūdzu pieslēdzieties.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <table>
            <tr>
                <th><h3>REKVIZĪTI</h3></th>
                <th><h3>KONTAKTI</h3></th>
            </tr>
            <tr>
                <td>
                    <p>SIA “Kempings SNIEDZES”<br>
                        Reģ.nr. 40203054474<br>
                        "Sniedzes", Tomes pag., Ķeguma nov., LV-5020<br>
                        Banka: AS "Citadele banka" PARXLV2X<br>
                        Konts: LV13 PARX 0020 1471 7000 1</p>
                <td><p>
                        Telefons: +371 29425800<br>
                        E-pasts: sniedzes@apollo.lv<br>
                        Mājas lapa: www.sniedzes.lv</p></td>
                <td><a href="https://www.facebook.com/sniedzes" target="_blank"><img src="media/starts/fb.png" alt="fb"></a>
                    <a href="https://shorturl.at/XZHDD" target="_blank"><img src="media/starts/gm.png" alt="gm"></a></td>
            </tr>
        </table>
    </footer>
</div>
<script src="script.js"></script>
</body>
</html>
