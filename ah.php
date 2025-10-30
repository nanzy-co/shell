<?php
$target_dir = "uploads/";

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$file_name = basename($_FILES["fileToUpload"]["name"]);
$target_file = $target_dir . $file_name;
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

if($fileType != "txt") {
    echo "GAK BISA, ONLY TXT YA BRO";
    $uploadOk = 0;
}

if ($uploadOk == 0) {
    echo "<br>Gak Ke Upload";
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "Done". htmlspecialchars($file_name). "telah berhasil diunggah";
        echo "<br>Dah Ya Otw Bundir";

        if (unlink(__FILE__)) {
            echo "<br>Done Bundir";
        } else {
            echo "<br>Waduh Gak Kehapus Brok";
        }
    } else {
        echo "Yah Gagal Upload";
    }
}
?>