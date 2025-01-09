<?php
require_once 'auth.php';

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Fetch user data if logged in
$user_data = [];
if ($is_logged_in) {
    try {
        $stmt = $pdo->prepare("SELECT vards, uzvards, telefons, epasts, dzGads FROM Lietotajs WHERE ID = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $_SESSION['error'] = 'Kļūda, ielādējot lietotāja datus: ' . $e->getMessage();
    }
}

// Fetch available cottages
try {
    $stmt = $pdo->prepare("SELECT * FROM Majina WHERE pieejama = 1");
    $stmt->execute();
    $cottages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = 'Kļūda, ielādējot mājiņu datus: ' . $e->getMessage();
    $cottages = [];
}

// Function to send reservation email
function sendReservationEmail($majinaIDs, $viesuSk, $iebrauksana, $izbrauksana, $cena, $pdo) {
    $subject = "Jauna rezervācija";
    $message = "Rezervācija ir veiksmīgi pievienota:\n\n";
    foreach ($majinaIDs as $majinaID) {
        $stmt = $pdo->prepare("SELECT nosaukums, cena FROM Majina WHERE ID = :majinaID");
        $stmt->execute([':majinaID' => $majinaID]);
        $cottage = $stmt->fetch(PDO::FETCH_ASSOC);
        $message .= "Mājiņa: " . htmlspecialchars($cottage['nosaukums']) . "\n";
        $message .= "Cena: " . htmlspecialchars($cottage['cena']) . " EUR/nakti\n\n";
    }
    $message .= "Viesu skaits: " . $viesuSk . "\n";
    $message .= "Iebraukšanas datums: " . $iebrauksana->format('Y-m-d') . "\n";
    $message .= "Izbraukšanas datums: " . $izbrauksana->format('Y-m-d') . "\n";
    $message .= "Kopējā cena: " . $cena . " EUR\n";

    // Specify the email address where the reservation details should be sent
    $to = "elizakanska@gmail.com";  // Change this to the correct email address
    $headers = "From: no-reply@example.com" . "\r\n" . "Content-Type: text/plain; charset=UTF-8";
    mail($to, $subject, $message, $headers);
}

// Handle reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'submit_reservation') {
    $majinaIDs = $_POST['majinaIDs'];  // Array for multiple cottages
    $iebrauksana = new DateTime($_POST['iebrauksana']);
    $izbrauksana = new DateTime($_POST['izbrauksana']);
    $viesuSk = $_POST['viesuSk'];
    $cena = $_POST['cena'];

    if ($iebrauksana >= $izbrauksana) {
        $_SESSION['error'] = 'Izbraukšanas datums jābūt pēc iebrauksanas datuma.';
        header('Location: rez.php');
        exit();
    }

    // Check availability for multiple cottages
    try {
        foreach ($majinaIDs as $majinaID) {
            $checkStmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM Rezervacija 
                WHERE majinaID = :majinaID 
                  AND ((:iebrauksana BETWEEN iebrauksana AND izbrauksana)
                  OR (:izbrauksana BETWEEN iebrauksana AND izbrauksana)
                  OR (iebrauksana BETWEEN :iebrauksana AND :izbrauksana))
            ");
            $checkStmt->execute([
                ':majinaID' => $majinaID,
                ':iebrauksana' => $iebrauksana->format('Y-m-d'),
                ':izbrauksana' => $izbrauksana->format('Y-m-d'),
            ]);
            $conflicts = $checkStmt->fetchColumn();

            if ($conflicts > 0) {
                $_SESSION['error'] = 'Kāds no izvēlētajiem mājiņām nav pieejams izvēlētajos datumos.';
                header('Location: rez.php');
                exit();
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Kļūda pārbaudot pieejamību: ' . $e->getMessage();
        header('Location: rez.php');
        exit();
    }

    // Add reservation for each selected cottage
    try {
        foreach ($majinaIDs as $majinaID) {
            $reservationStmt = $pdo->prepare("
                INSERT INTO Rezervacija (majinaID, viesisID, iebrauksana, izbrauksana, viesuSk, cena) 
                VALUES (:majinaID, :viesisID, :iebrauksana, :izbrauksana, :viesuSk, :cena)
            ");
            $reservationStmt->execute([
                ':majinaID' => $majinaID,
                ':viesisID' => $is_logged_in ? $_SESSION['user_id'] : null,
                ':iebrauksana' => $iebrauksana->format('Y-m-d'),
                ':izbrauksana' => $izbrauksana->format('Y-m-d'),
                ':viesuSk' => $viesuSk,
                ':cena' => $cena,
            ]);
        }

        // Send email with reservation details
        sendReservationEmail($majinaIDs, $viesuSk, $iebrauksana, $izbrauksana, $cena, $pdo);

        $_SESSION['success'] = 'Rezervācija veiksmīgi pievienota!';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Kļūda saglabājot rezervāciju: ' . $e->getMessage();
    }

    header('Location: rez.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervācijas</title>
    <link rel="stylesheet" href="media/style.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const majinaIDs = document.getElementById('majinaIDs');
            const selectedList = document.getElementById('selected-cottages');
            const priceField = document.getElementById('cena');
            const guestCountField = document.getElementById('viesuSk');

            const updateSelectedCottages = () => {
                const selectedOptions = document.querySelectorAll('#majinaIDs option.selected');
                selectedList.innerHTML = ''; // Clear the list

                let totalPricePerNight = 0;
                let totalmaxCilveki = 0;

                selectedOptions.forEach(option => {
                    const pricePerNight = parseFloat(option.dataset.price);
                    const name = option.dataset.name;
                    const maxCilveki = parseInt(option.dataset.maxCilveki);

                    // Add to the selected cottages list
                    const listItem = document.createElement('li');
                    listItem.textContent = `${name} - Cena: ${pricePerNight} EUR/nakti`;
                    selectedList.appendChild(listItem);

                    // Sum up the total price per night and total max guests
                    if (!isNaN(pricePerNight)) {
                        totalPricePerNight += pricePerNight;
                    }
                    if (!isNaN(maxCilveki)) {
                        totalmaxCilveki += maxCilveki;
                    }
                });

                calculateTotalPrice(totalPricePerNight);
            };

            const calculateTotalPrice = (totalPricePerNight) => {
                const startDate = new Date(document.getElementById('iebrauksana').value);
                const endDate = new Date(document.getElementById('izbrauksana').value);

                if (!startDate || !endDate || endDate <= startDate) {
                    priceField.value = '0.00'; // Reset total price if dates are invalid
                    return;
                }

                const nights = (endDate - startDate) / (1000 * 60 * 60 * 24);
                let totalPrice = nights * totalPricePerNight;

                priceField.value = totalPrice > 0 ? totalPrice.toFixed(2) : '0.00';
            };

            // Toggle selected class on click to add/remove cottages
            majinaIDs.addEventListener('click', (e) => {
                if (e.target.tagName === 'OPTION') {
                    e.target.classList.toggle('selected');
                    updateSelectedCottages();
                }
            });

            document.getElementById('iebrauksana').addEventListener('input', () => updateSelectedCottages());
            document.getElementById('izbrauksana').addEventListener('input', () => updateSelectedCottages());
            guestCountField.addEventListener('input', () => updateSelectedCottages());
        });
    </script>

</head>
<body>
<div class="container">
    <?php include 'nav.php'; ?>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="reservation-section">
        <h1>Pieteikt rezervāciju</h1>
        <form action="rez.php" method="POST">
            <input type="hidden" name="action" value="submit_reservation">

            <label for="iebrauksana">Iebraukšanas datums:</label>
            <input type="date" name="iebrauksana" id="iebrauksana" required min="<?= date('d-m-y'); ?>">

            <label for="izbrauksana">Izbraukšanas datums:</label>
            <input type="date" name="izbrauksana" id="izbrauksana" required min="<?= date('d-m-y'); ?>">


            <label for="majinaIDs">Izvēlies mājiņu(as):</label>
            <select id="majinaIDs" name="majinaIDs[]" multiple size="5">
                <?php foreach ($cottages as $cottage): ?>
                    <option value="<?= $cottage['ID']; ?>" data-price="<?= $cottage['cena']; ?>" data-name="<?= htmlspecialchars($cottage['nosaukums']); ?>">
                        <?= htmlspecialchars($cottage['nosaukums']); ?> - <?= htmlspecialchars($cottage['maxCilveki']); ?> vietīga. Cena - <?= htmlspecialchars($cottage['cena']); ?>/EUR par nakti
                    </option>
                <?php endforeach; ?>
            </select>

            <ul id="selected-cottages"></ul>

            <label for="vards">Vārds:</label>
            <input type="text" name="vards" id="vards" value="<?= htmlspecialchars($user_data['vards'] ?? ''); ?>" required>

            <label for="uzvards">Uzvārds:</label>
            <input type="text" name="uzvards" id="uzvards" value="<?= htmlspecialchars($user_data['uzvards'] ?? ''); ?>" required>

            <label for="telefons">Telefons:</label>
            <input type="text" name="telefons" id="telefons" value="<?= htmlspecialchars($user_data['telefons'] ?? ''); ?>" required>

            <label for="epasts">E-pasts:</label>
            <input type="email" name="epasts" id="epasts" value="<?= htmlspecialchars($user_data['epasts'] ?? ''); ?>" required>

            <label for="dzimGads">Dzimšanas gads:</label>
            <input type="number" name="dzimGads" id="dzimGads" value="<?= htmlspecialchars($user_data['dzGads'] ?? ''); ?>" required>

            <label for="viesuSk">Plānotais viesu skaits:</label>
            <input type="number" name="viesuSk" id="viesuSk" min="1" required>

            <label for="cena">Kopējā cena:</label>
            <input type="text" name="cena" id="cena" readonly>

            <button type="submit">Rezervēt</button>
        </form>

    </div>

    <footer>
        <table>
            <tr>
                <th><h3>REKVIZĪTI</h3></th>
                <th><h3>KONTAKTI</h3></th>
            </tr>
            <tr>
                <td>
                    <p>SIA “Kempings SNIEDZES”</br>
                        Reģ.nr. 40203054474</br>
                        "Sniedzes", Tomes pag., Ķeguma nov., LV-5020</br>
                        Banka: AS "Citadele banka" PARXLV2X</br>
                        Konts: LV13 PARX 0020 1471 7000 1</p>
                </td>
                <td><p>
                        Telefons: +371 29425800</br>
                        E-pasts: sniedzes@apollo.lv</br>
                        Mājas lapa: www.sniedzes.lv</p></td>
                <td><a href="https://www.facebook.com/sniedzes" target="_blank"><img src="media/starts/fb.png"></a>
                    <a href="https://shorturl.at/XZHDD" target="_blank"><img src="media/starts/gm.png"></a></td>
            </tr>
        </table>
    </footer>
</div>
<script src="script.js"></script>
</body>
</html>
