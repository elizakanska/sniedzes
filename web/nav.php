<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
?>
<header class="navbar">
    <img src="media/logo.png" alt="Logo" class="logo">
    <nav>
        <a href="index.php">Sākums</a>
        <a href="rev.php">Atsauksmes</a>
        <a href="rez.php">Rezervēt mājiņu</a>
        <a href="#" onclick="handleAuthAction(<?php echo json_encode($is_logged_in); ?>)">
            <?php echo $is_logged_in ? 'Atslēgties' : 'Pieslēgties'; ?>
        </a>
    </nav>
</header>

<!-- Authentication Modal (Login/Register) -->
<div id="auth-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <!-- Login Form -->
        <form id="login-form" method="POST" action="auth.php">
            <input type="hidden" name="action" value="login">
            <h2>Pieslēgties</h2>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Pieslēgties</button>
            <p>Vēl nav konts? <a href="#" onclick="toggleAuthMode()">Reģistrēties</a></p>
        </form>

        <!-- Register Form -->
        <form id="register-form" method="POST" action="auth.php" style="display: none;">
            <input type="hidden" name="action" value="register">
            <h2>Reģistrēties</h2>
            <input type="text" name="name" placeholder="Vārds" required>
            <input type="text" name="surname" placeholder="Uzvārds" required>
            <input type="number" name="dzGads" placeholder="Dzimšanas gads" required>
            <input type="text" name="phone" placeholder="Telefons" required>
            <input type="email" name="email" placeholder="Epasts" required>
            <input type="password" name="password" placeholder="Parole" required>
            <input type="password" name="confirm_password" placeholder="Apstiprināt paroli" required>
            <button type="submit">Reģistrēties</button>
            <p>Jau ir konts? <a href="#" onclick="toggleAuthMode()">Pieslēgties</a></p>
        </form>
    </div>
</div>
