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
    echo '<form method="post">';
    echo "<input type='button' value='Főoldal' onclick=\"window.location.href='all_table.php'\" />";
    echo '</form>';
}

$stid = oci_parse($conn, 'SELECT u.*, f.felh_nev, TO_CHAR(u.kuldes_ideje, \'YYYY.MM.DD HH24:MI\') as idopontformatted
                          FROM uzenet u
                          LEFT JOIN felhasznalo f ON u.kuldo = f.felh_id
                          WHERE u.fogado = :felh_id');
oci_bind_by_name($stid, ':felh_id', $_SESSION["felh_id"]);
oci_execute($stid);

$uzenetek = array();

while ( $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    array_push($uzenetek,$row);
}

usort($uzenetek, function($a, $b) {
    $dt1 = DateTime::createFromFormat('Y.m.d H:i', $a['IDOPONTFORMATTED']);
    $dt2 = DateTime::createFromFormat('Y.m.d H:i', $b['IDOPONTFORMATTED']);

    return ($dt1 < $dt2) ? 1 : -1;
});

foreach ($uzenetek as $row) {
    echo '<div class="post">';
    echo '<div class="postfejlec">' . $row["IDOPONTFORMATTED"] . "  " . $row["NEV"] . '</div>';
    echo '<div class="postleiras"> Leírás: ' . $row["TARTALOM"] . '</div>';

    echo '</div>';
}

echo '<div>';
?>
</body>
</html>