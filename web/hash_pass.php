<?php
// update_passwords.php - Script to Hash Existing Passwords

require_once 'db.php';

// Fetch all users
$stmt = $pdo->prepare("SELECT ID, parole FROM Lietotajs");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $id = $user['ID'];
    $hashed_password = password_hash($user['parole'], PASSWORD_BCRYPT);

    // Update the password with the hashed version
    $update_stmt = $pdo->prepare("UPDATE Lietotajs SET parole = :parole WHERE ID = :id");
    $update_stmt->bindParam(':parole', $hashed_password);
    $update_stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $update_stmt->execute();
}

echo "Paroles ir veiksmīgi šifrētas";
?>
