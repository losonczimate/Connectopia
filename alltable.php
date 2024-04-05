<html>
<head>
    <link rel=stylesheet type="text/css" href="style.css" />
</head>
<script>

</script>
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

$conn = oci_connect('C##TRPV59', 'Hisztiteam', $tns,'UTF8');

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['Valami hiba van!'], ENT_QUOTES), E_USER_ERROR);
} else {
    echo "<script>alert('Működő kapcsolat!');</script>";
}

echo '<h2>Az Felhasznalo tábla adatai: </h2>';
echo generateTable('Felhasznalo', $conn);
echo '<br><h2>Az Esemeny tábla adatai: </h2>';
echo generateTable('Esemeny', $conn);
echo '<br><h2>Az Klub tábla adatai: </h2>';
echo generateTable('Csoport', $conn);
echo '<br><h2>Az Poszt tábla adatai: </h2>';
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
