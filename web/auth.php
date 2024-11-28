<?php

session_start();
function getPdo()
{
    $host = 'localhost';
    $dbname = 'sniedzes';
    $db_username = 'root';
    $db_password = 'root';

    try {
        // Create a new PDO instance
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_username, $db_password);

        // Set PDO error mode to exception for better error handling
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
        // If there's an error, display a message and exit
        die("Database connection failed: " . $e->getMessage());
    }
    return $pdo;
}

$pdo = getPdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        // Pieslēgšanās sadaļa
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            header('Location: index.php');
            exit();
        }

        // Sameklē lietotāju datubāzē
        $stmt = $pdo->prepare("SELECT * FROM Lietotajs WHERE epasts = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Pārbauda paroles atbilstību
        if ($user && password_verify($password, $user['parole'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['vards'] = $user['vards'];
            $_SESSION['uzvards'] = $user['uzvards'];
            $_SESSION['isAdmin'] = $user['isAdmin'];

            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Nepareizs epasts un/vai parole.';
            header('Location: index.php');
            exit();
        }

    } elseif ($action === 'register') {
        // Reģistrē jaunu lietotāju
        $vards = trim($_POST['name'] ?? '');
        $uzvards = trim($_POST['surname'] ?? '');
        $dzGads = trim($_POST['dzGads'] ?? '');
        $telefons = trim($_POST['phone'] ?? '');
        $epasts = trim($_POST['email'] ?? '');
        $parole = trim($_POST['password'] ?? '');
        $confirm_parole = trim($_POST['confirm_password'] ?? '');

        // Datu atbilstības pārbaude
        if (empty($vards) || empty($uzvards) || empty($dzGads) || empty($telefons) || empty($epasts) || empty($parole) || empty($confirm_parole)) {
            $_SESSION['error'] = 'Lūdzu aizpildiet visus lauciņus.';
            header('Location: index.php');
            exit();
        }

        // Pārbauda, vai dzimšanas gads ir pirms vismaz 18 gadiem
        $currentYear = date('Y');
        if ($currentYear - $dzGads < 18) {
            $_SESSION['error'] = 'Reģistrācijai nepieciešams būt vismaz 18 gadu vecam.';
            header('Location: index.php');
            exit();
        }

        if (!filter_var($epasts, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Neatbilstošs epasta formāts.';
            header('Location: index.php');
            exit();
        }

        if ($parole !== $confirm_parole) {
            $_SESSION['error'] = 'Paroles nesakrīt.';
            header('Location: index.php');
            exit();
        }

        // Pārbauda vai epasts ir jau datubāzē
        $stmt = $pdo->prepare("SELECT * FROM Lietotajs WHERE epasts = :email");
        $stmt->bindParam(':email', $epasts);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = 'Šim epastam jau ir piesaistīts konts.';
            header('Location: index.php');
            exit();
        }

        // Šifrē jaunizveidoto paroli
        $hashed_password = password_hash($parole, PASSWORD_BCRYPT);

        // Ievieto jauno lietotāju datubāzē
        $stmt = $pdo->prepare("INSERT INTO Lietotajs (vards, uzvards, dzGads, telefons, epasts, parole, isAdmin) VALUES (:vards, :uzvards, :dzGads, :telefons, :epasts, :parole, 0)");
        $stmt->bindParam(':vards', $vards);
        $stmt->bindParam(':uzvards', $uzvards);
        $stmt->bindParam(':dzGads', $dzGads, PDO::PARAM_INT);
        $stmt->bindParam(':telefons', $telefons);
        $stmt->bindParam(':epasts', $epasts);
        $stmt->bindParam(':parole', $hashed_password);

        if ($stmt->execute()) {
            // Automātiski pieslēdz lietotāju pec reģistrācijas
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['vards'] = $vards;
            $_SESSION['uzvards'] = $uzvards;
            $_SESSION['isAdmin'] = 0;

            $_SESSION['success'] = 'Reģistrācija veiksmīga! Sveicināti pilnajā Kempings "Sniedzes" pieredzē.';
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Reģistrācija neveiksmīga. Lūdzu mēģiniet vēlreiz.';
            header('Location: index.php');
            exit();
        }

    } elseif ($action === 'logout') {
        // Atslēgšanās process
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    } else {
        // Neatbilstoša rīcība
        $_SESSION['error'] = 'Neatbilstoša rīcība.';
        header('Location: index.php');
        exit();
    }
}
?>
