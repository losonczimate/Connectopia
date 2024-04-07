<?php
session_start();
if (isset($_SESSION["felhasznalo"])) {
    header('Location: login.php');
}
?>
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

if ($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['bejegyzes_id'])) {
    $sql_delete_post = "BEGIN del_post(:bid); END;";
    $stid_delete_post = oci_parse($conn, $sql_delete_post);
    oci_bind_by_name($stid_delete_post, ':bid', $_POST['bejegyzes_id']);
    oci_execute($stid_delete_post);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' and !isset($_POST['bejegyzes_id'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql_update_user = "UPDATE felhasznalo SET felh_nev = :nev, felh_email = :email, felh_jelszo = :jelszo WHERE felh_id = :felh_id";
    $stid_update_user = oci_parse($conn, $sql_update_user);
    oci_bind_by_name($stid_update_user, ':felh_id', $_SESSION["felhasznalo"]["FELH_ID"]);
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
        <label for='name'>Név:</label>
        <input type='text' id='name' name='name' value='" . $_SESSION["felhasznalo"]["FELH_NEV"] . "' required><br>

        <label for='email'>E-mail:</label>
        <input type='email' id='email' name='email' value='" . $_SESSION["felhasznalo"]["FELH_EMAIL"] . "' required><br>

        <label for='password'>Jelszó:</label>
        <input type='password' id='password' name='password' value='" . $_SESSION["felhasznalo"]["FELH_JELSZO"] . "' required><br>

        <input type='submit' value='Frissítés'>
        <input type='button' value='Főoldal' onclick=\"window.location.href='all_table.php'\" />
    </form>";

$stid = oci_parse($conn, 'SELECT p.*, f.url, u.nev, f.id as kepid
                          FROM poszt p
                          LEFT JOIN fenykep f ON p.fenykep_id = f.id
                          LEFT JOIN felhasznalo u ON p.felh_id = u.felh_id
                          WHERE p.felh_id = :felh_id');
oci_bind_by_name($stid, ':felh_id', $_SESSION["felhasznalo"]["FELH_ID"]);
oci_execute($stid);

while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo '<div class="post">';
    echo '<div class="postfejlec">' . $row["BEJEGYZES_IDOPONT"] . "  " . $row["FELH_NEV"] . '</div>';
    echo '<div class="postleiras"> Leírás: ' . $row["BEJEGYZES_LEIRAS"] . '</div>';
    echo '<div class="kep"><img src = ' . $row["KEP_URL"] . ' ></div>';

    echo "<td><form action='profile.php' method='post'>
             <input type='hidden' name='posztid' value='$row[ID]'/>
             <input type='submit' value='Törlés' />
          </form></td>";

    echo '</div>';
}
echo '<div>';
?>
</body>
</html>