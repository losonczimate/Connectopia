<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css"/>
</head>
<body>
<?php
echo '<div class="container">';

$tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))) (CONNECT_DATA = (SID = orania2)))";
$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, "UTF8");

// Ellenőrizzük a kapcsolatot
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

session_start();
if (!isset($_SESSION["felhasznalo"])) {
    header('Location: login.php');
} else {
    echo '<form action="logout.php" method="post">';
    echo "<input type='button' value='Főoldal' onclick=\"window.location.href='all_table.php'\" />";
    echo '</form><br>';
}

if ($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['friendid'])) {
    uzenet($_POST['friendid'], $_POST['uzenet']);
}

function uzenet($id, $text)
{
    global $conn;

    $sql_insert_new_id = "BEGIN :id := new_id('uzenet'); END;";
    $stid_insert_new_id = oci_parse($conn, $sql_insert_new_id);
    oci_bind_by_name($stid_insert_new_id, ':id', $id_uze);
    oci_execute($stid_insert_new_id);

    $idopont = date('Y-m-d H:i');

    $sql_insert_uze = "INSERT INTO uzenet (uzenet_id, tartalom, kuldes_ideje, kuldo, fogado) VALUES (:uzenet_id, :tartalom, TO_DATE(:kuldes_ideje, 'YYYY-MM-DD HH24:MI'), :kuldo, :fogado)";
    $stid_insert_uze = oci_parse($conn, $sql_insert_uze);
    oci_bind_by_name($stid_insert_uze, ':uzenet_id', $id_uze);
    oci_bind_by_name($stid_insert_uze, ':tartalom', $text);
    oci_bind_by_name($stid_insert_uze, ':kuldes_ideje', $idopont);
    oci_bind_by_name($stid_insert_uze, ':kuldo', $_SESSION['felh_id']);
    oci_bind_by_name($stid_insert_uze, ':fogado', $id);
    if (oci_execute($stid_insert_uze)) {
        echo "Sikeres üzenetküldés!";
    }
}

function generateTable($tableName, $conn)
{
    $tableHTML = '<table>';
    $searchTerm = isset($_GET['kereso']) ? $_GET['kereso'] : '';
    //// -- lekerdezzuk a tábla tartalmat
    $stid = oci_parse($conn, "SELECT felh_email, felh_szulinap, felh_nev, felh_id FROM $tableName
                                     LEFT JOIN ismerosok i on felh_id = i.felh_id2
                                     WHERE i.felh_id1 = :felh_id");
    oci_bind_by_name($stid, ':felh_id', $_SESSION["felh_id"]);
    oci_execute($stid);

    //// -- eloszor csak az oszlopneveket kerem le
    $nfields = oci_num_fields($stid);
    $tableHTML .= '<thead><tr>';
    for ($i = 1; $i <= $nfields; $i++) {
        $field = oci_field_name($stid, $i);
        $tableHTML .= '<th>' . $field . '</th>';
    }
    $tableHTML .= '<th>Üzenet küldése</th>';
    $tableHTML .= '</tr></thead>';

    //// -- ujra vegrehajtom a lekerdezest, es kiiratom a sorokat
    oci_execute($stid);

    $tableHTML .= '<tbody>';
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $tableHTML .= '<tr>';
        foreach ($row as $item) {
            $tableHTML .= '<td>' . $item . '</td>';
        }
        $tableHTML .= "<td><form action='friends.php' method='post' autocomplete='off'>
                           <input type='text' name='uzenet'/>
                           <input type='hidden' name='friendid' value='$row[felh_id]' />
                           <input type='submit' value='Küldés'/>
                       </form></td>";
        $tableHTML .= '</tr>';
    }
    $tableHTML .= '</tbody>';
    $tableHTML .= '</table>';

    return $tableHTML;
}

echo generateTable('Felhasznalo', $conn);

echo '<div>';
?>


</body>
</html>