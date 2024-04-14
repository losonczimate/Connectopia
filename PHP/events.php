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

    // Ha még nincs bejelentkezve a felhasználó, átirányítjuk a főoldalra
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

    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['esemenyid'])) #csoportid eventid
    {
        joinevent($_POST['esemenyid']);
    }
    function joinevent($id){
        global $conn;
        $sql_insert_ism = "INSERT INTO esemenytagok (felhid, esemenyid) VALUES (:felh_id, :esemeny_id)";
        $stid_insert_ism = oci_parse($conn, $sql_insert_ism);
        oci_bind_by_name($stid_insert_ism, ':felh_id', $_SESSION['felh_id']);
        oci_bind_by_name($stid_insert_ism, ':esemeny_id', $id);
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
        $stid = oci_parse($conn, "SELECT esemeny_id ,nev, leiras, idopont, COUNT(felhid) AS erdeklodok FROM esemeny INNER JOIN esemenytagok ON esemenyid = esemeny_id WHERE nev LIKE '%$searchTerm%' OR leiras LIKE '%$searchTerm%' GROUP BY esemeny_id, nev, leiras, idopont");
        oci_execute($stid);

        //// -- eloszor csak az oszlopneveket kerem le
        $nfields = oci_num_fields($stid);
        $tableHTML .= '<thead><tr>';
        for ($i = 1; $i<=$nfields; $i++){
            $field = oci_field_name($stid, $i);
            $tableHTML .= '<th>' . $field . '</th>';
        }
        $tableHTML .= '<th>ÉRDEKEL</th></tr></thead>';

        //// -- ujra vegrehajtom a lekerdezest, es kiiratom a sorokat
        oci_execute($stid);

        $tableHTML .= '<tbody>';
        while ( $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $tableHTML .= '<tr>';
            foreach ($row as $item) {
                $tableHTML .= '<td>' . $item . '</td>';
            }
            $tableHTML .= "<td><form id='gomb' action='events.php?kereso=' method='post'>
                           <input type='submit' name='esemeny_id' value='Ok'/>
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