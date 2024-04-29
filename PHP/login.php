<?php
if (!isset($_SESSION["loggedin"])) {
    $_SESSION["loggedin"] = FALSE;
}
?>
<html lang="hu" xmlns="http://www.w3.org/1999/html">
<head>
    <title>Connectopia</title>
    <link rel="stylesheet" type="text/css" href="../CSS/connectopia.css">
</head>
<body>
<?php

// adatbázis kapcsolódási adatok
$tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521)))(CONNECT_DATA = (SID = orania2)))";
$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, 'UTF8');
echo "<script>console.log('Sikeres kapcsolat!');</script>";
if (!$conn) {
    $m = oci_error();
    echo $m, "\n";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // felhasználó bejelentkeztetése
    $stmt = oci_parse($conn, "SELECT * FROM Felhasznalo WHERE FELH_EMAIL=:email");

    $email = $_POST["email"];
    oci_bind_by_name($stmt, ":email", $email);

    if (oci_execute($stmt)) {
        $row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS);
        if ($row != false) {
            // sikeres email ellenőrzés

            if (password_verify($_POST['password'], $row['FELH_JELSZO']) || $_POST['password'] == $row['FELH_JELSZO']) {
                // sikeres bejelentkezés
                echo "Sikeres bejelentkezés!";
                // tároljuk a felhasználó adatait a sessionben
                session_start();
                $_SESSION['felhasznalo'] = $row;
                echo "<script>alert('Sikeres kapcsolat!');</script>";
                // átirányítás a főoldalra
                header("Location: profile.php");

                exit();
            } else {
                // sikertelen jelszó ellenőrzés
                echo "<script>alert('Hibás jelszó!');</script>";
            }
        } else {
            // sikertelen email ellenőrzés
            echo "<script>alert('Hibás email-cím!');</script>";
        }
    } else {
        // adatbázis hiba
        echo "<script>alert('Adatbázis hiba!');</script>";
    }
}
?>

<div>
    <form method="post" action="login.php">

        <h2>Bejelentkezés</h2>
        <label for="email">E-mail cím:</label><br>
        <input type="email" id="email" name="email" required><br>

        <label for="password">Jelszó:</label><br>
        <input type="password" id="password" name="password" required><br>

        <input type="submit" value="Bejelentkezés">
        <h2><a href="register.php">Ha nincs fiókod, regisztrálj!</a></h2>
    </form>
</div>