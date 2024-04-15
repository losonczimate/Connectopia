<html>
<head>
    <link rel=stylesheet type="text/css" href="../CSS/connectopia.css"/>
</head>
<body>
<?php
echo '<div class="container">';

$tns = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))) (CONNECT_DATA = (SID = orania2)))";
$conn = oci_connect('C##Y6LP3X', 'Asdyxc123', $tns, 'UTF8');

// Ellenőrizzük a kapcsolatot
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}
session_start();
if(!isset($_SESSION["felhasznalo"])){
    header('Location: login.php');
}


if ($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['posztid'])) {
    $sql_delete_post = "BEGIN del_post(:pid); END;";
    $stid_delete_post = oci_parse($conn, $sql_delete_post);
    oci_bind_by_name($stid_delete_post, ':pid', $_POST['posztid']);
    oci_execute($stid_delete_post);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' and !isset($_POST['posztid'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql_update_user = "UPDATE felhasznalo SET felh_nev = :nev, felh_email = :email, felh_jelszo = :jelszo WHERE felh_id = :felh_id";
    $stid_update_user = oci_parse($conn, $sql_update_user);
    oci_bind_by_name($stid_update_user, ':felh_id', $_SESSION['felhasznalo']["FELH_ID"]);
    oci_bind_by_name($stid_update_user, ':nev', $name);
    oci_bind_by_name($stid_update_user, ':email', $email);
    oci_bind_by_name($stid_update_user, ':jelszo', $password);
    if (oci_execute($stid_update_user)) {
        $_SESSION['felhasznalo']['FELH_NEV'] = $name;
        $_SESSION['felhasznalo']['FELH_EMAIL'] = $email;
        $_SESSION['felhasznalo']['FELH_JELSZO'] = $password;
        header('Location: profile.php');
        exit;
    }
}

echo "<h1>Profil</h1>
    <form action='" . $_SERVER['PHP_SELF'] . "' method='post'>
        <h2>Szia ". $_SESSION["felhasznalo"]["FELH_NEV"]."</h2>
        <h1>Itt módosíthatod az adataidat!</h1>
        <label for='name'>Név:</label>
        <input type='text' id='name' name='name' value='" . $_SESSION["felhasznalo"]["FELH_NEV"]."' required><br>

        <label for='email'>E-mail:</label>
        <input type='email' id='email' name='email' value='" . $_SESSION["felhasznalo"]["FELH_EMAIL"]. "' required><br>

        <label for='password'>Jelszó:</label>
        <input type='password' id='password' name='password' value='" . $_SESSION["felhasznalo"]["FELH_JELSZO"]. "' required><br>

        <input type='submit' value='Frissítés'>
        <input type='button' value='Főoldal' onclick=\"window.location.href='all_table.php'\" />
    </form>";

$stid = oci_parse($conn, 'SELECT b.*, f.kep_url, u.FELH_NEV, f.kep_id as kepid 
                                FROM bejegyzes b 
                                LEFT JOIN fenykep f ON b.fenykep_id = f.kep_id 
                                LEFT JOIN felhasznalo u ON b.felhid = u.felh_id 
                                WHERE b.felhid = :felh_id');
oci_bind_by_name($stid, ':felh_id', $_SESSION['felhasznalo']["FELH_ID"]);
oci_execute($stid);

if (oci_fetch_all($stid, $result) == 0) {
    echo "<h2>Nincs még bejegyzés!</h2>";
} else {
    foreach ($result as $row) {
        echo '<div class="post">';
        echo '<div class="postfejlec">' . $row["BEJEGYZES_IDOPONT"] . "  " . $row["FELH_NEV"] . '</div>';
        echo '<div class="postleiras"> Leírás: ' . $row["BEJEGYZES_LEIRAS"] . '</div>';
        echo '<div class="kep"><img src = ' . $row["KEP_URL"] . ' ></div>';

        echo "<td><form action='profile.php' method='post'>
                 <input type='hidden' name='posztid' value='$row[BEJEGYZES_ID]'/>
                 <input type='submit' value='Törlés' />
              </form></td>";

        echo '</div>';
    }
}
echo '<div>';
?>
