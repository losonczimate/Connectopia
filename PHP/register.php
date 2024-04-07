<?php
session_start();
if (isset($_SESSION["felhasznalo"])) {
    header('Location: all_table.php');
}
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../CSS/connectopia.css"
</head>
<body>
<?php
$tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521)))(CONNECT_DATA = (SID = orania2)))";
$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, 'UTF8');

// Ellenőrizzük a kapcsolatot
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lekérdezzük a legnagyobb id értéket a felhasználók táblából
    $sql_max_id = "SELECT MAX(felh_id) AS max_id FROM felhasznalo";
    $stid_max_id = oci_parse($conn, $sql_max_id);
    oci_execute($stid_max_id);
    $max_id_row = oci_fetch_array($stid_max_id, OCI_ASSOC);
    $max_id = $max_id_row['MAX_ID'];

    //beállítjuk az új id-t
    $new_id = $max_id + 1;

    // Az űrlapból kapott adatok
    $name = $_POST['name'];
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    if (isset($_POST['password']) && isset($_POST['password2']) && $_POST['password'] === $_POST['password2']) {
        // A jelszavak egyeznek
        echo "A jelszavak egyeznek.";
        $sql_insert_user = "INSERT INTO felhasznalo (felh_id, felh_email, felh_jelszo, felh_szulinap,felh_nev) VALUES (:felh_id, :email, :jelszo, TO_DATE(:szul_ido, 'YYYY-MM-DD'), :nev)";
        $stid_insert_user = oci_parse($conn, $sql_insert_user);
        oci_bind_by_name($stid_insert_user, ':felh_id', $new_id);
        oci_bind_by_name($stid_insert_user, ':nev', $name);
        oci_bind_by_name($stid_insert_user, ':email', $email);
        oci_bind_by_name($stid_insert_user, ':szul_ido', $birthdate);
        oci_bind_by_name($stid_insert_user, ':jelszo', $password);
        echo "<script>alert('A jelszavak egyeznek.');</script>";
        if (oci_execute($stid_insert_user)) {
            echo "<script>alert('Sikeres regisztráció, tovább a bejelentkező oldalra!');</script>";
            header('Location: login.php');
            exit;
        }
    } else {
        echo "<script>alert('A jelszavak nem egyeznek és/vagy valamelyik mező nincs kitöltve!');</script>";
    }
}
?>

<h1>Regisztráció</h1>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <label for="name">Név:</label>
    <input type="text" id="name" name="name" required><br>

    <label for="email">E-mail:</label>
    <input type="email" id="email" name="email" required><br>

    <label for="birthdate">Születési dátum:</label>
    <input type="date" id="birthdate" name="birthdate" required><br>

    <label for="password">Jelszó:</label>
    <input type="password" id="password" name="password" required><br>

    <label for="password2">Jelszó mégegyszer</label>
    <input type="password" id="password2" name="password2" required><br>

    <input type="submit" value="Regisztráció">
    <input type="button" value="Bejelentkezés" onclick="window.location.href='login.php'"/>
</form>
</body>
</html>