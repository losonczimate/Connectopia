<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css"/>
</head>
<body class="galery">
<div class="container">
    <?php
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

    $sql_insert_ism = "SELECT felh_id2 FROM ismerosok WHERE felh_id1 = :felh_id";
    $stid_insert_ism = oci_parse($conn, $sql_insert_ism);
    oci_bind_by_name($stid_insert_ism, ':felh_id', $_SESSION['felhasznalo']['FELH_ID']);
    oci_execute($stid_insert_ism);
    $friends = array();
    while ($row = oci_fetch_array($stid_insert_ism, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $friends[] = $row['FELH_ID2'];
    }

    function generateTable($felh_id, $conn, $usename)
    {
        $stid = oci_parse($conn, "SELECT felh_nev FROM felhasznalo WHERE felh_id = $felh_id");
        oci_execute($stid);
        $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
        if ($usename) {
            $tableHTML = '<table><thead><tr><th colspan="3">' . $row['FELH_NEV'] . ' képei</th></tr></thead>';

        } else {
            $tableHTML = '<table><thead><tr><th colspan="3">Saját fényképek</th></tr></thead>';
        }

        $stid = oci_parse($conn, "SELECT kep_url FROM fenykep, bejegyzes WHERE fenykep.kep_id = bejegyzes.fenykep_id AND bejegyzes.felhid = '$felh_id'");
        oci_execute($stid);

        $i = 1;
        $tableHTML .= '<tbody><tr>';
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            if ($i > 3) {
                $tableHTML .= '</tr><tr>';
                $i = 1;
            }
            $tableHTML .= '<td><img src="' . $row['KEP_URL'] . '" alt=""></td>';
            $i++;
        }
        while ($i <= 3) {
            $tableHTML .= '<td></td>';
            $i++;
        }
        $tableHTML .= '</tr></tbody>';
        $tableHTML .= '</table>';

        return $tableHTML;
    }

    echo generateTable($_SESSION['felhasznalo']['FELH_ID'], $conn, false);
    echo '<h2>Ismerősök képei</h2>';
    foreach ($friends as $f) {
        echo '<br>';
        echo generateTable($f, $conn, true);
    }
    ?>
</div>
</body>
</html>
