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
$cate = [];
$action = isset($_GET['action']) ? $_GET['action'] : 'add'; // add/edit/del
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

// 处理删除
if ($action == 'del' && $id > 0) {
    // 先删除分类下的站点
    mysqli_query($conn, "DELETE FROM nav_site WHERE cate_id = $id");
    // 再删除分类
    if (mysqli_query($conn, "DELETE FROM nav_cate WHERE id = $id")) {
        $success = '分类删除成功';
    } else {
        $error = '删除失败：' . mysqli_error($conn);
    }
    // 直接显示结果，不跳转
    if ($success) {
        echo '<div class="alert alert-success">' . $success . '</div>';
    } else if ($error) {
        echo '<div class="alert alert-danger">' . $error . '</div>';
    }
    exit;
}

// 处理编辑：获取分类信息
if ($action == 'edit' && $id > 0) {
    $cate_result = mysqli_query($conn, "SELECT * FROM nav_cate WHERE id = $id");
    $cate = mysqli_fetch_assoc($cate_result);
    if (!$cate) {
        echo '<div class="error">分类不存在</div>';
        echo '<a href="main.php?page=cate" class="back-link">← 返回分类管理</a>';
        exit;
    }
}

// 处理添加/编辑提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = custom_filter_input($_POST['name']);
    $sort = intval($_POST['sort']);

    if (empty($name)) {
        $error = '分类名称不能为空';
    } else {
        if ($action == 'add') {
            // 添加分类
            $sql = "INSERT INTO nav_cate (name, sort) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $name, $sort);
            if (mysqli_stmt_execute($stmt)) {
                $success = '分类添加成功';
                $_POST = [];
            } else {
                $error = '添加失败：' . mysqli_error($conn);
            }
        } else if ($action == 'edit' && $id > 0) {
            // 编辑分类
            $sql = "UPDATE nav_cate SET name = ?, sort = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sii", $name, $sort, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = '分类编辑成功';
                $cate['name'] = $name;
                $cate['sort'] = $sort;
            } else {
                $error = '编辑失败：' . mysqli_error($conn);
            }
        }
    }
}
?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --card-bg: #ffffff;
        --text-primary: #333333;
        --text-secondary: #666666;
        --success-color: #2ecc71;
        --danger-color: #e74c3c;
        --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        --radius: 12px;
        --radius-sm: 8px;
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        --border-color: #e2e8f0;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Inter", "Microsoft Yahei", sans-serif;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
    }

    .form-box {
        background: var(--card-bg);
        border-radius: var(--radius);
        padding: 30px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
    }

    .form-box h2 {
        margin-bottom: 25px;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 15px;
        font-size: 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-box h2::before {
        content: '';
        width: 4px;
        height: 20px;
        background: var(--primary-gradient);
        border-radius: 4px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-secondary);
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        font-size: 14px;
        transition: var(--transition);
        background: #f8fafc;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
    }

    .btn {
        width: 100%;
        padding: 12px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-size: 16px;
        transition: var(--transition);
        margin-top: 10px;
        font-weight: 500;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .alert {
        padding: 12px 15px;
        border-radius: var(--radius-sm);
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: rgba(46, 204, 113, 0.1);
        color: var(--success-color);
        border-left: 4px solid var(--success-color);
    }

    .alert-danger {
        background: rgba(231, 76, 60, 0.1);
        color: var(--danger-color);
        border-left: 4px solid var(--danger-color);
    }

    /* 响应式适配 */
    @media (max-width: 768px) {
        .form-box {
            padding: 20px;
        }

        .form-box h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .form-control {
            padding: 10px 12px;
        }

        .btn {
            padding: 10px;
            font-size: 14px;
        }
    }
</style>

<div class="container">
    <div class="form-box">
        <h2><?php echo $action == 'add' ? '添加新分类' : '编辑分类'; ?></h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label class="form-label" for="name">分类名称</label>
                <input type="text" id="name" name="name" value="<?php echo $action == 'edit' ? $cate['name'] : (isset($_POST['name']) ? $_POST['name'] : ''); ?>" required placeholder="例如：常用网站" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label" for="sort">排序（数字越小越靠前）</label>
                <input type="number" id="sort" name="sort" value="<?php echo $action == 'edit' ? $cate['sort'] : (isset($_POST['sort']) ? $_POST['sort'] : 0); ?>" placeholder="默认0" class="form-control">
            </div>
            <div class="form-group">
                <button type="submit" class="btn"><?php echo $action == 'add' ? '添加分类' : '保存修改'; ?></button>
            </div>
        </form>
    </div>
</div>