<?php
/*
 * 更新日志:
 * 2026-03-01: 优化页面布局
 *   - 删除导航栏下方的标题和简介区域
 * 2026-03-01: 修复和优化
 *   - 修复网站icon显示问题
 *   - 调整顶部导航栏搜索框和按钮大小及居中
 *   - 美化底部区域
 * 2026-03-01: 进一步优化
 *   - 恢复底部原来简单的内容,添加背景色
 *   - 底部文字和备案号改为横向排列
 * 2026-03-01: 添加网站统计和公告功能
 *   - 底部添加网站运行天数统计（从2026年1月12日开始）
 *   - 底部添加累计访问数量统计
 *   - 导航栏下方添加动态大小的公告列表区域
 * 2026-03-04: 公告区域样式调整
 *   - 移除公告区域白色背景
 *   - 改为一行一行的卡片样式
 *   - 只显示一行内容的大小
 *   - 调整间距和字体大小
 * 2026-03-04: 公告区域样式统一
 *   - 为公告区域添加白底卡片样式
 *   - 确保index.php和search.php样式一致
 *   - 调整公告区域与导航栏的间距
 *   - 为最新公告标题添加白底卡片样式
 * 2026-03-04: 公告添加发布日期
 *   - 在每条公告后面添加发布日期
 *   - 为日期添加样式，显示在标题右侧
 *   - 确保日期格式美观
 *   - 在index.php和search.php中都添加日期显示
 * 2026-03-04: 导航栏显示优化
 *   - 当上传了logo文件时，在logo旁边显示网站标题
 *   - 确保index.php和search.php导航栏显示一致
 * 2026-03-04: 公告功能优化
 *   - 点击公告卡片弹出弹窗显示公告详情
 *   - 弹窗显示公告标题、内容和发布时间
 *   - 支持点击外部关闭弹窗和ESC键关闭弹窗
 *   - 确保index.php和search.php公告功能一致
 */
require_once 'config.php';

$keyword  = '';
$sites    = [];
$site_name = get_system_config('site_name');
$site_desc = get_system_config('site_desc');
$site_favicon = get_system_config('site_favicon');
$site_logo   = get_system_config('site_logo');
$footer_text = get_system_config('footer_text');

// 计算网站运行天数
$start_date = new DateTime('2026-01-12');
$current_date = new DateTime();
$days_running = $start_date->diff($current_date)->days;

// 获取累计访问数量
$total_clicks_result = mysqli_query($conn, "SELECT SUM(click_num) as total_clicks FROM nav_site");
$total_clicks = mysqli_fetch_assoc($total_clicks_result)['total_clicks'] ?? 0;

// 获取所有公告
$notices_result = mysqli_query($conn, "SELECT * FROM nav_notice WHERE is_show = 1 ORDER BY id DESC LIMIT 5");
$notices = [];
while ($n = mysqli_fetch_assoc($notices_result)) {
    $notices[] = $n;
}

if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $keyword = custom_filter_input($_GET['keyword']);
    $sql = "SELECT s.*, c.name AS cate_name
            FROM nav_site s
            LEFT JOIN nav_cate c ON s.cate_id = c.id
            WHERE s.name LIKE '%$keyword%' OR s.desc LIKE '%$keyword%'
            ORDER BY s.sort ASC";
    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $row['icon_url'] = '/images/default-icon.png';
        $sites[] = $row;
    }
}
?>
<!doctype html>
<html lang="zh-CN">

<head>
    <?php if ($site_favicon): ?>
        <link rel="icon" href="/<?= $site_favicon ?>">
    <?php endif; ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $keyword ? $keyword . ' - 搜索结果' : '搜索' ?> - 瓜娃子导航 - <?= $site_name ?></title>
    <meta name="description" content="<?= $keyword ? $keyword . '的搜索结果 - ' : '搜索' ?> 瓜娃子导航提供全网优质网址收录，快速找到您需要的网站。">
    <meta name="keywords" content="瓜娃子导航,<?= $keyword ?>,网站搜索,网址导航,实用工具,搜索引擎,学习资源">
    <meta name="author" content="瓜娃子导航">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="https://awenz.cn/search.php<?= $keyword ? '?keyword=' . urlencode($keyword) : '' ?>">

    <!-- Open Graph 社交媒体标签 -->
    <meta property="og:title" content="<?= $keyword ? $keyword . ' - 搜索结果' : '搜索' ?> - 瓜娃子导航">
    <meta property="og:description" content="<?= $keyword ? $keyword . '的搜索结果 - ' : '搜索' ?> 瓜娃子导航提供全网优质网址收录。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://awenz.cn/search.php<?= $keyword ? '?keyword=' . urlencode($keyword) : '' ?>">
    <meta property="og:image" content="https://awenz.cn/images/background.png">
    <meta property="og:site_name" content="瓜娃子导航">
    <meta property="og:locale" content="zh_CN">

    <!-- Twitter Card 标签 -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $keyword ? $keyword . ' - 搜索结果' : '搜索' ?> - 瓜娃子导航">
    <meta name="twitter:description" content="<?= $keyword ? $keyword . '的搜索结果 - ' : '搜索' ?> 瓜娃子导航提供全网优质网址收录。">
    <meta name="twitter:image" content="https://awenz.cn/images/background.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8/normalize.css">
    <style>
        /* ========= 现代化主题变量 ========= */
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #764ba2;
            --accent: #f093fb;
            --bg: #f8fafc;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card: #ffffff;
            --text: #1a202c;
            --text-light: #718096;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.1), 0 10px 10px rgba(0, 0, 0, 0.04);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --trans: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ========= 全局背景 ========= */
        body {
            background: var(--bg);
            background-image:
                url('/images/background.png'),
                radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.08) 0%, transparent 50%);
            background-attachment: fixed;
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            min-height: 100vh;
            padding-top: 70px;
            display: flex;
            flex-direction: column;
        }

        /* 主内容区域 */
        .main-content {
            flex: 1;
        }

        /* ========= 飘落特效 ========= */
        .falling-leaves {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 999;
            overflow: hidden;
        }

        .leaf {
            position: absolute;
            top: -10%;
            z-index: 999;
            animation: fall linear infinite;
            opacity: 0.7;
        }

        @keyframes fall {
            to {
                transform: translateY(110vh) rotate(720deg);
                opacity: 0;
            }
        }

        /* ========= 点击特效 ========= */
        .click-effect {
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
            transform: scale(0);
            animation: clickEffect 0.6s ease-out forwards;
        }

        @keyframes clickEffect {
            0% {
                transform: scale(0);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        /* ========= 顶部导航栏 ========= */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-md);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .navbar-logo {
            font-size: 26px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #e0e7ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-decoration: none;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-logo img {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            object-fit: cover;
        }

        .navbar-center {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            justify-content: center;
        }

        .navbar-search {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--trans);
            height: 48px;
        }

        .navbar-search:focus-within {
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        .navbar-search input {
            border: 0;
            padding: 0 20px;
            font-size: 16px;
            background: transparent;
            outline: none;
            color: #fff;
            width: 400px;
            height: 100%;
        }

        .navbar-search input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .navbar-search button {
            border: 0;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            transition: var(--trans);
            padding: 0 28px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .navbar-search button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-btn {
            padding: 10px 24px;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--trans);
        }

        .navbar-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        /* 移动端菜单按钮 */
        .navbar-menu-btn {
            display: none;
            padding: 10px;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--radius-md);
            font-size: 18px;
            cursor: pointer;
            transition: var(--trans);
        }

        .navbar-menu-btn:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* 移动端下拉菜单 */
        .mobile-menu {
            position: fixed;
            top: 70px;
            right: 0;
            left: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-md);
            z-index: 999;
            display: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu-content {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .mobile-search {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 16px;
            height: 48px;
        }

        .mobile-search input {
            border: 0;
            padding: 0 20px;
            font-size: 16px;
            background: transparent;
            outline: none;
            color: #fff;
            flex: 1;
            height: 100%;
        }

        .mobile-search input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .mobile-search button {
            border: 0;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            transition: var(--trans);
            padding: 0 28px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-search button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .mobile-menu-btn {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--trans);
            margin-bottom: 12px;
        }

        .mobile-menu-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        /* ========= 基础样式 ========= */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;
        }

        body {
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        img {
            max-width: 100%;
            display: block;
        }



        /* ========= 主体布局 ========= */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 80px 24px 0;
        }

        /* ========= 搜索结果卡片 ========= */
        .result-card {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: 44px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 44px;
            border: 1px solid var(--border);
        }

        .result-title {
            font-size: 28px;
            margin-bottom: 32px;
            font-weight: 700;
            background: var(--bg-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .site-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .site-item {
            background: var(--bg);
            border-radius: var(--radius-md);
            padding: 24px;
            display: flex;
            align-items: center;
            transition: var(--trans);
            border: 2px solid transparent;
            cursor: pointer;
        }

        .site-item:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .site-item img {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-md);
            margin-right: 20px;
            object-fit: cover;
            box-shadow: var(--shadow-sm);
            transition: var(--trans);
        }

        .site-item:hover img {
            transform: scale(1.1);
        }

        .site-info {
            flex: 1;
        }

        .site-name {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 6px;
            color: var(--text);
            transition: var(--trans);
        }

        .site-item:hover .site-name {
            color: var(--primary);
        }

        .site-desc {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 8px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .site-pv {
            font-size: 13px;
            color: var(--text-light);
            opacity: 0.8;
        }

        .no-result {
            text-align: center;
            padding: 80px 20px;
            font-size: 18px;
            color: var(--text-light);
            font-weight: 500;
        }

        /* ========= 公告列表区域 ========= */
        .notice-list {
            margin: 20px 0 30px;
        }

        .notice-list-content {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
        }

        .notice-list-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 16px;
            background: var(--bg-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .notice-list-items {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notice-list-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            transition: var(--trans);
            display: flex;
            align-items: center;
        }

        .notice-list-item:last-child {
            border-bottom: none;
        }

        .notice-list-item:hover {
            color: var(--primary);
        }

        .notice-list-item-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            flex: 1;
        }

        .notice-list-item-date {
            font-size: 12px;
            color: var(--text-light);
            margin-left: 16px;
            white-space: nowrap;
        }

        .notice-list-item-content {
            display: none;
        }

        /* ========= 底部 ========= */
        footer {
            text-align: center;
            margin-top: 80px;
            padding: 40px 20px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
            border-top: 1px solid var(--border);
        }

        footer .stats {
            margin-bottom: 20px;
        }

        footer .stats span {
            display: inline-block;
            margin: 0 16px 8px 0;
            font-size: 14px;
            color: var(--primary);
            font-weight: 600;
        }

        footer p {
            display: inline-block;
            margin: 0 8px 12px 0;
            font-size: 15px;
            color: var(--text-light);
            font-weight: 500;
        }

        footer a {
            color: var(--primary);
            transition: var(--trans);
            font-weight: 600;
        }

        footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* ========= 响应式设计 ========= */
        @media(max-width:768px) {
            .container {
                padding: 0 16px;
            }

            .result-card {
                padding: 32px;
            }

            .result-title {
                font-size: 24px;
            }

            .site-list {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .site-item {
                padding: 20px;
            }

            .site-item img {
                width: 56px;
                height: 56px;
                margin-right: 16px;
            }

            /* 导航栏响应式 - 平板和手机 */
            .navbar-center {
                display: none;
            }

            .navbar-right .navbar-btn {
                display: none;
            }

            .navbar-menu-btn {
                display: block;
            }

            .mobile-menu {
                top: 70px;
            }
        }

        @media(max-width:480px) {
            .site-item {
                flex-direction: column;
                text-align: center;
                transition: var(--trans);
            }

            .site-item img {
                width: 64px;
                height: 64px;
                margin: 0 0 12px 0;
                transition: var(--trans);
            }

            .site-item:active {
                transform: scale(0.98);
            }

            /* 导航栏响应式 */
            .navbar {
                padding: 0 16px;
                height: 60px;
                transition: var(--trans);
            }

            .navbar-logo {
                font-size: 20px;
                gap: 8px;
                transition: var(--trans);
            }

            .navbar-logo img {
                width: 36px;
                height: 36px;
                transition: var(--trans);
            }

            .mobile-menu {
                top: 60px;
                animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .mobile-menu-content {
                padding: 16px;
            }

            .mobile-search {
                height: 44px;
                transition: var(--trans);
            }

            .mobile-search input {
                font-size: 14px;
                padding: 0 16px;
                transition: var(--trans);
            }

            .mobile-search button {
                padding: 0 20px;
                font-size: 16px;
                transition: var(--trans);
            }

            .mobile-menu-btn {
                padding: 10px;
                font-size: 13px;
                transition: var(--trans);
            }

            .mobile-menu-btn:active {
                transform: scale(0.95);
            }

            /* 申请收录弹窗响应式 */
            .apply-modal-in {
                padding: 24px;
                width: 95%;
                animation: slideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .apply-modal-title {
                font-size: 20px;
            }

            .apply-modal input,
            .apply-modal textarea,
            .apply-modal select {
                padding: 12px 16px;
                font-size: 14px;
                transition: var(--trans);
            }

            .apply-modal button {
                padding: 14px;
                font-size: 15px;
                transition: var(--trans);
            }

            .apply-modal button:active {
                transform: scale(0.95);
            }

            /* 公告区域响应式 */
            .notice-list-content {
                padding: 16px;
            }

            .notice-list-item {
                padding: 10px 0;
                transition: var(--trans);
            }

            .notice-list-item:active {
                transform: scale(0.98);
            }

            /* 搜索结果卡片响应式 */
            .result-card {
                padding: 24px;
                transition: var(--trans);
            }

            .result-title {
                font-size: 20px;
                transition: var(--trans);
            }
        }

        /* ========= 申请收录弹窗 ========= */
        .apply-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9998;
            animation: fadeIn 0.3s ease;
        }

        .apply-modal.active {
            display: flex;
        }

        .apply-modal-in {
            background: var(--card);
            border-radius: var(--radius-xl);
            padding: 40px;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-xl);
            animation: slideIn 0.3s ease;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .apply-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
            width: 100%;
            flex-wrap: nowrap;
        }

        .apply-modal-title {
            font-size: 24px;
            font-weight: 800;
            background: var(--bg-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin: 0;
            flex: 1;
        }

        .apply-modal-close {
            width: 24px !important;
            height: 24px !important;
            border: 0 !important;
            background: var(--card) !important;
            border-radius: 50% !important;
            font-size: 14px !important;
            cursor: pointer !important;
            color: var(--text-light) !important;
            transition: var(--trans) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
            flex-shrink: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .apply-modal-close:hover {
            background: var(--primary);
            color: #fff;
            transform: rotate(90deg);
        }

        .apply-modal input,
        .apply-modal textarea,
        .apply-modal select {
            width: 100%;
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            margin-bottom: 16px;
            font-size: 15px;
            background: var(--bg);
            color: var(--text);
            transition: var(--trans);
            outline: none;
        }

        .apply-modal input:focus,
        .apply-modal textarea:focus,
        .apply-modal select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .apply-modal input::placeholder,
        .apply-modal textarea::placeholder {
            color: var(--text-light);
        }

        .apply-modal textarea {
            min-height: 100px;
            resize: vertical;
        }

        .apply-modal button {
            width: 100%;
            border: 0;
            padding: 16px;
            border-radius: var(--radius-md);
            background: var(--bg-gradient);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--trans);
            box-shadow: var(--shadow-md);
            margin-top: 8px;
        }

        .apply-modal button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .apply-modal button:active {
            transform: translateY(0);
        }

        /* ========= 公告详情弹窗 ========= */
        .notice-detail-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .notice-detail-content {
            background: var(--card);
            border-radius: var(--radius-xl);
            padding: 40px;
            max-width: 600px;
            width: 90%;
            box-shadow: var(--shadow-xl);
            animation: slideIn 0.3s ease;
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }

        .notice-detail-close {
            position: absolute;
            top: 16px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-light);
            transition: var(--trans);
            line-height: 1;
        }

        .notice-detail-close:hover {
            color: var(--text);
            transform: rotate(90deg);
        }

        .notice-detail-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 16px;
            color: var(--text);
        }

        .notice-detail-date {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .notice-detail-body {
            font-size: 16px;
            color: var(--text);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .notice-detail-body p {
            margin: 0 0 16px;
        }

        .notice-detail-btn {
            width: 100%;
            border: 0;
            padding: 14px;
            border-radius: var(--radius-md);
            background: var(--bg-gradient);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--trans);
            box-shadow: var(--shadow-md);
        }

        .notice-detail-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
    </style>
</head>

<body>
    <!-- 页面加载指示器 -->
    <div id="pageLoader" style="position: fixed; top: 0; left: 0; width: 100%; height: 3px; background: #f1f1f1; z-index: 9999;">
        <div id="loaderProgress" style="height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); width: 0%; transition: width 0.3s ease;"></div>
    </div>

    <!-- 飘落特效容器 -->
    <div class="falling-leaves" id="fallingLeaves"></div>

    <!-- 顶部导航栏 -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="/" class="navbar-logo">
                <?= $site_logo ? '<img src="/' . $site_logo . '" alt="' . $site_name . '">' : '' ?>
                <?= $site_name ?>
            </a>
        </div>
        <div class="navbar-center">
            <form class="navbar-search" action="search.php" method="get">
                <input name="keyword" placeholder="搜索站点名称/描述…" required value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">🔍</button>
            </form>
        </div>
        <div class="navbar-right">
            <button class="navbar-btn" onclick="window.location.href='/'">🏠 返回首页</button>
            <button class="navbar-btn" onclick="openApplyModal()">📝 申请收录</button>
            <!-- 移动端菜单按钮 -->
            <button class="navbar-menu-btn" onclick="toggleMobileMenu()">☰</button>
        </div>
    </nav>

    <!-- 移动端下拉菜单 -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-content">
            <form class="mobile-search" action="search.php" method="get">
                <input name="keyword" placeholder="搜索站点名称/描述…" required value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">🔍</button>
            </form>
            <button class="mobile-menu-btn" onclick="window.location.href='/'">🏠 返回首页</button>
            <button class="mobile-menu-btn" onclick="openApplyModal()">📝 申请收录</button>
        </div>
    </div>

    <!-- 公告列表区域 -->
    <?php if (!empty($notices)): ?>
        <div class="notice-list">
            <div class="container">
                <div class="notice-list-content">
                    <h3 class="notice-list-title">📢 最新公告</h3>
                    <div class="notice-list-items">
                        <?php foreach ($notices as $n): ?>
                            <div class="notice-list-item" onclick="showNoticeDetail('<?= htmlspecialchars($n['title']) ?>', '<?= htmlspecialchars($n['content']) ?>', '<?= $n['create_time'] ?? date('Y-m-d') ?>')">
                                <div class="notice-list-item-title"><?= $n['title'] ?></div>
                                <div class="notice-list-item-date"><?= $n['create_time'] ?? date('Y-m-d') ?></div>
                                <div class="notice-list-item-content"><?= mb_substr(strip_tags($n['content']), 0, 100) . '...' ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 公告详情弹窗 -->
    <div class="notice-detail-modal" id="noticeDetailModal" style="display: none;">
        <div class="notice-detail-content">
            <span class="notice-detail-close" onclick="closeNoticeDetail()">&times;</span>
            <h4 class="notice-detail-title" id="noticeDetailTitle"></h4>
            <div class="notice-detail-date" id="noticeDetailDate"></div>
            <div class="notice-detail-body" id="noticeDetailBody"></div>
            <button class="notice-detail-btn" onclick="closeNoticeDetail()">关闭</button>
        </div>
    </div>

    <!-- 申请收录弹窗 -->
    <div class="apply-modal" id="applyModal">
        <div class="apply-modal-in">
            <div class="apply-modal-header">
                <h3 class="apply-modal-title">申请收录</h3>
                <button class="apply-modal-close" onclick="closeApplyModal()">✕</button>
            </div>
            <form id="applyModalForm">
                <input name="name" placeholder="站点名称" required>
                <input name="url" type="text" placeholder="https://awenz.cn 或 awenz.cn" required>
                <textarea name="desc" placeholder="一句话描述您的网站" required></textarea>
                <input name="contact" placeholder="QQ / 微信 / 邮箱" required>
                <select name="cate_id" required>
                    <option value="">选择分类</option>
                    <?php
                    $cates = [];
                    $res = mysqli_query($conn, "SELECT * FROM nav_cate ORDER BY sort ASC");
                    while ($c = mysqli_fetch_assoc($res)) {
                        $cates[$c['id']] = $c;
                    }
                    foreach ($cates as $c):
                    ?>
                        <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">提交申请</button>
            </form>
        </div>
    </div>

    <!-- 主内容区域 -->
    <div class="main-content">
        <!-- 搜索结果 -->
        <div class="container">
            <div class="result-card">
                <h2 class="result-title">“<?= $keyword ?>”的搜索结果</h2>
                <?php if ($sites): ?>
                    <div class="site-list">
                        <?php foreach ($sites as $s): ?>
                            <a class="site-item" href="index.php?action=click&id=<?= $s['id'] ?>" target="_blank" title="<?= $s['name'] ?> - <?= $s['desc'] ?>">
                                <img src="<?= $s['icon_url'] ?>" alt="<?= $s['name'] ?> 图标">
                                <div class="site-info">
                                    <div class="site-name"><?= $s['name'] ?></div>
                                    <div class="site-desc"><?= $s['desc'] ?> (<?= $s['cate_name'] ?>)</div>
                                    <div class="site-url" style="font-size: 12px; color: var(--text-light); margin-bottom: 4px; word-break: break-all;"><?= parse_url($s['url'], PHP_URL_HOST) ?></div>
                                    <div class="site-pv">访问量 <?= $s['click_num'] ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-result">没有找到相关站点</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 底部 -->
    <footer>
        <div class="stats">
            <span>📅 网站已正常运行 <?= $days_running ?> 天</span>
            <span>👁️ 累计访问数量 <?= $total_clicks ?> 次</span>
        </div>
        <p><?= $footer_text ?></p>
        <?php if (get_system_config('site_icp')): ?>
            <p><a href="https://beian.miit.gov.cn/" target="_blank"><?= get_system_config('site_icp') ?></a></p>
        <?php endif; ?>
    </footer>

    <script>
        // 页面加载指示器
        window.addEventListener('load', function() {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.remove();
                }, 300);
            }
        });

        // 模拟加载进度
        let progress = 0;
        const progressBar = document.getElementById('loaderProgress');
        if (progressBar) {
            const interval = setInterval(() => {
                progress += Math.random() * 20;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                }
                progressBar.style.width = progress + '%';
            }, 200);
        }

        // 平滑滚动
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // 搜索结果卡片动画效果
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px 50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // 观察所有搜索结果卡片
        document.querySelectorAll('.site-item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(item);
        });

        /* 打开申请收录弹窗 */
        function openApplyModal() {
            document.getElementById('applyModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        /* 关闭申请收录弹窗 */
        function closeApplyModal() {
            document.getElementById('applyModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        /* 点击弹窗外部关闭 */
        document.getElementById('applyModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeApplyModal();
            }
        });

        /* ESC键关闭弹窗 */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeApplyModal();
            }
        });

        /* 弹窗表单提交 */
        applyModalForm.onsubmit = async e => {
            e.preventDefault();
            const f = new FormData(applyModalForm);
            f.append('apply_submit', 1);
            const r = await fetch('index.php', {
                method: 'POST',
                body: f
            }).then(res => res.text());
            const msgDiv = document.createElement('div');
            msgDiv.style.cssText = 'margin-bottom:20px;padding:16px;border-radius:12px;text-align:center;font-weight:600;';
            msgDiv.className = (r.includes('成功') ? 'success' : 'error');
            msgDiv.textContent = r.includes('成功') ? '申请已提交，管理员会尽快审核！' : '提交失败，请检查输入';
            applyModalForm.parentNode.insertBefore(msgDiv, applyModalForm);
            if (r.includes('成功')) {
                applyModalForm.reset();
                setTimeout(closeApplyModal, 2000);
            }
        };

        // 移动端菜单切换
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('active');
        }

        // 点击页面其他地方关闭菜单
        document.addEventListener('click', function(e) {
            const mobileMenu = document.getElementById('mobileMenu');
            const menuBtn = document.querySelector('.navbar-menu-btn');
            if (!mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) {
                mobileMenu.classList.remove('active');
            }
        });

        /* 显示公告详情弹窗 */
        function showNoticeDetail(title, content, date) {
            document.getElementById('noticeDetailTitle').textContent = title;
            document.getElementById('noticeDetailDate').textContent = date;
            document.getElementById('noticeDetailBody').innerHTML = content;
            document.getElementById('noticeDetailModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        /* 关闭公告详情弹窗 */
        function closeNoticeDetail() {
            document.getElementById('noticeDetailModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        /* 点击弹窗外部关闭公告详情 */
        document.getElementById('noticeDetailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNoticeDetail();
            }
        });

        /* ESC键关闭公告详情弹窗 */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeNoticeDetail();
            }
        });

        // 飘落特效（仅在非移动设备上启用）
        (function() {
            // 检测是否为移动设备
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            if (isMobile) return; // 移动设备上禁用飘落特效

            const container = document.getElementById('fallingLeaves');
            const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c'];
            const shapes = ['🍃', '🍂', '🌿', '🌸'];

            function createLeaf() {
                const leaf = document.createElement('div');
                leaf.className = 'leaf';
                leaf.textContent = shapes[Math.floor(Math.random() * shapes.length)];
                leaf.style.left = Math.random() * 100 + '%';
                leaf.style.fontSize = (Math.random() * 20 + 10) + 'px';
                leaf.style.color = colors[Math.floor(Math.random() * colors.length)];
                leaf.style.animationDuration = (Math.random() * 10 + 10) + 's';
                leaf.style.animationDelay = Math.random() * 5 + 's';
                leaf.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';

                container.appendChild(leaf);

                // 移除动画结束的叶子
                setTimeout(() => {
                    leaf.remove();
                }, 20000);
            }

            // 初始化创建叶子
            for (let i = 0; i < 8; i++) {
                createLeaf();
            }

            // 定时创建新叶子
            setInterval(createLeaf, 3000); // 增加间隔，减少性能消耗
        })();

        // 点击特效（仅在非移动设备上启用）
        (function() {
            // 检测是否为移动设备
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            if (isMobile) return; // 移动设备上禁用点击特效

            document.addEventListener('click', function(e) {
                const effect = document.createElement('div');
                effect.className = 'click-effect';
                effect.style.left = (e.clientX - 20) + 'px';
                effect.style.top = (e.clientY - 20) + 'px';
                document.body.appendChild(effect);

                // 移除特效元素
                setTimeout(() => {
                    effect.remove();
                }, 600);
            });
        })();

        // 优化移动端滚动性能
        (function() {
            // 检测是否为移动设备
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            if (!isMobile) return;

            // 添加 passive 事件监听器以提高滚动性能
            document.addEventListener('touchstart', function() {}, {
                passive: true
            });
            document.addEventListener('touchmove', function() {}, {
                passive: true
            });
            document.addEventListener('touchend', function() {}, {
                passive: true
            });
        })();

        // 图片懒加载
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    </script>

</body>

</html>
<?php mysqli_close($conn); ?>