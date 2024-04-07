<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css" />
</head>
<body>
<?php
echo '<div class="container">';

$tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))) (CONNECT_DATA = (SID = orania2)))";
$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns,'UTF8');

// Ellenőrizzük a kapcsolatot
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

session_start();
if(!isset($_SESSION["felhasznalo"])){
    header('Location: login.php');
}else{
    echo '<form action="logout.php" method="post">';
    echo "<input type='button' value='Főoldal' onclick=\"window.location.href='all_table.php'\" />";
    echo '</form>';
}

if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['posztid']))
{
    kommenteles($_POST['bejegyzes_id'], $_POST['commentszoveg']);
}

function kommenteles($id, $text){
    global $conn;

    $sql_insert_new_id = "BEGIN :id := new_id('komment'); END;";
    $stid_insert_new_id = oci_parse($conn, $sql_insert_new_id);
    oci_bind_by_name($stid_insert_new_id, ':id', $id_kom);
    oci_execute($stid_insert_new_id);
    $time = date('Y-m-d H:i');
    $sql_insert_kom = "INSERT INTO komment (komment_id, kommentelo_id, idopont, szoveg, bejegyzesid) VALUES (:id, :iro_id, TO_DATE(:idopont, 'YYYY-MM-DD HH24:MI'), :szoveg, :bejegyzesid)";
    $stid_insert_kom = oci_parse($conn, $sql_insert_kom);
    oci_bind_by_name($stid_insert_kom, ':id', $id_kom);
    oci_bind_by_name($stid_insert_kom, ':iro_id', $_SESSION['felh_id']);
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
oci_bind_by_name($stid, ':felh_id', $_SESSION["felh_id"]);
oci_execute($stid);
while ( $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    array_push($posts, $row);
}

$stid = oci_parse($conn, 'SELECT b.*, f.kep_url, u.felh_nev, TO_CHAR(b.bejegyzes_idopont, \'YYYY.MM.DD HH24:MI\') as idopontformatted
                          FROM bejegyzes b
                          LEFT JOIN fenykep f ON b.fenykep_id = f.kep_id
                          LEFT JOIN felhasznalo u ON b.felhid = u.felh_id
                          WHERE b.felhid = :felh_id AND b.csoportid IS NULL');
oci_bind_by_name($stid, ':felh_id', $_SESSION["felh_id"]);
oci_execute($stid);

while ( $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    array_push($posts, $row);
}

usort($posts, function($a, $b) {
    $dt1 = DateTime::createFromFormat('Y.m.d H:i', $a['IDOPONTFORMATTED']);
    $dt2 = DateTime::createFromFormat('Y.m.d H:i', $b['IDOPONTFORMATTED']);

    return ($dt1 < $dt2) ? 1 : -1;
});

foreach ($posts as $row) {

    $stid = oci_parse($conn, 'SELECT k.*, u.felh_nev, TO_CHAR(k.idopont, \'YYYY.MM.DD HH24:MI\') as idopontformatted
                          FROM komment k
                          LEFT JOIN felhasznalo u ON k.kommentelo_id = u.felh_id
                          WHERE k.bejegyzesid = :bejegyzesid');
    oci_bind_by_name($stid, ':poszt_id', $row["BEJEGYZES_ID"]);
    oci_execute($stid);

    echo '<div class="post">';
    echo '<div class="postfejlec">' . $row["IDOPONTFORMATTED"] . "  " . $row["felhasznalo"] . '</div>';
    echo '<div class="postleiras"> Leírás: ' . $row["bejegyzes_leiras"] . '</div>';
    echo '<div class="kep"><img src = ' . $row["kep_url"] . ' ></div>';

    while ( $row2 = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        echo '<div class="post">';
        echo '<div class="postfejlec">' . $row2["IDOPONTFORMATTED"] . "  " . $row2["NEV"] . '</div>';
        echo '<div class="postleiras">' . $row2["SZOVEG"] . '</div>';
        echo '</div>';
    }

    echo "<td><form action='feed.php' method='post' autocomplete='off'>
             <input type='text' name='commentszoveg' required />
             <input type='hidden' name='posztid' value='$row[bejegyzes_id]'/>
             <input type='submit' value='Kommentelés' />
          </form></td>";

    echo '</div>';
}

echo '<div>';
?>

</body>
</html>

