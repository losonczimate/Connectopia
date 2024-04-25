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

$felh_id = $_SESSION['felhasznalo']['FELH_ID'];

$sql = "SELECT
    CASE
        WHEN ismeros_nev = best_friend_nev THEN ismeros_nev || ' (legjobb barát)'
        ELSE ismeros_nev
    END AS ismeros_nev,
    osszes_uzenet
FROM (
    SELECT
        CASE
            WHEN uzenetek.kuldo = :felh_id THEN fogado_nev.felh_nev
            ELSE kuldo_nev.felh_nev
        END AS ismeros_nev,
        COUNT(*) AS osszes_uzenet,
        RANK() OVER (ORDER BY COUNT(*) DESC) AS rang,
        FIRST_VALUE(CASE
                       WHEN uzenetek.kuldo = :felh_id THEN fogado_nev.felh_nev
                       ELSE kuldo_nev.felh_nev
                   END) OVER (ORDER BY COUNT(*) DESC) AS best_friend_nev
    FROM
        Uzenet uzenetek
    JOIN
        Felhasznalo kuldo_nev ON uzenetek.kuldo = kuldo_nev.felh_id
    JOIN
        Felhasznalo fogado_nev ON uzenetek.fogado = fogado_nev.felh_id
    WHERE
        uzenetek.kuldo = :felh_id OR uzenetek.fogado = :felh_id
    GROUP BY
        CASE
            WHEN uzenetek.kuldo = :felh_id THEN fogado_nev.felh_nev
            ELSE kuldo_nev.felh_nev
        END
)
WHERE
    rang = 1";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ':felh_id', $felh_id);
oci_execute($stid);

echo "<h2>Legjobb barátod</h2>";
echo "<table border='1'>
        <tr>
            <th>Ismerős neve</th>
            <th>Üzenetek száma</th>
        </tr>";

while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo "<tr>";
    echo "<td>" . $row['ISMEROS_NEV'] . "</td>";
    echo "<td>" . $row['OSSZES_UZENET'] . "</td>";
    echo "</tr>";
}

echo "</table>";

oci_free_statement($stid);

$stid = oci_parse($conn, 'SELECT u.*, f.felh_nev, TO_CHAR(u.kuldes_ideje, \'YYYY.MM.DD HH24:MI\') as idopontformatted
                          FROM uzenet u
                          LEFT JOIN felhasznalo f ON u.kuldo = f.felh_id
                          WHERE u.fogado = :felh_id');
oci_bind_by_name($stid, ':felh_id', $_SESSION['felhasznalo']['FELH_ID']);
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
    echo '<div class="postfejlec">' . $row["IDOPONTFORMATTED"] . "  " . $row["FELH_NEV"] . '</div>';
    echo '<div class="postleiras"> Leírás: ' . $row["TARTALOM"] . '</div>';

    echo '</div>';
}

echo '<div>';
?>
</body>
</html>