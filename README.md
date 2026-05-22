# 瓜娃子导航 - 优质网址导航系统

> 一个简洁、高效、美观的网址导航网站，帮助用户快速找到优质网站资源。

**项目地址**：[https://awenz.cn](https://awenz.cn)

---

## 项目简介

瓜娃子导航是一个基于 PHP + MySQL 的网址导航系统，提供站点分类、搜索、申请收录、公告管理等功能。采用现代化 UI 设计，支持响应式布局，适配桌面端和移动端。

---

## 技术栈

| 类别 | 技术 | 版本要求 |
|------|------|----------|
| 后端 | PHP | 7.4+ |
| 数据库 | MySQL | 5.7+ |
| 前端 | HTML5 + CSS3 + JavaScript | - |
| 服务器 | Nginx / Apache | - |

---

## 功能特性

### 前台功能

- **站点分类浏览** - 左侧边栏显示所有分类，点击分类筛选站点
- **站点搜索** - 支持按站点名称和描述进行模糊搜索
- **申请收录** - 用户可提交站点收录申请，包含站点名称、链接、描述、联系方式
- **公告系统** - 支持多公告展示，点击查看详情弹窗
- **访问统计** - 记录每个站点的点击访问量
- **网站统计** - 底部显示网站运行天数和累计访问量
- **SEO 优化** - 支持 Open Graph、Twitter Card、Schema.org 结构化数据
- **响应式设计** - 完美适配桌面、平板、手机等多种设备
- **视觉特效** - 飘落特效、点击特效、卡片动画等（移动端自动禁用以优化性能）

### 后台管理

- **登录认证** - 管理员账号密码登录
- **站点管理** - 添加、编辑、删除站点
- **分类管理** - 添加、编辑、删除分类，支持排序
- **公告管理** - 发布、编辑、删除公告，控制显示状态
- **申请审核** - 审核用户提交的收录申请
- **系统配置** - 自定义网站名称、描述、备案号、Logo、Favicon 等

---

## 目录结构

```
awenz.cn/
├── admin/                    # 后台管理
│   ├── index.php             # 登录页面
│   ├── main.php              # 管理主页（站点/分类/公告管理）
│   ├── site_manage.php       # 站点管理（添加/编辑/删除）
│   ├── cate_manage.php       # 分类管理（添加/编辑/删除）
│   └── notice_manage.php     # 公告管理（添加/编辑/删除）
├── images/                   # 图片资源
│   ├── background.png        # 背景图片
│   └── default-icon.png      # 默认站点图标
├── uploads/                  # 上传文件目录
│   ├── icons/                # 站点图标
│   └── system/               # 系统文件（Logo、Favicon）
├── sql/                      # 数据库备份
│   └── awenz_cn_*.sql        # 数据库导出文件
├── js/                       # JavaScript 文件
│   └── main.js               # 通用 JS 功能
├── .well-known/              # SSL 证书验证目录
├── config.php                # 数据库配置和公共函数
├── index.php                 # 首页（站点展示）
├── search.php                # 搜索结果页
├── agent.html                # AI 智能体页面
├── sitemap.php               # 站点地图生成
├── sitemap.xml               # 站点地图
├── robots.txt                # 搜索引擎爬虫规则
└── README.md                 # 项目说明文档
```

---

## 数据库设计

### 数据表说明

| 表名 | 说明 | 主要字段 |
|------|------|----------|
| `nav_site` | 站点表 | id, cate_id, name, url, desc, sort, click_num, icon |
| `nav_cate` | 分类表 | id, name, sort |
| `nav_apply` | 申请表 | id, name, url, desc, contact, cate_id, status |
| `nav_notice` | 公告表 | id, title, content, is_show |
| `nav_system` | 系统配置表 | id, config_key, config_value, config_desc |

### 数据库导入

```bash
mysql -u 用户名 -p 数据库名 < sql/awenz_cn_*.sql
```

---

## 安装部署

### 1. 环境准备

- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- Nginx 或 Apache 服务器
- 确保 PHP 已启用 `mysqli` 扩展

### 2. 配置数据库

编辑 `config.php` 文件，修改数据库连接信息：

```php
$db_host = 'localhost';      // 数据库主机
$db_user = 'your_username';  // 数据库用户名
$db_pass = 'your_password';  // 数据库密码
$db_name = 'your_database';  // 数据库名称
```

### 3. 导入数据库

```bash
mysql -u 用户名 -p 数据库名 < sql/awenz_cn_2026-05-22_09-00-09_mysql_data_8tKFK.sql
```

### 4. 配置 Web 服务器

**Nginx 配置示例：**

```nginx
server {
    listen 80;
    server_name awenz.cn;
    root /path/to/awenz.cn;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. 设置目录权限

```bash
chmod 755 uploads/
chmod 755 uploads/icons/
chmod 755 uploads/system/
```

### 6. 访问后台

- 后台地址：`/admin/`
- 默认账号：`admin`
- 默认密码：`123456Aa?`

**请在首次登录后立即修改默认密码！**

---

## 配置说明

### 系统配置项

在后台「系统配置」中可修改以下配置：

| 配置项 | 说明 | 默认值 |
|--------|------|--------|
| site_name | 网站名称 | 瓜娃子导航 - awenz.cn |
| site_desc | 网站描述 | 欢迎大家提交申请！ |
| site_icp | ICP 备案号 | 粤ICP备2024322061号-2 |
| footer_text | 底部版权文字 | © 2026 awenz.cn 保留所有权利 |
| notice_status | 公告显示状态 | 1（显示） |
| notice_delay | 弹窗自动关闭延迟（秒） | 5 |
| admin_nav_title | 后台顶部导航文字 | 瓜娃子管理端 |
| site_favicon | 网站 Favicon 路径 | - |
| site_logo | 网站 Logo 路径 | - |

---

## SEO 优化

项目已内置以下 SEO 优化：

- **Meta 标签**：完整的 title、description、keywords
- **Open Graph**：社交媒体分享优化
- **Twitter Card**：Twitter 分享优化
- **Schema.org**：结构化数据（WebSite、BreadcrumbList）
- **Canonical URL**：规范化链接
- **Sitemap**：自动生成站点地图（`/sitemap.php`）
- **Robots.txt**：搜索引擎爬虫规则

---

## 更新日志

### 2026-05-22
- 完善项目文档
- 添加数据库备份

### 2026-03-04
- 公告区域样式统一优化
- 公告支持点击查看详情弹窗
- 导航栏显示优化（Logo 旁边显示网站标题）

### 2026-03-01
- 优化页面布局，新增左侧分类侧边栏
- 实现 AJAX 分类筛选功能
- 修复网站 icon 显示问题
- 美化底部区域

---

## 许可证

本项目仅供学习和个人使用。

---

## 联系方式

- 网站：[https://awenz.cn](https://awenz.cn)
- 申请收录：通过网站首页「申请收录」按钮提交

---

> 如果觉得不错，欢迎 Star 支持！
