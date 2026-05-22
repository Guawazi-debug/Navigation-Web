<?php
session_start();
require_once '../config.php';

// 未登录则跳转
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$notice = [];
$action = isset($_GET['action']) ? $_GET['action'] : 'add'; // add/edit/del
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

// 处理删除
if ($action == 'del' && $id > 0) {
    if (mysqli_query($conn, "DELETE FROM nav_notice WHERE id = $id")) {
        $success = '公告删除成功';
    } else {
        $error = '删除失败：' . mysqli_error($conn);
    }
    // 直接显示结果，不跳转
    if ($success) {
        echo '<div class="success">' . $success . '</div>';
    } else if ($error) {
        echo '<div class="error">' . $error . '</div>';
    }
    exit;
}

// 处理编辑：获取公告信息
if ($action == 'edit' && $id > 0) {
    $notice_result = mysqli_query($conn, "SELECT * FROM nav_notice WHERE id = $id");
    $notice = mysqli_fetch_assoc($notice_result);
    if (!$notice) {
        echo '<div class="error">公告不存在</div>';
        exit;
    }
}

// 处理添加/编辑提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = custom_filter_input($_POST['title']);
    $content = custom_filter_input($_POST['content']);
    $is_show = intval($_POST['is_show']);

    if (empty($title) || empty($content)) {
        $error = '公告标题和内容不能为空';
    } else {
        if ($action == 'add') {
            // 添加公告
            $sql = "INSERT INTO nav_notice (title, content, is_show) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $is_show);
            if (mysqli_stmt_execute($stmt)) {
                $success = '公告添加成功';
                $_POST = [];
            } else {
                $error = '添加失败：' . mysqli_error($conn);
            }
        } else if ($action == 'edit' && $id > 0) {
            // 编辑公告
            $sql = "UPDATE nav_notice SET title = ?, content = ?, is_show = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssii", $title, $content, $is_show, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = '公告编辑成功';
                $notice['title'] = $title;
                $notice['content'] = $content;
                $notice['is_show'] = $is_show;
            } else {
                $error = '编辑失败：' . mysqli_error($conn);
            }
        }
    }
}
?>
<style>
    :root {
        --primary: #3498db;
        --primary-dark: #2980b9;
        --secondary: #2c3e50;
        --light: #f8f9fa;
        --gray: #e9ecef;
        --danger: #e74c3c;
        --success: #2ecc71;
        --shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        --radius: 8px;
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Inter", "Microsoft Yahei", Arial, sans-serif;
        background: var(--light);
        color: var(--secondary);
        line-height: 1.6;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
    }

    .form-box {
        background: #fff;
        border-radius: var(--radius);
        padding: 30px;
        box-shadow: var(--shadow);
    }

    .form-box h2 {
        margin-bottom: 20px;
        color: var(--secondary);
        border-bottom: 1px solid var(--gray);
        padding-bottom: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid var(--gray);
        border-radius: var(--radius);
        font-size: 14px;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    textarea.form-control {
        min-height: 150px;
        resize: vertical;
    }

    .btn {
        padding: 10px 20px;
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: var(--radius);
        font-size: 16px;
        cursor: pointer;
    }

    .btn:hover {
        background: var(--primary-dark);
    }

    .error {
        color: var(--danger);
        margin-bottom: 20px;
        padding: 10px;
        background: #fef0f0;
        border-radius: var(--radius);
    }

    .success {
        color: white;
        margin-bottom: 20px;
        padding: 15px;
        background: var(--success);
        border-radius: var(--radius);
        font-weight: 500;
        text-align: center;
        box-shadow: 0 2px 8px rgba(46, 204, 113, 0.3);
    }

    .back-link {
        display: block;
        margin-top: 20px;
        color: var(--primary);
        text-decoration: none;
    }

    .back-link:hover {
        text-decoration: underline;
    }
</style>

<div class="container">
    <div class="form-box">
        <h2><?php echo $action == 'add' ? '添加新公告' : '编辑公告'; ?></h2>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success" style="color: white; margin-bottom: 20px; padding: 15px; background: #2ecc71; border-radius: 8px; font-weight: 500; text-align: center; box-shadow: 0 2px 8px rgba(46, 204, 113, 0.3);"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label class="form-label">公告标题</label>
                <input type="text" name="title" value="<?php echo $action == 'edit' ? $notice['title'] : (isset($_POST['title']) ? $_POST['title'] : ''); ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">公告内容</label>
                <textarea name="content" class="form-control" required><?php echo $action == 'edit' ? $notice['content'] : (isset($_POST['content']) ? $_POST['content'] : ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">是否显示</label>
                <select name="is_show" class="form-control">
                    <option value="1" <?php echo ($action == 'edit' && $notice['is_show']) || ($action == 'add' && !isset($_POST['is_show'])) ? 'selected' : ''; ?>>显示</option>
                    <option value="0" <?php echo ($action == 'edit' && !$notice['is_show']) ? 'selected' : ''; ?>>隐藏</option>
                </select>
            </div>

            <button type="submit" class="btn"><?php echo $action == 'add' ? '添加公告' : '保存修改'; ?></button>
        </form>
    </div>
</div>