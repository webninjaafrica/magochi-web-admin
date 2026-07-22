<?php
// ================================================================
// MAGOCHI HOST ADMIN v10.0 – ENTERPRISE EDITION (FULLY FIXED)
// Complete Web Hosting Control Panel
// ================================================================

// ================================================================
// SYSTEM CONFIGURATION
// ================================================================
$__dev = array(
    'name' => base64_decode('S2VsdmluIE1hZ29jaGk='),
    'phone' => base64_decode('KzI1NDcxODI2NTcwOA=='),
    'email' => base64_decode('YWRtaW5Ad2VibmluamFmcmljYS5jb20=')
);
define('_MAGOCHI_DEV_', true);
$__sig = hash('sha256', 'magochi_host_2026_secure');

// ================================================================
// CONFIGURATION
// ================================================================
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'magochi_host';
$admin_password = 'MagochiAdmin2026!';
$api_secret = 'MagochiHostAPI2026SecureKey!';
$version = '10.0';
$server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

// ================================================================
// DATABASE CONNECTION
// ================================================================
$conn = null;
$connected = false;

$attempts = array(
    array('host' => 'localhost', 'user' => $db_user, 'pass' => $db_pass, 'port' => 3306),
    array('host' => 'localhost', 'user' => $db_user, 'pass' => '', 'port' => 3306),
    array('host' => '127.0.0.1', 'user' => $db_user, 'pass' => $db_pass, 'port' => 3306),
    array('host' => '127.0.0.1', 'user' => $db_user, 'pass' => '', 'port' => 3306),
);

foreach ($attempts as $a) {
    $test = @new mysqli($a['host'], $a['user'], $a['pass'], null, $a['port']);
    if (!$test->connect_error) {
        $conn = $test;
        $connected = true;
        break;
    }
}

if (!$connected) {
    $sockets = array(
        '/data/data/com.termux/files/usr/var/run/mysqld/mysqld.sock',
        '/var/run/mysqld/mysqld.sock',
        '/tmp/mysql.sock',
        '/var/mysql/mysql.sock',
        '/run/mysqld/mysqld.sock',
        '/var/lib/mysql/mysql.sock'
    );
    foreach ($sockets as $sock) {
        if (file_exists($sock)) {
            $test = @new mysqli('localhost', $db_user, $db_pass, null, null, $sock);
            if (!$test->connect_error) {
                $conn = $test;
                $connected = true;
                break;
            }
            $test = @new mysqli('localhost', $db_user, '', null, null, $sock);
            if (!$test->connect_error) {
                $conn = $test;
                $connected = true;
                break;
            }
        }
    }
}

if (!$connected) {
    die("<h2>MySQL Connection Error</h2><p>Start MySQL: <code>mysqld_safe &amp;</code></p>");
}

// ================================================================
// CREATE DATABASE & TABLES
// ================================================================
$conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
$conn->select_db($db_name);

$tables = array(
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        full_name VARCHAR(100),
        role ENUM('admin','user') DEFAULT 'user',
        status ENUM('active','suspended') DEFAULT 'active',
        disk_limit INT DEFAULT 100,
        disk_used INT DEFAULT 0,
        root_dir VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )",
    "CREATE TABLE IF NOT EXISTS domains (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        domain VARCHAR(255) NOT NULL,
        folder VARCHAR(255) NOT NULL,
        nameserver1 VARCHAR(100),
        nameserver2 VARCHAR(100),
        ssl_cert TEXT,
        ssl_key TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS dns_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        domain_id INT NOT NULL,
        type VARCHAR(10) NOT NULL,
        name VARCHAR(255) NOT NULL,
        value TEXT NOT NULL,
        ttl INT DEFAULT 3600,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS user_databases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        db_name VARCHAR(255) NOT NULL,
        db_user VARCHAR(50) NOT NULL,
        db_pass VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS cron_jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        url VARCHAR(500) NOT NULL,
        schedule VARCHAR(50) NOT NULL,
        enabled TINYINT DEFAULT 1,
        last_run TIMESTAMP NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS ftp_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        home_dir VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS backups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255),
        file_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS service_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        url VARCHAR(500) NOT NULL,
        icon VARCHAR(50) DEFAULT 'fa-link',
        color VARCHAR(20) DEFAULT '#6C63FF',
        description VARCHAR(255),
        category VARCHAR(50) DEFAULT 'General',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(255),
        details TEXT,
        ip VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS api_keys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        api_key VARCHAR(64) UNIQUE NOT NULL,
        permissions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS api_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        api_key_id INT,
        endpoint VARCHAR(255),
        method VARCHAR(10),
        ip VARCHAR(50),
        response_code INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS wordpress_installs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        domain_id INT NOT NULL,
        path VARCHAR(255) NOT NULL,
        version VARCHAR(20) NOT NULL,
        admin_user VARCHAR(50) NOT NULL,
        admin_pass VARCHAR(255) NOT NULL,
        admin_email VARCHAR(100) NOT NULL,
        status ENUM('installing','active','failed') DEFAULT 'installing',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS email_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(100) NOT NULL,
        event VARCHAR(50) NOT NULL,
        enabled TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS server_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        config_key VARCHAR(100) UNIQUE NOT NULL,
        config_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )"
);

foreach ($tables as $sql) {
    $conn->query($sql);
}

if ($check->num_rows == 0) {
    $hashed = password_hash($admin_password, PASSWORD_DEFAULT);
    $dev_name = base64_decode('S2VsdmluIE1hZ29jaGk=');
    $root_dir = $_SERVER['DOCUMENT_ROOT'] . '/admin_root';
    if (!is_dir($root_dir)) mkdir($root_dir, 0755, true);
    $admin_email = base64_decode('YWRtaW5Ad2VibmluamFmcmljYS5jb20=');
    
    // Use an array to avoid reference issues
    $data = array(
        'admin',
        $hashed,
        $dev_name,
        $admin_email,
        'admin',
        'active',
        9999,
        $root_dir
    );
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role, status, disk_limit, root_dir) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssis", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7]);
    $stmt->execute();
    $stmt->close();
}

$root = $_SERVER['DOCUMENT_ROOT'];
$dirs = array($root . '/domains', $root . '/uploads', $root . '/backups', $root . '/temp', $root . '/config');
foreach ($dirs as $d) {
    if (!is_dir($d)) mkdir($d, 0755, true);
}

// Generate server config file
$config_file = $root . '/config/db_config.php';
if (!file_exists($config_file)) {
    $config_content = "<?php\n";
    $config_content .= "// ================================================================\n";
    $config_content .= "// DATABASE CONFIGURATION – AUTO GENERATED\n";
    $config_content .= "// Generated: " . date('Y-m-d H:i:s') . "\n";
    $config_content .= "// ================================================================\n\n";
    $config_content .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
    $config_content .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
    $config_content .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";
    $config_content .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
    $config_content .= "define('API_SECRET', '" . addslashes($api_secret) . "');\n";
    $config_content .= "define('SERVER_URL', '" . addslashes($server_url) . "');\n";
    $config_content .= "define('VERSION', '" . addslashes($version) . "');\n";
    $config_content .= "?>";
    file_put_contents($config_file, $config_content);
}

// ================================================================
// FUNCTIONS
// ================================================================
function getUserDomains($id) { global $conn; return $conn->query("SELECT * FROM domains WHERE user_id=$id ORDER BY created_at DESC"); }
function getUserDatabases($id) { global $conn; return $conn->query("SELECT * FROM user_databases WHERE user_id=$id ORDER BY created_at DESC"); }
function getUserCrons($id) { global $conn; return $conn->query("SELECT * FROM cron_jobs WHERE user_id=$id ORDER BY created_at DESC"); }
function getUserServices($id) { global $conn; return $conn->query("SELECT * FROM service_links WHERE user_id IS NULL OR user_id=$id ORDER BY category,name"); }
function getUserBackups($id) { global $conn; return $conn->query("SELECT * FROM backups WHERE user_id=$id ORDER BY created_at DESC"); }
function getActivityLogs($id=null) { global $conn; $q = "SELECT * FROM activity_logs".($id?" WHERE user_id=$id":"")." ORDER BY created_at DESC LIMIT 100"; return $conn->query($q); }
function getUserApiKeys($id) { global $conn; return $conn->query("SELECT * FROM api_keys WHERE user_id=$id ORDER BY created_at DESC"); }
function getWordPressInstalls($id) { global $conn; return $conn->query("SELECT * FROM wordpress_installs WHERE user_id=$id ORDER BY created_at DESC"); }
function getDnsRecords($domain_id) { global $conn; return $conn->query("SELECT * FROM dns_records WHERE domain_id=$domain_id ORDER BY type, name"); }
function getUserEmailNotifications($id) { global $conn; return $conn->query("SELECT * FROM email_notifications WHERE user_id=$id"); }

function getDirectoryContents($dir, $user_root = null, $is_admin = false) {
    $items = array(); 
    if (is_dir($dir) && ($is_admin || strpos($dir, $user_root) === 0)){ 
        $scan = scandir($dir); 
        foreach($scan as $item){ 
            if($item != '.' && $item != '..'){ 
                $path = $dir . '/' . $item; 
                $items[] = array(
                    'name' => $item,
                    'path' => $path,
                    'is_dir' => is_dir($path),
                    'is_file' => is_file($path),
                    'size' => is_file($path) ? filesize($path) : 0,
                    'size_formatted' => is_file($path) ? formatSize(filesize($path)) : '-',
                    'modified' => date('Y-m-d H:i:s', filemtime($path)),
                    'permissions' => substr(sprintf('%o', fileperms($path)), -4),
                    'icon' => getFileIcon($path),
                    'extension' => is_file($path) ? pathinfo($path, PATHINFO_EXTENSION) : '',
                    'editable' => is_file($path) && in_array(pathinfo($path, PATHINFO_EXTENSION), array('php','html','htm','css','js','txt','sql','json','xml','yml','ini','htaccess','md'))
                ); 
            } 
        } 
        usort($items, function($a, $b){ 
            if($a['is_dir'] != $b['is_dir']) return $a['is_dir'] ? -1 : 1; 
            return strcasecmp($a['name'], $b['name']); 
        }); 
    } 
    return $items;
}

function formatSize($bytes){ 
    if($bytes == 0) return '0 B';
    $units = array('B','KB','MB','GB','TB');
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

function getFileIcon($path){ 
    if(is_dir($path)) return 'fa-folder'; 
    $ext = pathinfo($path, PATHINFO_EXTENSION); 
    $icons = array(
        'php' => 'fa-php','html' => 'fa-html5','css' => 'fa-css3','js' => 'fa-js',
        'txt' => 'fa-file-alt','log' => 'fa-file-alt','jpg' => 'fa-image','png' => 'fa-image',
        'gif' => 'fa-image','svg' => 'fa-image','pdf' => 'fa-file-pdf','doc' => 'fa-file-word',
        'docx' => 'fa-file-word','xls' => 'fa-file-excel','xlsx' => 'fa-file-excel',
        'zip' => 'fa-file-archive','tar' => 'fa-file-archive','gz' => 'fa-file-archive',
        'sql' => 'fa-database','json' => 'fa-code','xml' => 'fa-code',
        'yml' => 'fa-code','ini' => 'fa-cog','htaccess' => 'fa-lock','md' => 'fa-file-alt'
    ); 
    return isset($icons[strtolower($ext)]) ? $icons[strtolower($ext)] : 'fa-file';
}

function deleteFolder($path, $user_root = null, $is_admin = false){ 
    if(!$is_admin && $user_root && strpos($path, $user_root) !== 0) return false;
    if(!is_dir($path)) return false; 
    $items = scandir($path); 
    foreach($items as $item){ 
        if($item != '.' && $item != '..'){ 
            $full = $path . '/' . $item; 
            if(is_dir($full)) deleteFolder($full, $user_root, $is_admin); 
            else unlink($full); 
        } 
    } 
    return rmdir($path); 
}

function getServerInfo(){ 
    $info = array(); 
    $info['server_software'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown'; 
    $info['php_version'] = phpversion(); 
    $info['server_name'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost'; 
    $info['document_root'] = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : ''; 
    $info['server_ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1'; 
    $info['upload_max_filesize'] = ini_get('upload_max_filesize'); 
    $info['post_max_size'] = ini_get('post_max_size'); 
    $info['memory_limit'] = ini_get('memory_limit'); 
    $info['max_execution_time'] = ini_get('max_execution_time') . 's'; 
    $info['mysql_version'] = $GLOBALS['conn']->server_info; 
    return $info; 
}

function getDatabaseList(){ 
    global $conn; 
    $dbs = array(); 
    $res = $conn->query("SHOW DATABASES"); 
    while($row = $res->fetch_assoc()) $dbs[] = $row['Database']; 
    return $dbs; 
}

function backupDatabase($dbname){ 
    global $conn,$root; 
    $filepath = $root . '/backups/backup_' . $dbname . '_' . date('Y-m-d_H-i-s') . '.sql'; 
    $tables = array();
    $res = $conn->query("SHOW TABLES FROM `$dbname`");
    while($row = $res->fetch_row()) $tables[] = $row[0]; 
    $output = "-- Database: $dbname\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n"; 
    foreach($tables as $t){ 
        $res = $conn->query("SELECT * FROM `$dbname`.`$t`"); 
        $create = $conn->query("SHOW CREATE TABLE `$dbname`.`$t`"); 
        $create_row = $create->fetch_row(); 
        $output .= "DROP TABLE IF EXISTS `$t`;\n" . $create_row[1] . ";\n\n"; 
        if($res->num_rows > 0){ 
            while($row = $res->fetch_row()){ 
                $escaped = array();
                foreach($row as $val) {
                    $escaped[] = $conn->real_escape_string($val);
                }
                $output .= "INSERT INTO `$t` VALUES ('" . implode("', '", $escaped) . "');\n"; 
            } 
            $output .= "\n"; 
        } 
    } 
    file_put_contents($filepath, $output); 
    return $filepath; 
}

function calculateFolderSize($dir, $user_root = null, $is_admin = false){ 
    if(!$is_admin && $user_root && strpos($dir, $user_root) !== 0) return 0;
    $size = 0; 
    if(is_dir($dir)){ 
        $files = scandir($dir); 
        foreach($files as $f){ 
            if($f != '.' && $f != '..'){ 
                $path = $dir . '/' . $f; 
                if(is_dir($path)) $size += calculateFolderSize($path, $user_root, $is_admin); 
                else $size += filesize($path); 
            } 
        } 
    } 
    return $size; 
}

function generateApiKey(){ 
    return bin2hex(random_bytes(32)); 
}

function unzipFile($zipPath, $extractTo) {
    if (!extension_loaded('zip')) return array('success' => false, 'error' => 'Zip extension not loaded');
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) return array('success' => false, 'error' => 'Failed to open zip file');
    if (!is_dir($extractTo)) mkdir($extractTo, 0755, true);
    if ($zip->extractTo($extractTo)) { 
        $zip->close(); 
        return array('success' => true, 'files' => $zip->numFiles); 
    }
    $zip->close();
    return array('success' => false, 'error' => 'Failed to extract');
}

function downloadWordPress($version = 'latest') {
    $temp_dir = $_SERVER['DOCUMENT_ROOT'] . '/temp';
    if (!is_dir($temp_dir)) mkdir($temp_dir, 0755, true);
    $zip_path = $temp_dir . '/wordpress.zip';
    $url = 'https://wordpress.org/latest.zip';
    if ($version != 'latest') {
        $url = "https://wordpress.org/wordpress-$version.zip";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code == 200 && file_put_contents($zip_path, $data)) {
        return $zip_path;
    }
    return false;
}

function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Magochi Host <noreply@magochihost.com>\r\n";
    return mail($to, $subject, $message, $headers);
}

// ================================================================
// API SYSTEM
// ================================================================
if (isset($_GET['api']) && $_GET['api'] == 'v1') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
    
    $api_key = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : (isset($_GET['key']) ? $_GET['key'] : '');
    $method = $_SERVER['REQUEST_METHOD'];
    $endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';
    $response = array('status' => 'error', 'message' => 'Invalid request');
    $http_code = 400;
    
    // Validate API key
    if (!empty($api_key)) {
        $key_data = $conn->query("SELECT * FROM api_keys WHERE api_key = '$api_key'")->fetch_assoc();
        if ($key_data) {
            $user_id = $key_data['user_id'];
            $permissions = $key_data['permissions'];
            $api_key_id = $key_data['id'];
            
            // Log API call
            $conn->query("INSERT INTO api_logs (api_key_id, endpoint, method, ip, response_code) VALUES ($api_key_id, '$endpoint', '$method', '{$_SERVER['REMOTE_ADDR']}', 200)");
            
            // Handle endpoints
            switch ($endpoint) {
                case 'domains':
                    if ($method == 'GET') {
                        $domains = getUserDomains($user_id);
                        $data = array();
                        while ($d = $domains->fetch_assoc()) {
                            $data[] = $d;
                        }
                        $response = array('status' => 'success', 'data' => $data);
                        $http_code = 200;
                    } elseif ($method == 'POST' && $permissions != 'read') {
                        $domain = isset($_POST['domain']) ? $_POST['domain'] : '';
                        $folder = isset($_POST['folder']) ? $_POST['folder'] : str_replace(array('.', ' '), '_', $domain);
                        if (!empty($domain)) {
                            $stmt = $conn->prepare("INSERT INTO domains (user_id, domain, folder) VALUES (?, ?, ?)");
                            $stmt->bind_param("iss", $user_id, $domain, $folder);
                            if ($stmt->execute()) {
                                $response = array('status' => 'success', 'message' => 'Domain created', 'id' => $conn->insert_id);
                                $http_code = 201;
                            } else {
                                $response = array('status' => 'error', 'message' => 'Failed to create domain');
                                $http_code = 400;
                            }
                            $stmt->close();
                        }
                    }
                    break;
                    
                case 'databases':
                    if ($method == 'GET') {
                        $dbs = getUserDatabases($user_id);
                        $data = array();
                        while ($db = $dbs->fetch_assoc()) {
                            $data[] = $db;
                        }
                        $response = array('status' => 'success', 'data' => $data);
                        $http_code = 200;
                    }
                    break;
                    
                case 'cron':
                    if ($method == 'GET') {
                        $crons = getUserCrons($user_id);
                        $data = array();
                        while ($c = $crons->fetch_assoc()) {
                            $data[] = $c;
                        }
                        $response = array('status' => 'success', 'data' => $data);
                        $http_code = 200;
                    } elseif ($method == 'POST' && $permissions != 'read') {
                        $name = isset($_POST['name']) ? $_POST['name'] : '';
                        $url = isset($_POST['url']) ? $_POST['url'] : '';
                        $schedule = isset($_POST['schedule']) ? $_POST['schedule'] : '0 2 * * *';
                        if (!empty($name) && !empty($url)) {
                            $stmt = $conn->prepare("INSERT INTO cron_jobs (user_id, name, url, schedule) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("isss", $user_id, $name, $url, $schedule);
                            if ($stmt->execute()) {
                                $response = array('status' => 'success', 'message' => 'Cron job created', 'id' => $conn->insert_id);
                                $http_code = 201;
                            } else {
                                $response = array('status' => 'error', 'message' => 'Failed to create cron');
                                $http_code = 400;
                            }
                            $stmt->close();
                        }
                    }
                    break;
                    
                case 'backup':
                    if ($method == 'POST' && $permissions != 'read') {
                        $dbname = isset($_POST['dbname']) ? $_POST['dbname'] : '';
                        if (!empty($dbname)) {
                            $file = backupDatabase($dbname);
                            if ($file) {
                                $stmt = $conn->prepare("INSERT INTO backups (user_id, name, file_path) VALUES (?, ?, ?)");
                                $stmt->bind_param("iss", $user_id, basename($file), $file);
                                $stmt->execute();
                                $stmt->close();
                                $response = array('status' => 'success', 'message' => 'Backup created', 'file' => basename($file));
                                $http_code = 201;
                            } else {
                                $response = array('status' => 'error', 'message' => 'Backup failed');
                                $http_code = 400;
                            }
                        }
                    }
                    break;
                    
                case 'server':
                    if ($method == 'GET') {
                        $response = array('status' => 'success', 'data' => getServerInfo());
                        $http_code = 200;
                    }
                    break;
                    
                default:
                    $response = array('status' => 'error', 'message' => 'Invalid endpoint');
                    $http_code = 404;
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Invalid API key');
            $http_code = 401;
        }
    } else {
        $response = array('status' => 'error', 'message' => 'API key required');
        $http_code = 401;
    }
    
    http_response_code($http_code);
    echo json_encode($response);
    exit;
}

// ================================================================
// SESSION & AUTH
// ================================================================
session_start();

function isLoggedIn() { return isset($_SESSION['user_id']); }
function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
function getUserID() { return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; }
function getUserRole() { return isset($_SESSION['role']) ? $_SESSION['role'] : 'user'; }
function getUserRoot() { return isset($_SESSION['root_dir']) ? $_SESSION['root_dir'] : $_SERVER['DOCUMENT_ROOT']; }

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['root_dir'] = isset($row['root_dir']) ? $row['root_dir'] : $_SERVER['DOCUMENT_ROOT'];
            $_SESSION['is_switched'] = false;
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = " . $row['id']);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else $error = "Invalid password!";
    } else $error = "User not found or suspended!";
    $stmt->close();
}

if (isset($_POST['register'])) {
    $username = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $confirm = $_POST['reg_confirm'];
    $email = trim($_POST['reg_email']);
    $full_name = trim($_POST['reg_fullname']);
    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $reg_error = "All fields required!";
    } elseif ($password !== $confirm) {
        $reg_error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $reg_error = "Password must be at least 6 characters!";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $reg_error = "Username already exists!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $root_dir = $_SERVER['DOCUMENT_ROOT'] . '/users/' . $username;
            if (!is_dir($root_dir)) mkdir($root_dir, 0755, true);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role, status, root_dir) VALUES (?, ?, ?, ?, 'user', 'active', ?)");
            $stmt->bind_param("sssss", $username, $hashed, $email, $full_name, $root_dir);
            if ($stmt->execute()) {
                $reg_success = "Account created! Please login.";
            } else $reg_error = "Registration failed!";
            $stmt->close();
        }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ================================================================
// HANDLE ACTIONS
// ================================================================
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
$message = '';
$msg_type = 'success';
$user_id = getUserID();
$is_admin = isAdmin();
$root = $_SERVER['DOCUMENT_ROOT'];
$user_root = getUserRoot();
$domains_root = $root . '/domains';
$currentPath = isset($_GET['path']) ? $_GET['path'] : $user_root;

// Security: Restrict path for non-admin - ONLY USER'S ROOT
if (!$is_admin) {
    if (strpos($currentPath, $user_root) !== 0) {
        $currentPath = $user_root;
    }
    $realpath = realpath($currentPath);
    if ($realpath === false || strpos($realpath, $user_root) !== 0) {
        $currentPath = $user_root;
    }
}

// ================================================================
// ADMIN SWITCH USER
// ================================================================
if ($is_admin && isset($_GET['switch_user'])) {
    $target = intval($_GET['switch_user']);
    $u = $conn->query("SELECT * FROM users WHERE id=$target AND status='active'")->fetch_assoc();
    if ($u) {
        $_SESSION['original_admin_id'] = $user_id;
        $_SESSION['is_switched'] = true;
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['role'] = $u['role'];
        $_SESSION['full_name'] = $u['full_name'];
        $_SESSION['root_dir'] = isset($u['root_dir']) ? $u['root_dir'] : $_SERVER['DOCUMENT_ROOT'];
        $message = "Switched to " . $u['username'];
        $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Switch User', 'Switched to {$u['username']}', '{$_SERVER['REMOTE_ADDR']}')");
    } else { 
        $message = "User not found!"; 
        $msg_type = 'error'; 
    }
}

// ----- ADMIN RETURN FROM SWITCH -----
if (isset($_GET['return_from_user']) && isset($_SESSION['is_switched']) && $_SESSION['is_switched']) {
    if (isset($_SESSION['original_admin_id'])) {
        $admin = $conn->query("SELECT * FROM users WHERE id=" . $_SESSION['original_admin_id'])->fetch_assoc();
        if ($admin) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['full_name'] = $admin['full_name'];
            $_SESSION['root_dir'] = isset($admin['root_dir']) ? $admin['root_dir'] : $_SERVER['DOCUMENT_ROOT'];
            $_SESSION['is_switched'] = false;
            unset($_SESSION['original_admin_id']);
            $message = "Returned to admin";
        }
    }
}

// ================================================================
// PHP SETTINGS MANAGEMENT
// ================================================================
if ($is_admin && isset($_POST['update_php_settings'])) {
    $php_ini_path = php_ini_loaded_file();
    $new_settings = array();
    $settings_fields = array('upload_max_filesize', 'post_max_size', 'memory_limit', 'max_execution_time', 'max_input_time');
    foreach ($settings_fields as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            $new_settings[$field] = trim($_POST[$field]);
        }
    }
    if (!empty($new_settings) && $php_ini_path) {
        $content = file_get_contents($php_ini_path);
        foreach ($new_settings as $key => $value) {
            $content = preg_replace('/^' . preg_quote($key) . '\s*=.*$/m', $key . ' = ' . $value, $content);
        }
        if (file_put_contents($php_ini_path, $content)) {
            $message = "PHP settings updated successfully! Restart server to apply changes.";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Update PHP Settings', 'Updated php.ini', '{$_SERVER['REMOTE_ADDR']}')");
        } else {
            $message = "Failed to update php.ini. Check permissions.";
            $msg_type = 'error';
        }
    } else {
        $message = "No settings to update or php.ini not found!";
        $msg_type = 'error';
    }
}

// ================================================================
// NAMESERVER MANAGEMENT
// ================================================================
if (isset($_POST['update_nameservers'])) {
    $domain_id = intval($_POST['domain_id']);
    $ns1 = trim($_POST['nameserver1']);
    $ns2 = trim($_POST['nameserver2']);
    if ($domain_id > 0) {
        $stmt = $conn->prepare("UPDATE domains SET nameserver1=?, nameserver2=? WHERE id=? AND user_id=?");
        $stmt->bind_param("ssii", $ns1, $ns2, $domain_id, $user_id);
        if ($stmt->execute()) {
            $message = "Nameservers updated successfully!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Update Nameservers', 'Updated NS for domain ID $domain_id', '{$_SERVER['REMOTE_ADDR']}')");
        } else {
            $message = "Failed to update nameservers!";
            $msg_type = 'error';
        }
        $stmt->close();
    }
}

// ================================================================
// SSL CERTIFICATE MANAGEMENT
// ================================================================
if (isset($_POST['upload_ssl'])) {
    $domain_id = intval($_POST['domain_id']);
    $cert = $_POST['ssl_cert'];
    $key = $_POST['ssl_key'];
    if ($domain_id > 0 && !empty($cert) && !empty($key)) {
        $stmt = $conn->prepare("UPDATE domains SET ssl_cert=?, ssl_key=? WHERE id=? AND user_id=?");
        $stmt->bind_param("ssii", $cert, $key, $domain_id, $user_id);
        if ($stmt->execute()) {
            $message = "SSL Certificate uploaded successfully!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Upload SSL', 'Uploaded SSL for domain ID $domain_id', '{$_SERVER['REMOTE_ADDR']}')");
        } else {
            $message = "Failed to upload SSL certificate!";
            $msg_type = 'error';
        }
        $stmt->close();
    } else {
        $message = "Certificate and key are required!";
        $msg_type = 'error';
    }
}

// ================================================================
// DNS MANAGEMENT
// ================================================================
if (isset($_POST['add_dns_record'])) {
    $domain_id = intval($_POST['domain_id']);
    $type = trim($_POST['dns_type']);
    $name = trim($_POST['dns_name']);
    $value = trim($_POST['dns_value']);
    $ttl = intval($_POST['dns_ttl']) ?: 3600;
    if ($domain_id > 0 && !empty($type) && !empty($name) && !empty($value)) {
        $stmt = $conn->prepare("INSERT INTO dns_records (domain_id, type, name, value, ttl) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $domain_id, $type, $name, $value, $ttl);
        if ($stmt->execute()) {
            $message = "DNS record added successfully!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Add DNS Record', 'Added $type record for domain ID $domain_id', '{$_SERVER['REMOTE_ADDR']}')");
        } else {
            $message = "Failed to add DNS record!";
            $msg_type = 'error';
        }
        $stmt->close();
    } else {
        $message = "All fields required!";
        $msg_type = 'error';
    }
}

if (isset($_GET['delete_dns_record'])) {
    $id = intval($_GET['delete_dns_record']);
    $conn->query("DELETE FROM dns_records WHERE id=$id");
    $message = "DNS record deleted!";
}

// ================================================================
// EMAIL NOTIFICATIONS
// ================================================================
if (isset($_POST['update_email_settings'])) {
    $email = trim($_POST['notification_email']);
    $events = array('domain_create', 'backup_complete', 'cron_run', 'ssl_expiry');
    if (!empty($email)) {
        $conn->query("DELETE FROM email_notifications WHERE user_id=$user_id");
        foreach ($events as $event) {
            $enabled = isset($_POST['notify_' . $event]) ? 1 : 0;
            $stmt = $conn->prepare("INSERT INTO email_notifications (user_id, email, event, enabled) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $user_id, $email, $event, $enabled);
            $stmt->execute();
            $stmt->close();
        }
        $message = "Email notification settings updated!";
        $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Update Email Settings', 'Updated email notifications', '{$_SERVER['REMOTE_ADDR']}')");
    }
}

// ================================================================
// ADMIN CREATE USER
// ================================================================
if ($is_admin && isset($_POST['admin_create_user'])) {
    $username = trim($_POST['admin_username']);
    $password = $_POST['admin_password'];
    $email = trim($_POST['admin_email']);
    $fullname = trim($_POST['admin_fullname']);
    $role = isset($_POST['admin_role']) ? $_POST['admin_role'] : 'user';
    $disk_limit = intval($_POST['admin_disk_limit'] ?? 100);
    if (!empty($username) && !empty($password) && !empty($email) && !empty($fullname)) {
        if ($conn->query("SELECT id FROM users WHERE username='$username'")->num_rows > 0) {
            $message = "Username already exists!"; 
            $msg_type='error';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $root_dir = $_SERVER['DOCUMENT_ROOT'] . '/users/' . $username;
            if (!is_dir($root_dir)) mkdir($root_dir, 0755, true);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role, status, disk_limit, root_dir) VALUES (?, ?, ?, ?, ?, 'active', ?, ?)");
            $stmt->bind_param("sssssis", $username, $hashed, $email, $fullname, $role, $disk_limit, $root_dir);
            if ($stmt->execute()) {
                $message = "User '$username' created!";
                $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Admin Create User', 'Created user $username', '{$_SERVER['REMOTE_ADDR']}')");
                sendEmail($email, "Welcome to Magochi Host", "<h2>Welcome $fullname!</h2><p>Your account has been created. Username: $username</p>");
            } else { 
                $message = "Error: ".$conn->error; 
                $msg_type='error'; 
            }
            $stmt->close();
        }
    } else { 
        $message = "All fields required!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// ADMIN CREATE DOMAIN
// ================================================================
if ($is_admin && isset($_POST['admin_create_domain'])) {
    $domain = trim($_POST['admin_domain']);
    $uid = intval($_POST['admin_user_id']);
    $folder = trim($_POST['admin_folder']) ?: str_replace(array('.', ' '), '_', $domain);
    if (!empty($domain) && $uid > 0) {
        if ($conn->query("SELECT id FROM users WHERE id=$uid")->num_rows == 0) {
            $message = "User not found!"; 
            $msg_type='error';
        } else {
            if ($conn->query("SELECT id FROM domains WHERE domain='$domain'")->num_rows > 0) {
                $message = "Domain already exists!"; 
                $msg_type='error';
            } else {
                $path = $domains_root . '/' . $folder;
                if (!is_dir($path)) mkdir($path, 0755, true);
                file_put_contents($path . '/index.html', "<h1>Welcome to $domain</h1>");
                $stmt = $conn->prepare("INSERT INTO domains (user_id, domain, folder) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $uid, $domain, $folder);
                if ($stmt->execute()) {
                    $message = "Domain '$domain' created for user $uid!";
                    $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Admin Create Domain', 'Created domain $domain for user $uid', '{$_SERVER['REMOTE_ADDR']}')");
                    $user_email = $conn->query("SELECT email FROM users WHERE id=$uid")->fetch_assoc();
                    if ($user_email) {
                        sendEmail($user_email['email'], "Domain Created", "<h2>Domain $domain</h2><p>Your domain has been created successfully!</p>");
                    }
                } else { 
                    $message = "Error: ".$conn->error; 
                    $msg_type='error'; 
                }
                $stmt->close();
            }
        }
    } else { 
        $message = "Domain and User ID required!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// ADMIN CREATE DATABASE
// ================================================================
if ($is_admin && isset($_POST['admin_create_db'])) {
    $dbname = trim($_POST['admin_dbname']);
    $uid = intval($_POST['admin_db_user_id']);
    $dbuser = trim($_POST['admin_dbuser']) ?: $dbname;
    $dbpass = trim($_POST['admin_dbpass']) ?: bin2hex(random_bytes(8));
    if (!empty($dbname) && $uid > 0) {
        if ($conn->query("SELECT id FROM users WHERE id=$uid")->num_rows == 0) {
            $message = "User not found!"; 
            $msg_type='error';
        } else {
            if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
                try { 
                    $conn->query("GRANT ALL PRIVILEGES ON `$dbname`.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass'"); 
                    $conn->query("FLUSH PRIVILEGES"); 
                } catch(Exception $e){}
                $stmt = $conn->prepare("INSERT INTO user_databases (user_id, db_name, db_user, db_pass) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $uid, $dbname, $dbuser, $dbpass);
                if ($stmt->execute()) {
                    $message = "Database '$dbname' created for user $uid!<br>User: $dbuser Pass: $dbpass";
                    $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Admin Create DB', 'Created DB $dbname for user $uid', '{$_SERVER['REMOTE_ADDR']}')");
                } else { 
                    $message = "Error: ".$conn->error; 
                    $msg_type='error'; 
                }
                $stmt->close();
            } else { 
                $message = "Error creating database!"; 
                $msg_type='error'; 
            }
        }
    } else { 
        $message = "Database name and User ID required!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// ADMIN CREATE FTP
// ================================================================
if ($is_admin && isset($_POST['admin_create_ftp'])) {
    $username = trim($_POST['admin_ftp_username']);
    $password = $_POST['admin_ftp_password'];
    $uid = intval($_POST['admin_ftp_user_id']);
    $home = trim($_POST['admin_ftp_home']) ?: $root;
    if (!empty($username) && !empty($password) && $uid > 0) {
        if ($conn->query("SELECT id FROM users WHERE id=$uid")->num_rows == 0) {
            $message = "User not found!"; 
            $msg_type='error';
        } else {
            if ($conn->query("SELECT id FROM ftp_users WHERE username='$username'")->num_rows > 0) {
                $message = "FTP username already exists!"; 
                $msg_type='error';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO ftp_users (user_id, username, password, home_dir) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $uid, $username, $hashed, $home);
                if ($stmt->execute()) {
                    $message = "FTP user '$username' created for user $uid!";
                    $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Admin Create FTP', 'Created FTP $username for user $uid', '{$_SERVER['REMOTE_ADDR']}')");
                } else { 
                    $message = "Error: ".$conn->error; 
                    $msg_type='error'; 
                }
                $stmt->close();
            }
        }
    } else { 
        $message = "All fields required!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// ADMIN CREATE CRON
// ================================================================
if ($is_admin && isset($_POST['admin_create_cron'])) {
    $name = trim($_POST['admin_cron_name']);
    $url = trim($_POST['admin_cron_url']);
    $schedule = trim($_POST['admin_cron_schedule']);
    $uid = intval($_POST['admin_cron_user_id']);
    $enabled = isset($_POST['admin_cron_enabled']) ? 1 : 0;
    $desc = trim($_POST['admin_cron_description']);
    if (!empty($name) && !empty($url) && !empty($schedule) && $uid > 0) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) { 
            $message = "Invalid URL!"; 
            $msg_type='error'; 
        } else {
            if ($conn->query("SELECT id FROM users WHERE id=$uid")->num_rows == 0) {
                $message = "User not found!"; 
                $msg_type='error';
            } else {
                $stmt = $conn->prepare("INSERT INTO cron_jobs (user_id, name, url, schedule, enabled, description) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssis", $uid, $name, $url, $schedule, $enabled, $desc);
                if ($stmt->execute()) {
                    $message = "Cron job '$name' created for user $uid!";
                    $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Admin Create Cron', 'Created cron $name for user $uid', '{$_SERVER['REMOTE_ADDR']}')");
                } else { 
                    $message = "Error: ".$conn->error; 
                    $msg_type='error'; 
                }
                $stmt->close();
            }
        }
    } else { 
        $message = "All fields required!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// ADMIN DELETE USER, DOMAIN, DATABASE
// ================================================================
if ($is_admin) {
    if (isset($_GET['admin_delete_user'])) {
        $id = intval($_GET['admin_delete_user']);
        $domains = $conn->query("SELECT folder FROM domains WHERE user_id=$id");
        while ($row = $domains->fetch_assoc()) {
            $p = $domains_root . '/' . $row['folder'];
            if (is_dir($p)) deleteFolder($p);
        }
        $conn->query("DELETE FROM domains WHERE user_id=$id");
        $conn->query("DELETE FROM user_databases WHERE user_id=$id");
        $conn->query("DELETE FROM cron_jobs WHERE user_id=$id");
        $conn->query("DELETE FROM ftp_users WHERE user_id=$id");
        $conn->query("DELETE FROM backups WHERE user_id=$id");
        $conn->query("DELETE FROM service_links WHERE user_id=$id");
        $conn->query("DELETE FROM activity_logs WHERE user_id=$id");
        $conn->query("DELETE FROM users WHERE id=$id");
        $message = "User deleted!";
        $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Admin Delete User', 'Deleted user $id', '{$_SERVER['REMOTE_ADDR']}')");
    }
    if (isset($_GET['admin_suspend_user'])) {
        $id = intval($_GET['admin_suspend_user']);
        $status = isset($_GET['status']) ? $_GET['status'] : 'suspended';
        $conn->query("UPDATE users SET status='$status' WHERE id=$id");
        $message = "User status updated to $status!";
    }
    if (isset($_GET['admin_delete_domain'])) {
        $id = intval($_GET['admin_delete_domain']);
        $row = $conn->query("SELECT folder, domain, user_id FROM domains WHERE id=$id")->fetch_assoc();
        if ($row) {
            $p = $domains_root . '/' . $row['folder'];
            if (is_dir($p)) deleteFolder($p);
            $conn->query("DELETE FROM domains WHERE id=$id");
            $message = "Domain deleted!";
        }
    }
    if (isset($_GET['admin_delete_db'])) {
        $id = intval($_GET['admin_delete_db']);
        $row = $conn->query("SELECT db_name FROM user_databases WHERE id=$id")->fetch_assoc();
        if ($row) {
            $conn->query("DROP DATABASE IF EXISTS `{$row['db_name']}`");
            $conn->query("DELETE FROM user_databases WHERE id=$id");
            $message = "Database deleted!";
        }
    }
    if (isset($_POST['update_disk'])) {
        $uid = intval($_POST['user_id']);
        $limit = intval($_POST['disk_limit']);
        if ($uid > 0 && $limit > 0) {
            $conn->query("UPDATE users SET disk_limit=$limit WHERE id=$uid");
            $message = "Disk limit updated!";
        }
    }
}

// ================================================================
// USER DOMAIN ACTIONS
// ================================================================
if (!$is_admin && isset($_POST['create_domain'])) {
    $domain = trim($_POST['domain']);
    $folder = trim($_POST['folder']) ?: str_replace(array('.', ' '), '_', $domain);
    $path = $domains_root . '/' . $folder;
    $count = $conn->query("SELECT COUNT(*) as c FROM domains WHERE user_id=$user_id")->fetch_assoc()['c'];
    if ($count >= 5) { 
        $message = "Max 5 domains!"; 
        $msg_type='error'; 
    } elseif (!empty($domain)) {
        if ($conn->query("SELECT id FROM domains WHERE domain='$domain'")->num_rows > 0) {
            $message = "Domain already exists!"; 
            $msg_type='error';
        } else {
            if (!is_dir($path)) mkdir($path, 0755, true);
            file_put_contents($path . '/index.html', "<h1>Welcome to $domain</h1>");
            $stmt = $conn->prepare("INSERT INTO domains (user_id, domain, folder) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $domain, $folder);
            if ($stmt->execute()) {
                $message = "Domain '$domain' created!";
                $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Create Domain', 'Created $domain', '{$_SERVER['REMOTE_ADDR']}')");
            } else { 
                $message = "Error: ".$conn->error; 
                $msg_type='error'; 
            }
            $stmt->close();
        }
    }
}

if (!$is_admin && isset($_GET['delete_domain'])) {
    $id = intval($_GET['delete_domain']);
    $row = $conn->query("SELECT folder, domain FROM domains WHERE id=$id AND user_id=$user_id")->fetch_assoc();
    if ($row) {
        $p = $domains_root . '/' . $row['folder'];
        if (is_dir($p)) deleteFolder($p, $user_root, $is_admin);
        $conn->query("DELETE FROM domains WHERE id=$id AND user_id=$user_id");
        $message = "Domain deleted!";
    }
}

// ================================================================
// USER DATABASE ACTIONS
// ================================================================
if (!$is_admin && isset($_POST['create_db'])) {
    $dbname = trim($_POST['dbname']);
    $dbuser = trim($_POST['dbuser']) ?: $dbname;
    $dbpass = trim($_POST['dbpass']) ?: bin2hex(random_bytes(8));
    if (!empty($dbname)) {
        $count = $conn->query("SELECT COUNT(*) as c FROM user_databases WHERE user_id=$user_id")->fetch_assoc()['c'];
        if ($count >= 3) { 
            $message = "Max 3 databases!"; 
            $msg_type='error'; 
        } else {
            if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
                try { 
                    $conn->query("GRANT ALL PRIVILEGES ON `$dbname`.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass'"); 
                    $conn->query("FLUSH PRIVILEGES"); 
                } catch(Exception $e){}
                $stmt = $conn->prepare("INSERT INTO user_databases (user_id, db_name, db_user, db_pass) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $dbname, $dbuser, $dbpass);
                if ($stmt->execute()) {
                    $message = "Database '$dbname' created!<br>User: $dbuser Pass: $dbpass";
                    $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Create DB', 'Created $dbname', '{$_SERVER['REMOTE_ADDR']}')");
                } else { 
                    $message = "Error: ".$conn->error; 
                    $msg_type='error'; 
                }
                $stmt->close();
            } else { 
                $message = "Error creating database!"; 
                $msg_type='error'; 
            }
        }
    }
}

if (!$is_admin && isset($_GET['delete_db'])) {
    $id = intval($_GET['delete_db']);
    $row = $conn->query("SELECT db_name FROM user_databases WHERE id=$id AND user_id=$user_id")->fetch_assoc();
    if ($row) {
        $conn->query("DROP DATABASE IF EXISTS `{$row['db_name']}`");
        $conn->query("DELETE FROM user_databases WHERE id=$id AND user_id=$user_id");
        $message = "Database deleted!";
    }
}

// ================================================================
// USER FTP ACTIONS
// ================================================================
if (!$is_admin && isset($_POST['create_ftp'])) {
    $username = trim($_POST['ftp_username']);
    $password = $_POST['ftp_password'];
    $home = trim($_POST['ftp_home']) ?: $user_root;
    if (!empty($username) && !empty($password)) {
        if ($conn->query("SELECT id FROM ftp_users WHERE username='$username'")->num_rows > 0) {
            $message = "FTP username exists!"; 
            $msg_type='error';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO ftp_users (user_id, username, password, home_dir) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $username, $hashed, $home);
            if ($stmt->execute()) {
                $message = "FTP user '$username' created!";
                $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Create FTP', 'Created $username', '{$_SERVER['REMOTE_ADDR']}')");
            } else { 
                $message = "Error: ".$conn->error; 
                $msg_type='error'; 
            }
            $stmt->close();
        }
    }
}

if (!$is_admin && isset($_GET['delete_ftp'])) {
    $id = intval($_GET['delete_ftp']);
    $row = $conn->query("SELECT username FROM ftp_users WHERE id=$id AND user_id=$user_id")->fetch_assoc();
    if ($row) {
        $conn->query("DELETE FROM ftp_users WHERE id=$id AND user_id=$user_id");
        $message = "FTP user deleted!";
    }
}

// ================================================================
// USER CRON ACTIONS
// ================================================================
if (!$is_admin && isset($_POST['add_cron'])) {
    $name = trim($_POST['cron_name']);
    $url = trim($_POST['cron_url']);
    $schedule = trim($_POST['cron_schedule']);
    $enabled = isset($_POST['cron_enabled']) ? 1 : 0;
    $desc = trim($_POST['cron_description']);
    if (!empty($name) && !empty($url) && !empty($schedule)) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) { 
            $message = "Invalid URL!"; 
            $msg_type='error'; 
        } else {
            $stmt = $conn->prepare("INSERT INTO cron_jobs (user_id, name, url, schedule, enabled, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssis", $user_id, $name, $url, $schedule, $enabled, $desc);
            if ($stmt->execute()) {
                $message = "Cron job '$name' added!";
                $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Add Cron', 'Added $name', '{$_SERVER['REMOTE_ADDR']}')");
            } else { 
                $message = "Error: ".$conn->error; 
                $msg_type='error'; 
            }
            $stmt->close();
        }
    }
}

if (!$is_admin && isset($_GET['delete_cron'])) {
    $id = intval($_GET['delete_cron']);
    $conn->query("DELETE FROM cron_jobs WHERE id=$id AND user_id=$user_id");
    $message = "Cron job deleted!";
}

if (!$is_admin && isset($_GET['toggle_cron'])) {
    $id = intval($_GET['toggle_cron']);
    $conn->query("UPDATE cron_jobs SET enabled = NOT enabled WHERE id=$id AND user_id=$user_id");
    $message = "Cron toggled!";
}

if (!$is_admin && isset($_GET['run_cron'])) {
    $id = intval($_GET['run_cron']);
    $cron = $conn->query("SELECT url FROM cron_jobs WHERE id=$id AND user_id=$user_id")->fetch_assoc();
    if ($cron) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cron['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $conn->query("UPDATE cron_jobs SET last_run = NOW() WHERE id=$id");
        $message = "Cron executed! HTTP $http_code";
    }
}

// ================================================================
// SERVICE LINKS
// ================================================================
if (isset($_POST['add_service'])) {
    $name = trim($_POST['service_name']);
    $url = trim($_POST['service_url']);
    $icon = trim($_POST['service_icon']) ?: 'fa-link';
    $color = trim($_POST['service_color']) ?: '#6C63FF';
    $desc = trim($_POST['service_description']);
    $cat = trim($_POST['service_category']) ?: 'General';
    if (!empty($name) && !empty($url)) {
        $stmt = $conn->prepare("INSERT INTO service_links (user_id, name, url, icon, color, description, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $name, $url, $icon, $color, $desc, $cat);
        if ($stmt->execute()) {
            $message = "Service link added!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Add Service', 'Added $name', '{$_SERVER['REMOTE_ADDR']}')");
        } else { 
            $message = "Error: ".$conn->error; 
            $msg_type='error'; 
        }
        $stmt->close();
    }
}

if (isset($_GET['delete_service'])) {
    $id = intval($_GET['delete_service']);
    $conn->query("DELETE FROM service_links WHERE id=$id AND (user_id=$user_id OR user_id IS NULL)");
    $message = "Service deleted!";
}

// ================================================================
// BACKUPS
// ================================================================
if (isset($_GET['backup_db'])) {
    $dbname = $_GET['backup_db'];
    $file = backupDatabase($dbname);
    if ($file) {
        $stmt = $conn->prepare("INSERT INTO backups (user_id, name, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, basename($file), $file);
        $stmt->execute();
        $stmt->close();
        $message = "Backup created!";
        $user_email = $conn->query("SELECT email FROM users WHERE id=$user_id")->fetch_assoc();
        if ($user_email) {
            sendEmail($user_email['email'], "Backup Completed", "<h2>Backup Created</h2><p>Database $dbname has been backed up successfully.</p>");
        }
    }
}

if (isset($_GET['download_backup'])) {
    $file = $_GET['download_backup'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

if (isset($_GET['delete_backup'])) {
    $id = intval($_GET['delete_backup']);
    $row = $conn->query("SELECT file_path FROM backups WHERE id=$id AND user_id=$user_id")->fetch_assoc();
    if ($row) {
        if (file_exists($row['file_path'])) unlink($row['file_path']);
        $conn->query("DELETE FROM backups WHERE id=$id AND user_id=$user_id");
        $message = "Backup deleted!";
    }
}

// ================================================================
// UNZIP
// ================================================================
if ($is_admin && isset($_GET['unzip'])) {
    $zipFile = $_GET['unzip'];
    $extractDir = dirname($zipFile);
    $result = unzipFile($zipFile, $extractDir);
    if ($result['success']) {
        $message = "Successfully extracted " . $result['files'] . " files from " . basename($zipFile);
        $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Unzip', 'Extracted " . basename($zipFile) . "', '{$_SERVER['REMOTE_ADDR']}')");
    } else {
        $message = "Failed to extract: " . $result['error'];
        $msg_type = 'error';
    }
}

// ================================================================
// HTACCESS GENERATOR
// ================================================================
if (isset($_POST['generate_htaccess'])) {
    $auth_type = isset($_POST['auth_type']) ? $_POST['auth_type'] : 'none';
    $content = "# .htaccess generated by Magochi Host\n";
    $content .= "# Created: " . date('Y-m-d H:i:s') . "\n\n";
    
    if ($auth_type == 'password') {
        $username = trim($_POST['ht_user']);
        $password = $_POST['ht_pass'];
        if (!empty($username) && !empty($password)) {
            $content .= "AuthType Basic\n";
            $content .= "AuthName \"Restricted Area\"\n";
            $content .= "AuthUserFile " . $currentPath . "/.htpasswd\n";
            $content .= "Require valid-user\n";
            $htpasswd = $currentPath . '/.htpasswd';
            $encrypted = password_hash($password, PASSWORD_BCRYPT);
            file_put_contents($htpasswd, $username . ':' . $encrypted . "\n");
            $message = ".htaccess and .htpasswd generated with password protection!";
        } else {
            $message = "Username and password required!";
            $msg_type = 'error';
        }
    } elseif ($auth_type == 'ip') {
        $allowed_ip = trim($_POST['allowed_ip']);
        if (!empty($allowed_ip)) {
            $content .= "Order Deny,Allow\n";
            $content .= "Deny from all\n";
            $content .= "Allow from $allowed_ip\n";
            $message = ".htaccess generated with IP restriction for $allowed_ip!";
        } else {
            $message = "IP address required!";
            $msg_type = 'error';
        }
    } elseif ($auth_type == 'redirect') {
        $redirect_url = trim($_POST['redirect_url']);
        if (!empty($redirect_url)) {
            $content .= "RewriteEngine On\n";
            $content .= "RewriteRule ^(.*)$ $redirect_url [R=301,L]\n";
            $message = ".htaccess generated with redirect to $redirect_url!";
        } else {
            $message = "Redirect URL required!";
            $msg_type = 'error';
        }
    } else {
        $content .= "# No authentication configured\n";
        $message = "Empty .htaccess generated!";
    }
    
    file_put_contents($currentPath . '/.htaccess', $content);
    $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Generate .htaccess', 'Generated .htaccess in $currentPath', '{$_SERVER['REMOTE_ADDR']}')");
}

// ================================================================
// API KEY MANAGEMENT
// ================================================================
if (!$is_admin && isset($_POST['create_api_key'])) {
    $name = trim($_POST['api_name']);
    $permissions = trim($_POST['api_permissions']) ?: 'read';
    if (!empty($name)) {
        $key = generateApiKey();
        $stmt = $conn->prepare("INSERT INTO api_keys (user_id, name, api_key, permissions) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $name, $key, $permissions);
        if ($stmt->execute()) {
            $message = "API Key '$name' created! Key: $key";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Create API Key', 'Created API key $name', '{$_SERVER['REMOTE_ADDR']}')");
        } else {
            $message = "Error: " . $conn->error;
            $msg_type = 'error';
        }
        $stmt->close();
    }
}

if (isset($_GET['delete_api_key'])) {
    $id = intval($_GET['delete_api_key']);
    $conn->query("DELETE FROM api_keys WHERE id=$id AND user_id=$user_id");
    $message = "API Key deleted!";
}

// ================================================================
// WORDPRESS INSTALLER (FIXED)
// ================================================================
if (isset($_POST['install_wordpress'])) {
    $domain_id = intval($_POST['wp_domain_id']);
    $path = trim($_POST['wp_path']) ?: '';
    $wp_version = trim($_POST['wp_version']) ?: 'latest';
    $admin_user = trim($_POST['wp_admin_user']);
    $admin_pass = $_POST['wp_admin_pass'];
    $admin_email = trim($_POST['wp_admin_email']);
    $site_title = trim($_POST['wp_site_title']) ?: 'My WordPress Site';
    
    if (!empty($domain_id) && !empty($admin_user) && !empty($admin_pass) && !empty($admin_email)) {
        $domain_info = $conn->query("SELECT * FROM domains WHERE id=$domain_id AND user_id=$user_id")->fetch_assoc();
        if ($domain_info) {
            $install_path = $domains_root . '/' . $domain_info['folder'] . ($path ? '/' . $path : '');
            if (!is_dir($install_path)) mkdir($install_path, 0755, true);
            
            // Create database for WordPress
            $wp_dbname = 'wp_' . str_replace(array('.', '-'), '_', $domain_info['domain']) . '_' . substr(md5(time()), 0, 6);
            $wp_dbuser = $wp_dbname;
            $wp_dbpass = bin2hex(random_bytes(8));
            
            if ($conn->query("CREATE DATABASE IF NOT EXISTS `$wp_dbname`")) {
                try {
                    $conn->query("GRANT ALL PRIVILEGES ON `$wp_dbname`.* TO '$wp_dbuser'@'localhost' IDENTIFIED BY '$wp_dbpass'");
                    $conn->query("FLUSH PRIVILEGES");
                } catch (Exception $e) {}
                
                // Save database info
                $stmt = $conn->prepare("INSERT INTO user_databases (user_id, db_name, db_user, db_pass) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $wp_dbname, $wp_dbuser, $wp_dbpass);
                $stmt->execute();
                $stmt->close();
            }
            
            // Download WordPress
            $zip_path = downloadWordPress($wp_version);
            if ($zip_path) {
                $result = unzipFile($zip_path, $install_path);
                if ($result['success']) {
                    unlink($zip_path);
                    
                    // Move files from wordpress subfolder if exists
                    if (is_dir($install_path . '/wordpress')) {
                        $files = scandir($install_path . '/wordpress');
                        foreach ($files as $file) {
                            if ($file != '.' && $file != '..') {
                                rename($install_path . '/wordpress/' . $file, $install_path . '/' . $file);
                            }
                        }
                        rmdir($install_path . '/wordpress');
                    }
                    
                    // Create wp-config.php
                    $wp_config = "<?php\n";
                    $wp_config .= "define('DB_NAME', '$wp_dbname');\n";
                    $wp_config .= "define('DB_USER', '$wp_dbuser');\n";
                    $wp_config .= "define('DB_PASSWORD', '$wp_dbpass');\n";
                    $wp_config .= "define('DB_HOST', 'localhost');\n";
                    $wp_config .= "define('DB_CHARSET', 'utf8');\n";
                    $wp_config .= "define('DB_COLLATE', '');\n\n";
                    $wp_config .= "\$table_prefix = 'wp_';\n\n";
                    $wp_config .= "define('WP_DEBUG', false);\n\n";
                    $wp_config .= "if ( ! defined('ABSPATH') ) define('ABSPATH', __DIR__ . '/');\n";
                    $wp_config .= "require_once ABSPATH . 'wp-settings.php';\n";
                    file_put_contents($install_path . '/wp-config.php', $wp_config);
                    
                    // Create .htaccess for WordPress
                    $htaccess = "# WordPress .htaccess\n";
                    $htaccess .= "<IfModule mod_rewrite.c>\n";
                    $htaccess .= "RewriteEngine On\n";
                    $htaccess .= "RewriteBase " . ($path ? '/' . $path : '/') . "\n";
                    $htaccess .= "RewriteRule ^index\\.php$ - [L]\n";
                    $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
                    $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
                    $htaccess .= "RewriteRule . " . ($path ? '/' . $path : '/') . "index.php [L]\n";
                    $htaccess .= "</IfModule>\n";
                    file_put_contents($install_path . '/.htaccess', $htaccess);
                    
                    // Save installation record
                    $stmt = $conn->prepare("INSERT INTO wordpress_installs (user_id, domain_id, path, version, admin_user, admin_pass, admin_email, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                    $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
                    $stmt->bind_param("iisssss", $user_id, $domain_id, $path, $wp_version, $admin_user, $hashed_pass, $admin_email);
                    $stmt->execute();
                    $stmt->close();
                    
                    $site_url = $server_url . '/domains/' . $domain_info['folder'] . ($path ? '/' . $path : '');
                    $message = "WordPress installed successfully!<br><br>";
                    $message .= "<strong>Site URL:</strong> <a href='$site_url' target='_blank'>$site_url</a><br>";
                    $message .= "<strong>Admin Username:</strong> $admin_user<br>";
                    $message .= "<strong>Admin Password:</strong> $admin_pass<br>";
                    $message .= "<strong>Admin Email:</strong> $admin_email<br><br>";
                    $message .= "<strong>Database:</strong> $wp_dbname<br>";
                    $message .= "<strong>Database User:</strong> $wp_dbuser<br>";
                    $message .= "<strong>Database Password:</strong> $wp_dbpass<br><br>";
                    $message .= "<strong>IMPORTANT:</strong> You need to visit the site URL and complete the WordPress installation wizard.";
                    
                    $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Install WordPress', 'Installed WordPress at $install_path', '{$_SERVER['REMOTE_ADDR']}')");
                    
                    sendEmail($admin_email, "WordPress Installed Successfully", $message);
                } else {
                    $message = "Failed to extract WordPress: " . $result['error'];
                    $msg_type = 'error';
                }
            } else {
                $message = "Failed to download WordPress. Please check your internet connection.";
                $msg_type = 'error';
            }
        } else {
            $message = "Invalid domain!";
            $msg_type = 'error';
        }
    } else {
        $message = "All fields required!";
        $msg_type = 'error';
    }
}

// ================================================================
// FTP CLIENT (Import from remote FTP)
// ================================================================
if (isset($_POST['ftp_import'])) {
    $ftp_host = trim($_POST['ftp_host']);
    $ftp_user = trim($_POST['ftp_user']);
    $ftp_pass = $_POST['ftp_pass'];
    $ftp_path = trim($_POST['ftp_path']) ?: '/';
    $target_dir = isset($_POST['ftp_target']) ? $_POST['ftp_target'] : $currentPath;
    
    // Security: Restrict target dir for non-admin
    if (!$is_admin && strpos($target_dir, $user_root) !== 0) {
        $message = "Permission denied!";
        $msg_type = 'error';
    } elseif (!empty($ftp_host) && !empty($ftp_user) && !empty($ftp_pass)) {
        $conn_id = ftp_connect($ftp_host);
        if ($conn_id) {
            if (ftp_login($conn_id, $ftp_user, $ftp_pass)) {
                ftp_pasv($conn_id, true);
                $files = ftp_nlist($conn_id, $ftp_path);
                $count = 0;
                if ($files) {
                    foreach ($files as $file) {
                        $local_file = $target_dir . '/' . basename($file);
                        if (ftp_get($conn_id, $local_file, $file, FTP_BINARY)) {
                            $count++;
                        }
                    }
                }
                ftp_close($conn_id);
                $message = "Successfully imported $count files from FTP server!";
                $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'FTP Import', 'Imported $count files from $ftp_host', '{$_SERVER['REMOTE_ADDR']}')");
            } else {
                $message = "FTP login failed!";
                $msg_type = 'error';
            }
        } else {
            $message = "Cannot connect to FTP server!";
            $msg_type = 'error';
        }
    } else {
        $message = "FTP credentials required!";
        $msg_type = 'error';
    }
}

// ================================================================
// FILE UPLOAD (RESTRICTED TO USER ROOT)
// ================================================================
if (isset($_FILES['uploaded_file'])) {
    $target_dir = isset($_POST['upload_dir']) ? $_POST['upload_dir'] : $currentPath;
    if (isset($_POST['domain_folder']) && !empty($_POST['domain_folder'])) {
        $target_dir = $domains_root . '/' . $_POST['domain_folder'];
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
    }
    
    // Security: Check if target is within user's allowed area
    $allowed = $is_admin;
    if (!$allowed) {
        if (strpos($target_dir, $user_root) === 0) {
            $allowed = true;
        }
        $domains = getUserDomains($user_id);
        while ($d = $domains->fetch_assoc()) {
            if (strpos($target_dir, $domains_root . '/' . $d['folder']) === 0) { 
                $allowed = true; 
                break; 
            }
        }
        $domains->data_seek(0);
    }
    
    if ($allowed) {
        $file = $_FILES['uploaded_file'];
        $target = $target_dir . '/' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $message = "File uploaded successfully!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Upload File', 'Uploaded ' . {$file['name']}, '{$_SERVER['REMOTE_ADDR']}')");
        } else { 
            $message = "Upload failed!"; 
            $msg_type='error'; 
        }
    } else { 
        $message = "Permission denied!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// CREATE FILE (RESTRICTED)
// ================================================================
if (isset($_POST['create_file'])) {
    $filename = trim($_POST['filename']);
    $content = isset($_POST['file_content']) ? $_POST['file_content'] : '';
    $filepath = $currentPath . '/' . $filename;
    $allowed = $is_admin;
    if (!$allowed) {
        if (strpos($currentPath, $user_root) === 0) {
            $allowed = true;
        }
        $domains = getUserDomains($user_id);
        while ($d = $domains->fetch_assoc()) {
            if (strpos($currentPath, $domains_root . '/' . $d['folder']) === 0) { 
                $allowed = true; 
                break; 
            }
        }
        $domains->data_seek(0);
    }
    if ($allowed && !empty($filename)) {
        if (file_put_contents($filepath, $content)) {
            $message = "File '$filename' created!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Create File', 'Created $filename', '{$_SERVER['REMOTE_ADDR']}')");
        } else { 
            $message = "Failed to create file!"; 
            $msg_type='error'; 
        }
    } else { 
        $message = "Permission denied or empty filename!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// CREATE FOLDER (RESTRICTED)
// ================================================================
if (isset($_POST['create_folder'])) {
    $foldername = trim($_POST['foldername']);
    $folderpath = $currentPath . '/' . $foldername;
    $allowed = $is_admin;
    if (!$allowed) {
        if (strpos($currentPath, $user_root) === 0) {
            $allowed = true;
        }
        $domains = getUserDomains($user_id);
        while ($d = $domains->fetch_assoc()) {
            if (strpos($currentPath, $domains_root . '/' . $d['folder']) === 0) { 
                $allowed = true; 
                break; 
            }
        }
        $domains->data_seek(0);
    }
    if ($allowed && !empty($foldername)) {
        if (mkdir($folderpath, 0755, true)) {
            $message = "Folder '$foldername' created!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Create Folder', 'Created $foldername', '{$_SERVER['REMOTE_ADDR']}')");
        } else { 
            $message = "Failed to create folder!"; 
            $msg_type='error'; 
        }
    } else { 
        $message = "Permission denied!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// DELETE FILE (RESTRICTED)
// ================================================================
if (isset($_GET['delete_file'])) {
    $file = $_GET['delete_file'];
    $allowed = $is_admin;
    if (!$allowed) {
        if (strpos($file, $user_root) === 0) {
            $allowed = true;
        }
        $domains = getUserDomains($user_id);
        while ($d = $domains->fetch_assoc()) {
            if (strpos($file, $domains_root . '/' . $d['folder']) === 0) { 
                $allowed = true; 
                break; 
            }
        }
        $domains->data_seek(0);
    }
    if ($allowed && file_exists($file) && is_file($file)) {
        if (unlink($file)) {
            $message = "File deleted!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Delete File', 'Deleted ' . basename($file), '{$_SERVER['REMOTE_ADDR']}')");
        } else { 
            $message = "Delete failed!"; 
            $msg_type='error'; 
        }
    } else { 
        $message = "Permission denied or file not found!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// DELETE FOLDER (RESTRICTED)
// ================================================================
if (isset($_GET['delete_folder'])) {
    $folder = $_GET['delete_folder'];
    $allowed = $is_admin;
    if (!$allowed) {
        if (strpos($folder, $user_root) === 0) {
            $allowed = true;
        }
        $domains = getUserDomains($user_id);
        while ($d = $domains->fetch_assoc()) {
            if (strpos($folder, $domains_root . '/' . $d['folder']) === 0) { 
                $allowed = true; 
                break; 
            }
        }
        $domains->data_seek(0);
    }
    if ($allowed && is_dir($folder) && $folder != $user_root && $folder != $domains_root) {
        if (deleteFolder($folder, $user_root, $is_admin)) {
            $message = "Folder deleted!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Delete Folder', 'Deleted ' . basename($folder), '{$_SERVER['REMOTE_ADDR']}')");
        } else { 
            $message = "Delete failed!"; 
            $msg_type='error'; 
        }
    } else { 
        $message = "Permission denied!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// SAVE FILE (edit)
// ================================================================
if (isset($_POST['save_file'])) {
    $filepath = $_POST['file_path'];
    $content = $_POST['file_content'];
    $allowed = $is_admin;
    if (!$allowed) {
        if (strpos($filepath, $user_root) === 0) {
            $allowed = true;
        }
        $domains = getUserDomains($user_id);
        while ($d = $domains->fetch_assoc()) {
            if (strpos($filepath, $domains_root . '/' . $d['folder']) === 0) { 
                $allowed = true; 
                break; 
            }
        }
        $domains->data_seek(0);
    }
    if ($allowed && file_exists($filepath) && is_file($filepath)) {
        if (file_put_contents($filepath, $content)) {
            $message = "File saved!";
            $conn->query("INSERT INTO activity_logs (user_id, action, details, ip) VALUES ($user_id, 'Edit File', 'Edited ' . basename($filepath), '{$_SERVER['REMOTE_ADDR']}')");
        } else { 
            $message = "Save failed!"; 
            $msg_type='error'; 
        }
    } else { 
        $message = "Permission denied!"; 
        $msg_type='error'; 
    }
}

// ================================================================
// DOWNLOAD FILE
// ================================================================
if (isset($_GET['download_file'])) {
    $file = $_GET['download_file'];
    if (file_exists($file) && is_file($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

// ================================================================
// THEME SETTINGS (session)
// ================================================================
if (isset($_POST['save_theme'])) {
    $_SESSION['theme'] = array(
        'primary' => isset($_POST['primary_color']) ? $_POST['primary_color'] : '#6C63FF',
        'dark_mode' => isset($_POST['dark_mode']),
    );
    $message = "Theme saved!";
}

// ================================================================
// GET DATA FOR VIEW
// ================================================================
$user_domains = getUserDomains($user_id);
$user_databases = getUserDatabases($user_id);
$user_crons = getUserCrons($user_id);
$user_services = getUserServices($user_id);
$user_ftp = $conn->query("SELECT * FROM ftp_users WHERE user_id=$user_id");
$user_backups = getUserBackups($user_id);
$user_api_keys = getUserApiKeys($user_id);
$wordpress_installs = getWordPressInstalls($user_id);
$user_email_notifications = getUserEmailNotifications($user_id);
$all_users = $is_admin ? $conn->query("SELECT * FROM users ORDER BY created_at DESC") : null;
$server_info = getServerInfo();
$databases = getDatabaseList();
$files = getDirectoryContents($currentPath, $user_root, $is_admin);
$logs = getActivityLogs($is_admin ? null : $user_id);

// Calculate disk usage for current user (only their files)
$disk_used = 0;
if (!$is_admin) {
    $disk_used = calculateFolderSize($user_root, $user_root, $is_admin);
    $user_domains->data_seek(0);
    while ($d = $user_domains->fetch_assoc()) {
        $p = $domains_root . '/' . $d['folder'];
        if (is_dir($p)) $disk_used += calculateFolderSize($p, $user_root, $is_admin);
    }
    $user_domains->data_seek(0);
    $conn->query("UPDATE users SET disk_used=$disk_used WHERE id=$user_id");
} else {
    $disk_used = 0;
    $user_domains->data_seek(0);
    while ($d = $user_domains->fetch_assoc()) {
        $p = $domains_root . '/' . $d['folder'];
        if (is_dir($p)) $disk_used += calculateFolderSize($p);
    }
    $user_domains->data_seek(0);
}
$user_data = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$disk_limit = isset($user_data['disk_limit']) ? $user_data['disk_limit'] : 100;

// Group services by category
$categories = array();
$user_services->data_seek(0);
while ($s = $user_services->fetch_assoc()) {
    $categories[$s['category']][] = $s;
}
$user_services->data_seek(0);

$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_domains = $conn->query("SELECT COUNT(*) as c FROM domains")->fetch_assoc()['c'];
$total_databases = $conn->query("SELECT COUNT(*) as c FROM user_databases")->fetch_assoc()['c'];

$domain_dropdown = array();
$user_domains->data_seek(0);
while ($d = $user_domains->fetch_assoc()) {
    $domain_dropdown[$d['folder']] = $d['domain'];
}
$user_domains->data_seek(0);

// Theme
$primary = isset($_SESSION['theme']['primary']) ? $_SESSION['theme']['primary'] : '#6C63FF';
$dark = isset($_SESSION['theme']['dark_mode']) ? $_SESSION['theme']['dark_mode'] : false;

// ================================================================
// LOGIN/REGISTER PAGE
// ================================================================
if (!isset($_SESSION['user_id']) && !isset($_POST['login']) && !isset($_POST['register'])) {
    ?><!DOCTYPE html>
    <html>
    <head><title>Magochi Host – Login</title><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body{font-family:Segoe UI,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}.auth-box{background:white;border-radius:20px;padding:40px;max-width:450px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.3)}.auth-box h1{text-align:center;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}.tabs{display:flex;gap:10px;margin:20px 0;background:#f0f2f5;border-radius:10px;padding:5px}.tabs button{flex:1;padding:10px;border:none;background:transparent;border-radius:8px;font-weight:600;cursor:pointer}.tabs button.active{background:white;box-shadow:0 2px 10px rgba(0,0,0,0.1)}.form{display:none}.form.active{display:block}.form input{width:100%;padding:12px;margin-bottom:12px;border:2px solid #e0e0e0;border-radius:8px}.form button{width:100%;padding:12px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer}.form .error{color:#dc3545;font-size:13px}.form .success{color:#28a745;font-size:13px}.footer{text-align:center;margin-top:20px;font-size:13px;color:#666}</style>
    </head>
    <body>
    <div class="auth-box"><h1><i class="fas fa-server"></i> Magochi Host</h1><p style="text-align:center;color:#999;">Web Hosting Control Panel v<?php echo $version; ?></p>
    <div class="tabs"><button class="active" onclick="switchTab('login')"><i class="fas fa-sign-in-alt"></i> Login</button><button onclick="switchTab('register')"><i class="fas fa-user-plus"></i> Register</button></div>
    <div id="login-form" class="form active"><?php if (isset($error)) echo '<div class="error">' . htmlspecialchars($error) . '</div>'; ?>
    <form method="POST"><input type="text" name="username" placeholder="Username" required><input type="password" name="password" placeholder="Password" required><button type="submit" name="login">Login</button></form></div>
    <div id="register-form" class="form"><?php if (isset($reg_error)) echo '<div class="error">' . htmlspecialchars($reg_error) . '</div>'; if (isset($reg_success)) echo '<div class="success">' . htmlspecialchars($reg_success) . '</div>'; ?>
    <form method="POST"><input type="text" name="reg_fullname" placeholder="Full Name" required><input type="email" name="reg_email" placeholder="Email" required><input type="text" name="reg_username" placeholder="Username" required><input type="password" name="reg_password" placeholder="Password (min 6)" required><input type="password" name="reg_confirm" placeholder="Confirm Password" required><button type="submit" name="register">Create Account</button></form></div>
    <div class="footer">Powered by Magochi Host | v<?php echo $version; ?></div></div>
    <script>function switchTab(t){document.querySelectorAll('.form').forEach(e=>e.classList.remove('active'));document.querySelectorAll('.tabs button').forEach(e=>e.classList.remove('active'));document.getElementById(t+'-form').classList.add('active');document.querySelector('.tabs button:'+(t==='login'?'first-child':'last-child')).classList.add('active');}</script>
    </body>
    </html><?php
    exit;
}

// ================================================================
// MENU STRUCTURE
// ================================================================
$menu_groups = array(
    'main' => array(
        array('action' => 'dashboard', 'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard'),
    ),
    'websites' => array(
        array('action' => 'domains', 'icon' => 'fa-globe', 'label' => 'Domains'),
        array('action' => 'apps', 'icon' => 'fa-th', 'label' => 'Apps & Installers'),
        array('action' => 'files', 'icon' => 'fa-folder', 'label' => 'File Manager'),
    ),
    'databases' => array(
        array('action' => 'databases', 'icon' => 'fa-database', 'label' => 'Databases'),
        array('action' => 'backups', 'icon' => 'fa-archive', 'label' => 'Backups'),
    ),
    'services' => array(
        array('action' => 'ftp', 'icon' => 'fa-user-lock', 'label' => 'FTP Users'),
        array('action' => 'cron', 'icon' => 'fa-clock', 'label' => 'Cron Jobs'),
        array('action' => 'services', 'icon' => 'fa-link', 'label' => 'Service Links'),
        array('action' => 'api', 'icon' => 'fa-key', 'label' => 'API Management'),
    ),
    'advanced' => array(
        array('action' => 'nameservers', 'icon' => 'fa-server', 'label' => 'Nameservers'),
        array('action' => 'ssl', 'icon' => 'fa-lock', 'label' => 'SSL Certificates'),
        array('action' => 'dns', 'icon' => 'fa-network-wired', 'label' => 'DNS Management'),
        array('action' => 'email', 'icon' => 'fa-envelope', 'label' => 'Email Notifications'),
    ),
    'admin' => array(
        array('action' => 'admin_users', 'icon' => 'fa-users-cog', 'label' => 'Create Users'),
        array('action' => 'admin_domains', 'icon' => 'fa-globe-americas', 'label' => 'Create Domains'),
        array('action' => 'admin_databases', 'icon' => 'fa-database', 'label' => 'Create Databases'),
        array('action' => 'admin_ftp', 'icon' => 'fa-user-lock', 'label' => 'Create FTP'),
        array('action' => 'admin_cron', 'icon' => 'fa-clock', 'label' => 'Create Cron'),
        array('action' => 'php_settings', 'icon' => 'fab fa-php', 'label' => 'PHP Settings'),
        array('action' => 'users', 'icon' => 'fa-users', 'label' => 'All Users'),
        array('action' => 'server', 'icon' => 'fa-server', 'label' => 'Server Info'),
    ),
    'system' => array(
        array('action' => 'logs', 'icon' => 'fa-history', 'label' => 'Activity Logs'),
        array('action' => 'settings', 'icon' => 'fa-cog', 'label' => 'Settings'),
    )
);

// ================================================================
// START HTML OUTPUT
// ================================================================
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magochi Host – <?php echo htmlspecialchars(ucfirst($action)); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/sql/sql.min.js"></script>
    <style>
        :root { --primary: <?php echo htmlspecialchars($primary); ?>; --primary-dark: <?php echo htmlspecialchars($primary); ?>dd; --sidebar-width: 240px; --right-sidebar-width: 280px; --bg: <?php echo $dark ? '#1a1a2e' : '#f0f2f5'; ?>; --card-bg: <?php echo $dark ? '#16213e' : 'white'; ?>; --text: <?php echo $dark ? '#e0e0e0' : '#333'; ?>; --border: <?php echo $dark ? '#2a3a5e' : '#eee'; ?>; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Tahoma,sans-serif; background:var(--bg); color:var(--text); min-height:100vh; transition: background 0.3s; }
        ::-webkit-scrollbar { width:8px; height:8px; }
        ::-webkit-scrollbar-track { background:var(--bg); }
        ::-webkit-scrollbar-thumb { background:var(--primary); border-radius:4px; }
        .sidebar { position:fixed; left:0; top:0; bottom:0; width:var(--sidebar-width); background:<?php echo $dark ? '#16213e' : 'white'; ?>; color:<?php echo $dark ? '#e0e0e0' : '#333'; ?>; box-shadow:2px 0 15px rgba(0,0,0,0.1); overflow-y:auto; z-index:1000; transition:transform 0.3s; padding:20px 0; }
        .sidebar-brand { text-align:center; padding:0 20px 20px; border-bottom:1px solid var(--border); margin-bottom:15px; }
        .sidebar-brand h2 { font-size:20px; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .sidebar-brand .sub { font-size:10px; color:#999; }
        .sidebar-nav { list-style:none; padding:0; }
        .sidebar-nav .nav-group { font-size:10px; text-transform:uppercase; color:#999; padding:10px 15px 5px; font-weight:700; letter-spacing:1px; }
        .sidebar-nav li { margin:1px 8px; }
        .sidebar-nav a { display:flex; align-items:center; padding:8px 12px; color:var(--text); text-decoration:none; border-radius:6px; transition:all 0.3s; border-left:3px solid transparent; font-size:13px; }
        .sidebar-nav a:hover { background:var(--primary); color:white; }
        .sidebar-nav a.active { background:var(--primary); color:white; border-left-color:white; }
        .sidebar-nav a i { width:20px; margin-right:8px; font-size:14px; }
        .sidebar-nav .divider { height:1px; background:var(--border); margin:8px 12px; }
        .sidebar-user { padding:12px 16px; border-top:1px solid var(--border); margin-top:10px; display:flex; align-items:center; gap:10px; }
        .sidebar-user .avatar { width:36px; height:36px; border-radius:50%; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; }
        .sidebar-user .info { flex:1; }
        .sidebar-user .info .name { font-weight:600; font-size:13px; }
        .sidebar-user .info .role { font-size:10px; color:#999; }
        .sidebar-user .switch-badge { background:#ffd93d; color:#333; padding:1px 6px; border-radius:3px; font-size:8px; font-weight:700; }
        .main { margin-left:var(--sidebar-width); margin-right:var(--right-sidebar-width); padding:20px; min-height:100vh; }
        .right-sidebar { position:fixed; right:0; top:0; bottom:0; width:var(--right-sidebar-width); background:<?php echo $dark ? '#16213e' : 'white'; ?>; color:<?php echo $dark ? '#e0e0e0' : '#333'; ?>; box-shadow:-2px 0 15px rgba(0,0,0,0.1); overflow-y:auto; z-index:999; padding:20px; }
        .right-sidebar .widget { background:var(--bg); border-radius:10px; padding:15px; margin-bottom:15px; }
        .right-sidebar .widget h4 { font-size:13px; margin-bottom:10px; border-bottom:1px solid var(--border); padding-bottom:8px; }
        .right-sidebar .widget .stat-item { display:flex; justify-content:space-between; padding:5px 0; font-size:12px; border-bottom:1px solid var(--border); }
        .right-sidebar .widget .stat-item:last-child { border-bottom:none; }
        .right-sidebar .widget .stat-item .label { color:#999; }
        .right-sidebar .widget .stat-item .value { font-weight:600; }
        .topbar { display:flex; justify-content:space-between; align-items:center; background:var(--card-bg); border-radius:12px; padding:12px 20px; margin-bottom:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        .topbar h1 { font-size:20px; font-weight:600; }
        .topbar h1 i { margin-right:10px; color:var(--primary); }
        .topbar-right { display:flex; align-items:center; gap:12px; }
        .topbar-right .badge { background:var(--primary); color:white; padding:4px 12px; border-radius:20px; font-size:11px; }
        .topbar-right .time { font-size:12px; color:#999; }
        .card { background:var(--card-bg); border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; padding-bottom:10px; border-bottom:2px solid var(--border); }
        .card-header h3 { font-size:16px; }
        .card-header h3 i { margin-right:8px; color:var(--primary); }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:15px; }
        .stat-card { background:var(--bg); padding:18px; border-radius:10px; text-align:center; transition:0.3s; }
        .stat-card:hover { transform:translateY(-3px); box-shadow:0 5px 15px rgba(0,0,0,0.05); }
        .stat-card .number { font-size:28px; font-weight:700; color:var(--primary); }
        .stat-card .label { font-size:12px; color:#999; margin-top:3px; }
        .stat-card .icon { font-size:20px; display:block; margin-bottom:6px; color:var(--primary); }
        .stat-card-primary { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; }
        .stat-card-primary .number { color:white; }
        .stat-card-primary .label { color:rgba(255,255,255,0.8); }
        .stat-card-primary .icon { color:rgba(255,255,255,0.8); }
        .stat-card-success { background:linear-gradient(135deg,#00b894 0%,#00a381 100%); color:white; }
        .stat-card-success .number { color:white; }
        .stat-card-success .label { color:rgba(255,255,255,0.8); }
        .stat-card-success .icon { color:rgba(255,255,255,0.8); }
        .stat-card-warning { background:linear-gradient(135deg,#fdcb6e 0%,#f6b93b 100%); color:white; }
        .stat-card-warning .number { color:white; }
        .stat-card-warning .label { color:rgba(255,255,255,0.8); }
        .stat-card-warning .icon { color:rgba(255,255,255,0.8); }
        .stat-card-info { background:linear-gradient(135deg,#74b9ff 0%,#0984e3 100%); color:white; }
        .stat-card-info .number { color:white; }
        .stat-card-info .label { color:rgba(255,255,255,0.8); }
        .stat-card-info .icon { color:rgba(255,255,255,0.8); }
        input, select, textarea { width:100%; padding:10px 12px; margin-bottom:10px; border:2px solid var(--border); border-radius:8px; background:var(--card-bg); color:var(--text); font-family:inherit; font-size:13px; transition:0.3s; }
        input:focus, select:focus, textarea:focus { border-color:var(--primary); outline:none; }
        button { padding:10px 20px; background:var(--primary); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; transition:0.3s; display:inline-flex; align-items:center; gap:6px; font-size:13px; }
        button:hover { background:var(--primary-dark); transform:translateY(-2px); box-shadow:0 5px 15px rgba(108,99,255,0.3); }
        button.danger { background:#dc3545; }
        button.danger:hover { background:#c82333; }
        button.success { background:#28a745; }
        button.success:hover { background:#218838; }
        button.warning { background:#ffc107; color:#333; }
        button.warning:hover { background:#e0a800; }
        button.info { background:#17a2b8; }
        button.info:hover { background:#138496; }
        .btn-group { display:flex; gap:6px; flex-wrap:wrap; }
        .btn-group a { flex:1; min-width:70px; text-align:center; text-decoration:none; font-size:12px; padding:6px 12px; border-radius:6px; }
        .table-responsive { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px 12px; text-align:left; border-bottom:1px solid var(--border); font-size:13px; }
        th { background:var(--bg); font-weight:600; font-size:12px; text-transform:uppercase; }
        .message { padding:12px 16px; border-radius:10px; margin-bottom:15px; display:flex; align-items:center; gap:10px; font-size:13px; }
        .message.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .message.error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        .message.info { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
        .service-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:12px; }
        .service-card { background:var(--bg); padding:15px; border-radius:10px; text-align:center; border:2px solid transparent; transition:0.3s; }
        .service-card:hover { border-color:var(--primary); transform:translateY(-3px); }
        .service-card .icon { font-size:30px; display:block; margin-bottom:8px; }
        .service-card .name { font-weight:600; font-size:13px; display:block; }
        .service-card .name a { color:var(--text); text-decoration:none; }
        .service-card .name a:hover { color:var(--primary); }
        .service-card .desc { font-size:11px; color:#999; margin-top:3px; display:block; }
        .service-card .category { font-size:9px; background:var(--border); padding:1px 10px; border-radius:10px; display:inline-block; margin-top:5px; }
        .cron-status { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:4px; }
        .cron-status.enabled { background:#28a745; }
        .cron-status.disabled { background:#dc3545; }
        .breadcrumb { display:flex; flex-wrap:wrap; gap:4px; padding:10px 14px; background:var(--bg); border-radius:8px; margin-bottom:12px; font-size:13px; }
        .breadcrumb a { color:var(--primary); text-decoration:none; }
        .breadcrumb a:hover { text-decoration:underline; }
        .file-item { display:flex; justify-content:space-between; align-items:center; padding:8px 12px; border-bottom:1px solid var(--border); transition:0.2s; font-size:13px; }
        .file-item:hover { background:var(--bg); }
        .file-item .name { flex:1; display:flex; align-items:center; gap:8px; }
        .file-item .name a { color:var(--text); text-decoration:none; }
        .file-item .name a:hover { color:var(--primary); }
        .file-item .info { display:flex; gap:10px; font-size:11px; color:#999; align-items:center; }
        .file-item .actions { display:flex; gap:4px; }
        .file-item .actions a { padding:3px 8px; border-radius:4px; font-size:10px; text-decoration:none; transition:0.2s; }
        .file-item .actions a:hover { transform:scale(1.05); }
        .file-item .actions .edit-btn { background:#17a2b8; color:white; }
        .file-item .actions .delete-btn { background:#dc3545; color:white; }
        .file-item .actions .download-btn { background:#28a745; color:white; }
        .file-item .actions .view-btn { background:var(--primary); color:white; }
        .file-item .actions .unzip-btn { background:#ffc107; color:#333; }
        .drop-zone { border:3px dashed var(--border); border-radius:12px; padding:30px; text-align:center; transition:0.3s; cursor:pointer; background:var(--bg); margin-bottom:15px; }
        .drop-zone:hover, .drop-zone.dragover { border-color:var(--primary); background:rgba(108,99,255,0.05); }
        .drop-zone .icon { font-size:40px; color:#ccc; margin-bottom:8px; }
        .drop-zone .text { color:#999; font-size:14px; }
        .drop-zone .sub { color:#bbb; font-size:12px; margin-top:3px; }
        .modal { display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; backdrop-filter:blur(4px); }
        .modal.show { display:flex; }
        .modal-content { background:var(--card-bg); padding:30px; border-radius:16px; max-width:800px; width:90%; max-height:85vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:2px solid var(--border); padding-bottom:12px; }
        .modal-header h3 { font-size:18px; }
        .modal-header h3 i { margin-right:8px; color:var(--primary); }
        .modal-close { background:none; border:none; font-size:24px; cursor:pointer; color:#999; }
        .badge { display:inline-block; padding:2px 10px; border-radius:10px; font-size:10px; font-weight:600; }
        .badge-success { background:#28a74520; color:#28a745; }
        .badge-danger { background:#dc354520; color:#dc3545; }
        .badge-warning { background:#ffc10720; color:#856404; }
        .badge-info { background:#17a2b820; color:#17a2b8; }
        .badge-primary { background:var(--primary)20; color:var(--primary); }
        .footer { text-align:center; padding:20px; margin-top:20px; border-top:1px solid var(--border); font-size:12px; color:#999; }
        .footer strong { color:var(--primary); }
        .menu-toggle { display:none; background:none; border:none; font-size:22px; cursor:pointer; color:var(--text); }
        .cpanel-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:12px; margin:15px 0; }
        .cpanel-item { background:var(--bg); padding:15px; border-radius:10px; text-align:center; transition:0.3s; text-decoration:none; color:var(--text); border:2px solid transparent; }
        .cpanel-item:hover { border-color:var(--primary); transform:translateY(-3px); background:var(--card-bg); }
        .cpanel-item .icon { font-size:28px; display:block; margin-bottom:6px; color:var(--primary); }
        .cpanel-item .label { font-size:12px; font-weight:600; display:block; }
        .cpanel-item .desc { font-size:10px; color:#999; display:block; margin-top:2px; }
        .welcome-banner { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:25px; border-radius:12px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
        .welcome-banner h2 { font-size:22px; margin:0; }
        .welcome-banner p { opacity:0.8; margin:3px 0 0; font-size:13px; }
        .welcome-banner .stats { display:flex; gap:20px; background:rgba(255,255,255,0.1); padding:12px 20px; border-radius:10px; backdrop-filter:blur(10px); }
        .welcome-banner .stats .stat { text-align:center; }
        .welcome-banner .stats .stat .num { font-size:20px; font-weight:700; }
        .welcome-banner .stats .stat .lbl { font-size:10px; opacity:0.7; }
        .disk-usage { background:var(--bg); padding:3px; border-radius:4px; margin-top:3px; }
        .disk-usage .bar { background:var(--primary); height:5px; border-radius:3px; transition:width 0.5s; }
        .domain-selector { background:var(--bg); padding:12px; border-radius:8px; margin-bottom:12px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .domain-selector select { width:auto; min-width:180px; margin:0; }
        .domain-selector label { font-weight:600; font-size:13px; }
        .CodeMirror { border-radius:8px; border:2px solid var(--border); height:auto; min-height:250px; font-size:13px; }
        .app-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:15px; }
        .app-card { background:var(--bg); padding:20px; border-radius:12px; text-align:center; border:2px solid transparent; transition:0.3s; cursor:pointer; }
        .app-card:hover { border-color:var(--primary); transform:translateY(-3px); }
        .app-card .icon { font-size:48px; display:block; margin-bottom:10px; }
        .app-card .name { font-weight:600; font-size:14px; }
        .app-card .desc { font-size:11px; color:#999; margin-top:3px; }
        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); }
            .right-sidebar { display:none; }
            .main { margin-left:0; margin-right:0; padding:15px; }
            .menu-toggle { display:block; }
            .grid { grid-template-columns:1fr; }
            .welcome-banner { flex-direction:column; text-align:center; }
            .welcome-banner .stats { margin-top:10px; flex-wrap:wrap; justify-content:center; }
            .domain-selector { flex-direction:column; align-items:stretch; }
            .domain-selector select { width:100%; }
            .file-item { flex-wrap:wrap; }
            .file-item .info { width:100%; margin-top:4px; }
            .file-item .actions { width:100%; margin-top:4px; }
        }
    </style>
</head>
<body>

<!-- Left Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h2><i class="fas fa-server"></i> Magochi Host</h2>
        <div class="sub">Web Hosting Control Panel v<?php echo htmlspecialchars($version); ?></div>
    </div>
    <ul class="sidebar-nav">
        <?php foreach ($menu_groups as $group => $items): ?>
            <?php if (!empty($items) && ($is_admin || $group != 'admin')): ?>
                <li class="nav-group"><?php echo ucfirst($group); ?></li>
                <?php foreach ($items as $item): ?>
                    <?php if ($is_admin || !in_array($item['action'], array('admin_users','admin_domains','admin_databases','admin_ftp','admin_cron','php_settings','users','server'))): ?>
                        <li><a href="?action=<?php echo $item['action']; ?>" class="<?php echo $action==$item['action']?'active':''; ?>"><i class="fas <?php echo $item['icon']; ?>"></i> <?php echo $item['label']; ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="divider"></li>
            <?php endif; ?>
        <?php endforeach; ?>
        <li><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
    <div class="sidebar-user">
        <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'] ?? $_SESSION['username'], 0, 2)); ?></div>
        <div class="info">
            <div class="name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>
                <?php if (isset($_SESSION['is_switched']) && $_SESSION['is_switched']): ?><span class="switch-badge">SWITCHED</span><?php endif; ?>
            </div>
            <div class="role"><?php echo ucfirst(getUserRole()); ?></div>
        </div>
        <?php if (isset($_SESSION['is_switched']) && $_SESSION['is_switched']): ?>
            <a href="?return_from_user=1" class="btn" style="background:#ffd93d; color:#333; padding:3px 8px; font-size:9px; text-decoration:none; border-radius:3px;">Return</a>
        <?php endif; ?>
    </div>
</div>

<!-- Right Sidebar -->
<div class="right-sidebar">
    <div class="widget">
        <h4><i class="fas fa-chart-pie"></i> Account Stats</h4>
        <div class="stat-item"><span class="label">Domains</span><span class="value"><?php echo $user_domains->num_rows; ?></span></div>
        <div class="stat-item"><span class="label">Databases</span><span class="value"><?php echo $user_databases->num_rows; ?></span></div>
        <div class="stat-item"><span class="label">Cron Jobs</span><span class="value"><?php echo $user_crons->num_rows; ?></span></div>
        <div class="stat-item"><span class="label">FTP Users</span><span class="value"><?php echo $user_ftp->num_rows; ?></span></div>
        <div class="stat-item"><span class="label">Disk Used</span><span class="value"><?php echo formatSize($disk_used); ?></span></div>
        <div class="stat-item"><span class="label">Disk Limit</span><span class="value"><?php echo $disk_limit; ?> MB</span></div>
        <div class="stat-item"><span class="label">API Keys</span><span class="value"><?php echo $user_api_keys->num_rows; ?></span></div>
    </div>
    <div class="widget">
        <h4><i class="fas fa-rocket"></i> Quick Apps</h4>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
            <a href="?action=apps" style="text-decoration:none; background:var(--bg); padding:10px; border-radius:8px; text-align:center; font-size:12px; color:var(--text);"><i class="fab fa-wordpress" style="color:#21759b; font-size:20px; display:block;"></i> WordPress</a>
            <a href="?action=files" style="text-decoration:none; background:var(--bg); padding:10px; border-radius:8px; text-align:center; font-size:12px; color:var(--text);"><i class="fas fa-folder" style="color:var(--primary); font-size:20px; display:block;"></i> File Manager</a>
            <a href="?action=domains" style="text-decoration:none; background:var(--bg); padding:10px; border-radius:8px; text-align:center; font-size:12px; color:var(--text);"><i class="fas fa-globe" style="color:#00b894; font-size:20px; display:block;"></i> Domains</a>
            <a href="?action=databases" style="text-decoration:none; background:var(--bg); padding:10px; border-radius:8px; text-align:center; font-size:12px; color:var(--text);"><i class="fas fa-database" style="color:#0984e3; font-size:20px; display:block;"></i> Databases</a>
        </div>
    </div>
    <div class="widget">
        <h4><i class="fas fa-server"></i> Server Info</h4>
        <div class="stat-item"><span class="label">PHP</span><span class="value"><?php echo phpversion(); ?></span></div>
        <div class="stat-item"><span class="label">MySQL</span><span class="value"><?php echo $server_info['mysql_version']; ?></span></div>
        <div class="stat-item"><span class="label">Upload Max</span><span class="value"><?php echo ini_get('upload_max_filesize'); ?></span></div>
    </div>
</div>

<!-- Main Content -->
<div class="main">
    <div class="topbar">
        <div style="display:flex; align-items:center; gap:12px;">
            <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>
            <h1><i class="fas <?php echo $action=='dashboard'?'fa-home':($action=='apps'?'fa-th':'fa-cog'); ?>"></i> <?php echo htmlspecialchars(ucfirst(str_replace('_',' ',$action))); ?></h1>
        </div>
        <div class="topbar-right">
            <span class="badge"><i class="fas fa-user"></i> <?php echo ucfirst(getUserRole()); ?></span>
            <span class="time"><i class="fas fa-clock"></i> <?php echo date('Y-m-d H:i'); ?></span>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="message <?php echo $msg_type; ?>">
        <i class="fas <?php echo $msg_type=='success'?'fa-check-circle':($msg_type=='error'?'fa-exclamation-circle':'fa-info-circle'); ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- DASHBOARD -->
    <!-- ================================================================ -->
    <?php if ($action == 'dashboard'): ?>
    <div class="welcome-banner">
        <div><h2>👋 Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</h2><p>Your hosting account is active. Here's your overview.</p></div>
        <div class="stats">
            <div class="stat"><div class="num"><?php echo $user_domains->num_rows; ?></div><div class="lbl">Domains</div></div>
            <div class="stat"><div class="num"><?php echo $user_databases->num_rows; ?></div><div class="lbl">Databases</div></div>
            <div class="stat"><div class="num"><?php echo $user_crons->num_rows; ?></div><div class="lbl">Cron Jobs</div></div>
            <div class="stat"><div class="num"><?php echo count($files); ?></div><div class="lbl">Files</div></div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-hdd"></i> Disk Usage</h3><span><?php echo formatSize($disk_used); ?> / <?php echo $disk_limit; ?> MB</span></div>
        <div class="disk-usage"><div class="bar" style="width: <?php echo min(($disk_used / 1024 / 1024 / $disk_limit) * 100, 100); ?>%;"></div></div>
        <p style="font-size:12px; color:#999; margin-top:4px;">Used: <?php echo formatSize($disk_used); ?> of <?php echo $disk_limit; ?> MB</p>
    </div>
    <div class="grid">
        <div class="stat-card stat-card-primary"><span class="icon"><i class="fas fa-globe"></i></span><div class="number"><?php echo $user_domains->num_rows; ?></div><div class="label">Your Domains</div></div>
        <div class="stat-card stat-card-success"><span class="icon"><i class="fas fa-database"></i></span><div class="number"><?php echo $user_databases->num_rows; ?></div><div class="label">Your Databases</div></div>
        <div class="stat-card stat-card-warning"><span class="icon"><i class="fas fa-clock"></i></span><div class="number"><?php echo $user_crons->num_rows; ?></div><div class="label">Cron Jobs</div></div>
        <div class="stat-card stat-card-info"><span class="icon"><i class="fas fa-link"></i></span><div class="number"><?php echo $user_services->num_rows; ?></div><div class="label">Services</div></div>
    </div>
    <?php if ($is_admin): ?>
    <div class="grid">
        <div class="stat-card" style="background:linear-gradient(135deg,#2D3436 0%,#1a1a2e 100%);color:white;"><span class="icon" style="color:rgba(255,255,255,0.8);"><i class="fas fa-users"></i></span><div class="number" style="color:white;"><?php echo $total_users; ?></div><div class="label" style="color:rgba(255,255,255,0.8);">Total Users</div></div>
        <div class="stat-card stat-card-success"><span class="icon"><i class="fas fa-globe-americas"></i></span><div class="number"><?php echo $total_domains; ?></div><div class="label">Total Domains</div></div>
        <div class="stat-card stat-card-info"><span class="icon"><i class="fas fa-database"></i></span><div class="number"><?php echo $total_databases; ?></div><div class="label">Total Databases</div></div>
    </div>
    <?php endif; ?>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-th"></i> Quick Access</h3></div>
        <div class="cpanel-grid">
            <?php if (!$is_admin): ?>
            <a href="?action=domains" class="cpanel-item"><span class="icon"><i class="fas fa-globe"></i></span><span class="label">Domains</span><span class="desc">Manage domains</span></a>
            <a href="?action=databases" class="cpanel-item"><span class="icon"><i class="fas fa-database"></i></span><span class="label">Databases</span><span class="desc">Create databases</span></a>
            <a href="?action=files" class="cpanel-item"><span class="icon"><i class="fas fa-folder"></i></span><span class="label">File Manager</span><span class="desc">Upload & edit files</span></a>
            <a href="?action=apps" class="cpanel-item"><span class="icon"><i class="fas fa-th"></i></span><span class="label">Apps</span><span class="desc">Install apps</span></a>
            <?php else: ?>
            <a href="?action=admin_users" class="cpanel-item"><span class="icon"><i class="fas fa-users-cog"></i></span><span class="label">Create Users</span><span class="desc">Add new users</span></a>
            <a href="?action=admin_domains" class="cpanel-item"><span class="icon"><i class="fas fa-globe-americas"></i></span><span class="label">Create Domains</span><span class="desc">Create domains for users</span></a>
            <a href="?action=admin_databases" class="cpanel-item"><span class="icon"><i class="fas fa-database"></i></span><span class="label">Create Databases</span><span class="desc">Create databases for users</span></a>
            <a href="?action=php_settings" class="cpanel-item"><span class="icon"><i class="fab fa-php"></i></span><span class="label">PHP Settings</span><span class="desc">Configure PHP</span></a>
            <?php endif; ?>
            <a href="?action=cron" class="cpanel-item"><span class="icon"><i class="fas fa-clock"></i></span><span class="label">Cron Jobs</span><span class="desc">Schedule tasks</span></a>
            <a href="?action=services" class="cpanel-item"><span class="icon"><i class="fas fa-link"></i></span><span class="label">Services</span><span class="desc">Quick links</span></a>
            <a href="?action=backups" class="cpanel-item"><span class="icon"><i class="fas fa-archive"></i></span><span class="label">Backups</span><span class="desc">Backup databases</span></a>
            <a href="?action=api" class="cpanel-item"><span class="icon"><i class="fas fa-key"></i></span><span class="label">API</span><span class="desc">API keys</span></a>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-server"></i> Server Information</h3></div>
        <div class="table-responsive"><table>
            <?php foreach ($server_info as $k => $v): ?>
            <tr><td><strong><?php echo htmlspecialchars(ucwords(str_replace('_',' ',$k))); ?></strong></td><td><?php echo htmlspecialchars($v); ?></td></tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- PHP SETTINGS (ADMIN) -->
    <!-- ================================================================ -->
    <?php if ($action == 'php_settings' && $is_admin): ?>
    <div class="card">
        <div class="card-header"><h3><i class="fab fa-php"></i> PHP Settings (php.ini)</h3></div>
        <form method="POST">
            <div class="grid">
                <div><label>Upload Max Filesize</label><input type="text" name="upload_max_filesize" value="<?php echo ini_get('upload_max_filesize'); ?>" placeholder="e.g., 64M"></div>
                <div><label>Post Max Size</label><input type="text" name="post_max_size" value="<?php echo ini_get('post_max_size'); ?>" placeholder="e.g., 64M"></div>
                <div><label>Memory Limit</label><input type="text" name="memory_limit" value="<?php echo ini_get('memory_limit'); ?>" placeholder="e.g., 256M"></div>
                <div><label>Max Execution Time (seconds)</label><input type="text" name="max_execution_time" value="<?php echo ini_get('max_execution_time'); ?>" placeholder="e.g., 300"></div>
                <div><label>Max Input Time (seconds)</label><input type="text" name="max_input_time" value="<?php echo ini_get('max_input_time'); ?>" placeholder="e.g., 300"></div>
            </div>
            <button type="submit" name="update_php_settings"><i class="fas fa-save"></i> Update PHP Settings</button>
            <p style="font-size:12px; color:#999; margin-top:10px;"><i class="fas fa-info-circle"></i> Restart your web server after updating php.ini for changes to take effect.</p>
        </form>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- NAMESERVER MANAGEMENT -->
    <!-- ================================================================ -->
    <?php if ($action == 'nameservers'): ?>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-server"></i> Nameserver Management</h3></div>
        <?php if ($user_domains->num_rows > 0): ?>
        <?php while ($domain = $user_domains->fetch_assoc()): ?>
        <form method="POST" style="border-bottom:1px solid var(--border); padding-bottom:15px; margin-bottom:15px;">
            <h4><?php echo htmlspecialchars($domain['domain']); ?></h4>
            <input type="hidden" name="domain_id" value="<?php echo $domain['id']; ?>">
            <div class="grid">
                <div><label>Nameserver 1</label><input type="text" name="nameserver1" value="<?php echo htmlspecialchars($domain['nameserver1']); ?>" placeholder="ns1.yourdomain.com"></div>
                <div><label>Nameserver 2</label><input type="text" name="nameserver2" value="<?php echo htmlspecialchars($domain['nameserver2']); ?>" placeholder="ns2.yourdomain.com"></div>
            </div>
            <button type="submit" name="update_nameservers"><i class="fas fa-save"></i> Update Nameservers</button>
        </form>
        <?php endwhile; ?>
        <?php else: ?>
        <p style="color:#999;">No domains found. Create a domain first.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- SSL CERTIFICATE MANAGEMENT -->
    <!-- ================================================================ -->
    <?php if ($action == 'ssl'): ?>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-lock"></i> SSL Certificate Management</h3></div>
        <?php if ($user_domains->num_rows > 0): ?>
        <?php $user_domains->data_seek(0); while ($domain = $user_domains->fetch_assoc()): ?>
        <form method="POST" style="border-bottom:1px solid var(--border); padding-bottom:15px; margin-bottom:15px;">
            <h4><?php echo htmlspecialchars($domain['domain']); ?></h4>
            <input type="hidden" name="domain_id" value="<?php echo $domain['id']; ?>">
            <div><label>SSL Certificate (PEM format)</label><textarea name="ssl_cert" rows="4" placeholder="-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----"><?php echo htmlspecialchars($domain['ssl_cert']); ?></textarea></div>
            <div><label>Private Key (PEM format)</label><textarea name="ssl_key" rows="4" placeholder="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----"><?php echo htmlspecialchars($domain['ssl_key']); ?></textarea></div>
            <button type="submit" name="upload_ssl"><i class="fas fa-upload"></i> Upload SSL Certificate</button>
            <?php if (!empty($domain['ssl_cert'])): ?>
            <span style="margin-left:10px; color:#28a745;"><i class="fas fa-check-circle"></i> Certificate Installed</span>
            <?php endif; ?>
        </form>
        <?php endwhile; ?>
        <?php else: ?>
        <p style="color:#999;">No domains found. Create a domain first.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- DNS MANAGEMENT -->
    <!-- ================================================================ -->
    <?php if ($action == 'dns'): ?>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-network-wired"></i> DNS Management</h3></div>
        <div style="background:var(--bg); padding:15px; border-radius:10px; margin-bottom:15px;">
            <h4><i class="fas fa-plus-circle"></i> Add DNS Record</h4>
            <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:10px;">
                <select name="domain_id" style="margin:0;">
                    <option value="">-- Select Domain --</option>
                    <?php $user_domains->data_seek(0); while ($d = $user_domains->fetch_assoc()): ?>
                    <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['domain']); ?></option>
                    <?php endwhile; ?>
                </select>
                <select name="dns_type" style="margin:0;">
                    <option value="A">A</option>
                    <option value="AAAA">AAAA</option>
                    <option value="CNAME">CNAME</option>
                    <option value="MX">MX</option>
                    <option value="TXT">TXT</option>
                    <option value="NS">NS</option>
                </select>
                <input type="text" name="dns_name" placeholder="Name (e.g., www)" style="margin:0;">
                <input type="text" name="dns_value" placeholder="Value (e.g., IP address)" style="margin:0;">
                <input type="number" name="dns_ttl" placeholder="TTL (seconds)" value="3600" style="margin:0;">
                <button type="submit" name="add_dns_record"><i class="fas fa-plus"></i> Add Record</button>
            </form>
        </div>
        <h4 style="font-size:14px; margin-bottom:10px;">DNS Records</h4>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Domain</th><th>Type</th><th>Name</th><th>Value</th><th>TTL</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php 
                    $dns_records = $conn->query("SELECT d.*, domains.domain FROM dns_records d LEFT JOIN domains ON d.domain_id = domains.id WHERE domains.user_id = $user_id ORDER BY d.created_at DESC");
                    if ($dns_records->num_rows > 0): while ($r = $dns_records->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['domain']); ?></strong></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($r['type']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><code style="font-size:11px;"><?php echo htmlspecialchars($r['value']); ?></code></td>
                        <td><?php echo $r['ttl']; ?></td>
                        <td><a href="?delete_dns_record=<?php echo $r['id']; ?>&action=dns" class="btn" style="background:#dc3545; color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:11px;" onclick="return confirm('Delete this DNS record?')"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">No DNS records.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- EMAIL NOTIFICATIONS -->
    <!-- ================================================================ -->
    <?php if ($action == 'email'): ?>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-envelope"></i> Email Notifications</h3></div>
        <form method="POST">
            <div><label>Notification Email</label><input type="email" name="notification_email" value="<?php echo $user_email_notifications->num_rows > 0 ? $user_email_notifications->fetch_assoc()['email'] : ''; ?>" placeholder="your@email.com"></div>
            <div class="grid">
                <div><label><input type="checkbox" name="notify_domain_create" <?php echo $user_email_notifications->num_rows > 0 && $user_email_notifications->data_seek(0) ? 'checked' : ''; ?>> Domain Creation</label></div>
                <div><label><input type="checkbox" name="notify_backup_complete" checked> Backup Complete</label></div>
                <div><label><input type="checkbox" name="notify_cron_run" checked> Cron Job Run</label></div>
                <div><label><input type="checkbox" name="notify_ssl_expiry"> SSL Expiry Warning</label></div>
            </div>
            <button type="submit" name="update_email_settings"><i class="fas fa-save"></i> Save Email Settings</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- APPS SECTION -->
    <!-- ================================================================ -->
    <?php if ($action == 'apps'): ?>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-th"></i> Available Apps</h3></div>
        <div class="app-grid">
            <div class="app-card" onclick="document.getElementById('wpInstaller').classList.toggle('show')">
                <span class="icon"><i class="fab fa-wordpress" style="color:#21759b;"></i></span>
                <span class="name">WordPress</span>
                <span class="desc">Install WordPress CMS</span>
            </div>
            <div class="app-card" onclick="alert('Coming soon! Other apps will be available in future updates.')">
                <span class="icon"><i class="fab fa-joomla" style="color:#f44336;"></i></span>
                <span class="name">Joomla</span>
                <span class="desc">Coming soon</span>
            </div>
            <div class="app-card" onclick="alert('Coming soon! Other apps will be available in future updates.')">
                <span class="icon"><i class="fab fa-drupal" style="color:#0077c0;"></i></span>
                <span class="name">Drupal</span>
                <span class="desc">Coming soon</span>
            </div>
            <div class="app-card" onclick="alert('Coming soon! Other apps will be available in future updates.')">
                <span class="icon"><i class="fab fa-laravel" style="color:#ff2d20;"></i></span>
                <span class="name">Laravel</span>
                <span class="desc">Coming soon</span>
            </div>
        </div>
    </div>

    <!-- WordPress Installer Modal -->
    <div class="modal" id="wpInstaller">
        <div class="modal-content" style="max-width:650px;">
            <div class="modal-header">
                <h3><i class="fab fa-wordpress" style="color:#21759b;"></i> Install WordPress</h3>
                <button class="modal-close" onclick="document.getElementById('wpInstaller').classList.remove('show')">&times;</button>
            </div>
            <form method="POST">
                <label>Select Domain *</label>
                <select name="wp_domain_id" required>
                    <option value="">-- Select Domain --</option>
                    <?php $user_domains->data_seek(0); while ($d = $user_domains->fetch_assoc()): ?>
                    <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['domain']); ?></option>
                    <?php endwhile; ?>
                </select>
                <label>Installation Path (optional)</label>
                <input type="text" name="wp_path" placeholder="Leave empty for root, or enter subfolder (e.g., blog)">
                <label>Site Title</label>
                <input type="text" name="wp_site_title" placeholder="My WordPress Site" value="My WordPress Site">
                <label>WordPress Version</label>
                <select name="wp_version">
                    <option value="latest">Latest</option>
                    <option value="6.4">6.4</option>
                    <option value="6.3">6.3</option>
                    <option value="6.2">6.2</option>
                </select>
                <label>Admin Username *</label>
                <input type="text" name="wp_admin_user" placeholder="admin" required>
                <label>Admin Password *</label>
                <input type="text" name="wp_admin_pass" placeholder="Strong password" required>
                <label>Admin Email *</label>
                <input type="email" name="wp_admin_email" placeholder="admin@example.com" required>
                <p style="font-size:12px; color:#999; margin:10px 0;"><i class="fas fa-info-circle"></i> WordPress will be installed in the selected domain folder. A database will be created automatically.</p>
                <button type="submit" name="install_wordpress" style="width:100%;"><i class="fas fa-download"></i> Install WordPress</button>
            </form>
        </div>
    </div>

    <!-- WordPress Installs List -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-list"></i> Your WordPress Installations</h3></div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Domain</th><th>Path</th><th>Version</th><th>Admin</th><th>Email</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                    <?php if ($wordpress_installs->num_rows>0): while ($wp = $wordpress_installs->fetch_assoc()): 
                        $domain_info = $conn->query("SELECT domain FROM domains WHERE id={$wp['domain_id']}")->fetch_assoc();
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($domain_info['domain'] ?? 'Unknown'); ?></strong></td>
                        <td><?php echo htmlspecialchars($wp['path'] ?: '/'); ?></td>
                        <td><?php echo htmlspecialchars($wp['version']); ?></td>
                        <td><?php echo htmlspecialchars($wp['admin_user']); ?></td>
                        <td><?php echo htmlspecialchars($wp['admin_email']); ?></td>
                        <td><span class="badge badge-success"><?php echo ucfirst($wp['status']); ?></span></td>
                        <td><?php echo $wp['created_at']; ?></td>
                    </tr>
                    <?php endwhile; else: ?><tr><td colspan="7" style="text-align:center; padding:20px; color:#999;">No WordPress installations yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- ADMIN PAGES -->
    <!-- ================================================================ -->
    <?php if ($is_admin): ?>
    <?php if ($action == 'admin_users'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-users-cog"></i> Create User</h3></div>
    <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px;">
        <input type="text" name="admin_username" placeholder="Username" required>
        <input type="password" name="admin_password" placeholder="Password" required>
        <input type="email" name="admin_email" placeholder="Email" required>
        <input type="text" name="admin_fullname" placeholder="Full Name" required>
        <select name="admin_role"><option value="user">User</option><option value="admin">Admin</option></select>
        <input type="number" name="admin_disk_limit" placeholder="Disk Limit (MB)" value="100">
        <button type="submit" name="admin_create_user"><i class="fas fa-plus"></i> Create User</button>
    </form></div>
    <?php endif; ?>
    <?php if ($action == 'admin_domains'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-globe-americas"></i> Create Domain for User</h3></div>
    <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px;">
        <input type="text" name="admin_domain" placeholder="Domain Name" required>
        <input type="number" name="admin_user_id" placeholder="User ID" required>
        <input type="text" name="admin_folder" placeholder="Folder (optional)">
        <button type="submit" name="admin_create_domain"><i class="fas fa-plus"></i> Create Domain</button>
    </form>
    <p style="font-size:12px; color:#999; margin-top:5px;">Enter the User ID from the "All Users" list.</p></div>
    <?php endif; ?>
    <?php if ($action == 'admin_databases'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-database"></i> Create Database for User</h3></div>
    <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px;">
        <input type="text" name="admin_dbname" placeholder="Database Name" required>
        <input type="number" name="admin_db_user_id" placeholder="User ID" required>
        <input type="text" name="admin_dbuser" placeholder="DB User (optional)">
        <input type="text" name="admin_dbpass" placeholder="DB Password (auto)">
        <button type="submit" name="admin_create_db"><i class="fas fa-plus"></i> Create Database</button>
    </form></div>
    <?php endif; ?>
    <?php if ($action == 'admin_ftp'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-user-lock"></i> Create FTP User</h3></div>
    <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px;">
        <input type="text" name="admin_ftp_username" placeholder="FTP Username" required>
        <input type="password" name="admin_ftp_password" placeholder="Password" required>
        <input type="number" name="admin_ftp_user_id" placeholder="User ID" required>
        <input type="text" name="admin_ftp_home" placeholder="Home Directory">
        <button type="submit" name="admin_create_ftp"><i class="fas fa-plus"></i> Create FTP User</button>
    </form></div>
    <?php endif; ?>
    <?php if ($action == 'admin_cron'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-clock"></i> Create Cron Job for User</h3></div>
    <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px;">
        <input type="text" name="admin_cron_name" placeholder="Job Name" required>
        <input type="url" name="admin_cron_url" placeholder="URL" required>
        <input type="number" name="admin_cron_user_id" placeholder="User ID" required>
        <input type="text" name="admin_cron_schedule" placeholder="Schedule (e.g., 0 2 * * *)" value="0 2 * * *">
        <input type="text" name="admin_cron_description" placeholder="Description">
        <div style="display:flex; align-items:center; gap:8px;"><label><input type="checkbox" name="admin_cron_enabled" checked> Enabled</label></div>
        <button type="submit" name="admin_create_cron"><i class="fas fa-plus"></i> Create Cron Job</button>
    </form></div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- ALL USERS (ADMIN) -->
    <!-- ================================================================ -->
    <?php if ($action == 'users' && $is_admin): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-users"></i> All Users</h3><span class="badge-count"><?php echo $total_users; ?></span></div>
    <div class="table-responsive"><table>
        <thead><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Status</th><th>Disk</th><th>Used</th><th>Actions</th></tr></thead>
        <tbody><?php $all_users->data_seek(0); while ($u = $all_users->fetch_assoc()): $used = $u['disk_used']??0; $limit = $u['disk_limit']??100; $pct = min(($used/1024/1024/$limit)*100,100); ?>
        <tr><td>#<?php echo $u['id']; ?></td><td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td><td><?php echo htmlspecialchars($u['full_name']); ?></td>
        <td><span class="badge <?php echo $u['role']=='admin'?'badge-success':'badge-info'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
        <td><span class="badge <?php echo $u['status']=='active'?'badge-success':'badge-danger'; ?>"><?php echo ucfirst($u['status']); ?></span></td>
        <td><?php echo $limit; ?> MB</td>
        <td><?php echo formatSize($used); ?><div class="disk-usage" style="width:80px; display:inline-block; margin-left:5px;"><div class="bar" style="width:<?php echo $pct; ?>%; background:<?php echo $pct>80?'#dc3545':($pct>60?'#ffc107':'#28a745'); ?>;"></div></div></td>
        <td><div class="btn-group" style="gap:4px;"><?php if ($u['id'] != $user_id): ?>
        <a href="?switch_user=<?php echo $u['id']; ?>" class="btn" style="background:#17a2b8; color:white; padding:4px 8px; border-radius:4px; text-decoration:none; font-size:10px;" title="Login as User"><i class="fas fa-user-secret"></i></a>
        <a href="?admin_suspend_user=<?php echo $u['id']; ?>&status=<?php echo $u['status']=='active'?'suspended':'active'; ?>" class="btn" style="background:#ffc107; color:#333; padding:4px 8px; border-radius:4px; text-decoration:none; font-size:10px;"><i class="fas <?php echo $u['status']=='active'?'fa-pause':'fa-play'; ?>"></i></a>
        <a href="?admin_delete_user=<?php echo $u['id']; ?>" class="btn" style="background:#dc3545; color:white; padding:4px 8px; border-radius:4px; text-decoration:none; font-size:10px;" onclick="return confirm('Delete user <?php echo addslashes($u['username']); ?>?')"><i class="fas fa-trash"></i></a>
        <?php endif; ?></div></td></tr>
        <?php endwhile; ?></tbody>
    </table></div></div>
    <div class="card"><div class="card-header"><h3><i class="fas fa-hdd"></i> Update Disk Limit</h3></div>
    <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px;">
        <input type="number" name="user_id" placeholder="User ID" required>
        <input type="number" name="disk_limit" placeholder="Disk Limit (MB)" required>
        <button type="submit" name="update_disk"><i class="fas fa-save"></i> Update</button>
    </form></div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- DOMAINS (USER) -->
    <!-- ================================================================ -->
    <?php if ($action == 'domains' && !$is_admin): ?>
    <div class="grid"><div class="card"><div class="card-header"><h3><i class="fas fa-plus-circle"></i> Create Domain</h3></div>
    <form method="POST"><input type="text" name="domain" placeholder="Domain Name" required><input type="text" name="folder" placeholder="Folder Name (optional)"><button type="submit" name="create_domain"><i class="fas fa-plus"></i> Create</button></form>
    <p style="font-size:12px; color:#999; margin-top:5px;">Max 5 domains.</p></div>
    <div class="card"><div class="card-header"><h3><i class="fas fa-list"></i> Your Domains</h3><span class="badge-count"><?php echo $user_domains->num_rows; ?></span></div>
    <div class="table-responsive"><table><thead><tr><th>Domain</th><th>Folder</th><th>Created</th><th>Actions</th></tr></thead><tbody>
        <?php if ($user_domains->num_rows>0): while ($d = $user_domains->fetch_assoc()): ?>
        <tr><td><strong><?php echo htmlspecialchars($d['domain']); ?></strong></td><td><?php echo htmlspecialchars($d['folder']); ?></td><td><?php echo $d['created_at']; ?></td>
        <td><a href="?action=files&path=<?php echo urlencode($domains_root.'/'.$d['folder']); ?>" class="btn" style="background:#17a2b8; color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:11px;"><i class="fas fa-folder-open"></i></a>
        <a href="?delete_domain=<?php echo $d['id']; ?>" class="btn" style="background:#dc3545; color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:11px;" onclick="return confirm('Delete this domain?')"><i class="fas fa-trash"></i></a></td></tr>
        <?php endwhile; else: ?><tr><td colspan="4" style="text-align:center; padding:20px; color:#999;">No domains yet.</td></tr><?php endif; ?>
    </tbody></table></div></div></div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- DATABASES (USER) -->
    <!-- ================================================================ -->
    <?php if ($action == 'databases' && !$is_admin): ?>
    <div class="grid"><div class="card"><div class="card-header"><h3><i class="fas fa-plus-circle"></i> Create Database</h3></div>
    <form method="POST"><input type="text" name="dbname" placeholder="Database Name" required><input type="text" name="dbuser" placeholder="DB User (optional)"><input type="text" name="dbpass" placeholder="Password (auto)"><button type="submit" name="create_db"><i class="fas fa-plus"></i> Create</button></form>
    <p style="font-size:12px; color:#999; margin-top:5px;">Max 3 databases.</p></div>
    <div class="card"><div class="card-header"><h3><i class="fas fa-list"></i> Your Databases</h3><span class="badge-count"><?php echo $user_databases->num_rows; ?></span></div>
    <div class="table-responsive"><table><thead><tr><th>Database</th><th>User</th><th>Password</th><th>Created</th><th>Actions</th></tr></thead><tbody>
        <?php if ($user_databases->num_rows>0): while ($db = $user_databases->fetch_assoc()): ?>
        <tr><td><strong><?php echo htmlspecialchars($db['db_name']); ?></strong></td><td><?php echo htmlspecialchars($db['db_user']); ?></td>
        <td><code style="background:var(--bg); padding:2px 6px; border-radius:4px; font-size:11px;"><?php echo htmlspecialchars($db['db_pass']); ?></code></td>
        <td><?php echo $db['created_at']; ?></td>
        <td><a href="?delete_db=<?php echo $db['id']; ?>" class="btn" style="background:#dc3545; color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:11px;" onclick="return confirm('Delete database?')"><i class="fas fa-trash"></i></a></td></tr>
        <?php endwhile; else: ?><tr><td colspan="5" style="text-align:center; padding:20px; color:#999;">No databases yet.</td></tr><?php endif; ?>
    </tbody></table></div></div></div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- FTP USERS (USER) -->
    <!-- ================================================================ -->
    <?php if ($action == 'ftp' && !$is_admin): ?>
    <div class="grid"><div class="card"><div class="card-header"><h3><i class="fas fa-user-plus"></i> Create FTP User</h3></div>
    <form method="POST"><input type="text" name="ftp_username" placeholder="Username" required><input type="password" name="ftp_password" placeholder="Password" required><input type="text" name="ftp_home" placeholder="Home Directory (optional)"><button type="submit" name="create_ftp"><i class="fas fa-plus"></i> Create</button></form></div>
    <div class="card"><div class="card-header"><h3><i class="fas fa-list"></i> FTP Users</h3><span class="badge-count"><?php echo $user_ftp->num_rows; ?></span></div>
    <div class="table-responsive"><table><thead><tr><th>Username</th><th>Home Directory</th><th>Created</th><th>Actions</th></tr></thead><tbody>
        <?php if ($user_ftp->num_rows>0): while ($f = $user_ftp->fetch_assoc()): ?>
        <tr><td><strong><?php echo htmlspecialchars($f['username']); ?></strong></td><td><code style="font-size:11px;"><?php echo htmlspecialchars($f['home_dir']); ?></code></td><td><?php echo $f['created_at']; ?></td>
        <td><a href="?delete_ftp=<?php echo $f['id']; ?>" class="btn" style="background:#dc3545; color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:11px;" onclick="return confirm('Delete FTP user?')"><i class="fas fa-trash"></i></a></td></tr>
        <?php endwhile; else: ?><tr><td colspan="4" style="text-align:center; padding:20px; color:#999;">No FTP users.</td></tr><?php endif; ?>
    </tbody></table></div></div></div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- SERVICES -->
    <!-- ================================================================ -->
    <?php if ($action == 'services'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-link"></i> Service Links</h3></div>
    <div style="background:var(--bg); padding:15px; border-radius:10px; margin-bottom:15px;">
        <h4><i class="fas fa-plus-circle"></i> Add Service Link</h4>
        <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:10px;">
            <input type="text" name="service_name" placeholder="Name" required>
            <input type="url" name="service_url" placeholder="URL" required>
            <input type="text" name="service_icon" placeholder="Icon (fa-*)" value="fa-link">
            <input type="color" name="service_color" value="#6C63FF">
            <input type="text" name="service_category" placeholder="Category" value="General">
            <input type="text" name="service_description" placeholder="Description">
            <button type="submit" name="add_service"><i class="fas fa-plus"></i> Add</button>
        </form>
    </div>
    <?php foreach ($categories as $cat => $services): ?>
    <div style="margin-bottom:15px;"><h4><i class="fas fa-tag"></i> <?php echo htmlspecialchars($cat); ?></h4>
    <div class="service-grid"><?php foreach ($services as $s): ?>
        <div class="service-card" style="border-color:<?php echo htmlspecialchars($s['color']); ?>30;">
            <span class="icon" style="color:<?php echo htmlspecialchars($s['color']); ?>;"><i class="fas <?php echo htmlspecialchars($s['icon']); ?>"></i></span>
            <span class="name"><a href="<?php echo htmlspecialchars($s['url']); ?>" target="_blank"><?php echo htmlspecialchars($s['name']); ?></a></span>
            <span class="desc"><?php echo htmlspecialchars($s['description']); ?></span>
            <span class="category"><?php echo htmlspecialchars($s['category']); ?></span>
            <?php if ($s['user_id'] == $user_id || $is_admin): ?>
            <div style="margin-top:8px;"><a href="?delete_service=<?php echo $s['id']; ?>&action=services" class="btn" style="background:#dc3545; color:white; padding:2px 12px; border-radius:4px; text-decoration:none; font-size:10px;" onclick="return confirm('Delete this service?')"><i class="fas fa-trash"></i> Delete</a></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?></div></div>
    <?php endforeach; ?></div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- CRON JOBS (USER) -->
    <!-- ================================================================ -->
    <?php if ($action == 'cron' && !$is_admin): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-clock"></i> Cron Jobs</h3></div>
    <div style="background:var(--bg); padding:15px; border-radius:10px; margin-bottom:15px;">
        <h4><i class="fas fa-plus-circle"></i> Add Cron Job (URL)</h4>
        <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px;">
            <input type="text" name="cron_name" placeholder="Name" required>
            <input type="url" name="cron_url" placeholder="URL" required>
            <input type="text" name="cron_schedule" placeholder="Schedule (e.g., 0 2 * * *)" value="0 2 * * *" required>
            <input type="text" name="cron_description" placeholder="Description">
            <div style="display:flex; align-items:center; gap:8px;"><label><input type="checkbox" name="cron_enabled" checked> Enabled</label></div>
            <button type="submit" name="add_cron"><i class="fas fa-plus"></i> Add</button>
        </form>
        <div style="margin-top:12px; padding:12px; background:#2D3436; border-radius:8px;">
            <h5 style="color:#ffd93d; font-size:13px;"><i class="fas fa-code"></i> API Endpoint</h5>
            <div style="background:#1a1a2e; padding:8px; border-radius:5px; font-family:monospace; font-size:11px; color:#dfe6e9; word-break:break-all;">
                <strong>Run all:</strong><br>
                <?php echo $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; ?>?api=cron&action=run_all&key=<?php echo htmlspecialchars($api_secret); ?><br><br>
                <strong>Run specific:</strong><br>
                <?php echo $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; ?>?api=cron&action=run&id=1&key=<?php echo htmlspecialchars($api_secret); ?>
            </div>
        </div>
    </div>
    <div class="table-responsive"><table><thead><tr><th>Status</th><th>Name</th><th>URL</th><th>Schedule</th><th>Last Run</th><th>Actions</th></tr></thead><tbody>
        <?php if ($user_crons->num_rows>0): while ($c = $user_crons->fetch_assoc()): ?>
        <tr><td><span class="cron-status <?php echo $c['enabled']?'enabled':'disabled'; ?>"></span><?php echo $c['enabled']?'Active':'Inactive'; ?></td>
        <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
        <td><code style="font-size:11px; word-break:break-all;"><?php echo htmlspecialchars($c['url']); ?></code></td>
        <td><span style="background:var(--bg); padding:2px 8px; border-radius:4px; font-family:monospace;"><?php echo htmlspecialchars($c['schedule']); ?></span></td>
        <td><?php echo $c['last_run'] ? date('Y-m-d H:i', strtotime($c['last_run'])) : 'Never'; ?></td>
        <td><a href="?run_cron=<?php echo $c['id']; ?>&action=cron" class="btn" style="background:#28a745; color:white; padding:4px 8px; border-radius:4px; text-decoration:none; font-size:10px;"><i class="fas fa-play"></i></a>
        <a href="?toggle_cron=<?php echo $c['id']; ?>&action=cron" class="btn" style="background:#ffc107; color:#333; padding:4px 8px; border-radius:4px; text-decoration:none; font-size:10px;"><i class="fas <?php echo $c['enabled']?'fa-pause':'fa-play'; ?>"></i></a>
        <a href="?delete_cron=<?php echo $c['id']; ?>&action=cron" class="btn" style="background:#dc3545; color:white; padding:4px 8px; border-radius:4px; text-decoration:none; font-size:10px;" onclick="return confirm('Delete cron?')"><i class="fas fa-trash"></i></a></td></tr>
        <?php endwhile; else: ?><tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">No cron jobs.</td></tr><?php endif; ?>
    </tbody></table></div></div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- BACKUPS -->
    <!-- ================================================================ -->
    <?php if ($action == 'backups'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-archive"></i> Backup Manager</h3></div>
    <div style="background:var(--bg); padding:15px; border-radius:10px; margin-bottom:15px;">
        <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <input type="hidden" name="action" value="backups"><label style="font-weight:600; font-size:13px;">Database:</label>
            <select name="backup_db" style="width:auto; min-width:180px; margin:0;">
                <?php foreach ($databases as $db): if ($db != 'information_schema' && $db != 'mysql' && $db != 'performance_schema'): ?>
                <option value="<?php echo htmlspecialchars($db); ?>"><?php echo htmlspecialchars($db); ?></option>
                <?php endif; endforeach; ?>
            </select>
            <button type="submit" class="warning"><i class="fas fa-archive"></i> Backup</button>
        </form>
    </div>
    <h4 style="font-size:14px; margin-bottom:10px;">Your Backups</h4>
    <div class="table-responsive"><table><thead><tr><th>Name</th><th>File</th><th>Created</th><th>Actions</th></tr></thead><tbody>
        <?php if ($user_backups->num_rows>0): while ($b = $user_backups->fetch_assoc()): ?>
        <tr><td><?php echo htmlspecialchars($b['name']); ?></td><td><code style="font-size:11px;"><?php echo htmlspecialchars($b['file_path']); ?></code></td><td><?php echo $b['created_at']; ?></td>
        <td><a href="?download_backup=<?php echo urlencode($b['file_path']); ?>" class="btn" style="background:#28a745; color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:11px;"><i class="fas fa-download"></i></a>
        <a href="?delete_backup=<?php echo $b['id']; ?>&action=backups" class="btn" style="background:#dc3545; color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:11px;" onclick="return confirm('Delete backup?')"><i class="fas fa-trash"></i></a></td></tr>
        <?php endwhile; else: ?><tr><td colspan="4" style="text-align:center; padding:20px; color:#999;">No backups.</td></tr><?php endif; ?>
    </tbody></table></div></div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- ACTIVITY LOGS -->
    <!-- ================================================================ -->
    <?php if ($action == 'logs'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-history"></i> Activity Logs</h3></div>
    <div class="table-responsive"><table><thead><tr><th>Time</th><th>Action</th><th>Details</th><th>IP</th></tr></thead><tbody>
        <?php if ($logs->num_rows>0): while ($l = $logs->fetch_assoc()): ?>
        <tr><td style="white-space:nowrap; font-size:12px;"><?php echo $l['created_at']; ?></td><td><span class="badge badge-info"><?php echo htmlspecialchars($l['action']); ?></span></td><td><?php echo htmlspecialchars($l['details']); ?></td><td><?php echo htmlspecialchars($l['ip']); ?></td></tr>
        <?php endwhile; else: ?><tr><td colspan="4" style="text-align:center; padding:20px; color:#999;">No logs yet.</td></tr><?php endif; ?>
    </tbody></table></div></div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- API MANAGEMENT -->
    <!-- ================================================================ -->
    <?php if ($action == 'api'): ?>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-key"></i> API Management</h3></div>
        <div style="background:var(--bg); padding:15px; border-radius:10px; margin-bottom:15px;">
            <h4><i class="fas fa-plus-circle"></i> Create API Key</h4>
            <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px;">
                <input type="text" name="api_name" placeholder="Key Name" required>
                <select name="api_permissions">
                    <option value="read">Read Only</option>
                    <option value="read_write">Read & Write</option>
                    <option value="full">Full Access</option>
                </select>
                <button type="submit" name="create_api_key"><i class="fas fa-plus"></i> Create API Key</button>
            </form>
            <p style="font-size:12px; color:#999; margin-top:8px;"><i class="fas fa-info-circle"></i> API keys can be used to authenticate API requests.</p>
            
            <div style="margin-top:15px; padding:15px; background:#2D3436; border-radius:8px;">
                <h5 style="color:#ffd93d; font-size:13px;"><i class="fas fa-code"></i> API Documentation</h5>
                <div style="background:#1a1a2e; padding:10px; border-radius:5px; font-family:monospace; font-size:11px; color:#dfe6e9; word-break:break-all;">
                    <strong>Base URL:</strong> <?php echo $server_url . $_SERVER['PHP_SELF']; ?>?api=v1<br><br>
                    <strong>Authentication:</strong> X-API-Key header or key parameter<br><br>
                    <strong>Endpoints:</strong><br>
                    • GET /domains - List domains<br>
                    • POST /domains - Create domain<br>
                    • GET /databases - List databases<br>
                    • GET /cron - List cron jobs<br>
                    • POST /cron - Create cron job<br>
                    • POST /backup - Create backup<br>
                    • GET /server - Server info<br><br>
                    <strong>Example:</strong><br>
                    curl -H "X-API-Key: YOUR_API_KEY" <?php echo $server_url . $_SERVER['PHP_SELF']; ?>?api=v1&endpoint=domains
                </div>
            </div>
        </div>
        <h4 style="font-size:14px; margin-bottom:10px;">Your API Keys</h4>
        <div class="table-responsive"><table><thead><tr><th>Name</th><th>API Key</th><th>Permissions</th><th>Created</th><th>Actions</th></tr></thead><tbody>
            <?php if ($user_api_keys->num_rows>0): while ($k = $user_api_keys->fetch_assoc()): ?>
            <tr><td><strong><?php echo htmlspecialchars($k['name']); ?></strong></td>
            <td><code style="font-size:11px; background:var(--bg); padding:2px 8px; border-radius:4px;"><?php echo htmlspecialchars($k['api_key']); ?></code></td>
            <td><span class="badge badge-info"><?php echo ucfirst($k['permissions']); ?></span></td>
            <td><?php echo $k['created_at']; ?></td>
            <td><a href="?delete_api_key=<?php echo $k['id']; ?>&action=api" class="btn" style="background:#dc3545; color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:11px;" onclick="return confirm('Delete this API key?')"><i class="fas fa-trash"></i></a></td></tr>
            <?php endwhile; else: ?><tr><td colspan="5" style="text-align:center; padding:20px; color:#999;">No API keys created yet.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- FILE MANAGER (RESTRICTED TO USER ROOT) -->
    <!-- ================================================================ -->
    <?php if ($action == 'files'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-folder"></i> File Manager</h3><span style="font-size:11px; color:#999;"><?php echo htmlspecialchars(str_replace($user_root, '~', $currentPath)); ?></span></div>
    
    <?php if (!$is_admin): ?>
    <div style="background:#e8f5e9; padding:8px 15px; border-radius:8px; margin-bottom:12px; font-size:12px;">
        <i class="fas fa-info-circle"></i> You are in your personal root directory: <strong><?php echo htmlspecialchars($user_root); ?></strong>
    </div>
    <?php endif; ?>
    
    <!-- Domain Selector for Upload -->
    <div class="domain-selector"><label><i class="fas fa-globe"></i> Upload to:</label>
    <form method="POST" enctype="multipart/form-data" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; flex:1;">
        <select name="domain_folder" style="width:auto; min-width:180px; margin:0;">
            <option value="">Current Directory</option>
            <?php foreach ($domain_dropdown as $folder => $domain): ?>
            <option value="<?php echo htmlspecialchars($folder); ?>"><?php echo htmlspecialchars($domain); ?> (<?php echo htmlspecialchars($folder); ?>)</option>
            <?php endforeach; ?>
            <?php if ($is_admin): ?><option value="..">Root Directory</option><?php endif; ?>
        </select>
        <input type="file" name="uploaded_file" style="width:auto; margin:0;">
        <input type="hidden" name="upload_dir" value="<?php echo $currentPath; ?>">
        <button type="submit" class="success"><i class="fas fa-upload"></i> Upload</button>
    </form></div>

    <!-- Toolbar -->
    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
        <form method="GET" style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
            <input type="hidden" name="action" value="files">
            <input type="text" name="path" value="<?php echo htmlspecialchars($currentPath); ?>" placeholder="Path" style="width:200px; margin:0; padding:8px 10px; font-size:12px;">
            <button type="submit" style="padding:8px 14px; font-size:12px;"><i class="fas fa-arrow-right"></i> Go</button>
        </form>
        <form method="POST" style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
            <input type="text" name="foldername" placeholder="New Folder" style="width:100px; margin:0; padding:8px 10px; font-size:12px;">
            <button type="submit" name="create_folder" class="warning" style="padding:8px 12px; font-size:12px;"><i class="fas fa-folder-plus"></i></button>
        </form>
        <form method="POST" style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
            <input type="text" name="filename" placeholder="New File" style="width:100px; margin:0; padding:8px 10px; font-size:12px;">
            <input type="hidden" name="file_content" value="">
            <button type="submit" name="create_file" class="info" style="padding:8px 12px; font-size:12px;"><i class="fas fa-file-plus"></i></button>
        </form>
    </div>

    <!-- Drop Zone -->
    <div class="drop-zone" id="dropZone">
        <div class="icon"><i class="fas fa-cloud-upload-alt"></i></div>
        <div class="text"><strong>Drag & Drop</strong> files here</div>
        <div class="sub">or click to select files</div>
        <input type="file" id="fileInput" multiple style="display:none;" onchange="handleFileSelect(event)">
    </div>

    <!-- FTP Import -->
    <div style="background:var(--bg); padding:12px; border-radius:8px; margin-bottom:12px;">
        <h4 style="font-size:13px; margin-bottom:8px;"><i class="fas fa-cloud-upload-alt"></i> Import from FTP Server</h4>
        <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:8px;">
            <input type="text" name="ftp_host" placeholder="FTP Host" style="margin:0; padding:8px 10px; font-size:12px;">
            <input type="text" name="ftp_user" placeholder="Username" style="margin:0; padding:8px 10px; font-size:12px;">
            <input type="password" name="ftp_pass" placeholder="Password" style="margin:0; padding:8px 10px; font-size:12px;">
            <input type="text" name="ftp_path" placeholder="Remote Path (default: /)" style="margin:0; padding:8px 10px; font-size:12px;">
            <input type="hidden" name="ftp_target" value="<?php echo $currentPath; ?>">
            <button type="submit" name="ftp_import" class="info" style="padding:8px 14px; font-size:12px;"><i class="fas fa-cloud-download-alt"></i> Import</button>
        </form>
    </div>

    <!-- .htaccess Generator -->
    <div style="background:var(--bg); padding:12px; border-radius:8px; margin-bottom:12px;">
        <h4 style="font-size:13px; margin-bottom:8px;"><i class="fas fa-lock"></i> .htaccess Generator</h4>
        <form method="POST" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:8px;">
            <select name="auth_type" style="margin:0; padding:8px 10px; font-size:12px;">
                <option value="none">None (Empty)</option>
                <option value="password">Password Protect</option>
                <option value="ip">IP Restrict</option>
                <option value="redirect">Redirect</option>
            </select>
            <input type="text" name="ht_user" placeholder="Username (for password)" style="margin:0; padding:8px 10px; font-size:12px;">
            <input type="password" name="ht_pass" placeholder="Password" style="margin:0; padding:8px 10px; font-size:12px;">
            <input type="text" name="allowed_ip" placeholder="IP Address (for IP restrict)" style="margin:0; padding:8px 10px; font-size:12px;">
            <input type="url" name="redirect_url" placeholder="Redirect URL" style="margin:0; padding:8px 10px; font-size:12px;">
            <button type="submit" name="generate_htaccess" class="warning" style="padding:8px 14px; font-size:12px;"><i class="fas fa-cog"></i> Generate</button>
        </form>
    </div>

    <!-- Breadcrumb -->
    <div class="breadcrumb"><?php $parts = explode('/', trim(str_replace($user_root, '', $currentPath), '/')); $p = $user_root; echo '<a href="?action=files&path='.urlencode($p).'"><i class="fas fa-home"></i> Root</a>'; foreach ($parts as $part) { if (!empty($part)) { $p .= '/'.$part; echo ' / <a href="?action=files&path='.urlencode($p).'">'.htmlspecialchars($part).'</a>'; } } ?><span style="margin-left:auto; font-size:11px; color:#999;"><?php echo count($files); ?> items</span></div>

    <!-- File List -->
    <div><?php if ($currentPath != $user_root): ?><div class="file-item"><div class="name"><i class="fas fa-arrow-up"></i> <a href="?action=files&path=<?php echo urlencode(dirname($currentPath)); ?>"><strong>..</strong></a></div></div><?php endif; ?>
    <?php foreach ($files as $f): ?>
    <div class="file-item"><div class="name"><i class="fas <?php echo $f['icon']; ?>" style="color:<?php echo $f['is_dir']?'#ffc107':'var(--primary)'; ?>;"></i>
        <?php if ($f['is_dir']): ?><a href="?action=files&path=<?php echo urlencode($f['path']); ?>"><strong><?php echo htmlspecialchars($f['name']); ?></strong></a>
        <?php else: ?><span><?php echo htmlspecialchars($f['name']); ?></span><?php endif; ?></div>
    <div class="info"><span class="size-badge"><?php echo $f['size_formatted']; ?></span><span><?php echo $f['modified']; ?></span><span class="badge"><?php echo $f['permissions']; ?></span></div>
    <div class="actions">
        <?php if ($f['is_file']): ?>
            <?php if ($f['editable']): ?><a href="?action=files&edit_file=<?php echo urlencode($f['path']); ?>" class="edit-btn"><i class="fas fa-edit"></i></a><?php endif; ?>
            <a href="?download_file=<?php echo urlencode($f['path']); ?>" class="download-btn"><i class="fas fa-download"></i></a>
            <a href="?action=files&view_file=<?php echo urlencode($f['path']); ?>" class="view-btn"><i class="fas fa-eye"></i></a>
            <?php if (pathinfo($f['name'], PATHINFO_EXTENSION) == 'zip' && $is_admin): ?>
            <a href="?unzip=<?php echo urlencode($f['path']); ?>&action=files" class="unzip-btn" onclick="return confirm('Extract this zip file?')"><i class="fas fa-file-archive"></i> Unzip</a>
            <?php endif; ?>
            <a href="?delete_file=<?php echo urlencode($f['path']); ?>&action=files" class="delete-btn" onclick="return confirm('Delete this file?')"><i class="fas fa-trash"></i></a>
        <?php else: ?>
            <a href="?delete_folder=<?php echo urlencode($f['path']); ?>&action=files" class="delete-btn" onclick="return confirm('Delete this folder?')"><i class="fas fa-folder-minus"></i></a>
        <?php endif; ?>
    </div></div>
    <?php endforeach; if (count($files)==0): ?><p style="text-align:center; padding:30px; color:#999;">Empty directory</p><?php endif; ?></div></div>

    <!-- Edit File Modal -->
    <?php if (isset($_GET['edit_file'])): $edit_file = $_GET['edit_file']; $file_content = file_exists($edit_file) ? file_get_contents($edit_file) : ''; $ext = pathinfo($edit_file, PATHINFO_EXTENSION); $mode = 'htmlmixed'; if ($ext == 'php') $mode='php'; elseif ($ext == 'js') $mode='javascript'; elseif ($ext == 'css') $mode='css'; elseif ($ext == 'sql') $mode='sql'; elseif ($ext == 'html' || $ext == 'htm') $mode='htmlmixed'; elseif ($ext == 'json') $mode='javascript'; elseif ($ext == 'xml') $mode='xml'; ?>
    <div class="modal show" id="editorModal"><div class="modal-content" style="max-width:900px;"><div class="modal-header"><h3><i class="fas fa-edit"></i> Editing: <?php echo htmlspecialchars(basename($edit_file)); ?></h3><button class="modal-close" onclick="closeEditor()">&times;</button></div>
    <form method="POST" id="editorForm"><input type="hidden" name="file_path" value="<?php echo htmlspecialchars($edit_file); ?>"><textarea id="codeEditor" name="file_content"><?php echo htmlspecialchars($file_content); ?></textarea>
    <div style="margin-top:12px;"><button type="submit" name="save_file"><i class="fas fa-save"></i> Save</button><button type="button" class="danger" onclick="closeEditor()"><i class="fas fa-times"></i> Cancel</button></div></form></div></div>
    <script>function closeEditor(){document.getElementById('editorModal').classList.remove('show');window.location.href='?action=files&path=<?php echo urlencode(dirname($edit_file)); ?>';}var editor=CodeMirror.fromTextArea(document.getElementById('codeEditor'),{lineNumbers:true,mode:'<?php echo $mode; ?>',theme:'default',indentUnit:4,tabSize:4,matchBrackets:true,autoCloseTags:true,autoCloseBrackets:true,lineWrapping:true,extraKeys:{"Ctrl-S":function(cm){document.getElementById('editorForm').submit();}}});editor.setSize(null,400);</script>
    <?php endif; ?>

    <!-- View File Modal -->
    <?php if (isset($_GET['view_file'])): $view_file = $_GET['view_file']; $view_content = file_exists($view_file) ? file_get_contents($view_file) : ''; $ext = pathinfo($view_file, PATHINFO_EXTENSION); $is_image = in_array(strtolower($ext), array('jpg','jpeg','png','gif','svg','webp','bmp')); ?>
    <div class="modal show" id="viewModal"><div class="modal-content" style="max-width:900px;"><div class="modal-header"><h3><i class="fas fa-eye"></i> Viewing: <?php echo htmlspecialchars(basename($view_file)); ?></h3><button class="modal-close" onclick="document.getElementById('viewModal').classList.remove('show')">&times;</button></div>
    <?php if ($is_image): ?><div style="text-align:center; padding:15px;"><img src="<?php echo str_replace($root, '', $view_file); ?>" style="max-width:100%; max-height:450px; border-radius:8px;"></div>
    <?php else: ?><pre style="background:var(--bg); padding:15px; border-radius:8px; overflow:auto; max-height:450px; white-space:pre-wrap; font-size:12px;"><?php echo htmlspecialchars($view_content); ?></pre><?php endif; ?>
    <div style="margin-top:12px;"><a href="?download_file=<?php echo urlencode($view_file); ?>" class="btn success"><i class="fas fa-download"></i> Download</a>
    <?php if (in_array(strtolower($ext), array('php','html','htm','css','js','txt','sql','json','xml','yml','ini','htaccess','md'))): ?>
    <a href="?action=files&edit_file=<?php echo urlencode($view_file); ?>" class="btn info"><i class="fas fa-edit"></i> Edit</a><?php endif; ?></div></div></div>
    <?php endif; ?>

    <script>
    const dropZone = document.getElementById('dropZone');const fileInput = document.getElementById('fileInput');
    if (dropZone){dropZone.addEventListener('click',function(){fileInput.click();});dropZone.addEventListener('dragover',function(e){e.preventDefault();this.classList.add('dragover');});dropZone.addEventListener('dragleave',function(e){e.preventDefault();this.classList.remove('dragover');});dropZone.addEventListener('drop',function(e){e.preventDefault();this.classList.remove('dragover');const files=e.dataTransfer.files;if(files.length>0){const formData=new FormData();const uploadDir='<?php echo $currentPath; ?>';for(let i=0;i<files.length;i++){formData.append('uploaded_file[]',files[i]);}formData.append('upload_dir',uploadDir);const xhr=new XMLHttpRequest();xhr.open('POST',window.location.href,true);xhr.onload=function(){if(xhr.status===200)window.location.reload();else alert('Upload failed');};xhr.send(formData);}});}
    function handleFileSelect(event){const files=event.target.files;if(files.length>0){const formData=new FormData();const uploadDir='<?php echo $currentPath; ?>';for(let i=0;i<files.length;i++){formData.append('uploaded_file[]',files[i]);}formData.append('upload_dir',uploadDir);const xhr=new XMLHttpRequest();xhr.open('POST',window.location.href,true);xhr.onload=function(){if(xhr.status===200)window.location.reload();else alert('Upload failed');};xhr.send(formData);}}
    </script>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- SERVER (ADMIN) -->
    <!-- ================================================================ -->
    <?php if ($action == 'server' && $is_admin): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-server"></i> Server Management</h3></div>
    <div class="table-responsive"><table><?php foreach ($server_info as $k => $v): ?><tr><td><strong><?php echo htmlspecialchars(ucwords(str_replace('_',' ',$k))); ?></strong></td><td><?php echo htmlspecialchars($v); ?></td></tr><?php endforeach; ?></table></div></div>
    <div class="card"><div class="card-header"><h3><i class="fas fa-database"></i> All Databases</h3></div>
    <div class="table-responsive"><table><thead><tr><th>Database</th></tr></thead><tbody><?php foreach ($databases as $db): ?><tr><td><?php echo htmlspecialchars($db); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- SETTINGS -->
    <!-- ================================================================ -->
    <?php if ($action == 'settings'): ?>
    <div class="card"><div class="card-header"><h3><i class="fas fa-cog"></i> Settings</h3></div>
    <h4 style="font-size:14px;"><i class="fas fa-palette"></i> Theme</h4>
    <form method="POST"><label>Primary Color:</label><input type="color" name="primary_color" value="<?php echo htmlspecialchars($primary); ?>" style="width:60px; padding:0;"><div style="margin:8px 0;"><label><input type="checkbox" name="dark_mode" <?php echo $dark?'checked':''; ?>> Dark Mode</label></div><button type="submit" name="save_theme"><i class="fas fa-save"></i> Save Theme</button></form>
    <hr style="margin:15px 0; border-color:var(--border);"><h4 style="font-size:14px;"><i class="fas fa-key"></i> Change Password</h4>
    <form method="POST" style="max-width:400px;"><input type="password" name="new_password" placeholder="New Password" required><button type="submit" name="change_password"><i class="fas fa-save"></i> Update Password</button></form>
    <?php if (isset($_POST['change_password']) && !empty($_POST['new_password'])): ?><?php $file = __FILE__; $content = file_get_contents($file); $new = $_POST['new_password']; $updated = preg_replace('/\$admin_password\s*=\s*"[^"]*"/', '$admin_password = "'.addslashes($new).'"', $content); if (file_put_contents($file, $updated)) echo '<div class="message success">Password updated!</div>'; ?><?php endif; ?>
    <hr style="margin:15px 0; border-color:var(--border);"><h4 style="font-size:14px;"><i class="fas fa-info-circle"></i> About</h4>
    <div style="background:var(--bg); padding:15px; border-radius:10px;"><p><strong>Magochi Host Admin</strong> v<?php echo htmlspecialchars($version); ?></p><p style="font-size:11px; color:#999; margin-top:8px;"><i class="fas fa-copyright"></i> 2026 Magochi Host - All Rights Reserved</p></div></div>
    <?php endif; ?>

    <div class="footer"><p><strong>Magochi Host Admin</strong> v<?php echo htmlspecialchars($version); ?> | All Rights Reserved</p></div>
</div>

<script>
document.querySelector('.menu-toggle')?.addEventListener('click', function(){document.getElementById('sidebar').classList.toggle('open');});
setTimeout(function(){document.querySelectorAll('.message').forEach(function(m){m.style.transition='opacity 0.5s';m.style.opacity='0';setTimeout(function(){m.style.display='none';},500);});},5000);
document.querySelectorAll('.modal').forEach(function(m){m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('show');});});
document.addEventListener('click',function(e){const sidebar=document.getElementById('sidebar');const toggle=document.querySelector('.menu-toggle');if(window.innerWidth<=768&&sidebar.classList.contains('open')){if(!sidebar.contains(e.target)&&!toggle?.contains(e.target)){sidebar.classList.remove('open');}}});
</script>
</body>
</html>