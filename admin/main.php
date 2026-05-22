<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header('Location: index.php');
    exit;
}
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['admin_login']);
    header('Location: index.php');
    exit;
}
// 对于直接访问，默认显示数据概览；对于AJAX请求，根据page参数返回相应内容
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$admin_nav_title = get_system_config('admin_nav_title');
$site_favicon = get_system_config('site_favicon'); // 读取系统全局ICO配置
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <!-- 加载网站全局ICO（favicon） -->
    <?php if (!empty($site_favicon)): ?>
        <link rel="icon" href="/<?php echo $site_favicon; ?>" type="image/x-icon">
        <link rel="shortcut icon" href="/<?php echo $site_favicon; ?>" type="image/x-icon">
    <?php endif; ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $admin_nav_title; ?></title>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-bg: #2c3e50;
            --sidebar-active: #667eea;
            --card-bg: #ffffff;
            --text-primary: #333333;
            --text-secondary: #666666;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.15);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --border-color: #e2e8f0;
            --tab-bg: #f8fafc;
            --tab-active: #667eea;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Inter", "Microsoft Yahei", sans-serif;
        }

        body {
            background-color: #f0f2f5;
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* 顶部导航 */
        .navbar {
            background: var(--primary-gradient);
            color: white;
            padding: 0 30px;
            height: 65px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-md);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-right: 15px;
        }

        .navbar-title {
            font-size: 20px;
            font-weight: 600;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* 布局容器 */
        .layout-container {
            display: flex;
            margin-top: 65px;
            min-height: calc(100vh - 65px);
        }

        /* 侧边栏 */
        .sidebar {
            width: 220px;
            background: var(--sidebar-bg);
            padding-top: 30px;
            position: fixed;
            height: calc(100vh - 65px);
            overflow-y: auto;
            box-shadow: var(--shadow-md);
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-item {
            margin-bottom: 2px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
            font-size: 15px;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: rgba(102, 126, 234, 0.2);
            color: white;
            border-left-color: var(--sidebar-active);
        }

        .sidebar-link span {
            flex: 1;
        }

        /* 主内容区 */
        .main-content {
            flex: 1;
            margin-left: 220px;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 65px);
            overflow-x: hidden;
        }

        /* 标签页导航 */
        .tab-container {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 0 30px;
            box-shadow: var(--shadow-md);
            z-index: 100;
            position: relative;
        }

        .tab-nav {
            display: flex;
            gap: 2px;
            list-style: none;
        }

        .tab-item {
            position: relative;
        }

        .tab-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 15px 20px;
            text-decoration: none;
            color: var(--text-secondary);
            background: var(--tab-bg);
            border-bottom: 3px solid transparent;
            transition: var(--transition);
            font-size: 14px;
            font-weight: 500;
        }

        .tab-link:hover {
            color: var(--text-primary);
            background: #f1f5f9;
        }

        .tab-link.active {
            color: var(--tab-active);
            background: var(--card-bg);
            border-bottom-color: var(--tab-active);
        }

        .tab-close {
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            opacity: 0.6;
            transition: var(--transition);
        }

        .tab-close:hover {
            opacity: 1;
            color: var(--danger-color);
        }

        /* 标签内容区 */
        .tab-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            position: relative;
            z-index: 10;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 30px;
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 22px;
            color: var(--text-primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: var(--primary-gradient);
            border-radius: 4px;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* 表格样式 */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            background-color: #f8fafc;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 14px;
        }

        .data-table tr:hover {
            background-color: #f8fafc;
        }

        .oper-btn {
            display: flex;
            gap: 8px;
        }

        .oper-btn a {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            text-decoration: none;
            transition: var(--transition);
        }

        .oper-edit {
            background: #3498db;
            color: white;
        }

        .oper-edit:hover {
            background: #2980b9;
        }

        .oper-del {
            background: var(--danger-color);
            color: white;
        }

        .oper-del:hover {
            background: #c0392b;
        }

        .oper-pass {
            background: var(--success-color);
            color: white;
        }

        .oper-pass:hover {
            background: #27ae60;
        }

        .oper-reject {
            background: var(--warning-color);
            color: white;
        }

        .oper-reject:hover {
            background: #e67e22;
        }

        /* 搜索框样式 */
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            max-width: 400px;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-btn {
            padding: 10px 20px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: var(--transition);
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* 筛选按钮组 */
        .filter-group {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: white;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 14px;
            transition: var(--transition);
        }

        .filter-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: var(--primary);
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* 分页样式 */
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .pagination-btn {
            padding: 6px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: white;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 14px;
            transition: var(--transition);
        }

        .pagination-btn:hover {
            background: #f8fafc;
            color: var(--text-primary);
        }

        .pagination-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: var(--primary);
        }

        .pagination-input {
            width: 60px;
            padding: 6px 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        }

        .pagination-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .pagination-go {
            padding: 6px 12px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: var(--transition);
        }

        .pagination-go:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* 表单样式 */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
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

        .upload-tip {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 5px;
        }

        /* 提示消息 */
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
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

        /* 数据概览样式 */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .stat-card {
            color: white;
            padding: 20px;
            border-radius: var(--radius);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-cate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-site {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-apply {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-notice {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 16px;
        }

        .recent-updates {
            margin-top: 30px;
        }

        .recent-updates h3 {
            margin-bottom: 20px;
            font-size: 18px;
            color: var(--text-primary);
        }

        .recent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .recent-card {
            margin-bottom: 0;
        }

        .recent-card h4 {
            margin-bottom: 15px;
            font-size: 16px;
            color: var(--text-primary);
        }

        /* 弹窗样式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border: 1px solid #888;
            width: 90%;
            max-width: 800px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            animation: modalFadeIn 0.3s ease;
        }

        .modal-header {
            padding: 20px;
            background: var(--primary-gradient);
            color: white;
            border-top-left-radius: var(--radius);
            border-top-right-radius: var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
        }

        .modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-close:hover,
        .modal-close:focus {
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
            max-height: 70vh;
            overflow-y: auto;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 响应式 */
        @media (max-width: 992px) {
            .sidebar {
                width: 60px;
            }

            .sidebar-link span {
                display: none;
            }

            .main-content {
                margin-left: 60px;
            }

            .card {
                padding: 20px;
            }

            .tab-container {
                padding: 0 15px;
            }

            .tab-content {
                padding: 20px;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0 15px;
            }

            .navbar-title {
                font-size: 16px;
            }

            .navbar-user {
                gap: 10px;
            }

            .logout-btn {
                padding: 6px 12px;
                font-size: 13px;
            }

            .sidebar {
                position: fixed;
                left: -220px;
                z-index: 1100;
                transition: var(--transition);
                width: 220px;
                height: calc(100vh - 65px);
                overflow-y: auto;
                background: var(--sidebar-bg);
            }

            .sidebar-link span {
                display: block !important;
                flex: 1;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .tab-link {
                padding: 12px 15px;
                font-size: 13px;
                white-space: nowrap;
            }

            .tab-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                scrollbar-color: var(--primary) var(--border-color);
            }

            .tab-container::-webkit-scrollbar {
                height: 6px;
            }

            .tab-container::-webkit-scrollbar-track {
                background: var(--border-color);
                border-radius: 3px;
            }

            .tab-container::-webkit-scrollbar-thumb {
                background: var(--primary);
                border-radius: 3px;
            }

            .tab-container::-webkit-scrollbar-thumb:hover {
                background: var(--primary-dark);
            }

            .tab-nav {
                min-width: max-content;
            }

            .tab-content {
                padding: 15px;
            }

            .data-table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .data-table th,
            .data-table td {
                padding: 8px 10px;
                font-size: 13px;
                white-space: nowrap;
            }

            .oper-btn {
                flex-direction: column;
                gap: 4px;
            }

            .oper-btn a {
                font-size: 11px;
                padding: 3px 8px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .recent-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .modal-content {
                width: 98%;
                margin: 15% auto;
            }

            .modal-body {
                max-height: 60vh;
            }

            /* 移动端菜单按钮 */
            .menu-toggle {
                display: block;
                background: none;
                border: none;
                color: white;
                font-size: 20px;
                cursor: pointer;
                margin-right: 15px;
            }
        }
    </style>
</head>

<body>
    <!-- 顶部导航 -->
    <nav class="navbar">
        <div style="display: flex; align-items: center;">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <div class="navbar-title"><?php echo $admin_nav_title; ?></div>
        </div>
        <div class="navbar-user">
            <span>管理员</span>
            <a href="javascript:void(0);" class="logout-btn" onclick="confirmLogout()">退出登录</a>
        </div>
    </nav>

    <!-- 布局容器 -->
    <div class="layout-container">
        <!-- 侧边栏 -->
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="javascript:void(0);" onclick="openTab('dashboard', '数据概览')" class="sidebar-link" data-page="dashboard">
                        <span>📊 数据概览</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="javascript:void(0);" onclick="openTab('cate', '分类管理')" class="sidebar-link" data-page="cate">
                        <span>📁 分类管理</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="javascript:void(0);" onclick="openTab('site', '站点管理')" class="sidebar-link" data-page="site">
                        <span>🔗 站点管理</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="javascript:void(0);" onclick="openTab('apply', '申请审核')" class="sidebar-link" data-page="apply">
                        <span>📝 申请审核</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="javascript:void(0);" onclick="openTab('notice', '公告管理')" class="sidebar-link" data-page="notice">
                        <span>📢 公告管理</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="javascript:void(0);" onclick="openTab('system', '系统设置')" class="sidebar-link" data-page="system">
                        <span>⚙️ 系统设置</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- 主内容区 -->
        <main class="main-content">
            <!-- 标签页导航 -->
            <div class="tab-container">
                <ul class="tab-nav" id="tabNav">
                    <li class="tab-item">
                        <a href="javascript:void(0);" class="tab-link active" data-page="dashboard">
                            📊 数据概览
                            <span class="tab-close" onclick="closeTab('dashboard')">×</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 标签内容区 -->
            <div class="tab-content" id="tabContent">
                <?php
                // 数据概览
                if ($current_page == 'dashboard'):
                    // 获取统计数据（使用COUNT(*)优化查询）
                    $cate_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM nav_cate"))['count'];
                    $site_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM nav_site"))['count'];
                    $apply_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM nav_apply WHERE status = 0"))['count'];
                    $notice_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM nav_notice"))['count'];
                ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">数据概览</h2>
                        </div>
                        <div class="stats-grid">
                            <!-- 分类统计 -->
                            <div class="stat-card stat-cate">
                                <div class="stat-number"><?php echo $cate_count; ?></div>
                                <div class="stat-label">分类数量</div>
                            </div>
                            <!-- 站点统计 -->
                            <div class="stat-card stat-site">
                                <div class="stat-number"><?php echo $site_count; ?></div>
                                <div class="stat-label">站点数量</div>
                            </div>
                            <!-- 待审核统计 -->
                            <div class="stat-card stat-apply">
                                <div class="stat-number"><?php echo $apply_count; ?></div>
                                <div class="stat-label">待审核</div>
                            </div>
                            <!-- 公告统计 -->
                            <div class="stat-card stat-notice">
                                <div class="stat-number"><?php echo $notice_count; ?></div>
                                <div class="stat-label">公告数量</div>
                            </div>
                        </div>
                        <div class="recent-updates">
                            <h3>最近更新</h3>
                            <div class="recent-grid">
                                <!-- 最近添加的站点 -->
                                <div class="card recent-card">
                                    <h4>最近添加的站点</h4>
                                    <table class="data-table">
                                        <tr>
                                            <th>站点名称</th>
                                            <th>添加时间</th>
                                        </tr>
                                        <?php
                                        // 限制查询结果数量，提高加载速度
                                        $recent_sites = mysqli_query($conn, "SELECT name, create_time FROM nav_site ORDER BY create_time DESC LIMIT 5");
                                        while ($site = mysqli_fetch_assoc($recent_sites)):
                                        ?>
                                            <tr>
                                                <td><?php echo $site['name']; ?></td>
                                                <td><?php echo $site['create_time']; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </table>
                                </div>
                                <!-- 最近的公告 -->
                                <div class="card recent-card">
                                    <h4>最近的公告</h4>
                                    <table class="data-table">
                                        <tr>
                                            <th>公告标题</th>
                                            <th>发布时间</th>
                                        </tr>
                                        <?php
                                        // 限制查询结果数量，提高加载速度
                                        $recent_notices = mysqli_query($conn, "SELECT title, create_time FROM nav_notice ORDER BY id DESC LIMIT 5");
                                        while ($notice = mysqli_fetch_assoc($recent_notices)):
                                        ?>
                                            <tr>
                                                <td><?php echo $notice['title']; ?></td>
                                                <td><?php echo $notice['create_time']; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                // 分类管理
                elseif ($current_page == 'cate'):
                    $cate_msg = '';
                    $cate_status = '';
                    if (isset($_GET['del']) && is_numeric($_GET['del'])) {
                        $del_id = intval($_GET['del']);
                        mysqli_query($conn, "DELETE FROM nav_site WHERE cate_id = $del_id");
                        mysqli_query($conn, "DELETE FROM nav_cate WHERE id = $del_id");
                        $cate_msg = '分类删除成功！';
                        $cate_status = 'success';
                    }

                    // 搜索和分页
                    $search = isset($_GET['search']) ? custom_filter_input($_GET['search']) : '';
                    $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
                    $limit = 10;
                    $offset = ($page - 1) * $limit;

                    // 构建查询
                    $where = $search ? "WHERE c.name LIKE '%$search%'" : '';
                    $cate_sql = "SELECT c.*, (SELECT COUNT(*) FROM nav_site WHERE cate_id = c.id) as site_count FROM nav_cate c $where ORDER BY c.sort ASC LIMIT $offset, $limit";
                    $count_sql = "SELECT COUNT(*) as total FROM nav_cate c $where";

                    $total_result = mysqli_query($conn, $count_sql);
                    $total = mysqli_fetch_assoc($total_result)['total'];
                    $total_pages = ceil($total / $limit);

                    $cate_result = mysqli_query($conn, $cate_sql);
                ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">分类管理</h2>
                            <a href="javascript:void(0);" onclick="openModal('cate_manage.php?action=add', '添加分类')" class="btn btn-primary">添加分类</a>
                        </div>
                        <?php if ($cate_msg): ?>
                            <div class="alert alert-<?php echo $cate_status; ?>"><?php echo $cate_msg; ?></div>
                        <?php endif; ?>

                        <!-- 搜索框 -->
                        <div class="search-container">
                            <input type="text" class="search-input" id="cateSearch" placeholder="搜索分类名称..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="search-btn" onclick="searchCate()">搜索</button>
                        </div>

                        <table class="data-table">
                            <tr>
                                <th>排序</th>
                                <th>ID</th>
                                <th>分类名称</th>
                                <th>站点数量</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            <?php
                            while ($cate = mysqli_fetch_assoc($cate_result)):
                            ?>
                                <tr>
                                    <td><?php echo $cate['sort']; ?></td>
                                    <td><?php echo $cate['id']; ?></td>
                                    <td><?php echo $cate['name']; ?></td>
                                    <td><?php echo $cate['site_count']; ?></td>
                                    <td><?php echo $cate['create_time']; ?></td>
                                    <td class="oper-btn">
                                        <a href="javascript:void(0);" onclick="openModal('cate_manage.php?action=edit&id=<?php echo $cate['id']; ?>', '编辑分类')" class="oper-edit">编辑</a>
                                        <a href="javascript:void(0);" onclick="openConfirmModal('确定删除？该分类下站点也会被删除！', function(){openModal('cate_manage.php?action=del&id=<?php echo $cate['id']; ?>', '删除分类', function(){loadTabContent('cate', '分类管理');});})" class="oper-del">删除</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>

                        <!-- 分页 -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <button class="pagination-btn" onclick="goToPage(1)" <?php echo $page == 1 ? 'disabled' : ''; ?>>首页</button>
                                <button class="pagination-btn" onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page == 1 ? 'disabled' : ''; ?>>上一页</button>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $start + 4);
                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                    <button class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>" onclick="goToPage(<?php echo $i; ?>)"><?php echo $i; ?></button>
                                <?php endfor; ?>

                                <button class="pagination-btn" onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>下一页</button>
                                <button class="pagination-btn" onclick="goToPage(<?php echo $total_pages; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>末页</button>

                                <span>跳转到：</span>
                                <input type="number" class="pagination-input" id="catePageInput" value="<?php echo $page; ?>" min="1" max="<?php echo $total_pages; ?>">
                                <button class="pagination-go" onclick="goToPageFromInput()">Go</button>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php
                // 站点管理
                elseif ($current_page == 'site'):
                    $site_msg = '';
                    $site_status = '';
                    if (isset($_GET['del']) && is_numeric($_GET['del'])) {
                        $del_id = intval($_GET['del']);
                        mysqli_query($conn, "DELETE FROM nav_site WHERE id = $del_id");
                        $site_msg = '站点删除成功！';
                        $site_status = 'success';
                    }

                    // 搜索和分页
                    $search = isset($_GET['search']) ? custom_filter_input($_GET['search']) : '';
                    $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
                    $limit = 10;
                    $offset = ($page - 1) * $limit;

                    // 构建查询
                    $where = $search ? "WHERE s.name LIKE '%$search%' OR s.url LIKE '%$search%' OR c.name LIKE '%$search%'" : '';
                    $site_sql = "SELECT s.*, c.name as cate_name FROM nav_site s LEFT JOIN nav_cate c ON s.cate_id = c.id $where ORDER BY s.sort ASC LIMIT $offset, $limit";
                    $count_sql = "SELECT COUNT(*) as total FROM nav_site s LEFT JOIN nav_cate c ON s.cate_id = c.id $where";

                    $total_result = mysqli_query($conn, $count_sql);
                    $total = mysqli_fetch_assoc($total_result)['total'];
                    $total_pages = ceil($total / $limit);

                    $site_result = mysqli_query($conn, $site_sql);
                ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">站点管理</h2>
                            <a href="javascript:void(0);" onclick="openModal('site_manage.php?action=add', '添加站点')" class="btn btn-primary">添加站点</a>
                        </div>
                        <?php if ($site_msg): ?>
                            <div class="alert alert-<?php echo $site_status; ?>"><?php echo $site_msg; ?></div>
                        <?php endif; ?>

                        <!-- 搜索框 -->
                        <div class="search-container">
                            <input type="text" class="search-input" id="siteSearch" placeholder="搜索站点名称、分类或网址..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="search-btn" onclick="searchSite()">搜索</button>
                        </div>

                        <table class="data-table">
                            <tr>
                                <th>ID</th>
                                <th>图标</th>
                                <th>站点名称</th>
                                <th>网站地址</th>
                                <th>排序值</th>
                                <th>所属分类</th>
                                <th>点击量</th>
                                <th>操作</th>
                            </tr>
                            <?php
                            while ($site = mysqli_fetch_assoc($site_result)):
                                // 仅使用默认图标
                                $icon_url = '/images/default-icon.png';
                            ?>
                                <tr>
                                    <td><?php echo $site['id']; ?></td>
                                    <td><img src="<?php echo $icon_url; ?>" width="32" height="32" alt="icon"></td>
                                    <td><?php echo $site['name']; ?></td>
                                    <td><a href="<?php echo $site['url']; ?>" target="_blank" style="color:#667eea; word-break: break-all;"><?php echo $site['url']; ?></a></td>
                                    <td><?php echo $site['sort']; ?></td>
                                    <td><?php echo $site['cate_name']; ?></td>
                                    <td><?php echo $site['click_num']; ?></td>
                                    <td class="oper-btn">
                                        <a href="javascript:void(0);" onclick="openModal('site_manage.php?action=edit&id=<?php echo $site['id']; ?>', '编辑站点')" class="oper-edit">编辑</a>
                                        <a href="javascript:void(0);" onclick="openConfirmModal('确定删除该站点？', function(){openModal('site_manage.php?action=del&id=<?php echo $site['id']; ?>', '删除站点');})" class="oper-del">删除</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>

                        <!-- 分页 -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <button class="pagination-btn" onclick="goToSitePage(1)" <?php echo $page == 1 ? 'disabled' : ''; ?>>首页</button>
                                <button class="pagination-btn" onclick="goToSitePage(<?php echo $page - 1; ?>)" <?php echo $page == 1 ? 'disabled' : ''; ?>>上一页</button>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $start + 4);
                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                    <button class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>" onclick="goToSitePage(<?php echo $i; ?>)"><?php echo $i; ?></button>
                                <?php endfor; ?>

                                <button class="pagination-btn" onclick="goToSitePage(<?php echo $page + 1; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>下一页</button>
                                <button class="pagination-btn" onclick="goToSitePage(<?php echo $total_pages; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>末页</button>

                                <span>跳转到：</span>
                                <input type="number" class="pagination-input" id="sitePageInput" value="<?php echo $page; ?>" min="1" max="<?php echo $total_pages; ?>">
                                <button class="pagination-go" onclick="goToSitePageFromInput()">Go</button>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php
                // 申请审核
                elseif ($current_page == 'apply'):
                    $apply_msg = '';
                    $apply_status = '';
                    // 审核通过
                    if (isset($_GET['pass']) && is_numeric($_GET['pass'])) {
                        $pass_id = intval($_GET['pass']);
                        $apply_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM nav_apply WHERE id = $pass_id LIMIT 1"));
                        if ($apply_info) {
                            $name = $apply_info['name'];
                            $url = $apply_info['url'];
                            $desc = $apply_info['desc'];
                            $cate_id = $apply_info['cate_id'];
                            $sort = 999; // 默认排序
                            mysqli_query($conn, "INSERT INTO nav_site (name, url, `desc`, cate_id, sort, click_num, create_time) VALUES ('$name', '$url', '$desc', $cate_id, $sort, 0, NOW())");
                            mysqli_query($conn, "UPDATE nav_apply SET status = 1 WHERE id = $pass_id");
                            $apply_msg = '审核通过，站点已上架！';
                            $apply_status = 'success';
                        }
                    }
                    // 拒绝审核
                    if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
                        $reject_id = intval($_GET['reject']);
                        mysqli_query($conn, "UPDATE nav_apply SET status = 2 WHERE id = $reject_id");
                        $apply_msg = '已拒绝该申请！';
                        $apply_status = 'danger';
                    }
                    // 删除申请
                    if (isset($_GET['del']) && is_numeric($_GET['del'])) {
                        $del_id = intval($_GET['del']);
                        mysqli_query($conn, "DELETE FROM nav_apply WHERE id = $del_id");
                        $apply_msg = '申请记录已删除！';
                        $apply_status = 'success';
                    }

                    // 搜索、筛选和分页
                    $search = isset($_GET['search']) ? custom_filter_input($_GET['search']) : '';
                    $status = isset($_GET['status']) ? intval($_GET['status']) : -1; // -1 表示全部
                    $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
                    $limit = 10;
                    $offset = ($page - 1) * $limit;

                    // 构建查询
                    $where = [];
                    if ($search) {
                        $where[] = "(a.name LIKE '%$search%' OR a.url LIKE '%$search%' OR a.contact LIKE '%$search%')";
                    }
                    if ($status != -1) {
                        $where[] = "a.status = $status";
                    }
                    $where_clause = $where ? "WHERE " . implode(' AND ', $where) : '';

                    $apply_sql = "SELECT a.*, c.name as cate_name FROM nav_apply a LEFT JOIN nav_cate c ON a.cate_id = c.id $where_clause ORDER BY a.create_time DESC LIMIT $offset, $limit";
                    $count_sql = "SELECT COUNT(*) as total FROM nav_apply a LEFT JOIN nav_cate c ON a.cate_id = c.id $where_clause";

                    $total_result = mysqli_query($conn, $count_sql);
                    $total = mysqli_fetch_assoc($total_result)['total'];
                    $total_pages = ceil($total / $limit);

                    $apply_result = mysqli_query($conn, $apply_sql);
                ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">网址申请审核</h2>
                        </div>
                        <?php if ($apply_msg): ?>
                            <div class="alert alert-<?php echo $apply_status; ?>"><?php echo $apply_msg; ?></div>
                        <?php endif; ?>

                        <!-- 搜索框 -->
                        <div class="search-container">
                            <input type="text" class="search-input" id="applySearch" placeholder="搜索站点名称、邮箱、链接或联系方式..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="search-btn" onclick="searchApply()">搜索</button>
                        </div>

                        <!-- 筛选按钮组 -->
                        <div class="filter-group">
                            <button class="filter-btn <?php echo $status == -1 ? 'active' : ''; ?>" onclick="filterApply(-1)">全部</button>
                            <button class="filter-btn <?php echo $status == 0 ? 'active' : ''; ?>" onclick="filterApply(0)">未审核</button>
                            <button class="filter-btn <?php echo $status == 1 ? 'active' : ''; ?>" onclick="filterApply(1)">已审核</button>
                        </div>

                        <table class="data-table">
                            <tr>
                                <th>ID</th>
                                <th>站点名称</th>
                                <th>站点链接</th>
                                <th>所属分类</th>
                                <th>联系方式</th>
                                <th>申请状态</th>
                                <th>申请时间</th>
                                <th>操作</th>
                            </tr>
                            <?php
                            while ($apply = mysqli_fetch_assoc($apply_result)):
                                $status_text = $apply['status'] == 0 ? '<span style="color:var(--warning-color)">待审核</span>' : ($apply['status'] == 1 ? '<span style="color:var(--success-color)">已通过</span>' : '<span style="color:var(--danger-color)">已拒绝</span>');
                            ?>
                                <tr>
                                    <td><?php echo $apply['id']; ?></td>
                                    <td><?php echo $apply['name']; ?></td>
                                    <td><a href="<?php echo $apply['url']; ?>" target="_blank" style="color:#667eea"><?php echo $apply['url']; ?></a></td>
                                    <td><?php echo $apply['cate_name']; ?></td>
                                    <td><?php echo $apply['contact']; ?></td>
                                    <td><?php echo $status_text; ?></td>
                                    <td><?php echo $apply['create_time']; ?></td>
                                    <td class="oper-btn">
                                        <?php if ($apply['status'] == 0): ?>
                                            <a href="javascript:void(0);" onclick="openConfirmModal('确定通过该申请？', function(){window.location.href='?page=apply&pass=<?php echo $apply['id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>';})" class="oper-pass">通过</a>
                                            <a href="javascript:void(0);" onclick="openConfirmModal('确定拒绝该申请？', function(){window.location.href='?page=apply&reject=<?php echo $apply['id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>';})" class="oper-reject">拒绝</a>
                                        <?php endif; ?>
                                        <a href="javascript:void(0);" onclick="openConfirmModal('确定删除该记录？', function(){window.location.href='?page=apply&del=<?php echo $apply['id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>';})" class="oper-del">删除</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>

                        <!-- 分页 -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <button class="pagination-btn" onclick="goToApplyPage(1)" <?php echo $page == 1 ? 'disabled' : ''; ?>>首页</button>
                                <button class="pagination-btn" onclick="goToApplyPage(<?php echo $page - 1; ?>)" <?php echo $page == 1 ? 'disabled' : ''; ?>>上一页</button>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $start + 4);
                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                    <button class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>" onclick="goToApplyPage(<?php echo $i; ?>)"><?php echo $i; ?></button>
                                <?php endfor; ?>

                                <button class="pagination-btn" onclick="goToApplyPage(<?php echo $page + 1; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>下一页</button>
                                <button class="pagination-btn" onclick="goToApplyPage(<?php echo $total_pages; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>末页</button>

                                <span>跳转到：</span>
                                <input type="number" class="pagination-input" id="applyPageInput" value="<?php echo $page; ?>" min="1" max="<?php echo $total_pages; ?>">
                                <button class="pagination-go" onclick="goToApplyPageFromInput()">Go</button>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php
                // 公告管理
                elseif ($current_page == 'notice'):
                    $notice_msg = '';
                    $notice_status = '';
                    if (isset($_GET['del']) && is_numeric($_GET['del'])) {
                        $del_id = intval($_GET['del']);
                        mysqli_query($conn, "DELETE FROM nav_notice WHERE id = $del_id");
                        $notice_msg = '公告删除成功！';
                        $notice_status = 'success';
                    }
                    if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
                        $toggle_id = intval($_GET['toggle']);
                        $status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_show FROM nav_notice WHERE id = $toggle_id"));
                        $new_status = $status['is_show'] ? 0 : 1;
                        mysqli_query($conn, "UPDATE nav_notice SET is_show = $new_status WHERE id = $toggle_id");
                        $notice_msg = $new_status ? '公告已显示！' : '公告已隐藏！';
                        $notice_status = 'success';
                    }

                    // 筛选和分页
                    $show_status = isset($_GET['show_status']) ? intval($_GET['show_status']) : -1; // -1 表示全部
                    $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
                    $limit = 10;
                    $offset = ($page - 1) * $limit;

                    // 构建查询
                    $where = $show_status != -1 ? "WHERE is_show = $show_status" : '';
                    $notice_sql = "SELECT * FROM nav_notice $where ORDER BY id DESC LIMIT $offset, $limit";
                    $count_sql = "SELECT COUNT(*) as total FROM nav_notice $where";

                    $total_result = mysqli_query($conn, $count_sql);
                    $total = mysqli_fetch_assoc($total_result)['total'];
                    $total_pages = ceil($total / $limit);

                    $notice_result = mysqli_query($conn, $notice_sql);
                ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">公告管理</h2>
                            <a href="javascript:void(0);" onclick="openModal('notice_manage.php?action=add', '添加公告')" class="btn btn-primary">添加公告</a>
                        </div>
                        <?php if ($notice_msg): ?>
                            <div class="alert alert-<?php echo $notice_status; ?>"><?php echo $notice_msg; ?></div>
                        <?php endif; ?>

                        <!-- 筛选按钮组 -->
                        <div class="filter-group">
                            <button class="filter-btn <?php echo $show_status == -1 ? 'active' : ''; ?>" onclick="filterNotice(-1)">全部</button>
                            <button class="filter-btn <?php echo $show_status == 1 ? 'active' : ''; ?>" onclick="filterNotice(1)">已显示</button>
                            <button class="filter-btn <?php echo $show_status == 0 ? 'active' : ''; ?>" onclick="filterNotice(0)">已隐藏</button>
                        </div>

                        <table class="data-table">
                            <tr>
                                <th>ID</th>
                                <th>公告标题</th>
                                <th>是否显示</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            <?php
                            while ($notice = mysqli_fetch_assoc($notice_result)):
                                $show_text = $notice['is_show'] ? '<span style="color:var(--success-color)">显示</span>' : '<span style="color:var(--danger-color)">隐藏</span>';
                            ?>
                                <tr>
                                    <td><?php echo $notice['id']; ?></td>
                                    <td><?php echo $notice['title']; ?></td>
                                    <td><?php echo $show_text; ?></td>
                                    <td><?php echo $notice['create_time']; ?></td>
                                    <td class="oper-btn">
                                        <a href="javascript:void(0);" onclick="openModal('notice_manage.php?action=edit&id=<?php echo $notice['id']; ?>', '编辑公告')" class="oper-edit">编辑</a>
                                        <a href="javascript:void(0);" onclick="toggleNoticeStatus(<?php echo $notice['id']; ?>, <?php echo $notice['is_show']; ?>, <?php echo $show_status; ?>)" class="oper-pass"><?php echo $notice['is_show'] ? '隐藏' : '显示'; ?></a>
                                        <a href="javascript:void(0);" onclick="openConfirmModal('确定删除该公告？', function(){openModal('notice_manage.php?action=del&id=<?php echo $notice['id']; ?>', '删除公告');})" class="oper-del">删除</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>

                        <!-- 分页 -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <button class="pagination-btn" onclick="goToNoticePage(1)" <?php echo $page == 1 ? 'disabled' : ''; ?>>首页</button>
                                <button class="pagination-btn" onclick="goToNoticePage(<?php echo $page - 1; ?>)" <?php echo $page == 1 ? 'disabled' : ''; ?>>上一页</button>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $start + 4);
                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                    <button class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>" onclick="goToNoticePage(<?php echo $i; ?>)"><?php echo $i; ?></button>
                                <?php endfor; ?>

                                <button class="pagination-btn" onclick="goToNoticePage(<?php echo $page + 1; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>下一页</button>
                                <button class="pagination-btn" onclick="goToNoticePage(<?php echo $total_pages; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>末页</button>

                                <span>跳转到：</span>
                                <input type="number" class="pagination-input" id="noticePageInput" value="<?php echo $page; ?>" min="1" max="<?php echo $total_pages; ?>">
                                <button class="pagination-go" onclick="goToNoticePageFromInput()">Go</button>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php
                // 系统设置（含网站ICO/Logo配置）
                elseif ($current_page == 'system'):
                    $system_msg = '';
                    $system_status = '';

                    // 处理ICO/logo上传
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        // 1. 处理网站ICO上传
                        $site_favicon = get_system_config('site_favicon');
                        if ($_FILES['site_favicon']['name'] != '' && $_FILES['site_favicon']['error'] == 0) {
                            $allowed_favicon = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/x-icon', 'image/vnd.microsoft.icon'];
                            if (!in_array($_FILES['site_favicon']['type'], $allowed_favicon)) {
                                $system_msg = 'ICO仅支持PNG/JPG/GIF/ICO格式！';
                                $system_status = 'danger';
                            } else {
                                // 创建上传目录
                                if (!file_exists('../uploads/system/')) {
                                    mkdir('../uploads/system/', 0755, true);
                                }
                                // 删除旧ICO
                                if (!empty($site_favicon) && file_exists('../' . $site_favicon)) {
                                    unlink('../' . $site_favicon);
                                }
                                // 保存新ICO
                                $ext = pathinfo($_FILES['site_favicon']['name'], PATHINFO_EXTENSION);
                                $ext = $ext ?: 'ico';
                                $favicon_name = 'favicon_' . uniqid() . '.' . $ext;
                                $favicon_path = 'uploads/system/' . $favicon_name;
                                if (move_uploaded_file($_FILES['site_favicon']['tmp_name'], '../' . $favicon_path)) {
                                    update_system_config('site_favicon', $favicon_path);
                                    $site_favicon = $favicon_path;
                                } else {
                                    $system_msg = 'ICO上传失败，请检查目录权限！';
                                    $system_status = 'danger';
                                }
                            }
                        }
                        // 清除ICO
                        if (isset($_POST['clear_favicon']) && $_POST['clear_favicon'] == '1') {
                            if (!empty($site_favicon) && file_exists('../' . $site_favicon)) {
                                unlink('../' . $site_favicon);
                            }
                            update_system_config('site_favicon', '');
                            $site_favicon = '';
                        }

                        // 2. 处理logo上传
                        $site_logo = get_system_config('site_logo');
                        if (empty($system_msg) && $_FILES['site_logo']['name'] != '' && $_FILES['site_logo']['error'] == 0) {
                            $allowed_logo = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];
                            if (!in_array($_FILES['site_logo']['type'], $allowed_logo)) {
                                $system_msg = 'Logo仅支持PNG/JPG/GIF格式！';
                                $system_status = 'danger';
                            } else {
                                // 删除旧logo
                                if (!empty($site_logo) && file_exists('../' . $site_logo)) {
                                    unlink('../' . $site_logo);
                                }
                                // 保存新logo
                                $ext = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
                                $logo_name = 'logo_' . uniqid() . '.' . $ext;
                                $logo_path = 'uploads/system/' . $logo_name;
                                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], '../' . $logo_path)) {
                                    update_system_config('site_logo', $logo_path);
                                    $site_logo = $logo_path;
                                } else {
                                    $system_msg = 'Logo上传失败，请检查目录权限！';
                                    $system_status = 'danger';
                                }
                            }
                        }
                        // 清除logo
                        if (isset($_POST['clear_logo']) && $_POST['clear_logo'] == '1') {
                            if (!empty($site_logo) && file_exists('../' . $site_logo)) {
                                unlink('../' . $site_logo);
                            }
                            update_system_config('site_logo', '');
                            $site_logo = '';
                        }

                        // 3. 保存其他系统设置
                        if (empty($system_msg)) {
                            $site_name = custom_filter_input($_POST['site_name']);
                            $site_desc = custom_filter_input($_POST['site_desc']);
                            $site_icp = custom_filter_input($_POST['site_icp']);
                            $footer_text = custom_filter_input($_POST['footer_text']);
                            $notice_delay = intval($_POST['notice_delay']);
                            $admin_nav_title = custom_filter_input($_POST['admin_nav_title']);
                            $site_domain = custom_filter_input($_POST['site_domain']);

                            update_system_config('site_name', $site_name);
                            update_system_config('site_desc', $site_desc);
                            update_system_config('site_icp', $site_icp);
                            update_system_config('footer_text', $footer_text);
                            update_system_config('notice_delay', $notice_delay);
                            update_system_config('admin_nav_title', $admin_nav_title);
                            update_system_config('site_domain', $site_domain);

                            $system_msg = '系统设置修改成功！';
                            $system_status = 'success';
                        }
                    }

                    // 读取当前配置
                    $site_name = get_system_config('site_name');
                    $site_desc = get_system_config('site_desc');
                    $site_icp = get_system_config('site_icp');
                    $footer_text = get_system_config('footer_text');
                    $notice_delay = get_system_config('notice_delay') ?: 5;
                    $admin_nav_title = get_system_config('admin_nav_title');
                    $site_domain = get_system_config('site_domain') ?: 'awenz.cn';
                    $site_favicon = get_system_config('site_favicon'); // 网站ICO
                    $site_logo = get_system_config('site_logo');     // 首页logo
                ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">系统设置</h2>
                        </div>
                        <?php if ($system_msg): ?>
                            <div class="alert alert-<?php echo $system_status; ?>"><?php echo $system_msg; ?></div>
                        <?php endif; ?>
                        <form method="post" enctype="multipart/form-data">
                            <!-- 网站ICO/Logo设置区域 -->
                            <div class="form-group" style="border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 25px;">
                                <label class="form-label" style="font-size: 18px; font-weight: 600; margin-bottom: 15px;">网站图标设置</label>

                                <!-- 网站全局ICO设置 -->
                                <div class="form-row" style="margin-bottom: 20px;">
                                    <div class="form-group" style="flex: 1;">
                                        <label class="form-label">网站全局ICO（favicon）</label>
                                        <input type="file" name="site_favicon" accept="image/png,image/jpg,image/jpeg,image/gif,image/x-icon,.ico" class="form-control">
                                        <div class="upload-tip">支持PNG/JPG/GIF/ICO格式，建议尺寸32x32px</div>
                                        <?php if (!empty($site_favicon)): ?>
                                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                                                <img src="/<?php echo $site_favicon; ?>" width="32" height="32" alt="当前ICO">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('确定清除ICO？')){document.getElementById('clear_favicon').value=1; handleSystemSettingsSubmit(this.form);}">清除ICO</button>
                                                <input type="hidden" id="clear_favicon" name="clear_favicon" value="0">
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- 首页Logo设置 -->
                                    <div class="form-group" style="flex: 1;">
                                        <label class="form-label">首页Logo图片</label>
                                        <input type="file" name="site_logo" accept="image/png,image/jpg,image/jpeg,image/gif" class="form-control">
                                        <div class="upload-tip">支持PNG/JPG/GIF格式，建议尺寸200x80px</div>
                                        <?php if (!empty($site_logo)): ?>
                                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                                                <img src="/<?php echo $site_logo; ?>" width="100" height="40" alt="当前Logo" style="object-fit: contain;">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('确定清除Logo？')){document.getElementById('clear_logo').value=1; handleSystemSettingsSubmit(this.form);}">清除Logo</button>
                                                <input type="hidden" id="clear_logo" name="clear_logo" value="0">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- 原有系统设置项 -->
                            <div class="form-group">
                                <label class="form-label">网站名称</label>
                                <input type="text" name="site_name" value="<?php echo $site_name; ?>" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">网站描述</label>
                                <input type="text" name="site_desc" value="<?php echo $site_desc; ?>" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">备案号</label>
                                <input type="text" name="site_icp" value="<?php echo $site_icp; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">底部文字</label>
                                <input type="text" name="footer_text" value="<?php echo $footer_text; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">公告自动关闭延迟(秒)</label>
                                <input type="number" name="notice_delay" value="<?php echo $notice_delay; ?>" class="form-control" min="1" max="30" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">后台导航栏文字</label>
                                <input type="text" name="admin_nav_title" value="<?php echo $admin_nav_title; ?>" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">站点域名</label>
                                <input type="text" name="site_domain" value="<?php echo $site_domain; ?>" class="form-control" required placeholder="例如：awenz.cn">
                            </div>
                            <button type="submit" class="btn btn-primary">保存设置</button>
                        </form>
                    </div>
                <?php endif; ?>
        </main>
    </div>
    <script>
        // 全局变量和函数
        window.tabs = {};
        window.activeTab = 'dashboard';
        window.modal = null;
        window.modalTitle = null;
        window.modalBody = null;
        window.modalClose = null;
        window.currentModalUrl = '';

        // 初始化标签页
        function initTabs() {
            // 初始化默认标签
            window.tabs['dashboard'] = {
                title: '📊 数据概览',
                content: document.querySelector('#tabContent').innerHTML
            };

            // 初始化侧边栏高亮状态
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
            });
            const sidebarLink = document.querySelector('.sidebar-link[data-page="dashboard"]');
            if (sidebarLink) {
                sidebarLink.classList.add('active');
            }

            // 为默认标签添加点击事件监听器
            const defaultTabLink = document.querySelector('.tab-link[data-page="dashboard"]');
            if (defaultTabLink) {
                defaultTabLink.addEventListener('click', function() {
                    activateTab('dashboard');
                });
            }

            // 设置实时搜索
            setupRealTimeSearch();
        }

        // 打开标签页
        function openTab(page, title) {
            // 关闭侧边栏
            const sidebar = document.querySelector('.sidebar');
            if (sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }

            // 如果标签已存在，直接激活
            if (window.tabs[page]) {
                activateTab(page);
                return;
            }

            // 创建新标签
            window.tabs[page] = {
                title: title,
                content: ''
            };

            // 创建标签元素
            const tabNav = document.getElementById('tabNav');
            const tabItem = document.createElement('li');
            tabItem.className = 'tab-item';
            tabItem.innerHTML = `
            <a href="javascript:void(0);" class="tab-link" data-page="${page}">
                ${title}
                <span class="tab-close" onclick="closeTab('${page}')">×</span>
            </a>
        `;
            tabNav.appendChild(tabItem);

            // 添加点击事件
            tabItem.querySelector('.tab-link').addEventListener('click', function() {
                activateTab(page);
            });

            // 激活新标签
            activateTab(page);

            // 加载标签内容
            loadTabContent(page, title);
        }

        // 加载标签内容
        function loadTabContent(page, title, params = {}) {
            // 显示加载中提示
            if (window.activeTab === page) {
                document.getElementById('tabContent').innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 400px;"><div style="text-align: center;"><div style="font-size: 48px; margin-bottom: 20px;">⏳</div><div style="font-size: 18px; color: var(--text-secondary);">数据正在加载中...</div></div></div>';
            }

            // 构建查询字符串
            const queryParams = new URLSearchParams({
                page
            });
            Object.entries(params).forEach(([key, value]) => {
                queryParams.set(key, value);
            });

            // 加载内容
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `?${queryParams.toString()}`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // 提取内容部分
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(xhr.responseText, 'text/html');
                    const content = doc.querySelector('.tab-content').innerHTML;
                    window.tabs[page].content = content;

                    // 如果当前是活动标签，更新内容
                    if (window.activeTab === page) {
                        document.getElementById('tabContent').innerHTML = content;

                        // 设置实时搜索
                        setupRealTimeSearch();

                        // 为搜索框添加回车键事件监听器
                        setTimeout(() => {
                            // 分类管理搜索框
                            const cateSearch = document.getElementById('cateSearch');
                            if (cateSearch) {
                                cateSearch.addEventListener('keypress', function(e) {
                                    if (e.key === 'Enter') {
                                        searchCate();
                                    }
                                });
                            }

                            // 站点管理搜索框
                            const siteSearch = document.getElementById('siteSearch');
                            if (siteSearch) {
                                siteSearch.addEventListener('keypress', function(e) {
                                    if (e.key === 'Enter') {
                                        searchSite();
                                    }
                                });
                            }

                            // 申请审核搜索框
                            const applySearch = document.getElementById('applySearch');
                            if (applySearch) {
                                applySearch.addEventListener('keypress', function(e) {
                                    if (e.key === 'Enter') {
                                        searchApply();
                                    }
                                });
                            }
                        }, 100);

                        // 如果是系统设置页面，为表单添加事件监听器
                        if (page === 'system') {
                            setTimeout(() => {
                                const systemForm = document.querySelector('form[enctype="multipart/form-data"]');
                                if (systemForm) {
                                    // 移除旧的事件监听器，避免重复绑定
                                    const newForm = systemForm.cloneNode(true);
                                    systemForm.parentNode.replaceChild(newForm, systemForm);

                                    // 添加新的事件监听器
                                    newForm.addEventListener('submit', function(e) {
                                        e.preventDefault();
                                        handleSystemSettingsSubmit(this);
                                    });
                                }
                            }, 100);
                        }
                    }
                } else {
                    // 加载失败提示
                    if (window.activeTab === page) {
                        document.getElementById('tabContent').innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 400px;"><div style="text-align: center;"><div style="font-size: 48px; margin-bottom: 20px;">❌</div><div style="font-size: 18px; color: var(--danger-color);">加载失败，请重试</div></div></div>';
                    }
                }
            };
            xhr.send();
        }

        // 激活标签页
        function activateTab(page) {
            // 更新活动标签
            window.activeTab = page;

            // 更新标签导航
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('active');
            });
            const activeTabLink = document.querySelector(`.tab-link[data-page="${page}"]`);
            activeTabLink.classList.add('active');

            // 自动滚动到当前标签
            const tabContainer = document.querySelector('.tab-container');
            const tabNav = document.querySelector('.tab-nav');
            const activeTabRect = activeTabLink.getBoundingClientRect();
            const containerRect = tabContainer.getBoundingClientRect();

            // 计算滚动位置
            const scrollLeft = tabNav.scrollLeft + (activeTabRect.left - containerRect.left) - (containerRect.width / 2) + (activeTabRect.width / 2);
            tabNav.scrollTo({
                left: scrollLeft,
                behavior: 'smooth'
            });

            // 更新侧边栏高亮状态
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
            });
            const sidebarLink = document.querySelector(`.sidebar-link[data-page="${page}"]`);
            if (sidebarLink) {
                sidebarLink.classList.add('active');
            }

            // 使用loadTabContent函数加载内容
            loadTabContent(page, window.tabs[page].title);
        }

        // 关闭标签页
        function closeTab(page) {
            // 移除标签
            delete window.tabs[page];

            // 移除标签元素
            const tabItem = document.querySelector(`.tab-link[data-page="${page}"]`).parentElement;
            tabItem.remove();

            // 如果没有标签页了，打开数据概览标签
            if (Object.keys(window.tabs).length === 0) {
                openTab('dashboard', '📊 数据概览');
            } else if (window.activeTab === page) {
                // 如果关闭的是活动标签，激活第一个标签
                const firstPage = Object.keys(window.tabs)[0];
                activateTab(firstPage);
            }
        }

        // 打开弹窗
        function openModal(url, title, callback) {
            window.currentModalUrl = url;
            window.modalTitle.textContent = title;
            window.modalBody.innerHTML = '<div style="text-align: center; padding: 50px;"><div>加载中...</div></div>';
            window.modal.style.display = 'block';

            // 加载弹窗内容
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // 提取内容部分
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(xhr.responseText, 'text/html');
                    const content = doc.body.innerHTML;
                    window.modalBody.innerHTML = content;

                    // 处理表单提交
                    const forms = window.modalBody.querySelectorAll('form');
                    forms.forEach(form => {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            submitForm(form, callback);
                        });
                    });

                    // 处理返回链接
                    const backLinks = window.modalBody.querySelectorAll('a[href*="main.php"]');
                    backLinks.forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            closeModal();
                            // 刷新当前标签页
                            if (window.tabs[window.activeTab] && window.tabs[window.activeTab].content) {
                                loadTabContent(window.activeTab, window.tabs[window.activeTab].title);
                            }
                        });
                    });

                    // 检查是否有成功消息
                    const successMsg = window.modalBody.querySelector('.success, .alert-success');
                    if (successMsg) {
                        // 2秒后自动关闭弹窗并刷新
                        setTimeout(() => {
                            closeModal();
                            // 调用回调函数
                            if (callback) {
                                callback();
                            }
                            // 刷新当前标签页
                            if (window.tabs[window.activeTab] && window.tabs[window.activeTab].content) {
                                loadTabContent(window.activeTab, window.tabs[window.activeTab].title);
                            }
                        }, 2000);
                    }
                } else {
                    window.modalBody.innerHTML = '<div style="text-align: center; padding: 50px; color: var(--danger-color);"><div>加载失败，请重试</div></div>';
                }
            };
            xhr.send();
        }

        // 关闭弹窗
        function closeModal() {
            window.modal.style.display = 'none';
        }

        // 提交表单
        function submitForm(form, callback) {
            const formData = new FormData(form);
            // 优先使用当前弹窗的 URL，确保表单提交到正确的地址
            const url = window.currentModalUrl || form.action || window.location.href;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // 提取内容部分
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(xhr.responseText, 'text/html');
                    const content = doc.body.innerHTML;
                    window.modalBody.innerHTML = content;

                    // 检查是否有成功消息
                    const successMsg = window.modalBody.querySelector('.success, .alert-success');
                    if (successMsg) {
                        // 2秒后自动关闭弹窗并刷新
                        setTimeout(() => {
                            closeModal();
                            // 调用回调函数
                            if (callback) {
                                callback();
                            }
                            // 刷新当前标签页
                            if (window.tabs[window.activeTab] && window.tabs[window.activeTab].content) {
                                loadTabContent(window.activeTab, window.tabs[window.activeTab].title);
                            }
                        }, 2000);
                    } else {
                        // 重新绑定表单提交事件
                        const forms = window.modalBody.querySelectorAll('form');
                        forms.forEach(form => {
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();
                                submitForm(form, callback);
                            });
                        });
                    }
                } else {
                    window.modalBody.innerHTML = '<div style="text-align: center; padding: 50px; color: var(--danger-color);"><div>提交失败，请重试</div></div>';
                }
            };
            xhr.send(formData);
        }

        // 全局搜索和分页函数
        function updateTableContent(page, params = {}) {
            // 找到当前页面的表格和分页元素
            const currentTable = document.querySelector('.data-table');
            const currentPagination = document.querySelector('.pagination');

            // 添加加载状态
            if (currentTable) {
                const loadingHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 200px;"><div style="text-align: center;"><div style="font-size: 32px; margin-bottom: 10px;">⏳</div><div style="font-size: 14px; color: var(--text-secondary);">加载中...</div></div></div>';
                currentTable.style.opacity = '0.5';
                const loadingContainer = document.createElement('div');
                loadingContainer.id = 'tableLoading';
                loadingContainer.style.position = 'absolute';
                loadingContainer.style.top = '0';
                loadingContainer.style.left = '0';
                loadingContainer.style.width = '100%';
                loadingContainer.style.height = '100%';
                loadingContainer.style.display = 'flex';
                loadingContainer.style.justifyContent = 'center';
                loadingContainer.style.alignItems = 'center';
                loadingContainer.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
                loadingContainer.style.zIndex = '10';
                loadingContainer.innerHTML = loadingHTML;

                // 确保表格容器有相对定位
                const tableContainer = currentTable.parentNode;
                if (tableContainer.style.position !== 'relative') {
                    tableContainer.style.position = 'relative';
                }

                // 添加加载指示器
                tableContainer.appendChild(loadingContainer);
            }

            // 构建查询字符串
            const queryParams = new URLSearchParams({
                page
            });
            Object.entries(params).forEach(([key, value]) => {
                queryParams.set(key, value);
            });

            // 加载内容
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `?${queryParams.toString()}`, true);
            xhr.onload = function() {
                // 移除加载状态
                const loadingContainer = document.getElementById('tableLoading');
                if (loadingContainer) {
                    loadingContainer.remove();
                }
                if (currentTable) {
                    currentTable.style.opacity = '1';
                }

                if (xhr.status === 200) {
                    // 提取内容部分
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(xhr.responseText, 'text/html');
                    const content = doc.querySelector('.tab-content').innerHTML;

                    // 创建临时元素来提取表格和分页部分
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = content;

                    // 找到表格和分页元素
                    const tableElement = tempDiv.querySelector('.data-table');
                    const paginationElement = tempDiv.querySelector('.pagination');

                    // 更新表格内容
                    if (tableElement && currentTable) {
                        // 保存表格的位置信息
                        const tableRect = currentTable.getBoundingClientRect();
                        // 替换表格内容
                        currentTable.innerHTML = tableElement.innerHTML;
                        // 保持表格宽度一致
                        currentTable.style.width = tableElement.style.width || '100%';
                    }

                    // 更新分页内容
                    if (paginationElement && currentPagination) {
                        currentPagination.innerHTML = paginationElement.innerHTML;
                    }

                    // 重新设置实时搜索
                    setupRealTimeSearch();

                    // 为搜索框添加回车键事件监听器
                    setTimeout(() => {
                        // 分类管理搜索框
                        const cateSearch = document.getElementById('cateSearch');
                        if (cateSearch) {
                            cateSearch.addEventListener('keypress', function(e) {
                                if (e.key === 'Enter') {
                                    searchCate();
                                }
                            });
                        }

                        // 站点管理搜索框
                        const siteSearch = document.getElementById('siteSearch');
                        if (siteSearch) {
                            siteSearch.addEventListener('keypress', function(e) {
                                if (e.key === 'Enter') {
                                    searchSite();
                                }
                            });
                        }

                        // 申请审核搜索框
                        const applySearch = document.getElementById('applySearch');
                        if (applySearch) {
                            applySearch.addEventListener('keypress', function(e) {
                                if (e.key === 'Enter') {
                                    searchApply();
                                }
                            });
                        }
                    }, 100);
                } else {
                    console.error('加载表格内容失败');
                }
            };
            xhr.send();
        }

        function searchCate() {
            var search = document.getElementById('cateSearch').value;
            updateTableContent('cate', {
                search: search,
                page_num: 1
            });
        }

        function goToPage(page) {
            var search = document.getElementById('cateSearch').value;
            updateTableContent('cate', {
                search: search,
                page_num: page
            });
        }

        function goToPageFromInput() {
            var page = document.getElementById('catePageInput').value;
            goToPage(page);
        }

        function searchSite() {
            var search = document.getElementById('siteSearch').value;
            updateTableContent('site', {
                search: search,
                page_num: 1
            });
        }

        function goToSitePage(page) {
            var search = document.getElementById('siteSearch').value;
            updateTableContent('site', {
                search: search,
                page_num: page
            });
        }

        function goToSitePageFromInput() {
            var page = document.getElementById('sitePageInput').value;
            goToSitePage(page);
        }

        function searchApply() {
            var search = document.getElementById('applySearch').value;
            var status = new URLSearchParams(window.location.search).get('status') || -1;
            updateTableContent('apply', {
                search: search,
                status: status,
                page_num: 1
            });
        }

        function filterApply(status) {
            var search = document.getElementById('applySearch').value;
            updateTableContent('apply', {
                search: search,
                status: status,
                page_num: 1
            });

            // 更新筛选按钮的高亮状态
            const filterBtns = document.querySelectorAll('.filter-btn');
            filterBtns.forEach(btn => {
                btn.classList.remove('active');
            });
            // 找到对应的按钮并添加active类
            filterBtns.forEach(btn => {
                if (btn.onclick.toString().includes(`filterApply(${status})`)) {
                    btn.classList.add('active');
                }
            });
        }

        function goToApplyPage(page) {
            var search = document.getElementById('applySearch').value;
            var status = new URLSearchParams(window.location.search).get('status') || -1;
            updateTableContent('apply', {
                search: search,
                status: status,
                page_num: page
            });
        }

        function goToApplyPageFromInput() {
            var page = document.getElementById('applyPageInput').value;
            goToApplyPage(page);
        }

        function filterNotice(show_status) {
            updateTableContent('notice', {
                show_status: show_status,
                page_num: 1
            });
        }

        function goToNoticePage(page) {
            var show_status = new URLSearchParams(window.location.search).get('show_status') || -1;
            updateTableContent('notice', {
                show_status: show_status,
                page_num: page
            });
        }

        function goToNoticePageFromInput() {
            var page = document.getElementById('noticePageInput').value;
            goToNoticePage(page);
        }

        // 实时搜索功能
        function setupRealTimeSearch() {
            // 分类管理实时搜索
            const cateSearch = document.getElementById('cateSearch');
            if (cateSearch) {
                let cateSearchTimeout;
                let lastSearchValue = cateSearch.value;
                cateSearch.addEventListener('input', function() {
                    clearTimeout(cateSearchTimeout);
                    // 只有当输入内容变化且长度大于1时才触发搜索
                    if (this.value !== lastSearchValue && this.value.length > 1) {
                        cateSearchTimeout = setTimeout(() => {
                            searchCate();
                            lastSearchValue = this.value;
                        }, 500); // 增加延迟时间，避免频繁搜索
                    } else if (this.value.length === 0) {
                        // 当内容清空时也触发搜索
                        cateSearchTimeout = setTimeout(() => {
                            searchCate();
                            lastSearchValue = this.value;
                        }, 300);
                    }
                });
            }

            // 站点管理实时搜索
            const siteSearch = document.getElementById('siteSearch');
            if (siteSearch) {
                let siteSearchTimeout;
                let lastSearchValue = siteSearch.value;
                siteSearch.addEventListener('input', function() {
                    clearTimeout(siteSearchTimeout);
                    // 只有当输入内容变化且长度大于1时才触发搜索
                    if (this.value !== lastSearchValue && this.value.length > 1) {
                        siteSearchTimeout = setTimeout(() => {
                            searchSite();
                            lastSearchValue = this.value;
                        }, 500); // 增加延迟时间，避免频繁搜索
                    } else if (this.value.length === 0) {
                        // 当内容清空时也触发搜索
                        siteSearchTimeout = setTimeout(() => {
                            searchSite();
                            lastSearchValue = this.value;
                        }, 300);
                    }
                });
            }

            // 申请审核实时搜索
            const applySearch = document.getElementById('applySearch');
            if (applySearch) {
                let applySearchTimeout;
                let lastSearchValue = applySearch.value;
                applySearch.addEventListener('input', function() {
                    clearTimeout(applySearchTimeout);
                    // 只有当输入内容变化且长度大于1时才触发搜索
                    if (this.value !== lastSearchValue && this.value.length > 1) {
                        applySearchTimeout = setTimeout(() => {
                            searchApply();
                            lastSearchValue = this.value;
                        }, 500); // 增加延迟时间，避免频繁搜索
                    } else if (this.value.length === 0) {
                        // 当内容清空时也触发搜索
                        applySearchTimeout = setTimeout(() => {
                            searchApply();
                            lastSearchValue = this.value;
                        }, 300);
                    }
                });
            }
        }

        function toggleNoticeStatus(id, currentStatus) {
            const newStatus = currentStatus ? 0 : 1;
            const actionText = currentStatus ? '隐藏' : '显示';

            openConfirmModal('确定' + actionText + '该公告？', function() {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', `?page=notice&toggle=${id}`, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // 刷新当前标签页内容
                        if (window.activeTab === 'notice') {
                            loadTabContent('notice', '公告管理');
                        }
                    }
                };
                xhr.send();
            });
        }

        // 确认弹窗元素
        window.confirmModal = null;
        window.confirmMessage = null;
        window.confirmOk = null;
        window.confirmCancel = null;
        window.confirmCallback = null;

        // 打开确认弹窗
        function openConfirmModal(message, callback) {
            window.confirmMessage.textContent = message;
            window.confirmCallback = callback;
            window.confirmModal.style.display = 'block';
        }

        // 关闭确认弹窗
        function closeConfirmModal() {
            window.confirmModal.style.display = 'none';
            window.confirmCallback = null;
        }

        // 处理确认操作
        function handleConfirm() {
            if (window.confirmCallback) {
                window.confirmCallback();
                closeConfirmModal();
            }
        }



        // 处理系统设置表单提交
        function handleSystemSettingsSubmit(form) {
            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '?page=system', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // 提取内容部分
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(xhr.responseText, 'text/html');
                    const content = doc.querySelector('.tab-content').innerHTML;
                    document.getElementById('tabContent').innerHTML = content;
                }
            };
            xhr.send(formData);
        }

        // 确认退出登录
        function confirmLogout() {
            openConfirmModal('确定要退出登录吗？', function() {
                window.location.href = '?action=logout';
            });
        }

        // 切换侧边栏
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }

        // 初始化
        window.onload = function() {
            // 初始化弹窗元素
            window.modal = document.getElementById('modal');
            window.modalTitle = document.getElementById('modalTitle');
            window.modalBody = document.getElementById('modalBody');
            window.modalClose = document.getElementById('modalClose');

            // 初始化确认弹窗元素
            window.confirmModal = document.getElementById('confirmModal');
            window.confirmMessage = document.getElementById('confirmMessage');
            window.confirmOk = document.getElementById('confirmOk');
            window.confirmCancel = document.getElementById('confirmCancel');

            // 为关闭按钮添加事件监听器
            window.modalClose.addEventListener('click', closeModal);
            window.confirmOk.addEventListener('click', handleConfirm);

            // 点击弹窗外部关闭弹窗
            window.addEventListener('click', function(e) {
                if (e.target === window.modal) {
                    closeModal();
                } else if (e.target === window.confirmModal) {
                    closeConfirmModal();
                }
            });

            // 为系统设置表单添加提交事件监听器
            const systemForm = document.querySelector('form[enctype="multipart/form-data"]');
            if (systemForm) {
                systemForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    handleSystemSettingsSubmit(this);
                });
            }

            // 初始化标签页
            initTabs();
        };
    </script>

    <!-- 弹窗容器 -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">操作</h2>
                <span class="modal-close" id="modalClose">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- 弹窗内容将通过JavaScript动态加载 -->
            </div>
        </div>
    </div>

    <!-- 确认弹窗容器 -->
    <div id="confirmModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2 class="modal-title">确认操作</h2>
                <span class="modal-close" onclick="closeConfirmModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p id="confirmMessage" style="text-align: center; margin: 20px 0;"></p>
                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
                    <button id="confirmOk" class="btn" style="width: auto; padding: 8px 20px;">确认</button>
                    <button id="confirmCancel" class="btn" style="width: auto; padding: 8px 20px; background: #94a3b8;" onclick="closeConfirmModal()">取消</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>