<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css"/>
</head>
<body>
<?php

function generateTable($tableName, $conn)
{
    $tableHTML = '<table>';

    //// -- lekerdezzuk a tábla tartalmat
    $stid = oci_parse($conn, 'SELECT * FROM ' . $tableName);
    oci_execute($stid);

    //// -- eloszor csak az oszlopneveket kerem le
    $nfields = oci_num_fields($stid);
    $tableHTML .= '<thead><tr>';
    for ($i = 1; $i <= $nfields; $i++) {
        $field = oci_field_name($stid, $i);
        $tableHTML .= '<th>' . $field . '</th>';
    }
    $tableHTML .= '</tr></thead>';

    //// -- ujra vegrehajtom a lekerdezest, es kiiratom a sorokat
    oci_execute($stid);

    $tableHTML .= '<tbody>';
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $tableHTML .= '<tr>';
        foreach ($row as $item) {
            $tableHTML .= '<td>' . $item . '</td>';
        }
        $tableHTML .= '</tr>';
    }
    $tableHTML .= '</tbody>';
    $tableHTML .= '</table>';

    return $tableHTML;
}

$tns = "
(DESCRIPTION =
    (ADDRESS_LIST =
      (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))
    )
    (CONNECT_DATA =
      (SID = orania2)
    )
  )";

$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, 'UTF8');

echo '<div class="container">';

session_start();
if(!isset($_SESSION["felhasznalo"])){
    header('Location: login.php');
} else {

    echo '<form action="logout.php" method="post">';
    echo '<input type="submit" value="Kijelentkezés">';
    echo "<input type='button' value='Ismerősők keresése' onclick=\"window.location.href='users.php?kereso='\" />";
    echo "<input type='button' value='Profil' onclick=\"window.location.href='profile.php'\" />";
    echo "<input type='button' value='Poszt írása' onclick=\"window.location.href='newpost.php'\" />";
    echo "<input type='button' value='Feed' onclick=\"window.location.href='feed.php'\" />";
    echo "<input type='button' value='Barátok' onclick=\"window.location.href='friends.php'\" />";
    echo "<input type='button' value='Postaláda' onclick=\"window.location.href='postalada.php'\" />";
    echo "<input type='button' value='Születésnaposok' onclick=\"window.location.href='szulnapok.php'\" />";
    echo "<input type='button' value='Csoportok keresése' onclick=\"window.location.href='group.php?kereso='\" />";
    echo "<input type='button' value='Csoport létrehozása' onclick=\"window.location.href='newgroup.php'\" />";
    echo "<input type='button' value='Események keresése' onclick=\"window.location.href='events.php?kereso='\" />";
    echo "<input type='button' value='Esemény létrehozása' onclick=\"window.location.href='newevent.php'\" />";
    echo "<input type='button' value='Csoportok' onclick=\"window.location.href='groups.php'\" />";
    echo "<input type='button' value='Fényképalbumok' onclick=\"window.location.href='galery.php'\" />";
    echo '</form>';
}

echo '<h2>Az Felhasznalo tábla adatai: </h2>';
echo generateTable('Felhasznalo', $conn);
echo '<br><h2>Az Esemeny tábla adatai: </h2>';
echo generateTable('Esemeny', $conn);
echo '<br><h2>A Csoport tábla adatai: </h2>';
echo generateTable('Csoport', $conn);
echo '<br><h2>A Bejegyzés tábla adatai: </h2>';
echo generateTable('Bejegyzes', $conn);
echo '<br><h2>Az Uzenet tábla adatai: </h2>';
echo generateTable('Uzenet', $conn);
echo '<br><h2>Az Komment tábla adatai: </h2>';
echo generateTable('Komment', $conn);
echo '<br><h2>Az Fenykep tábla adatai: </h2>';
echo generateTable('Fenykep', $conn);
echo '</div>';

oci_close($conn);
?>
</body>
</html>