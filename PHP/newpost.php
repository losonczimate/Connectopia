<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css"/>
</head>
<body>
<?php

$tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))) (CONNECT_DATA = (SID = orania2)))";
$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, "UTF8");

// Ellenőrizzük a kapcsolatot
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql_max_id_kep = "SELECT MAX(kep_id) AS max_id FROM fenykep";
    $stid_max_id_kep = oci_parse($conn, $sql_max_id_kep);
    oci_execute($stid_max_id_kep);
    $max_id_row_kep = oci_fetch_array($stid_max_id_kep, OCI_ASSOC);
    $max_id_kep = $max_id_row_kep['MAX_ID'];

    $id_kep = $max_id_kep + 1;
    $url = $_POST['url'];

    $sql_insert_kep = "INSERT INTO fenykep (kep_id, kep_url) VALUES (:id, :url)";
    $stid_insert_kep = oci_parse($conn, $sql_insert_kep);
    oci_bind_by_name($stid_insert_kep, ':id', $id_kep);
    oci_bind_by_name($stid_insert_kep, ':url', $url);

    if(oci_execute($stid_insert_kep)){
        $sql_max_id_post = "SELECT MAX(bejegyzes_id) AS max_id FROM bejegyzes";
        $stid_max_id_post = oci_parse($conn, $sql_max_id_post);
        oci_execute($stid_max_id_post);
        $max_id_row_post = oci_fetch_array($stid_max_id_post, OCI_ASSOC);
        $max_id_post = $max_id_row_post['MAX_ID'];

        $id_kep = $max_id_post + 1;
        $leiras = $_POST['leiras'];
        $idopont = date('Y-m-d H:i');
        $csoport_id = isset($_SESSION["csoport_id"]) ? $_SESSION["csoport_id"] : null;
        $esemeny_id = null;

        $sql_insert_post = "INSERT INTO bejegyzes (bejegyzes_id, bejegyzes_leiras, bejegyzes_idopont, fenykep_id, felhid, csoportid, esemenyid) VALUES (:bejegyzes_id, :bejegyzes_leiras,TO_DATE(:bejegyzes_idopont, 'YYYY-MM-DD HH24:MI'), :fenykep_id, :felhid, :csoportid, :esemenyid)";
        $stid_insert_post = oci_parse($conn, $sql_insert_post);
        oci_bind_by_name($stid_insert_post, ':bejegyzes_id', $id_kep);
        oci_bind_by_name($stid_insert_post, ':bejegyzes_leiras', $leiras);
        oci_bind_by_name($stid_insert_post, ':bejegyzes_idopont', $idopont);
        oci_bind_by_name($stid_insert_post, ':fenykep_id', $id_kep);
        oci_bind_by_name($stid_insert_post, ':felhid', $_SESSION['felhasznalo']['FELH_ID']);
        oci_bind_by_name($stid_insert_post, ':csoportid', $csoport_id);
        oci_bind_by_name($stid_insert_post, ':esemenyid', $esemeny_id);
        if(oci_execute($stid_insert_post)){
            echo "Sikeres posztolás!";
        }
    }
    if (isset($_SESSION["csoport_id"])){
        header('Location: newpost.php?csoport_id='.$_SESSION["csoport_id"]);
        unset($_SESSION["csoport_id"]);
    }
}
if(!isset($_SESSION["felhasznalo"])){
    header('Location: login.php');
}elseif(isset($_GET['csoport_id'])) {
    if (!checkclub($_GET['csoport_id'])){
        header('Location: all_table.php');
        exit;
    }
    $_SESSION["csoport_id"] = $_GET['csoport_id'];
}elseif (isset($_SESSION["csoport_id"])){
    unset($_SESSION["csoport_id"]);
}
function checkclub($id){
    global $conn;
    $user = $_SESSION['felh_id'];
    $stid = oci_parse($conn, "SELECT COUNT(*) AS db FROM csoporttagok WHERE felhid = ".$_GET['csoport_id']." AND felhid = $user");
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        if ($row['DB'] == 0) {
            return false;
        }
        return true;
    }
}
?>
<div class="container">
    <h1>Új poszt létrehozása</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
        <label for="url">Kép url:</label>
        <input type="text" id="url" name="url" required><br>

        <label for="leiras">Leírás:</label>
        <input type="text" id="leiras" name="leiras" required><br>

        <input type="submit" value="Posztolás">
        <?php
        if (isset($_GET['csoport_id'])) {
            echo '<input type="button" value="Vissza" onclick="window.location.href=\'group.php?id='.$_GET['csoport_id'].'\'" />';
        }else{
            echo '<input type="button" value="Főoldal" onclick="window.location.href=\'all_table.php\'" />';
        }
        ?>
    </form>
</div>
</body>
</html>
