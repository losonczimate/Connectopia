<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css"/>
</head>
<body>
<div class="container">
    <?php
    session_start();

    // Adatbáziskapcsolat létrehozása
    $tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))) (CONNECT_DATA = (SID = orania2)))";
    $conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, 'UTF8');

    // Ellenőrizzük a kapcsolatot
    if (!$conn) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }

    // Ha még nincs bejelentkezve a felhasználó, átirányítjuk a főoldalra
    if (!isset($_SESSION['felhasznalo'])) {
        header('Location: login.php');
        exit;
    } else {
        echo '<form action="logout.php" method="post">';
        echo "<input type='button' value='Főoldal' onclick=\"window.location.href='all_table.php'\" />";
        echo '</form><br>';
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset($_POST['clubdel'])) {
            leavegroup($_POST['clubdel'], 'club');
        } elseif (isset($_POST['eventdel'])) {
            leavegroup($_POST['eventdel'], 'event');
        }
    }
    function leavegroup($id, $type)
    {
        global $conn;
        $user = $_SESSION['felhasznalo']['FELH_ID'];
        if ($type == 'club') {
            $stid = oci_parse($conn, "DELETE FROM csoporttagok WHERE felhid = $user AND csoportid = $id");
        } else {
            $stid = oci_parse($conn, "DELETE FROM esemenytagok WHERE felhid = $user AND esemenyid = $id");
        }
        if (oci_execute($stid)) {
            header('Location: groups.php');
            exit;
        };
    }

    function generateTable($type)
    {
        global $conn;
        $tableHTML = '<table>';
        //// -- lekerdezzuk a tábla tartalmat
        $user = $_SESSION['felhasznalo']['FELH_ID'];
        if ($type == 'club') {
            $stid = oci_parse($conn, "SELECT csoport_nev as Név, csoport_leiras as Leírás, csoport_id FROM csoport INNER JOIN csoporttagok ON csoportid = csoport_id WHERE felhid = $user");
        } else {
            $stid = oci_parse($conn, "SELECT nev as Név, leiras as Leírás, idopont as Kezdeti_dátum, esemeny_id FROM esemeny INNER JOIN esemenytagok ON esemenyid = esemeny_id WHERE felhid = $user");
        }
        oci_execute($stid);

        //// -- eloszor csak az oszlopneveket kerem le
        $nfields = oci_num_fields($stid);
        $tableHTML .= '<thead><tr>';
        for ($i = 1; $i <= $nfields; $i++) {
            $field = oci_field_name($stid, $i);
            $tableHTML .= '<th>' . $field . '</th>';
        }
        if ($type == 'club') {
            $tableHTML .= '<th>Részletek</th><th>Kilépés</th></tr></thead>';
        } else {
            $tableHTML .= '<th>Kilépés</th></tr></thead>';
        }

        //// -- ujra vegrehajtom a lekerdezest, es kiiratom a sorokat
        oci_execute($stid);

        $tableHTML .= '<tbody>';
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $tableHTML .= '<tr>';
            foreach ($row as $item) {
                $tableHTML .= '<td>' . $item . '</td>';
            }

            if ($type == 'club') {
                $tableHTML .= "<td><form id='gomb' action='group.php?id=$row[CSOPORT_ID]' method='post'>";
                $tableHTML .= "<input type='submit' name='clubid' value='$row[CSOPORT_ID]' /></form></td>";
                $tableHTML .= "<td><form id='gomb' action='groups.php' method='post'>";
                $tableHTML .= "<input type='submit' name='clubdel' value='$row[CSOPORT_ID]' />";
            } else {
                $tableHTML .= "<td><form id='gomb' action='groups.php' method='post'>";
                $tableHTML .= "<input type='submit' name='eventdel' value='$row[ESEMENY_ID]' />";
            }
            $tableHTML .= '</form></td></tr>';
        }
        $tableHTML .= '</tbody>';
        $tableHTML .= '</table>';

        return $tableHTML;
    }

    echo '<h2>Csoportjaim</h2><br>';
    echo generateTable('club');
    echo '<h2>Eseményeim</h2><br>';
    echo generateTable('event');
    ?>
</div>
</body>
</html>