<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css" />
</head>
<body>
<?php

function generateTable($tableName, $conn){
    $tableHTML = '<table>';

    //// -- lekerdezzuk a tábla tartalmat
    $stid = oci_parse($conn, 'SELECT * FROM ' . $tableName);
    oci_execute($stid);

    //// -- eloszor csak az oszlopneveket kerem le
    $nfields = oci_num_fields($stid);
    $tableHTML .= '<thead><tr>';
    for ($i = 1; $i<=$nfields; $i++){
        $field = oci_field_name($stid, $i);
        $tableHTML .= '<th>' . $field . '</th>';
    }
    $tableHTML .= '</tr></thead>';

    //// -- ujra vegrehajtom a lekerdezest, es kiiratom a sorokat
    oci_execute($stid);

    $tableHTML .= '<tbody>';
    while ( $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
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

$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns,'UTF8');


echo '<div class="container">';



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