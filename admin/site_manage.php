<?php
session_start();
require_once '../config.php';

// 未登录则跳转
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header('Location: index.php');
    exit;
}

$msg = '';
$status = '';
$site = [];
$action = isset($_GET['action']) ? $_GET['action'] : 'add';
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

// 处理删除
if ($action == 'del' && $id > 0) {
    mysqli_query($conn, "DELETE FROM nav_site WHERE id = $id");
    $msg = '站点删除成功！';
    $status = 'success';
    // 直接显示结果，不跳转
    echo '<div class="alert alert-success">' . $msg . '</div>';
    exit;
}

// 处理编辑：获取站点信息
if ($action == 'edit' && $id > 0) {
    $site = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM nav_site WHERE id = $id LIMIT 1"));
    if (!$site) {
        echo '<div class="alert alert-danger">站点不存在</div>';
        exit;
    }
}

// 处理添加/编辑提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = custom_filter_input($_POST['name']);
    $url = custom_filter_input($_POST['url']);
    $desc = custom_filter_input($_POST['desc']);
    $cate_id = intval($_POST['cate_id']);
    $sort = intval($_POST['sort']);

    // 表单验证
    if (empty($name)) {
        $msg = '站点名称不能为空！';
        $status = 'danger';
    } elseif (empty($url)) {
        $msg = '站点链接不能为空！';
        $status = 'danger';
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $msg = '站点链接格式不正确！';
        $status = 'danger';
    } elseif (empty($desc)) {
        $msg = '站点描述不能为空！';
        $status = 'danger';
    } elseif ($cate_id <= 0) {
        $msg = '请选择所属分类！';
        $status = 'danger';
    } else {
        if ($action == 'add') {
            // 添加站点（仅使用默认图标，无ICO上传逻辑）
            $sql = "INSERT INTO nav_site (name, url, `desc`, cate_id, sort, click_num, create_time) 
                    VALUES (?, ?, ?, ?, ?, 0, NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssii", $name, $url, $desc, $cate_id, $sort);
            if (mysqli_stmt_execute($stmt)) {
                $msg = '站点添加成功！';
                $status = 'success';
                $_POST = []; // 清空表单
            } else {
                $msg = '添加失败：' . mysqli_error($conn);
                $status = 'danger';
            }
        } else if ($action == 'edit' && $id > 0) {
            // 编辑站点（无ICO修改逻辑）
            $sql = "UPDATE nav_site SET name=?, url=?, `desc`=?, cate_id=?, sort=? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssiii", $name, $url, $desc, $cate_id, $sort, $id);
            if (mysqli_stmt_execute($stmt)) {
                $msg = '站点修改成功！';
                $status = 'success';
                $site['name'] = $name;
                $site['url'] = $url;
                $site['desc'] = $desc;
                $site['cate_id'] = $cate_id;
                $site['sort'] = $sort;
            } else {
                $msg = '修改失败：' . mysqli_error($conn);
                $status = 'danger';
            }
        }
    }
}

// 获取所有分类
$cate_result = mysqli_query($conn, "SELECT * FROM nav_cate ORDER BY sort ASC");
// 默认图标（仅前台显示用，后台无配置项）
$default_icon = '/images/default-icon.png';
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
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Inter", "Microsoft Yahei", sans-serif;
    }

    .card {
        max-width: 800px;
        margin: 0 auto;
        background: var(--card-bg);
        border-radius: var(--radius);
        padding: 30px;
        box-shadow: var(--shadow);
    }

    .card-title {
        font-size: 24px;
        color: var(--text-primary);
        margin-bottom: 25px;
        text-align: center;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: var(--transition);
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* 美化下拉列表样式 */
    select.form-control {
        appearance: none;
        background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%23666" viewBox="0 0 16 16"%3E%3Cpath d="M8 11l-4-4h8l-4 4z"/%3E%3C/svg%3E');
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
        cursor: pointer;
    }

    select.form-control:hover {
        border-color: #667eea;
    }

    .btn {
        width: 100%;
        padding: 12px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: var(--transition);
        margin-top: 10px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .alert {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        text-align: center;
        font-size: 14px;
    }

    .alert-success {
        background: rgba(46, 204, 113, 0.1);
        color: var(--success-color);
    }

    .alert-danger {
        background: rgba(231, 76, 60, 0.1);
        color: var(--danger-color);
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #667eea;
        text-decoration: none;
        font-size: 14px;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    /* 响应式适配 */
    @media (max-width: 768px) {
        .card {
            padding: 20px;
        }

        .card-title {
            font-size: 20px;
        }

        .form-control {
            padding: 10px 12px;
        }
    }
</style>

<div class="card">
    <h1 class="card-title"><?php echo $action == 'add' ? '添加站点' : '编辑站点'; ?></h1>

    <!-- 提示消息 -->
    <?php if ($msg): ?>
        <div class="alert alert-<?php echo $status; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- 站点表单 -->
    <form method="post">
        <div class="form-group">
            <label class="form-label">站点名称</label>
            <input type="text" name="name" class="form-control"
                value="<?php echo isset($site['name']) ? $site['name'] : (isset($_POST['name']) ? $_POST['name'] : ''); ?>"
                placeholder="请输入站点名称" required>
        </div>

        <div class="form-group">
            <label class="form-label">站点链接</label>
            <input type="url" name="url" class="form-control"
                value="<?php echo isset($site['url']) ? $site['url'] : (isset($_POST['url']) ? $_POST['url'] : ''); ?>"
                placeholder="https://xxx.com" required>
        </div>

        <div class="form-group">
            <label class="form-label">站点描述</label>
            <textarea name="desc" class="form-control" rows="3" placeholder="请简要描述站点功能" required><?php echo isset($site['desc']) ? $site['desc'] : (isset($_POST['desc']) ? $_POST['desc'] : ''); ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">所属分类</label>
            <select name="cate_id" class="form-control" required>
                <option value="">请选择分类</option>
                <?php while ($cate = mysqli_fetch_assoc($cate_result)): ?>
                    <option value="<?php echo $cate['id']; ?>"
                        <?php if ((isset($site['cate_id']) && $site['cate_id'] == $cate['id']) || (isset($_POST['cate_id']) && $_POST['cate_id'] == $cate['id'])) echo 'selected'; ?>>
                        <?php echo $cate['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">排序值</label>
            <input type="number" name="sort" class="form-control"
                value="<?php echo isset($site['sort']) ? $site['sort'] : (isset($_POST['sort']) ? $_POST['sort'] : 0); ?>"
                placeholder="数字越小排序越靠前，默认0" min="0">
        </div>

        <button type="submit" class="btn"><?php echo $action == 'add' ? '添加站点' : '保存修改'; ?></button>
    </form>
</div>
<?php
// 关闭数据库连接
mysqli_close($conn);
?>