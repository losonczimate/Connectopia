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
    echo '<form method="post">';
    echo "<input type='button' value='Főoldal' onclick=\"window.location.href='all_table.php'\" />";
    echo '</form><br>';
}

$stid = oci_parse($conn, 'SELECT felh_nev, TO_CHAR(felh_szulinap, \'YYYY.MM.DD\') as idopontformatted FROM felhasznalo');

$tableHTML = '<table>';
$nfields = oci_num_fields($stid);
$tableHTML .= '<thead><tr>';

$tableHTML .= '<th>' . "Név" . '</th>';
$tableHTML .= '<th>' . "Születési dátum" . '</th>';
$tableHTML .= '</tr></thead>';

oci_execute($stid);

$szulinaposok = array();

while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    $dt1 = DateTime::createFromFormat('Y.m.d', $row['IDOPONTFORMATTED']);
    $today = date('n.d');
    if ($dt1->format('n.d') == $today) {
        array_push($szulinaposok, $row);
    }
}

foreach ($szulinaposok as $row) {
    $tableHTML .= '<tr>';
    foreach ($row as $item) {
        $tableHTML .= '<td>' . $item . '</td>';
    }
    $tableHTML .= '</tr>';
}

$tableHTML .= '</tbody>';
$tableHTML .= '</table>';
echo $tableHTML;

echo '<div>';
?>
</body>
</html>
