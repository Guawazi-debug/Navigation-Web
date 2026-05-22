<?php
$db_host = 'localhost';
$db_user = 'your_username';
$db_pass = 'your_password';
$db_name = 'your_database';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) die("数据库连接失败: " . mysqli_connect_error());
mysqli_set_charset($conn, 'utf8');

// 图标上传路径配置
define('ICON_UPLOAD_PATH', 'uploads/icons/');
define('ICON_URL', '/uploads/icons/');

// 创建上传目录
if (!file_exists(ICON_UPLOAD_PATH)) {
    mkdir(ICON_UPLOAD_PATH, 0755, true);
}

// 获取系统配置（带默认值）
function get_system_config($key) {
    global $conn;
    $key = mysqli_real_escape_string($conn, $key);
    $sql = "SELECT config_value FROM nav_system WHERE config_key = '$key' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $defaults = [
        'site_name' => '瓜娃子导航 - 优质网址导航',
        'site_desc' => '汇聚全网实用站点，畅享高效上网体验',
        'site_icp' => '粤ICP备20260000号-1',
        'footer_text' => '© 2026 瓜娃子导航 保留所有权利',
        'notice_status' => '1',
        'notice_title' => '欢迎使用瓜娃子导航导航',
        'notice_content' => '这是默认公告内容，管理员可在后台修改',
        'notice_delay' => '5',
        'admin_nav_title' => '瓜娃子导航导航管理系统',
        'site_domain' => 'awenz.cn',
        'site_favicon' => 'images/default-icon.png'
    ];
    return $row ? $row['config_value'] : ($defaults[$key] ?? '');
}

// 更新系统配置
function update_system_config($key, $value) {
    global $conn;
    $key = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, $value);
    $check_sql = "SELECT id FROM nav_system WHERE config_key = '$key' LIMIT 1";
    $check_result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_result) > 0) {
        $sql = "UPDATE nav_system SET config_value = '$value' WHERE config_key = '$key'";
    } else {
        $sql = "INSERT INTO nav_system (config_key, config_value, config_desc) VALUES ('$key', '$value', '自动添加')";
    }
    return mysqli_query($conn, $sql);
}

// 上传图标函数
function upload_icon($file) {
    if ($file['error'] != 0) return ['status' => false, 'msg' => '上传失败：错误码'.$file['error']];
    $allowed_types = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) return ['status' => false, 'msg' => '仅支持PNG/JPG/GIF格式'];

    // 检查上传目录
    if (!file_exists(ICON_UPLOAD_PATH)) {
        mkdir(ICON_UPLOAD_PATH, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $target_path = ICON_UPLOAD_PATH . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['status' => true, 'filename' => $filename];
    } else {
        return ['status' => false, 'msg' => '服务器存储失败：请检查uploads/icons目录权限'];
    }
}

$admin_user = 'admin';
$admin_pass = '123456Aa?';

function custom_filter_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

function get_current_notice() {
    global $conn;
    $sql = "SELECT * FROM nav_notice WHERE is_show = 1 ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}
?>
