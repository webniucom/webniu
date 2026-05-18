
CREATE TABLE IF NOT EXISTS `__PREFIX__admins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(32) COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户名',
  `nickname` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT '昵称',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '/app/webniu/images/avatar.png' COMMENT '头像',
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(16) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '手机',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `login_at` datetime DEFAULT NULL COMMENT '登录时间',
  `status` tinyint DEFAULT NULL COMMENT '禁用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员表';

CREATE TABLE IF NOT EXISTS `__PREFIX__admin_roles` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
  `role_id` int NOT NULL COMMENT '角色id',
  `admin_id` int NOT NULL COMMENT '管理员id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_admin_id` (`role_id`,`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员角色表';

CREATE TABLE IF NOT EXISTS `__PREFIX__confset` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `model` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '模型',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '键',
  `label` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '标签',
  `label_width` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '标签宽度',
  `desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '描述',
  `type` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '类型',
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '键',
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '值',
  `options` longtext COLLATE utf8mb4_general_ci COMMENT '选项',
  `dict` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '字典',
  `span` int DEFAULT '24' COMMENT '列数',
  `sort` int DEFAULT '0' COMMENT '排序',
  `hidden` int DEFAULT NULL COMMENT '隐藏',
  `props` longtext COLLATE utf8mb4_general_ci COMMENT '属性',
  `status` int DEFAULT '0' COMMENT '状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='选项表';

CREATE TABLE IF NOT EXISTS `__PREFIX__confset_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `model` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '模型',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '键',
  `label` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '标签',
  `sort` int DEFAULT '99' COMMENT '排序',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='选项表';

CREATE TABLE IF NOT EXISTS `__PREFIX__dict` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int DEFAULT NULL COMMENT '父级',
  `group` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '组',
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '标签',
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '值',
  `sort` int DEFAULT '0' COMMENT '排序',
  `disabled` int DEFAULT NULL COMMENT '禁用',
  `status` int DEFAULT '0' COMMENT '状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='字典表';

CREATE TABLE IF NOT EXISTS `__PREFIX__dict_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `label` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '标签',
  `value` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '值',
  `status` int DEFAULT '0' COMMENT '状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='字典组表';

CREATE TABLE IF NOT EXISTS `__PREFIX__options` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `model` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '模型',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '键',
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '值',
  `username` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '用户名',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='选项表';

CREATE TABLE IF NOT EXISTS `__PREFIX__refresh_tokens` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID(主键)',
  `admin_id` int NOT NULL COMMENT '管理员ID',
  `refresh_token` varchar(64) NOT NULL COMMENT '刷新令牌',
  `access_token` varchar(64) NOT NULL COMMENT '访问令牌',
  `expires_at` datetime NOT NULL COMMENT '过期时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `refresh_token` (`refresh_token`),
  KEY `admin_id` (`admin_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='刷新令牌表';

CREATE TABLE IF NOT EXISTS `__PREFIX__roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(80) COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色组',
  `rules` text COLLATE utf8mb4_general_ci COMMENT '权限',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `pid` int unsigned DEFAULT NULL COMMENT '父级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员角色';

CREATE TABLE IF NOT EXISTS `__PREFIX__rules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int unsigned DEFAULT '0' COMMENT '父级',
  `type` int DEFAULT NULL COMMENT '类型',
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '标题',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '名称',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '图标',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '路径',
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '键',
  `href` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '链接',
  `component` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '组件',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '链接',
  `show_text_badge` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '显示文本',
  `auth_mark` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '权限',
  `show_badge` int DEFAULT '0' COMMENT '显示徽章',
  `is_hide` int DEFAULT '0' COMMENT '隐藏菜单',
  `is_hide_tab` int DEFAULT '0' COMMENT '隐藏标签',
  `is_iframe` int DEFAULT '0' COMMENT '是否iframe',
  `is_enable` int DEFAULT '0' COMMENT '是否启用',
  `is_full_page` int DEFAULT '0' COMMENT '是否全屏页',
  `keep_alive` int DEFAULT '0' COMMENT '是否保持活动',
  `fixed_tab` int DEFAULT NULL COMMENT '固定标签',
  `sort` int DEFAULT '0' COMMENT '排序',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='菜单表';

CREATE TABLE IF NOT EXISTS `__PREFIX__uploads` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(128) COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件',
  `admin_id` int DEFAULT NULL COMMENT '管理员',
  `file_size` int NOT NULL COMMENT '文件大小',
  `mime_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'mime类型',
  `image_width` int DEFAULT NULL COMMENT '图片宽度',
  `image_height` int DEFAULT NULL COMMENT '图片高度',
  `ext` varchar(128) COLLATE utf8mb4_general_ci NOT NULL COMMENT '扩展名',
  `storage` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `created_at` date DEFAULT NULL COMMENT '上传时间',
  `category` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '类别',
  `updated_at` date DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `admin_id` (`admin_id`),
  KEY `name` (`name`),
  KEY `ext` (`ext`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='附件';

CREATE TABLE IF NOT EXISTS `__PREFIX__uploads_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int DEFAULT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `label` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort` int DEFAULT '99',
  `created_at` datetime DEFAULT '2022-08-15 00:00:00',
  `updated_at` datetime DEFAULT '2022-08-15 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='附件组表';

CREATE TABLE IF NOT EXISTS `__PREFIX__users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(32) COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户名',
  `nickname` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT '昵称',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `sex` enum('0','1') COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '性别',
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '头像',
  `email` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(16) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '手机',
  `level` tinyint NOT NULL DEFAULT '0' COMMENT '等级',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额(元)',
  `score` int NOT NULL DEFAULT '0' COMMENT '积分',
  `last_time` datetime DEFAULT NULL COMMENT '登录时间',
  `last_ip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '登录ip',
  `join_time` datetime DEFAULT NULL COMMENT '注册时间',
  `join_ip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '注册ip',
  `token` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'token',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `role` int NOT NULL DEFAULT '1' COMMENT '角色',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '禁用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `join_time` (`join_time`),
  KEY `mobile` (`mobile`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户表';

LOCK TABLES `__PREFIX__roles` WRITE;
INSERT INTO `__PREFIX__roles` VALUES (1,'超级管理员','*','2022-08-13 16:15:01','2022-12-23 12:05:07',NULL);
UNLOCK TABLES;

LOCK TABLES `__PREFIX__confset_group` WRITE;
INSERT INTO `__PREFIX__confset_group` VALUES (NULL, 'system', 'systemInfo', '基本设置', '0', '1988-06-15 00:00:00', '1988-06-15 00:00:00');
INSERT INTO `__PREFIX__confset_group` VALUES (NULL, 'system', 'systemSetting', '账户设置', '0', '1988-06-15 00:00:00', '1988-06-15 00:00:00');
INSERT INTO `__PREFIX__confset_group` VALUES (NULL, 'system', 'attachMent', '附件设置', '0', '1988-06-15 00:00:00', '1988-06-15 00:00:00');
INSERT INTO `__PREFIX__confset_group` VALUES (NULL, 'system', 'waterMark', '图片水印', '0', '1988-06-15 00:00:00', '1988-06-15 00:00:00');
INSERT INTO `__PREFIX__confset_group` VALUES (NULL, 'system', 'systemTheme', '全局样式', '0', '1988-06-15 00:00:00', '1988-06-15 00:00:00');
INSERT INTO `__PREFIX__confset_group` VALUES (NULL, 'system', 'interfaceConfig', '接口配置', '0', '1988-06-15 00:00:00', '1988-06-15 00:00:00');
UNLOCK TABLES;

LOCK TABLES `__PREFIX__confset` WRITE;
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemInfo', '站点标题', '100', '请输入站点标题', 'input', 'title', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"请输入站点标题\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:41:28', '1988-6-15 10:41:36');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemInfo', '站点关键字', '100', '请输入关键字', 'input', 'keyword', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"请输入关键字\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 23:22:56', '1988-6-15 16:50:34');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemInfo', '站点描述', '100', '请输入后台描述', 'textarea', 'description', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"请输入后台描述\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-1517:46:37', '1988-6-15 10:36:27');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemInfo', '站点名称', '100', '请输入后台名称', 'input', 'name', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"请输入后台名称\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 23:21:13', '1988-6-15 10:43:06');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemInfo', '站点连接', '100', '请输入站点连接', 'input', 'siteurl', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"请输入站点连接\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-1517:47:13', '1988-6-15 16:51:57');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemInfo', '站点LOGO', '100', '请上传图片', 'upload', 'logo', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"请上传图片\",\n  \"clearable\": true,\n  \"multiple\": false,\n  \"uploadlist\": true,\n  \"tooltip\": true\n}', 1, '1988-6-1517:48:41', '1988-6-15 10:36:39');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemInfo', '版权信息', '100', '请输入版权信息', 'input', 'copyright', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"请输入版权信息\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-1517:49:18', '1988-6-15 16:52:19');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemInfo', '备案信息', '100', '请输入备案号', 'input', 'ipcbeian', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"请输入备案号\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-1517:49:39', '1988-6-15 16:52:11');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemInfo', '登录标语', '100', '在登录也显示的一段信息', 'input', 'footerTxt', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"在登录也显示的一段信息\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:37:51', '1988-6-15 10:46:38');

INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '后台注册配置', '70', '', 'divider', 'divider', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": false,\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 20:54:28', '1988-6-15 08:29:31');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '图像验证', '100', '开启后登陆需要图像验证码', 'switch', 'isCaptcha', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"开启后登陆需要图像验证码\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:59:03', '1988-6-15 08:15:08');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '用户注册', '100', '请输入管理注册', 'switch', 'isRegister', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"请输入管理注册\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:51:36', '1988-6-15 08:14:38');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '角色权限', '100', '请选择权限', 'select', 'roles', '', '[{\"label\":\"王力宏\",\"value\":\"1\"},{\"label\":\"李彦宏\",\"value\":\"2\"}]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"请选择权限\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:52:37', '1988-6-15 09:29:57');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '手机验证', '100', '注册是需要验证手机号', 'switch', 'm_phone_verify', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"注册是需要验证手机号\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:29:24', '1988-6-15 17:29:33');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '密码找回', '100', '密码找回', 'switch', 'forgetPassword', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"密码找回\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 08:17:00', '1988-6-15 08:17:49');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '前端注册配置', '100', '', 'divider', 'divider', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": false,\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:29:59', '1988-6-15 17:29:59');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '会员注册', '100', '前端会员注册', 'switch', 'userreg', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"前端会员注册\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:53:12', '1988-6-15 17:31:21');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '图形验证', '100', '开启图像验证登陆', 'switch', 'u_image_verify', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"开启图像验证登陆\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:31:57', '1988-6-15 17:31:57');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '手机验证', '100', '前端注册手机验证', 'switch', 'u_phone_verify', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"前端注册手机验证\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:32:24', '1988-6-15 17:32:24');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemSetting', '邮箱验证', '100', '需要配置邮箱功能', 'switch', 'u_email_verify', '', '[]', NULL, 24, 0, 0, '{\n  \"placeholder\": \"需要配置邮箱功能\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:33:13', '1988-6-15 18:57:36');

INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '上传参数', '100', '', 'divider', 'divider', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": false,\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:14:19', '1988-6-15 10:14:19');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '保存路径', '130', '不熟悉建议默认即可！', 'input', 'dirname', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"不熟悉建议默认即可！\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:15:15', '1988-6-15 11:08:13');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '时间目录风格', '130', '不熟悉建议默认即可！', 'input', 'path_style', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"不熟悉建议默认即可！\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:15:31', '1988-6-15 11:08:18');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '被允许上传类型', '130', '上传的文件类型支持如:jpg,jpeg,png,gif多个以英文逗号分隔', 'input', 'include', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"上传的文件类型支持如:jpg,jpeg,png,gif多个以英文逗号分隔\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:16:28', '1988-6-15 11:08:22');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '不被允许的类型', '130', '不被允许的类型如:exe,php', 'input', 'exclude', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"不被允许的类型如:exe,php\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:16:43', '1988-6-15 11:08:27');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '单文件上传限制', '130', '单个文件的大小限制单位M;', 'input', 'single_limit', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"单个文件的大小限制单位M;\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:16:59', '1988-6-15 11:08:33');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '多文件总传限制', '130', '单个文件的大小限制单位M', 'input', 'total_limit', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"单个文件的大小限制单位M\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:17:12', '1988-6-15 11:08:37');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '文件数量限制', '130', '同时上传数量，建议设置1，单文件上传最佳！', 'input', 'nums', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"同时上传数量，建议设置1，单文件上传最佳！\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:17:31', '1988-6-15 11:08:40');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '远程上传', '130', '', 'divider', 'divder', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": false,\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 10:17:54', '1988-6-15 10:26:00');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '是否启用', '110', '选择上传服务云', 'select', 'storage_type', '', '[{\"label\":\"关闭\",\"value\":\"0\"},{\"label\":\"FTP附件\",\"value\":\"1\"},{\"label\":\"七牛云存储\",\"value\":\"2\"},{\"label\":\"阿里云OSS\",\"value\":\"3\"},{\"label\":\"腾讯云存储\",\"value\":\"4\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"选择上传服务云\",\n  \"tooltip\": true\n}', 1, '1988-6-15 11:29:36', '1988-6-15 15:22:08');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'FTP地址', '110', '配置完信息后上传图片卡死说明远程服务器有问题！', 'input', 'ftp_ip', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"配置完信息后上传图片卡死说明远程服务器有问题！\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 11:44:50', '1988-6-15 15:35:21');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'FTP端口', '110', 'FTP端口', 'input', 'ftp_port', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"FTP端口\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 15:15:10', '1988-6-15 15:35:19');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '被动模式', '110', '如果上传无响应切换开启被动模式。', 'input', 'ftp_pasv', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"如果上传无响应切换开启被动模式。\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 15:20:00', '1988-6-15 15:35:16');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'FTP用户名', '110', 'FTP用户名', 'input', 'ftp_username', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"FTP用户名\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 15:20:26', '1988-6-15 15:35:13');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'FTP密码', '110', 'FTP密码', 'input', 'ftp_password', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"FTP密码\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 15:20:46', '1988-6-15 15:34:23');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '远程图片地址', '110', '远程图片地址', 'input', 'ftp_domain', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"远程图片地址\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 15:21:07', '1988-6-15 15:34:20');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'Accesskey', '110', '请输入Accesskey', 'input', 'qiniu_accesskey', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请输入Accesskey\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 15:59:14', '1988-6-15 15:59:14');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'Secretkey', '110', '请输入Secretkey', 'input', 'qiniu_secretkey', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请输入Secretkey\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 15:59:56', '1988-6-15 15:59:56');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'Bucket', '110', '请保证bucket为可公共读取的', 'input', 'qiniu_bucket', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请保证bucket为可公共读取的\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 16:00:30', '1988-6-15 16:00:30');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'Url', '110', '注:url开头加http://或https://结尾不加 ‘/’例:http://xxxx.com', 'input', 'qiniu_domain', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"注:url开头加http://或https://结尾不加 ‘/’例:http://xxxx.com\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 16:01:17', '1988-6-15 16:01:17');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'KeyID', '110', '登录阿里云官网获取账户对应的KeyID', 'input', 'oss_accesskeyid', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"登录阿里云官网获取账户对应的KeyID\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 16:55:50', '1988-6-15 16:55:50');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'KeySecret', '110', '登录阿里云官网获取账户对应的KeySecret', 'input', 'oss_accesskeysecret', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"登录阿里云官网获取账户对应的KeySecret\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 16:56:14', '1988-6-15 16:56:14');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '内网上传', '110', '同为阿里云ecs服务器并且服务器与bucket在同一地区可以使用内网上传节省流量！', 'input', 'oss_local', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"同为阿里云ecs服务器并且服务器与bucket在同一地区可以使用内网上传节省流量！\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 16:56:40', '1988-6-15 16:56:40');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'Bucket', '110', '请保证bucket为可公共读取的', 'input', 'oss_bucket', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请保证bucket为可公共读取的\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 16:57:04', '1988-6-15 16:57:04');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'Endpoint', '110', '请保证Endpoint可用', 'input', 'oss_endpoint', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请保证Endpoint可用\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 16:57:21', '1988-6-15 16:57:21');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', '自定义URL', '110', '阿里云oss默认有生成url,用户可以自定义url，注:url开头加http://或https://结尾不加 ‘/’例:http://xxxx.com', 'input', 'oss_domain', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"阿里云oss默认有生成url,用户可以自定义url，注:url开头加http://或https://结尾不加 ‘/’例:http://xxxx.com\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 16:57:44', '1988-6-15 16:57:44');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'SecretID', '110', '请输入SecretID', 'input', 'cos_secretid', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请输入SecretID\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:01:41', '1988-6-15 17:03:17');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'SecretKEY', '110', '请输入SecretKEY', 'input', 'cos_secretkey', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请输入SecretKEY\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:01:55', '1988-6-15 17:03:10');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'Bucket', '110', '请输入Bucket', 'input', 'cos_bucket', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请输入Bucket\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:02:10', '1988-6-15 17:03:02');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'REGION', '110', '请输入region', 'input', 'cos_region', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请输入region\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:02:23', '1988-6-15 17:02:58');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'attachMent', 'Url', '110', '请输入Url', 'input', 'cos_domain', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请输入Url\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 17:02:49', '1988-6-15 17:02:49');

INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '图片压缩', '110', '图片压缩', 'divider', 'divder', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"图片压缩\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:24:25', '1988-6-15 18:24:25');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '图片大小压缩', '110', '缩略图', 'switch', 'pic_thumb_type', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"缩略图\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:26:12', '1988-6-15 18:26:12');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '宽度', '110', '图片宽度超过指定宽度执行比例压缩！', 'number', 'pic_thumb_width', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"图片宽度超过指定宽度执行比例压缩！\",\n  \"clearable\": true,\n  \"tooltip\": true,\n  \"min\": 300,\n  \"max\": 2000,\n  \"step\": 100\n}', 1, '1988-6-15 18:27:17', '1988-6-15 18:56:06');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '缩放比例', '110', '图片超过指定宽度执行对应比例压缩！格式0.1~1。比如0.5代表等比例缩放50%', 'number', 'pic_thumb_percent', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"图片超过指定宽度执行对应比例压缩！格式0.1~1。比如0.5代表等比例缩放50%\",\n  \"clearable\": true,\n  \"tooltip\": true,\n  \"min\": 0.1,\n  \"max\": 1,\n  \"step\": 0.1\n}', 1, '1988-6-15 18:27:45', '1988-6-15 18:55:01');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '图片水印', '110', '图片水印', 'divider', 'divder', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"图片水印\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:29:00', '1988-6-15 18:29:00');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '图片水印', '110', '图片水印', 'switch', 'pic_mark_type', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"图片水印\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:29:42', '1988-6-15 18:29:42');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '水印类型', '110', '图片水印', 'radiogroup', 'pic_mark_style', '', '[{\"label\":\"文字水印\",\"value\":\"0\"},{\"label\":\"图片水印\",\"value\":\"1\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"图片水印\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:31:13', '1988-6-15 18:31:13');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '水印文字', '110', '文字水印固定在图片右下角！', 'input', 'pic_thumb_text', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"文字水印固定在图片右下角！\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:31:45', '1988-6-15 18:47:58');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '文字大小', '110', '请输入文字大小', 'input', 'pic_thumb_size', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请输入文字大小\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:33:52', '1988-6-15 18:47:54');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '文字颜色', '110', '文字颜色', 'input', 'pic_thumb_color', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"文字颜色\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:34:28', '1988-6-15 18:47:50');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '水印图片', '110', '水印图片120x120像素。', 'upload', 'pic_thumb_img', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"水印图片120x120像素。\",\n  \"clearable\": true,\n  \"tooltip\": true,\n  \"multiple\": false,\n  \"uploadlist\": true\n}', 1, '1988-6-15 18:36:41', '1988-6-15 18:47:45');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'waterMark', '水印位置', '110', '请选择水印位置', 'select', 'pic_mark_weizhi', '', '[{\"label\":\"顶部居左\",\"value\":\"top-left\"},{\"label\":\"顶部居中\",\"value\":\"top-center\"},{\"label\":\"顶部居右\",\"value\":\"top-right\"},{\"label\":\"左边居中\",\"value\":\"middle-left\"},{\"label\":\"图片中心\",\"value\":\"middle-center\"},{\"label\":\"右边居中\",\"value\":\"middle-right\"},{\"label\":\"底部居左\",\"value\":\"bottom-left\"},{\"label\":\"底部居中\",\"value\":\"bottom-center\"},{\"label\":\"底部居右\",\"value\":\"bottom-right\"}]', '', 24, 0, 1, '{\n  \"placeholder\": \"请选择水印位置\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 18:39:30', '1988-6-15 18:47:42');

INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '全局默认样式', '110', '全局默认样式', 'divider', 'divder', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"全局默认样式\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 08:45:27', '1988-6-15 08:45:27');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '菜单类型', '150', '请选择菜单类型', 'select', 'menuType', '', '[{\"label\":\"左侧菜单\",\"value\":\"left\"},{\"label\":\"顶部菜单\",\"value\":\"top\"},{\"label\":\"顶部左侧菜单\",\"value\":\"top-left\"},{\"label\":\"双栏菜单\",\"value\":\"dual-menu\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"请选择菜单类型\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 21:48:49', '1988-6-15 22:41:39');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '菜单主题', '150', '请选择菜单主题', 'select', 'menuThemeType', '', '[{\"label\":\"暗色主题\",\"value\":\"dark\"},{\"label\":\"亮色主题\",\"value\":\"light\"},{\"label\":\"设计主题\",\"value\":\"design\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"请选择菜单主题\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 21:50:41', '1988-6-15 22:41:47');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '系统主题', '150', '请选择系统主题', 'select', 'systemThemeType', '', '[{\"label\":\"暗色主题\",\"value\":\"dark\"},{\"label\":\"亮色主题\",\"value\":\"light\"},{\"label\":\"自动主题（跟随系统）\",\"value\":\"auto\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"请选择系统主题\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 21:52:10', '1988-6-15 22:41:54');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '容器宽度', '150', '容器宽度', 'select', 'containerWidth', '', '[{\"label\":\"全屏宽度\",\"value\":\"100%\"},{\"label\":\"固定宽度\",\"value\":\"1200px\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"容器宽度\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:36:08', '1988-6-15 22:51:27');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '系统主题模式', '150', '系统主题模式', 'select', 'systemThemeMode', '', '[{\"label\":\"暗色主题\",\"value\":\"dark\"},{\"label\":\"亮色主题\",\"value\":\"light\"},{\"label\":\"自动主题（跟随系统）\",\"value\":\"auto\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"系统主题模式\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:00:06', '1988-6-15 22:42:11');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '系统主题颜色', '150', '系统主题颜色', 'select', 'systemThemeColor', '', '[{\"label\":\"蓝色\",\"value\":\"#5D87FF\"},{\"label\":\"紫色\",\"value\":\"#B48DF3\"},{\"label\":\"深蓝色\",\"value\":\"#1D84FF\"},{\"label\":\"绿色\",\"value\":\"#60C041\"},{\"label\":\"青蓝色\",\"value\":\"#38C0FC\"},{\"label\":\"橙色\",\"value\":\"#F9901F\"},{\"label\":\"粉色\",\"value\":\"#FF80C8\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"系统主题颜色\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:03:49', '1988-6-15 11:05:12');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '标签页样式', '150', '标签页样式', 'select', 'tabStyle', '', '[{\"label\":\"默认\",\"value\":\"tab-default\"},{\"label\":\"卡片\",\"value\":\"tab-card\"},{\"label\":\"谷歌\",\"value\":\"tab-google\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"标签页样式\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:34:50', '1988-6-15 22:51:02');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '页面过渡效果', '150', '页面过渡效果', 'select', 'pageTransition', '', '[{\"label\":\"无动画\",\"value\":\"\"},{\"label\":\"淡入淡出\",\"value\":\"fade\"},{\"label\":\"左侧滑入\",\"value\":\"slide-left\"},{\"label\":\"下方滑入\",\"value\":\"slide-bottom\"},{\"label\":\"上方滑入\",\"value\":\"slide-top\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"页面过渡效果\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:33:42', '1988-6-15 22:53:07');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '菜单展开宽度', '150', '菜单展开宽度', 'number', 'menuOpenWidth', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"菜单展开宽度\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 21:54:52', '1988-6-15 22:43:42');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '自定义圆角', '150', '自定义圆角', 'number', 'customRadius', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"自定义圆角\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:35:18', '1988-6-15 22:51:18');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '菜单是否展开', '150', '菜单是否展开', 'switch', 'menuOpen', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"菜单是否展开\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 21:55:43', '1988-6-15 23:11:24');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '双菜单是否显示文本', '150', '双菜单是否显示文本', 'switch', 'dualMenuShowText', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"双菜单是否显示文本\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 21:56:59', '1988-6-15 23:11:28');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示菜单按钮', '150', '是否显示菜单按钮', 'switch', 'showMenuButton', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示菜单按钮\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:15:50', '1988-6-15 23:11:45');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示快速入口', '150', '是否显示快速入口', 'switch', 'showFastEnter', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示快速入口\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:16:08', '1988-6-15 23:11:49');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示刷新按钮', '150', '是否显示刷新按钮', 'switch', 'showRefreshButton', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示刷新按钮\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:16:30', '1988-6-15 22:54:45');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示面包屑', '150', '是否显示面包屑', 'switch', 'showCrumbs', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示面包屑\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:16:45', '1988-6-15 22:54:24');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示工作台标签', '150', '是否显示工作台标签', 'switch', 'showWorkTab', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示工作台标签\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:16:59', '1988-6-15 22:16:59');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示语言切换', '150', '是否显示语言切换', 'switch', 'showLanguage', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示语言切换\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:17:14', '1988-6-15 22:54:31');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示进度条', '150', '是否显示进度条', 'switch', 'showNprogress', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示进度条\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:17:40', '1988-6-15 22:54:21');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示设置引导', '150', '是否显示设置引导', 'switch', 'showSettingGuide', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示设置引导\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:17:57', '1988-6-15 22:54:00');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示节日文本', '150', '是否显示节日文本', 'switch', 'showFestivalText', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示节日文本\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:18:20', '1988-6-15 22:54:58');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否显示水印', '150', '是否显示水印', 'switch', 'watermarkVisible', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否显示水印\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:18:38', '1988-6-15 22:18:38');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否自动关闭', '150', '是否自动关闭', 'switch', 'autoClose', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否自动关闭\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:18:51', '1988-6-15 22:55:02');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否唯一展开', '150', '是否唯一展开', 'switch', 'uniqueOpened', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否唯一展开\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:19:05', '1988-6-15 22:55:05');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否色弱模式', '150', '是否色弱模式', 'switch', 'colorWeak', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否色弱模式\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:19:26', '1988-6-15 22:55:07');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否刷新', '150', '是否刷新', 'switch', 'refresh', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否刷新\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:19:42', '1988-6-15 22:19:42');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '是否加载节日烟花', '150', '是否加载节日烟花', 'switch', 'holidayFireworksLoaded', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"是否加载节日烟花\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:21:03', '1988-6-15 22:21:03');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '边框模式', '150', '边框模式', 'switch', 'boxBorderMode', '', '[]', '', 12, 0, 0, '{\n  \"placeholder\": \"边框模式\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:21:33', '1988-6-15 22:21:33');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'systemTheme', '节日日期', '150', '节日日期', 'input', 'festivalDate', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"节日日期\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 22:36:55', '1988-6-15 22:51:21');

INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'SMTP邮件', '130', 'SMTP邮件', 'divider', 'divder', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"SMTP邮件\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 08:56:29', '1988-6-15 08:56:29');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', '是否启用', '130', '是否启用', 'switch', 'smtp_type', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"是否启用\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 08:57:11', '1988-6-15 08:57:11');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'SMTP服务器', '130', 'SMTP服务器', 'input', 'smtp_ip', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"SMTP服务器\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 08:57:35', '1988-6-15 08:57:35');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'SMTP端口', '130', 'SMTP端口', 'input', 'smtp_port', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"SMTP端口\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 08:57:56', '1988-6-15 08:57:56');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'SMTP用户名', '130', 'SMTP用户名', 'input', 'smtp_username', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"SMTP用户名\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 08:58:22', '1988-6-15 08:58:22');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'SMTP密码', '130', 'SMTP密码', 'input', 'smtp_password', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"SMTP密码\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 08:58:40', '1988-6-15 08:58:40');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', '验证方式', '130', 'SMTP验证方式', 'select', 'smtp_secure', '', '[{\"label\":\"无\",\"value\":\"\"},{\"label\":\"SSL\",\"value\":\"ssl\"},{\"label\":\"TSL\",\"value\":\"tsl\"}]', '', 24, 0, 1, '{\n  \"placeholder\": \"SMTP验证方式\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 08:59:28', '1988-6-15 09:42:25');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', '默认发件人', '130', '用于发送邮件显示，默认为管理员。', 'input', 'smtp_from', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"用于发送邮件显示，默认为管理员。\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:00:08', '1988-6-15 09:00:08');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', '测试邮箱', '130', '请输入SMTP邮件测试邮箱', 'input', 'smtp_test', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"请输入SMTP邮件测试邮箱\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:00:36', '1988-6-15 09:00:36');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', '短信SMS接口', '130', '短信SMS接口', 'divider', 'divder', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"短信SMS接口\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:01:43', '1988-6-15 09:03:45');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', '是否启用', '130', '是否启用', 'select', 'sms_type', '', '[{\"label\":\"关闭\",\"value\":\"0\"},{\"label\":\"阿里SMS短信\",\"value\":\"1\"},{\"label\":\"腾讯SMS短信\",\"value\":\"2\"}]', '', 24, 0, 0, '{\n  \"placeholder\": \"是否启用\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:05:00', '1988-6-15 09:05:00');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'ACCESS_ID', '130', '阿里云-AccessKey管理-AccessKey ID', 'input', 'sms_aliyun_access_key_id', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"阿里云-AccessKey管理-AccessKey ID\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:06:13', '1988-6-15 09:06:13');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'ACCESS_SECRET', '130', '阿里云-AccessKey管理-ACCESS_SECRET', 'input', 'sms_aliyun_access_key_secret', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"阿里云-AccessKey管理-ACCESS_SECRET\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:07:17', '1988-6-15 09:07:17');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', '签名名称', '130', '阿里云-签名管理-签名名称复制到这里', 'input', 'sms_aliyun_sign_name', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"阿里云-签名管理-签名名称复制到这里\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:07:44', '1988-6-15 09:07:44');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'SDK_APP_ID', '130', '腾讯SMS短信APP_ID', 'input', 'sms_qcloud_sdk_app_id', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"腾讯SMS短信APP_ID\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:08:18', '1988-6-15 09:08:18');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'SECRET_ID', '130', '腾讯SMS短信SECRET_ID', 'input', 'sms_qcloud_secret_id', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"腾讯SMS短信SECRET_ID\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:08:51', '1988-6-15 09:08:51');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'SECRET_KEY', '130', '腾讯SMS短信SECRET_KEY', 'input', 'sms_qcloud_secret_key', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"腾讯SMS短信SECRET_KEY\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:09:17', '1988-6-15 09:09:17');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', '签名名称', '130', '腾讯SMS短信APP_SIGN', 'input', 'sms_qcloud_sign_name', '', '[]', '', 24, 0, 1, '{\n  \"placeholder\": \"腾讯SMS短信APP_SIGN\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:09:42', '1988-6-15 09:09:42');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', '微信登录', '130', '微信登录', 'divider', 'divder', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"微信登录\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:21:34', '1988-6-15 09:21:34');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'APP_ID', '130', 'APP_ID', 'input', 'login_wechat_appid', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"APP_ID\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:22:05', '1988-6-15 09:22:05');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'APP_KEY', '130', 'APP_KEY', 'input', 'login_wechat_appkey', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"APP_KEY\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:22:24', '1988-6-15 09:22:24');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'callback地址', '130', 'callback地址', 'input', 'login_wechat_callback', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"callback地址\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:22:52', '1988-6-15 09:22:52');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'QQ登录', '130', 'QQ登录', 'divider', 'divder', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"QQ登录\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:23:19', '1988-6-15 09:23:19');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'APP_ID', '130', 'APP_ID', 'input', 'login_qq_appid', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"APP_ID\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:23:45', '1988-6-15 09:23:45');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'APP_KEY', '130', 'APP_KEY', 'input', 'login_qq_appkey', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"APP_KEY\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:24:03', '1988-6-15 09:24:03');
INSERT INTO `__PREFIX__confset` VALUES (NULL, 'system', 'interfaceConfig', 'callback地址', '130', 'callback地址', 'input', 'login_qq_callback', '', '[]', '', 24, 0, 0, '{\n  \"placeholder\": \"callback地址\",\n  \"clearable\": true,\n  \"tooltip\": true\n}', 1, '1988-6-15 09:24:23', '1988-6-15 09:24:23');

UNLOCK TABLES;
