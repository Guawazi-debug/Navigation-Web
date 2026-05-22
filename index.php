<?php
require_once 'config.php';

// 处理 AJAX 请求
if (isset($_GET['action']) && $_GET['action'] === 'get_category_sites') {
    $cate_id = isset($_GET['cate_id']) ? (int)$_GET['cate_id'] : 0;

    $sites = [];
    $cate_name = '全部站点';

    if ($cate_id === 0) {
        // 获取所有站点
        $res = mysqli_query($conn, "SELECT * FROM nav_site ORDER BY sort ASC");
    } else {
        // 获取指定分类的站点
        $res = mysqli_query($conn, "SELECT * FROM nav_site WHERE cate_id={$cate_id} ORDER BY sort ASC");
        // 获取分类名称
        $cate_res = mysqli_query($conn, "SELECT name FROM nav_cate WHERE id={$cate_id} LIMIT 1");
        if ($cate_row = mysqli_fetch_assoc($cate_res)) {
            $cate_name = $cate_row['name'];
        }
    }

    while ($s = mysqli_fetch_assoc($res)) {
        $s['icon_url'] = '/images/default-icon.png';
        $s['domain'] = parse_url($s['url'], PHP_URL_HOST);
        $sites[] = $s;
    }

    echo json_encode([
        'success' => true,
        'cate_name' => $cate_name,
        'sites' => $sites
    ]);
    exit;
}

/*
 * 更新日志:
 * 2026-03-01: 优化页面布局
 *   - 删除顶部导航栏的分类查询功能
 *   - 删除导航栏下方的标题和简介区域
 *   - 新增左侧分类侧边栏,显示所有分类
 *   - 实现分类筛选功能,点击分类显示对应站点
 *   - 默认显示全部站点
 * 2026-03-01: 修复和优化
 *   - 修复网站icon显示问题
 *   - 调整顶部导航栏搜索框和按钮大小及居中
 *   - 调整申请收录弹窗关闭按钮大小
 *   - 美化底部区域
 * 2026-03-01: 进一步优化
 *   - 恢复底部原来简单的内容,添加背景色
 *   - 修复点击侧边分类时弹出公告弹窗的问题(使用localStorage记录)
 *   - 优化申请收录弹窗布局(更紧凑合理)
 * 2026-03-01: 最终调整
 *   - 底部文字和备案号改为横向排列
 *   - 申请收录弹窗标题在左侧,关闭按钮在右侧并垂直居中
 *   - search.php底部样式与index.php保持一致
 * 2026-03-01: 弹窗样式修复
 *   - 申请收录弹窗关闭按钮修改为与标题同一行右侧,缩小为24px
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
 * 2026-03-04: 公告区域宽度调整
 *   - 修改index.php公告区域容器结构
 *   - 使用container容器替代wrap网格容器
 *   - 确保公告区域延伸到全部站点区域的宽度
 *   - 与search.php公告区域宽度保持一致
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


/*==================== 点击量跳转 ====================*/
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'click') {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE nav_site SET click_num=click_num+1 WHERE id=$id LIMIT 1");
    $url = mysqli_fetch_assoc(mysqli_query($conn, "SELECT url FROM nav_site WHERE id=$id LIMIT 1"))['url'];
    header("Location: $url");
    exit;
}

/*==================== 申请收录（PRG） ====================*/
$apply_msg = $apply_status = '';
if (!isset($_SESSION)) session_start();
if (isset($_SESSION['apply_msg'])) {
    $apply_msg   = $_SESSION['apply_msg'];
    $apply_status = $_SESSION['apply_status'];
    unset($_SESSION['apply_msg'], $_SESSION['apply_status']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_submit'])) {
    $name    = custom_filter_input($_POST['name']    ?? '');
    $url     = custom_filter_input($_POST['url']     ?? '');
    $desc    = custom_filter_input($_POST['desc']    ?? '');
    $contact = custom_filter_input($_POST['contact'] ?? '');
    $cate_id = (int)($_POST['cate_id'] ?? 0);

    $err = '';
    if (!$name) {
        $err = '站点名称不能为空';
    } elseif (!$url) {
        $err = '站点链接不能为空';
    } else {
        // 支持短域名，自动添加协议头
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'http://' . $url;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $err = '链接格式错误';
        }
    }

    if (!$err && !$desc) {
        $err = '站点描述不能为空';
    } elseif (!$err && !$contact) {
        $err = '联系方式不能为空';
    } elseif (!$err && $cate_id <= 0) {
        $err = '请选择分类';
    }

    if ($err) {
        $_SESSION['apply_msg'] = $err;
        $_SESSION['apply_status'] = 'error';
    } else {
        $sql = "INSERT INTO nav_apply (name,url,`desc`,contact,cate_id,create_time,status)
              VALUES ('$name','$url','$desc','$contact',$cate_id,NOW(),0)";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['apply_msg'] = '申请已提交，管理员会尽快审核';
            $_SESSION['apply_status'] = 'success';
        } else {
            $_SESSION['apply_msg'] = '提交失败：' . mysqli_error($conn);
            $_SESSION['apply_status'] = 'error';
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '#apply');
    exit;
}

/*==================== 读取分类 & 站点 ====================*/
$cates = [];
$all_sites = [];
$res = mysqli_query($conn, "SELECT * FROM nav_cate ORDER BY sort ASC");
while ($c = mysqli_fetch_assoc($res)) {
    $sites = [];
    $sres = mysqli_query($conn, "SELECT * FROM nav_site WHERE cate_id={$c['id']} ORDER BY sort ASC");
    while ($s = mysqli_fetch_assoc($sres)) {
        $s['icon_url'] = '/images/default-icon.png';
        $sites[] = $s;
        $all_sites[] = $s;
    }
    if ($sites) $cates[$c['id']] = array_merge($c, ['sites' => $sites]);
}

// 获取当前选中的分类ID
$current_cate_id = isset($_GET['cate_id']) ? (int)$_GET['cate_id'] : 0;

/*==================== 系统配置 ====================*/
$site_name   = get_system_config('site_name');
$site_desc   = get_system_config('site_desc');
$site_icp    = get_system_config('site_icp');
$footer_text = get_system_config('footer_text');
$notice      = get_current_notice();
$notice_delay = (int)(get_system_config('notice_delay') ?: 5);
$site_favicon = get_system_config('site_favicon');
$site_logo   = get_system_config('site_logo');

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
?>
<!doctype html>
<html lang="zh-CN">

<head>
    <?php if ($site_favicon): ?>
        <link rel="icon" href="/<?= $site_favicon ?>">
    <?php endif; ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>瓜娃子导航 - <?= $site_name ?> | 优质网址收录大全</title>
    <meta name="description" content="<?= $site_desc ?> - 瓜娃子导航提供全网优质网址收录，包括搜索引擎、工具软件、学习资源、娱乐休闲等各类实用网站，让您快速找到需要的网站。">
    <meta name="keywords" content="瓜娃子导航,网址导航,网站收录,实用工具,搜索引擎,学习资源,网址大全,网站目录">
    <meta name="author" content="瓜娃子导航">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="https://awenz.cn/">

    <!-- Open Graph 社交媒体标签 -->
    <meta property="og:title" content="<?= $site_name ?> - 瓜娃子导航">
    <meta property="og:description" content="<?= $site_desc ?> - 瓜娃子导航提供全网优质网址收录，包括搜索引擎、工具软件、学习资源、娱乐休闲等各类实用网站。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://awenz.cn/">
    <meta property="og:image" content="https://awenz.cn/images/background.png">
    <meta property="og:site_name" content="瓜娃子导航">
    <meta property="og:locale" content="zh_CN">

    <!-- Twitter Card 标签 -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $site_name ?> - 瓜娃子导航">
    <meta name="twitter:description" content="<?= $site_desc ?> - 瓜娃子导航提供全网优质网址收录，包括搜索引擎、工具软件、学习资源、娱乐休闲等各类实用网站。">
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
            padding-top: 80px;
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
        .wrap {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 32px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ========= 分类卡片 ========= */

        /* ========= 左侧侧边栏 ========= */
        .sidebar {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .sidebar-card {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
        }

        .sidebar-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 20px;
            background: var(--bg-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-item {
            padding: 14px 20px;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--trans);
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
            border: 2px solid transparent;
        }

        .sidebar-item:hover {
            background: var(--bg);
            color: var(--primary);
        }

        .sidebar-item.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            color: var(--primary);
            border-color: var(--primary);
        }

        .sidebar-item .count {
            float: right;
            font-size: 13px;
            opacity: 0.7;
            font-weight: 500;
        }

        /* ========= 分类卡片 ========= */
        .cate {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: 36px;
            box-shadow: var(--shadow-md);
            margin-bottom: 40px;
            border: 1px solid var(--border);
            transition: var(--trans);
        }

        .cate:hover {
            box-shadow: var(--shadow-lg);
        }

        .cate h3 {
            margin: 0 0 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
        }

        .cate h3 .icon {
            font-size: 28px;
            background: var(--bg-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* ========= 站点列表 ========= */
        .list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 24px;
        }

        .item {
            background: var(--bg);
            border-radius: var(--radius-md);
            padding: 24px 20px;
            text-align: center;
            transition: var(--trans);
            border: 2px solid transparent;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--bg-gradient);
            transform: scaleX(0);
            transition: var(--trans);
        }

        .item:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .item:hover::before {
            transform: scaleX(1);
        }

        .item img {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-md);
            margin: 0 auto 16px;
            object-fit: cover;
            box-shadow: var(--shadow-sm);
            transition: var(--trans);
        }

        .item:hover img {
            transform: scale(1.1);
        }

        .item .tit {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 6px;
            color: var(--text);
            transition: var(--trans);
        }

        .item:hover .tit {
            color: var(--primary);
        }

        .item .desc {
            font-size: 13px;
            color: var(--text-light);
            line-height: 1.4;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item .pv {
            font-size: 12px;
            color: var(--text-light);
            opacity: 0.8;
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

        footer p {
            display: inline-block;
            margin: 0 8px 12px 0;
            font-size: 15px;
            color: var(--text-light);
            font-weight: 500;
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

        footer a {
            color: var(--primary);
            transition: var(--trans);
            font-weight: 600;
        }

        footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* ========= Toast 提示 ========= */
        #toast {
            position: fixed;
            left: 50%;
            top: 30px;
            transform: translateX(-50%) translateY(-20px);
            background: var(--card);
            padding: 16px 28px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-xl);
            opacity: 0;
            transition: var(--trans);
            z-index: 9999;
            font-size: 15px;
            font-weight: 500;
            border: 2px solid var(--border);
        }

        #toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        #toast.error {
            color: #e53e3e;
            border-color: #fed7d7;
            background: #fff5f5;
        }

        #toast.success {
            color: #38a169;
            border-color: #c6f6d5;
            background: #f0fff4;
        }

        /* ========= 公告弹窗 ========= */
        .notice {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .notice-in {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: 40px;
            max-width: 520px;
            width: 90%;
            box-shadow: var(--shadow-xl);
            animation: slideIn 0.3s ease;
            position: relative;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .notice-in h4 {
            margin: 0 0 16px;
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
        }

        .notice-in p {
            margin: 0 0 24px;
            font-size: 16px;
            color: var(--text-light);
            line-height: 1.6;
        }

        .notice-in .close {
            position: absolute;
            top: 16px;
            right: 16px;
            font-size: 28px;
            cursor: pointer;
            color: var(--text-light);
            transition: var(--trans);
            line-height: 1;
        }

        .notice-in .close:hover {
            color: var(--text);
            transform: rotate(90deg);
        }

        .notice-in .btn {
            padding: 12px 32px;
            border: 0;
            border-radius: var(--radius-md);
            background: var(--bg-gradient);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--trans);
        }

        .notice-in .btn:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
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

        /* ========= 响应式设计 ========= */
        @media(max-width:1024px) {
            .wrap {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }
        }

        @media(max-width:768px) {
            .wrap {
                padding: 0 16px;
                gap: 24px;
            }

            .cate {
                padding: 24px;
            }

            .list {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 16px;
            }

            .item {
                padding: 20px 16px;
            }

            .sidebar-card {
                padding: 20px;
            }
        }

        @media(max-width:768px) {

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
            .list {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .item {
                display: flex;
                align-items: center;
                text-align: left;
                padding: 16px;
                transition: var(--trans);
            }

            .item img {
                width: 48px;
                height: 48px;
                margin: 0 16px 0 0;
                flex-shrink: 0;
                transition: var(--trans);
            }

            .item-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .item .desc {
                -webkit-line-clamp: 1;
            }

            .item .pv {
                font-size: 12px;
                color: var(--text-light);
                opacity: 0.8;
                margin-top: 4px;
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

            /* 分类侧边栏响应式 */
            .sidebar-card {
                padding: 20px;
            }

            .sidebar-item {
                padding: 12px 16px;
                transition: var(--trans);
            }

            .sidebar-item:active {
                transform: scale(0.98);
            }
        }
    </style>

    <!-- 结构化数据 Schema.org JSON-LD -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "<?= $site_name ?>",
            "alternateName": "瓜娃子导航",
            "url": "https://awenz.cn/",
            "description": "<?= $site_desc ?> - 瓜娃子导航提供全网优质网址收录，包括搜索引擎、工具软件、学习资源、娱乐休闲等各类实用网站。",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "https://awenz.cn/search.php?keyword={search_term_string}",
                "query-input": "required name=search_term_string"
            },
            "publisher": {
                "@type": "Organization",
                "name": "瓜娃子导航",
                "url": "https://awenz.cn/"
            }
        }
    </script>

    <!-- 面包屑导航结构化数据 -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [{
                "@type": "ListItem",
                "position": 1,
                "name": "首页",
                "item": "https://awenz.cn/"
            }]
        }
    </script>
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
                <input name="keyword" placeholder="搜索站点名称/描述…" required>
                <button type="submit">🔍</button>
            </form>
        </div>
        <div class="navbar-right">
            <button class="navbar-btn" onclick="openApplyModal()">📝 申请收录</button>
            <!-- 移动端菜单按钮 -->
            <button class="navbar-menu-btn" onclick="toggleMobileMenu()">☰</button>
        </div>
    </nav>

    <!-- 移动端下拉菜单 -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-content">
            <form class="mobile-search" action="search.php" method="get">
                <input name="keyword" placeholder="搜索站点名称/描述…" required>
                <button type="submit">🔍</button>
            </form>
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
            <?php if ($apply_msg): ?>
                <div id="applyMsg" class="<?= $apply_status === 'success' ? 'success' : 'error' ?>" style="margin-bottom:20px;padding:16px;border-radius:12px;text-align:center;font-weight:600;"><?= $apply_msg ?></div>
            <?php endif; ?>
            <form id="applyModalForm">
                <input name="name" placeholder="站点名称" required>
                <input name="url" type="text" placeholder="https://awenz.cn 或 awenz.cn" required>
                <textarea name="desc" placeholder="一句话描述您的网站" required></textarea>
                <input name="contact" placeholder="QQ / 微信 / 邮箱" required>
                <select name="cate_id" required>
                    <option value="">选择分类</option>
                    <?php foreach ($cates as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">提交申请</button>
            </form>
        </div>
    </div>

    <!-- 公告 -->
    <?php if ($notice && $notice['is_show']): ?>
        <div class="notice" id="notice" style="display:none;">
            <div class="notice-in">
                <span class="close">&times;</span>
                <h4><?= $notice['title'] ?></h4>
                <p><?= nl2br($notice['content']) ?></p>
                <button class="btn">知道了</button>
            </div>
        </div>
        <script>
            (() => {
                // 检查是否已经看过公告
                const noticeId = 'notice_<?= $notice['id'] ?>';
                let hasSeen = false;

                // 尝试从localStorage获取数据，添加错误处理
                try {
                    hasSeen = localStorage.getItem(noticeId);
                } catch (e) {
                    // 跟踪防护阻止访问存储时，静默处理错误
                    console.log('LocalStorage access blocked, showing notice anyway');
                }

                if (!hasSeen) {
                    const n = document.getElementById('notice');
                    n.style.display = 'flex';
                    const close = () => {
                        n.remove();
                        // 尝试设置localStorage，添加错误处理
                        try {
                            localStorage.setItem(noticeId, 'true');
                        } catch (e) {
                            // 跟踪防护阻止访问存储时，静默处理错误
                        }
                    };
                    n.querySelector('.close').onclick = close;
                    n.querySelector('.btn').onclick = close;
                    setTimeout(close, <?= $notice_delay * 1000 ?>);
                }
            })();
        </script>
    <?php endif; ?>


    <!-- 主内容区域 -->
    <div class="main-content">
        <!-- 主体 -->
        <div class="wrap">
            <!-- 左侧侧边栏 -->
            <aside class="sidebar">
                <div class="sidebar-card">
                    <h3 class="sidebar-title">📂 站点分类</h3>
                    <div class="sidebar-menu">
                        <a href="index.php" class="sidebar-item <?= $current_cate_id === 0 ? 'active' : '' ?>" data-cate-id="0">
                            全部站点
                            <span class="count"><?= count($all_sites) ?></span>
                        </a>
                        <?php foreach ($cates as $c): ?>
                            <a href="index.php?cate_id=<?= $c['id'] ?>" class="sidebar-item <?= (int)$current_cate_id === (int)$c['id'] ? 'active' : '' ?>" data-cate-id="<?= $c['id'] ?>">
                                <?= $c['name'] ?>
                                <span class="count"><?= count($c['sites']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>

            <!-- 右侧内容区 -->
            <main>
                <div id="categoryContent">
                    <?php
                    // 根据选中的分类ID显示对应站点
                    if ($current_cate_id === 0) {
                        // 显示全部站点
                        if (!empty($all_sites)):
                    ?>
                            <section class="cate">
                                <h3><span class="icon">📁</span> 全部站点</h3>
                                <div class="list">
                                    <?php foreach ($all_sites as $s): ?>
                                        <a class="item" href="index.php?action=click&id=<?= $s['id'] ?>" target="_blank" title="<?= $s['name'] ?> - <?= $s['desc'] ?>">
                                            <img src="<?= $s['icon_url'] ?>" alt="<?= $s['name'] ?> 图标">
                                            <div class="item-content">
                                                <div class="tit"><?= $s['name'] ?></div>
                                                <div class="desc"><?= $s['desc'] ?></div>
                                                <div class="url" style="font-size: 11px; color: var(--text-light); margin-bottom: 4px; word-break: break-all;"><?= parse_url($s['url'], PHP_URL_HOST) ?></div>
                                                <div class="pv">访问 <?= $s['click_num'] ?></div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php
                        endif;
                    } else {
                        // 显示选中分类的站点
                        if (isset($cates[$current_cate_id])):
                            $c = $cates[$current_cate_id];
                        ?>
                            <section class="cate">
                                <h3><span class="icon">📁</span> <?= $c['name'] ?></h3>
                                <div class="list">
                                    <?php foreach ($c['sites'] as $s): ?>
                                        <a class="item" href="index.php?action=click&id=<?= $s['id'] ?>" target="_blank" title="<?= $s['name'] ?> - <?= $s['desc'] ?>">
                                            <img src="<?= $s['icon_url'] ?>" alt="<?= $s['name'] ?> 图标">
                                            <div class="item-content">
                                                <div class="tit"><?= $s['name'] ?></div>
                                                <div class="desc"><?= $s['desc'] ?></div>
                                                <div class="url" style="font-size: 11px; color: var(--text-light); margin-bottom: 4px; word-break: break-all;"><?= parse_url($s['url'], PHP_URL_HOST) ?></div>
                                                <div class="pv">访问 <?= $s['click_num'] ?></div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                    <?php
                        endif;
                    }
                    ?>
                </div>
                <div id="loadingIndicator" style="display: none; text-align: center; padding: 40px;">
                    <div style="font-size: 24px; margin-bottom: 16px;">⏳</div>
                    <p>加载中...</p>
                </div>
            </main>
        </div>
    </div>

    <!-- 底部 -->
    <footer>
        <div class="stats">
            <span>📅 网站已正常运行 <?= $days_running ?> 天</span>
            <span>👁️ 累计访问数量 <?= $total_clicks ?> 次</span>
        </div>
        <p><?= $footer_text ?></p>
        <?php if ($site_icp): ?>
            <p><a href="https://beian.miit.gov.cn/" target="_blank"><?= $site_icp ?></a></p>
        <?php endif; ?>
    </footer>

    <script>
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
                const dropdown = document.getElementById('cateDropdown');
                if (dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                }
            }
        });

        /* 弹窗表单提交 */
        applyModalForm.onsubmit = async e => {
            e.preventDefault();
            const f = new FormData(applyModalForm);
            f.append('apply_submit', 1);
            const r = await fetch('', {
                method: 'POST',
                body: f
            }).then(res => res.text());
            const msgDiv = document.getElementById('applyMsg');
            if (!msgDiv) {
                const newMsg = document.createElement('div');
                newMsg.id = 'applyMsg';
                newMsg.style.cssText = 'margin-bottom:20px;padding:16px;border-radius:12px;text-align:center;font-weight:600;';
                newMsg.className = (r.includes('成功') ? 'success' : 'error');
                newMsg.textContent = r.includes('成功') ? '申请已提交，管理员会尽快审核！' : '提交失败，请检查输入';
                applyModalForm.parentNode.insertBefore(newMsg, applyModalForm);
            } else {
                msgDiv.className = (r.includes('成功') ? 'success' : 'error');
                msgDiv.textContent = r.includes('成功') ? '申请已提交，管理员会尽快审核！' : '提交失败，请检查输入';
            }
            if (r.includes('成功')) {
                applyModalForm.reset();
                setTimeout(closeApplyModal, 2000);
            }
        };
    </script>

    <script>
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

        // 平滑滚动
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    // 检测是否为移动设备，移动设备上使用简单滚动以提高性能
                    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                    if (isMobile) {
                        target.scrollIntoView({
                            behavior: 'auto',
                            block: 'start'
                        });
                    } else {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

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

        // 分类链接点击效果和 AJAX 加载
        document.querySelectorAll('.sidebar-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault(); // 阻止默认链接行为

                // 检测是否为移动设备
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                // 添加点击效果（仅在非移动设备上）
                if (!isMobile) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                }

                // 获取分类 ID
                const cateId = this.dataset.cateId;

                // 立即更新分类链接的 active 状态，提高响应速度
                document.querySelectorAll('.sidebar-item').forEach(link => {
                    link.classList.remove('active');
                });
                this.classList.add('active');

                // 显示加载指示器
                const categoryContent = document.getElementById('categoryContent');
                const loadingIndicator = document.getElementById('loadingIndicator');

                if (categoryContent) {
                    categoryContent.style.opacity = '0.7'; // 减少透明度变化的性能消耗
                }
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'block';
                }

                // 发送 AJAX 请求
                fetch(`index.php?action=get_category_sites&cate_id=${cateId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 构建站点列表 HTML
                            let html = '';

                            if (data.sites.length > 0) {
                                html = `
                                    <section class="cate">
                                        <h3><span class="icon">📁</span> ${data.cate_name}</h3>
                                        <div class="list">
                                `;

                                data.sites.forEach(s => {
                                    html += `
                                        <a class="item" href="index.php?action=click&id=${s.id}" target="_blank" title="${s.name} - ${s.desc}">
                                            <img src="${s.icon_url}" alt="${s.name} 图标">
                                            <div class="item-content">
                                                <div class="tit">${s.name}</div>
                                                <div class="desc">${s.desc}</div>
                                                <div class="url" style="font-size: 11px; color: var(--text-light); margin-bottom: 4px; word-break: break-all;">${s.domain}</div>
                                                <div class="pv">访问 ${s.click_num}</div>
                                            </div>
                                        </a>
                                    `;
                                });

                                html += `
                                        </div>
                                    </section>
                                `;
                            } else {
                                html = `
                                    <section class="cate">
                                        <h3><span class="icon">📁</span> ${data.cate_name}</h3>
                                        <div style="text-align: center; padding: 40px; color: var(--text-light);">
                                            该分类下暂无站点
                                        </div>
                                    </section>
                                `;
                            }

                            // 更新内容
                            if (categoryContent) {
                                categoryContent.innerHTML = html;
                            }

                            // 重新初始化网站卡片动画
                            // 在移动设备上简化动画以提高性能
                            document.querySelectorAll('.item').forEach(item => {
                                if (isMobile) {
                                    // 移动设备上使用更简单的动画
                                    item.style.opacity = '1';
                                    item.style.transform = 'none';
                                } else {
                                    // 非移动设备上使用完整动画
                                    item.style.opacity = '0';
                                    item.style.transform = 'translateY(20px)';
                                    item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                                    observer.observe(item);
                                }
                            });
                        }

                        // 隐藏加载指示器
                        if (categoryContent) {
                            categoryContent.style.opacity = '1';
                        }
                        if (loadingIndicator) {
                            loadingIndicator.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // 隐藏加载指示器
                        if (categoryContent) {
                            categoryContent.style.opacity = '1';
                        }
                        if (loadingIndicator) {
                            loadingIndicator.style.display = 'none';
                        }
                    });
            });
        });

        // 网站卡片悬停效果增强
        document.querySelectorAll('.item').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            });
        });

        // 网站卡片动画效果
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

        // 观察所有网站卡片
        document.querySelectorAll('.item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(item);
        });
    </script>
</body>

</html>
<?php mysqli_close($conn); ?>