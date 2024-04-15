<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css"/>
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
    $conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, "UTF8");

    // Ellenőrizzük a kapcsolatot
    if (!$conn) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }

    // Ha már be van jelentkezve a felhasználó, átirányítjuk a főoldalra
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

    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['friendid']))
    {
        friend($_POST['friendid']);
    }
    function friend($id){
        global $conn;
        $sql_insert_ism = "INSERT INTO ismerosok (felh_id1, felh_id2) VALUES (:felh_id1, :felh_id2)";
        $stid_insert_ism = oci_parse($conn, $sql_insert_ism);
        oci_bind_by_name($stid_insert_ism, ':felh_id1', $_SESSION['felhasznalo']['FELH_ID']);
        oci_bind_by_name($stid_insert_ism, ':felh_id2', $id);
        if(oci_execute($stid_insert_ism)){
            header('Location: all_table.php');
            exit;
        };
    }

    function generateTable($tableName, $conn){
        $tableHTML = '<table>';
        $searchTerm = isset($_GET['kereso']) ? $_GET['kereso'] : '';
        //// -- lekerdezzuk a tábla tartalmat
        $stid = oci_parse($conn, "SELECT felh_email, felh_szulinap, felh_nev, felh_id FROM $tableName WHERE felh_email LIKE '%$searchTerm%' OR felh_nev LIKE '%$searchTerm%'");
        oci_execute($stid);

        //// -- eloszor csak az oszlopneveket kerem le
        $nfields = oci_num_fields($stid);
        $tableHTML .= '<thead><tr>';
        for ($i = 1; $i<=$nfields; $i++){
            $field = oci_field_name($stid, $i);
            $tableHTML .= '<th>' . $field . '</th>';
        }
        $tableHTML .= '<th>FELVESZ</th></tr></thead>';

        //// -- ujra vegrehajtom a lekerdezest, es kiiratom a sorokat
        oci_execute($stid);

        $tableHTML .= '<tbody>';
        while ( $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $tableHTML .= '<tr>';
            foreach ($row as $item) {
                $tableHTML .= '<td>' . $item . '</td>';
            }
            $tableHTML .= "<td><form id='gomb' action='users.php?kereso=' method='post'>
                           <input type='submit' name='friendid' value='$row[FELH_ID]' />
                       </form></td>";
            $tableHTML .= '</tr>';
        }
        $tableHTML .= '</tbody>';
        $tableHTML .= '</table>';

        return $tableHTML;
    }
    if(isset($_GET['kereso'])){
        echo generateTable('Felhasznalo', $conn);}

    ?>
</div>
</body>
</html>
