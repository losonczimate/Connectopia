<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css" />
</head>
<script>
    function keres() {
        const kereso = document.getElementById("kereso");
        const searchParams = new URLSearchParams();
        searchParams.append('kereso', kereso.value);
        window.location.href = window.location.pathname + '?' + searchParams.toString();
    }
</script>
<body>
<div class="container">
    <?php
    session_start();

    // Adatbáziskapcsolat létrehozása
    $tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))) (CONNECT_DATA = (SID = orania2)))";
    $conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns,"UTF8");

    // Ellenőrizzük a kapcsolatot
    if (!$conn) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }

    // Ha még nincs bejelentkezve a felhasználó, átirányítjuk a login panelre.
    if (!isset($_SESSION['felhasznalo'])) {
        header('Location: login.php');
        exit;
    }else{
        echo '<form action="logout.php" method="post" autocomplete="off">';
        echo '<input type="text" id="kereso" name="kereso" placeholder="Keresés">';
        echo "<input type='button' value='Keres' onclick=keres() />";
        echo "<input type='button' value='Főoldal' onclick=\"window.location.href='all_table.php'\" />";
        echo '</form><br>';
    }

    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['clubid'])) //clubid csoport id
    {
        joinclub($_POST['clubid']);
    }
    function joinclub($id){
        global $conn;
        $sql_insert_ism = "INSERT INTO csoporttagok (felhid, csoportid) VALUES (:felhid, :csoport_id)";
        $stid_insert_ism = oci_parse($conn, $sql_insert_ism);
        oci_bind_by_name($stid_insert_ism, ':felhid', $_SESSION['felhasznalo']['FELH_ID']);
        oci_bind_by_name($stid_insert_ism, ':csoport_id', $id);
        if(oci_execute($stid_insert_ism)){
            header('Location: all_table.php');
            exit;
        };
    }
    function generateTable(){
        global $conn;
        $tableHTML = '<table>';
        $searchTerm = isset($_GET['kereso']) ? $_GET['kereso'] : '';
        //// -- lekerdezzuk a tábla tartalmat
        $stid = oci_parse($conn, "SELECT csoport_nev, csoport_leiras, COUNT(felhid) AS tagok, csoport_id FROM csoport INNER JOIN csoporttagok ON csoportid = csoport_id WHERE csoport_nev LIKE '%$searchTerm%' OR csoport_leiras LIKE '%$searchTerm%' GROUP BY csoport_id, csoport_nev, csoport_leiras");
        oci_execute($stid);

        //// -- eloszor csak az oszlopneveket kerem le
        $nfields = oci_num_fields($stid);
        $tableHTML .= '<thead><tr>';
        for ($i = 1; $i<=$nfields; $i++){
            $field = oci_field_name($stid, $i);
            $tableHTML .= '<th>' . $field . '</th>';
        }
        $tableHTML .= '<th>CSATLAKOZAS</th></tr></thead>';

        //// -- ujra vegrehajtom a lekerdezest, es kiiratom a sorokat
        oci_execute($stid);

        $tableHTML .= '<tbody>';
        while ( $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $tableHTML .= '<tr>';
            foreach ($row as $item) {
                $tableHTML .= '<td>' . $item . '</td>';
            }
            $tableHTML .= "<td><form id='gomb' action='group.php?kereso=' method='post'>
                           <input type='submit' name='clubid' value='$row[CSOPORT_ID]' />
                       </form></td>";
            $tableHTML .= '</tr>';
        }
        $tableHTML .= '</tbody>';
        $tableHTML .= '</table>';

        return $tableHTML;
    }
    if(isset($_GET['kereso'])){
        echo generateTable();
    }
    ?>
</div>
</body>
</html>