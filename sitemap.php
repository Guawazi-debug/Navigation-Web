<?php
/**
 * 瓜娃子导航 - 站点地图生成器
 * 用于SEO优化，帮助搜索引擎更好地索引网站
 * 更新日志：2026-03-01 - 初始创建
 */

require_once 'config.php';

header('Content-Type: application/xml; charset=utf-8');

// 获取当前时间
$current_time = date('Y-m-d\TH:i:sP');

// 获取所有分类和站点
$cates = [];
$res = mysqli_query($conn, "SELECT * FROM nav_cate ORDER BY sort ASC");
while ($c = mysqli_fetch_assoc($res)) {
    $sites = [];
    $sres = mysqli_query($conn, "SELECT * FROM nav_site WHERE cate_id={$c['id']} ORDER BY sort ASC");
    while ($s = mysqli_fetch_assoc($sres)) {
        $sites[] = $s;
    }
    if ($sites) {
        $cates[] = array_merge($c, ['sites' => $sites]);
    }
}

// 获取系统配置
$site_name = get_system_config('site_name');
$site_desc = get_system_config('site_desc');
?>
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- 首页 -->
    <url>
        <loc>https://awenz.cn/</loc>
        <lastmod><?= $current_time ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- 搜索页面 -->
    <url>
        <loc>https://awenz.cn/search.php</loc>
        <lastmod><?= $current_time ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    
    <!-- 分类页面和站点页面 -->
    <?php foreach ($cates as $c): ?>
        <!-- 分类页面 -->
        <url>
            <loc>https://awenz.cn/#cate<?= $c['id'] ?></loc>
            <lastmod><?= $current_time ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
        
        <!-- 站点页面 -->
        <?php foreach ($c['sites'] as $s): ?>
        <url>
            <loc>https://awenz.cn/index.php?action=click&id=<?= $s['id'] ?></loc>
            <lastmod><?= $current_time ?></lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.5</priority>
        </url>
        <?php endforeach; ?>
    <?php endforeach; ?>
</urlset>
<?php mysqli_close($conn); ?>
