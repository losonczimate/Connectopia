<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css" />
</head>
<script>
</script>
<body>
<?php
session_start();

// Adatbáziskapcsolat létrehozása
$tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))) (CONNECT_DATA = (SID = orania2)))";
$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns,'UTF8');

// Ellenőrizzük a kapcsolatot
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

// Ha még nincs bejelentkezve a felhasználó, átirányítjuk a főoldalra
if (!isset($_SESSION['felhasznalo'])) {
    header('Location: login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
    $sql_insert_new_id = "BEGIN :esemeny_id := new_id('esemeny'); END;";
    $stid_insert_new_id = oci_parse($conn, $sql_insert_new_id);
    oci_bind_by_name($stid_insert_new_id, ':esemeny_id', $new_id);
    oci_execute($stid_insert_new_id);
    $name = $_POST['name'];
    $text = $_POST['text'];
    $date = $_POST['date'];

    $sql_insert_user = "INSERT INTO esemeny (esemeny_id, nev, leiras, idopont) VALUES (:id, :nev, :leiras, TO_DATE(:idopont, 'YYYY-MM-DD'))";
    $stid_insert_user = oci_parse($conn, $sql_insert_user);
    oci_bind_by_name($stid_insert_user, ':id', $new_id);
    oci_bind_by_name($stid_insert_user, ':nev', $name);
    oci_bind_by_name($stid_insert_user, ':leiras', $text);
    oci_bind_by_name($stid_insert_user, ':idopont', $date);
    if(oci_execute($stid_insert_user)) {
        $sql_insert_ism = "INSERT INTO esemenytagok (felhid, esemenyid) VALUES (:felh_id, :esemeny_id)";
        $stid_insert_ism = oci_parse($conn, $sql_insert_ism);
        oci_bind_by_name($stid_insert_ism, ':felh_id', $_SESSION['felhasznalo']['FELH_ID']);
        oci_bind_by_name($stid_insert_ism, ':esemeny_id', $new_id);
        oci_execute($stid_insert_ism);
        header('Location: all_table.php');
        exit;
    }
    echo  '<script>alert("Nem sikerült az eseményt létrehozni!");</script>';
    exit;
}
?>
<div class="container">
    <h1>Esemény létrehozása</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
        <label for="name">Név:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="text">Leírás:</label>
        <input type="text" id="text" name="text" required><br>

        <label for="date">Időpont:</label>
        <input type="date" id="date" name="date" required><br>

        <input type="submit" value="Létrehozás">
        <input type='button' value='Főoldal' onclick="window.location.href='all_table.php'" />
    </form>
</div>
</body>
</html>
