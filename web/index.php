<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camping Map Gallery</title>
    <link rel="stylesheet" href="media/style.css">
</head>
<body>
<div class="container">
    <header class="navbar">
        <img src="media/logo.png" alt="Logo" class="logo">
        <nav>
            <a href="#">Sākums</a>
            <a href="#">Atsauksmes</a>
            <a href="#">Rezervēt mājiņu</a>
            <a href="#" onclick="handleAuthAction(<?php echo json_encode($is_logged_in); ?>)">
                <?php echo $is_logged_in ? 'Atslēgties' : 'Pieslēgties'; ?>
            </a>

        </nav>
    </header>

    <!-- Display Session Messages -->
    <div class="messages">
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="success-message">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        ?>
    </div>

    <main class="gallery-container">
        <div class="arrow left-arrow" onclick="prevImage()">&#10094;</div>

        <div class="main-image">
            <img id="center-image" src="media/starts/kempinga_karte.jpg" alt="Kempinga karte" onclick="openModal('Kempinga karte', 'This is the main camping map.')">
        </div>

        <div class="arrow right-arrow" onclick="nextImage()">&#10095;</div>

        <div class="thumbnail-gallery">
            <img src="media/starts/majina1.jpg" alt="Mazā mājiņa" class="thumbnail" onclick="selectImage(this, 'Mazā Mājiņa',
                'Mājiņa piemērota 4 vai 5 cilvēkiem.' + '\n'
                'Cena 4-vietīgajai mājiņai ir 60€/nakti.' + '\n'
                'Cena 5-vietīgajai mājiņai ir 70€/nakti.' + '\n'
                'Par šo cenu Jums ir tiesības atrasties mājiņā no iebraukšanas dienas plkst. 14:00 līdz nākamās apmaksātās dienas 12:00.')">
            <img src="media/starts/majina2.jpg" alt="Lielā mājiņa" class="thumbnail" onclick="selectImage(this, 'Lielā mājiņa', 'Mājiņa piemērota 6 cilvēkiem. Cena ir 90€/nakti.\nPar šo cenu Jums ir tiesības atrasties mājiņā no iebraukšanas dienas plkst. 14:00 līdz nākamās apmaksātās dienas 12:00.')">
            <img src="media/starts/majina3.jpg" alt="Mazā mājiņa" class="thumbnail" onclick="selectImage(this, 'Mazā Mājiņa', 'Mājiņa piemērota 4 vai 5 cilvēkiem.\nCena 4-vietīgajai mājiņai ir 60€/nakti.\nCena 5-vietīgajai mājiņai ir 70€/nakti.\nPar šo cenu Jums ir tiesības atrasties mājiņā no iebraukšanas dienas plkst. 14:00 līdz nākamās apmaksātās dienas 12:00.')">
            <img src="media/starts/majina4.jpg" alt="Lielā mājiņa" class="thumbnail" onclick="selectImage(this, 'Lielā mājiņa', 'Mājiņa piemērota 6 cilvēkiem. Cena ir 90€/nakti.\nPar šo cenu Jums ir tiesības atrasties mājiņā no iebraukšanas dienas plkst. 14:00 līdz nākamās apmaksātās dienas 12:00.')">
            <img src="media/starts/futbols.jpg" alt="Futbols" class="thumbnail" onclick="selectImage(this, 'Futbols', 'Kempingā ir pieejams futbola laukums, kura izmantošana neprasa papildus apmaksu.\nVajadzības gadījumā info ofisā var saņemt futbola bumbu, ko pēc spēles jānodod atpakaļ info.')">
            <img src="media/starts/volejbols.jpg" alt="Volejbols" class="thumbnail" onclick="selectImage(this, 'Volejbols', 'Kempingā ir pieejami divi volejbola laukumi, kuru izmantošana neprasa papildus apmaksu.\nVajadzības gadījumā info ofisā var saņemt volejbola bumbu, ko pēc spēles jānodod atpakaļ info.')">
            <img src="media/starts/laivas.jpg" alt="Laivas" class="thumbnail" onclick="selectImage(this, 'Laivas', 'Kempingā ir pieejamas 3 airu laivas, kurās var ērti apsēsties 4 pieaugušie cilvēki, vai 2 pieaugušie un 3 bērni.\nLaivas īres cena ir 5€/stundā vai 25€/dienā.\nLaivu drīkst īrēt tikai pieaudzis cilvēks (18+) un šim pieaugušajam ir jāatrodas laivā visa izbrauciena laikā.\nBērniem(<18) ir obligāti jāvelkā drošības vestes izbrauciena laikā, pieaugušie var paši izvēlēties.\nVestes, airus un laivas atslēgas var saņemt info ofisā, kur pēc atgriešanās krastā viss arī jānodod.\nLaivas īres laiks tiek skaitīts no aprīkojuma saņemšanas brīža (+10 min) līdz aprīkojuma nodošanas brīdim.')">
            <img src="media/starts/katamarans.jpg" alt="Katamarāns" class="thumbnail" onclick="selectImage(this, 'Katamarāns', 'Kempingā ir pieejams katamarāns, kurā var ērti apsēsties 4 pieaugušie cilvēki, vai 2 pieaugušie un 3 bērni.\nKatamarāna īres cena ir 7€/stundā vai 35€/dienā.\nKatamarānu drīkst īrēt tikai pieaudzis cilvēks (18+) un šim pieaugušajam ir jāatrodas katamarānā vai tā tuvumā visa izbrauciena laikā.\nBērniem(<18) ir obligāti jāvelkā drošības vestes izbrauciena laikā, pieaugušie var paši izvēlēties.\nVestes un katamarāna atslēgas var saņemt info ofisā, kur pēc atgriešanās krastā viss arī jānodod.\nKatamarāna īres laiks tiek skaitīts no aprīkojuma saņemšanas brīža (+10 min) līdz aprīkojuma nodošanas brīdim.')">
            <img src="media/starts/piknika_vieta.jpg" alt="Piknika vieta" class="thumbnail" onclick="selectImage(this, 'Piknika vieta', 'Kempingā ir pieejamas 5 piknika vietas, katrā var ērti apsēsties 8-10 cilvēki.\nPiknika vietas nāk komplektā ar stacionāro grillu, tomēr pārējais grillēšanas aprīkojums ir jānodrošina pašiem.\nPiknika vietas cena ir 15€/dienu (ja uzturās teritorijā tikai pa dienu, izbrauc līdz 20:00) + 3/€/pieaugušo cilvēku(18+) + 1.5€/bērnu(7-18) + 0/€/bērnu(<7).\nPiknika vieta ir atsevišķa no pārējiem nakšņošanas pakalpojumiem.\nJa viesi nakšņo kempinga mājiņā, netiek ņemta papildus nauda par cilvēkiem.\nJa viesi nakšņo teltī vai kemperī, netiek ņemta papildus nauda par cilvēkiem.')">
        </div>
    </main>
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
                <td><p>
                        Telefons: +371 29425800</br>
                        E-pasts: sniedzes@apollo.lv</br>
                        Mājas lapa: www.sniedzes.lv</p></td>
                <td><a href="https://www.facebook.com/sniedzes" target="_blank"><img src="media/starts/fb.png"></a>
                <a href="https://shorturl.at/XZHDD" target="_blank"><img src="media/starts/gm.png"></a></td>
            </tr>
        </table>
    </footer>

    <!-- Image Description Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modal-title"></h2>
            <p id="modal-description"></p>
        </div>
    </div>

    <!-- Authentication Modal (Login/Register) -->
    <div id="auth-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>

            <!-- Login Form -->
            <form id="login-form" method="POST" action="auth.php">
                <input type="hidden" name="action" value="login">
                <h2>Login</h2>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
                <p>Don’t have an account? <a href="#" onclick="toggleAuthMode()">Reģistrēties</a></p>
            </form>

            <!-- Register Form -->
            <form id="register-form" method="POST" action="auth.php" style="display: none;">
                <input type="hidden" name="action" value="register">
                <h2>Register</h2>
                <input type="text" name="name" placeholder="Vārds" required>
                <input type="text" name="surname" placeholder="Uzvārds" required>
                <input type="number" name="dzGads" placeholder="Dzimšanas gads" required>
                <input type="text" name="phone" placeholder="Telefons" required>
                <input type="email" name="email" placeholder="Epasts" required>
                <input type="password" name="password" placeholder="Parole" required>
                <input type="password" name="confirm_password" placeholder="Apstiprināt paroli" required>
                <button type="submit">Reģistrēties</button>
                <p>Already have an account? <a href="#" onclick="toggleAuthMode()">Login</a></p>
            </form>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
