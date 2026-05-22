<?php
session_start();
require_once '../config.php';

// 检查是否已登录，如果已登录则跳转到main.php
if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
    header('Location: main.php');
    exit;
}

// 读取后台标题配置和全局ICO配置
$admin_nav_title = get_system_config('admin_nav_title');
$site_favicon = get_system_config('site_favicon');

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username == $admin_user && $password == $admin_pass) {
        $_SESSION['admin_login'] = true;
        header('Location: main.php');
        exit;
    } else {
        $login_error = '账号或密码错误！';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <!-- 加载网站全局ICO -->
    <?php if (!empty($site_favicon)): ?>
        <link rel="icon" href="/<?php echo $site_favicon; ?>" type="image/x-icon">
        <link rel="shortcut icon" href="/<?php echo $site_favicon; ?>" type="image/x-icon">
    <?php endif; ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - <?php echo $admin_nav_title ?: '后台管理'; ?></title>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-bg: #ffffff;
            --text-primary: #333333;
            --text-secondary: #666666;
            --error-color: #e74c3c;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --radius: 16px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Inter", "Microsoft Yahei", sans-serif;
        }

        body {
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 40px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 450px;
            transition: var(--transition);
        }

        .login-card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .login-title {
            text-align: center;
            font-size: 28px;
            color: var(--text-primary);
            margin-bottom: 30px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
            outline: none;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .login-error {
            padding: 12px;
            background: rgba(231, 76, 60, 0.1);
            color: var(--error-color);
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        .copyright {
            text-align: center;
            margin-top: 20px;
            color: var(--text-secondary);
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h1 class="login-title"><?php echo $admin_nav_title ?: '后台管理系统'; ?></h1>
        <?php if ($login_error): ?>
            <div class="login-error"><?php echo $login_error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label class="form-label">管理员账号</label>
                <input type="text" name="username" class="form-control" placeholder="请输入账号" required>
            </div>
            <div class="form-group">
                <label class="form-label">管理员密码</label>
                <input type="password" name="password" class="form-control" placeholder="请输入密码" required>
            </div>
            <button type="submit" class="login-btn">登录管理后台</button>
        </form>
        <div class="copyright">
            © <?php echo date('Y'); ?> <?php echo $admin_nav_title ?: '后台管理系统'; ?>
        </div>
    </div>
</body>

</html>