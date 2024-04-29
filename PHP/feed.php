<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css"/>
</head>
<body>
<?php
echo '<div class="container">';

$tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))) (CONNECT_DATA = (SID = orania2)))";
$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, 'UTF8');

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
    echo '</form>';
}

if ($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['posztid'])) {
    kommenteles($_POST['posztid'], $_POST['commentszoveg']);
}
$stid_felh = oci_parse($conn, 'SELECT f.felh_nev, COUNT(b.bejegyzes_id) AS bejegyzesek_szama
                                FROM felhasznalo f
                                INNER JOIN ismerosok i ON f.felh_id = i.felh_id2 AND i.felh_id1 = :felh_id
                                LEFT JOIN bejegyzes b ON f.felh_id = b.felhid
                                GROUP BY f.felh_id, f.felh_nev');
oci_bind_by_name($stid_felh, ':felh_id', $_SESSION['felhasznalo']["FELH_ID"]);
oci_execute($stid_felh);

while ($row_felh = oci_fetch_array($stid_felh, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<div><h2>Felhasználó neve: ' . $row_felh['FELH_NEV'] . ' - Bejegyzések száma: ' . $row_felh['BEJEGYZESEK_SZAMA'] . '</h2></div>';
}
function kommenteles($id, $text)
{
    global $conn;

    $sql_max_id_kom = "SELECT MAX(komment_id) AS max_id FROM komment";
    $stid_max_id_kom = oci_parse($conn, $sql_max_id_kom);
    oci_execute($stid_max_id_kom);
    $max_id_row_kom = oci_fetch_array($stid_max_id_kom, OCI_ASSOC);
    $max_id_kom = $max_id_row_kom['MAX_ID'];
    $id_kom = $max_id_kom + 1;
    $time = date('Y-m-d H:i');
    $sql_insert_kom = "INSERT INTO komment (komment_id, kommentelo_id, idopont, szoveg, bejegyzesid) VALUES (:id, :iro_id, TO_DATE(:idopont, 'YYYY-MM-DD HH24:MI'), :szoveg, :bejegyzesid)";
    $stid_insert_kom = oci_parse($conn, $sql_insert_kom);
    oci_bind_by_name($stid_insert_kom, ':id', $id_kom);
    oci_bind_by_name($stid_insert_kom, ':iro_id', $_SESSION['felhasznalo']['FELH_ID']);
    oci_bind_by_name($stid_insert_kom, ':idopont', $time);
    oci_bind_by_name($stid_insert_kom, ':szoveg', $text);
    oci_bind_by_name($stid_insert_kom, ':bejegyzesid', $id);
    oci_execute($stid_insert_kom);
}

$posts = array();

$stid = oci_parse($conn, 'SELECT b.*, f.kep_url, u.felh_nev, TO_CHAR(b.bejegyzes_idopont, \'YYYY.MM.DD HH24:MI\') as idopontformatted
                          FROM bejegyzes b
                          INNER JOIN ismerosok i ON b.felhid = i.felh_id2 AND i.felh_id1 = :felh_id
                          LEFT JOIN fenykep f ON b.fenykep_id = f.kep_id
                          LEFT JOIN felhasznalo u ON i.felh_id2 = u.felh_id
                          WHERE i.felh_id2 != :felh_id AND b.csoportid IS NULL');
oci_bind_by_name($stid, ':felh_id', $_SESSION['felhasznalo']["FELH_ID"]);
oci_execute($stid);
while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    array_push($posts, $row);
}

$stid = oci_parse($conn, 'SELECT b.*, f.kep_url, u.felh_nev, TO_CHAR(b.bejegyzes_idopont, \'YYYY.MM.DD HH24:MI\') as idopontformatted
                          FROM bejegyzes b
                          LEFT JOIN fenykep f ON b.fenykep_id = f.kep_id
                          LEFT JOIN felhasznalo u ON b.felhid = u.felh_id
                          WHERE b.felhid = :felh_id AND b.csoportid IS NULL');
oci_bind_by_name($stid, ':felh_id', $_SESSION['felhasznalo']["FELH_ID"]);
oci_execute($stid);

while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    array_push($posts, $row);
}

usort($posts, function ($a, $b) {
    $dt1 = DateTime::createFromFormat('Y.m.d H:i', $a['IDOPONTFORMATTED']);
    $dt2 = DateTime::createFromFormat('Y.m.d H:i', $b['IDOPONTFORMATTED']);

    return ($dt1 < $dt2) ? 1 : -1;
});

foreach ($posts as $row) {

    $stid = oci_parse($conn, 'SELECT k.*, u.felh_nev, TO_CHAR(k.idopont, \'YYYY.MM.DD HH24:MI\') as idopontformatted
                          FROM komment k
                          LEFT JOIN felhasznalo u ON k.kommentelo_id = u.felh_id
                          WHERE k.bejegyzesid = :bejegyzesid');
    oci_bind_by_name($stid, ':bejegyzesid', $row["BEJEGYZES_ID"]);
    oci_execute($stid);

    echo '<div class="post">';
    echo '<div class="postfejlec">' . $row["IDOPONTFORMATTED"] . "  " . $row["FELH_NEV"] . '</div>';
    echo '<div class="postleiras"> Leírás: ' . $row["BEJEGYZES_LEIRAS"] . '</div>';
    echo '<div class="kep"><img src = ' . $row["KEP_URL"] . ' ></div>';

    while ($row2 = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        echo '<div class="post">';
        echo '<div class="postfejlec">' . $row2["IDOPONTFORMATTED"] . "  " . $row2["FELH_NEV"] . '</div>';
        echo '<div class="postleiras">' . $row2["SZOVEG"] . '</div>';
        echo '</div>';
    }

    echo "<td><form action='feed.php' method='post' autocomplete='off'>
             <input type='text' name='commentszoveg' required />
             <input type='hidden' name='posztid' value='$row[BEJEGYZES_ID]'/>
             <input type='submit' value='Kommentelés' />
          </form></td>";

    echo '</div>';
}

echo '<div>';
?>

</body>
</html>

