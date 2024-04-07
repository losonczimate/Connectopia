<?php
if(!isset($_SESSION["loggedin"])){
    $_SESSION["loggedin"]=FALSE;
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
    $stmt = oci_parse($conn, "SELECT FELH_ID,FELH_NEV, FELH_EMAIL, FELH_JELSZO FROM Felhasznalo WHERE FELH_EMAIL=:email");
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
                $_SESSION["loggedin"]=TRUE;
                $_SESSION["felhasznalo"] = $row["FELH_NEV"];
                $_SESSION["felh_id"] = $row["FELH_ID"];
                $_SESSION["jelszo"] = $row["FELH_JELSZO"];
                $_SESSION["email"] = $row["FELH_EMAIL"];
                $_SESSION["messaging_partner_ID"] = NULL;
                $_SESSION["messaging_partner_email"] = NULL;
                $_SESSION["messaging_partner_nev"] = NULL;
                $_SESSION["last_message_recieved_id"] = NULL;
                $_SESSION["messages"] = NULL;
                echo "<script>console.log('Sikeres kapcsolat!');</script>";
                // átirányítás a főoldalra
                header("Location: profile.php");

                exit();
            } else {
                // sikertelen jelszó ellenőrzés
                echo "Hibás jelszó!";
            }
        } else {
            // sikertelen email ellenőrzés
            echo "Hibás email cím!";
        }
    } else {
        // adatbázis hiba
        echo "Adatbázis hiba!";
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