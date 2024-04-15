<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css"/>
</head>
<script>
</script>
<body>
<?php
session_start();

// Adatbáziskapcsolat létrehozása
$tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))) (CONNECT_DATA = (SID = orania2)))";
$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, "UTF8");

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

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $sql_max_id = "SELECT MAX(csoport_id) AS max_id FROM csoport";
    $stid_max_id = oci_parse($conn, $sql_max_id);
    oci_execute($stid_max_id);
    $max_id_row = oci_fetch_array($stid_max_id, OCI_ASSOC);
    $max_id = $max_id_row['MAX_ID'];
    $new_id = $max_id + 1;
    $name = $_POST['name'];
    $text = $_POST['text'];

    $sql_insert_user = "INSERT INTO csoport (csoport_id, csoport_nev, csoport_leiras) VALUES (:id, :nev, :leiras)";
    $stid_insert_user = oci_parse($conn, $sql_insert_user);
    oci_bind_by_name($stid_insert_user, ':id', $new_id);
    oci_bind_by_name($stid_insert_user, ':nev', $name);
    oci_bind_by_name($stid_insert_user, ':leiras', $text);
    if (oci_execute($stid_insert_user)) {
        $sql_insert_ism = "INSERT INTO csoporttagok (felhid, csoportid) VALUES (:felhid, :csooportid)";
        $stid_insert_ism = oci_parse($conn, $sql_insert_ism);
        oci_bind_by_name($stid_insert_ism, ':felhid', $_SESSION ['felh_id']);
        oci_bind_by_name($stid_insert_ism, ':csoportid', $new_id);
        oci_execute($stid_insert_ism);
        header('Location: all_table.php');
        exit;
    }
    echo "Nem sikerült a klubot létrehozni";
    exit;
}
?>
<div class="container">
    <h1>Csoport létrehozása</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
        <label for="name">Név:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="text">Leírás:</label>
        <input type="text" id="text" name="text" required><br>

        <input type="submit" value="Létrehozás">
        <input type='button' value='Főoldal' onclick="window.location.href='all_table.php'"/>
    </form>
    <div>
</body>
</html>