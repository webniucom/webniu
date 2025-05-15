CREATE TABLE IF NOT EXISTS `__PREFIX__admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `nickname` varchar(40) NOT NULL COMMENT '昵称',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `avatar` varchar(255) DEFAULT '/app/webniu/skins/admin/images/avatar.png' COMMENT '头像',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(16) DEFAULT NULL COMMENT '手机',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `login_at` datetime DEFAULT NULL COMMENT '登录时间',
  `status` tinyint(4) DEFAULT NULL COMMENT '禁用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员表';

CREATE TABLE IF NOT EXISTS `__PREFIX__admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(32) NOT NULL COMMENT '账号',
  `nickname` varchar(40) DEFAULT NULL COMMENT '用户昵称',
  `user_ip` varchar(50) NOT NULL COMMENT '用户 IP',
  `user_agent` varchar(512) DEFAULT NULL COMMENT '浏览器 UA',
  `user_os` varchar(255) DEFAULT NULL COMMENT '操作系统',
  `user_browser` varchar(120) DEFAULT NULL COMMENT '浏览器',
  `admin_id` int(11) DEFAULT NULL COMMENT '身份',
  `error` varchar(255) DEFAULT NULL COMMENT '错误信息',
  `status` tinyint(4) DEFAULT NULL COMMENT '登录状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='登录日志';

CREATE TABLE IF NOT EXISTS `__PREFIX__admin_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `role_id` int(11) NOT NULL COMMENT '角色id',
  `admin_id` int(11) NOT NULL COMMENT '管理员id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_admin_id` (`role_id`,`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员角色表';

CREATE TABLE IF NOT EXISTS `__PREFIX__article` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `cateid` int(11) DEFAULT NULL COMMENT '分类ID',
  `title` varchar(254) DEFAULT NULL COMMENT '文章标题',
  `source` varchar(254) DEFAULT NULL COMMENT '来源',
  `author` varchar(49) DEFAULT NULL COMMENT '作者',
  `displayorder` int(10) DEFAULT '0' COMMENT '排序',
  `click` int(10) DEFAULT '0' COMMENT '阅读次数',
  `status` int(4) DEFAULT NULL COMMENT '状态',
  `content` text COMMENT '文章内容',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='文章公告';

CREATE TABLE IF NOT EXISTS `__PREFIX__article_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) DEFAULT NULL COMMENT '分类名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='公告分类';

CREATE TABLE IF NOT EXISTS `__PREFIX__email_temp`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT '0' COMMENT '平台ID',
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  `from` varchar(255) DEFAULT NULL COMMENT '发件人',
  `subject` varchar(255) DEFAULT NULL COMMENT '主题',
  `content` text COMMENT '内容',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='email模板';

CREATE TABLE IF NOT EXISTS `__PREFIX__options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `model` varchar(255) DEFAULT NULL COMMENT '模型',
  `group` varchar(100) DEFAULT NULL COMMENT '分组',
  `name` varchar(255) DEFAULT NULL COMMENT '键',
  `value` longtext COMMENT '值',
  `created_at` datetime NOT NULL DEFAULT '1988-06-15 00:00:00' COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT '1988-06-15 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='选项表';

CREATE TABLE IF NOT EXISTS `__PREFIX__plugin`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `plugin_type` varchar(32) DEFAULT NULL COMMENT '类型',
  `plugin_class` int(3) DEFAULT '0' COMMENT '分类id',
  `plugin_name` varchar(255) DEFAULT NULL COMMENT '应用标题',
  `plugin_desc` varchar(255) DEFAULT NULL COMMENT '描述',
  `plugin_author` varchar(100) DEFAULT NULL COMMENT '作者',
  `plugin_identifier` varchar(100) DEFAULT NULL COMMENT '应用标识',
  `plugin_logo` varchar(255) DEFAULT NULL COMMENT '应用图标',
  `plugin_icon` varchar(255) DEFAULT NULL COMMENT 'icon图标',
  `plugin_href` varchar(255) DEFAULT NULL COMMENT '应用入口',
  `plugin_open` varchar(50) DEFAULT NULL COMMENT '打开方式',
  `version` varchar(255) DEFAULT '0' COMMENT '当前版本',
  `installed` int(2) DEFAULT '0' COMMENT '是否安装',
  `disabled` int(2) DEFAULT '0' COMMENT '是否禁用',
  `jump` int(3) DEFAULT '0' COMMENT '跳转入口',
  `releases` varchar(255) DEFAULT '0' COMMENT '历史版本',
  `created_at` datetime DEFAULT NULL COMMENT '插入时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `installed` (`installed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='应用插件';

CREATE TABLE IF NOT EXISTS `__PREFIX__roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(80) NOT NULL COMMENT '角色组',
  `rules` text COMMENT '权限',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `pid` int(10) unsigned DEFAULT NULL COMMENT '父级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员角色';

CREATE TABLE IF NOT EXISTS `__PREFIX__rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `plugin` varchar(100) DEFAULT NULL COMMENT '插件',
  `name` varchar(100) DEFAULT NULL COMMENT '名称标识',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `key` varchar(255) NOT NULL COMMENT '标识',
  `pid` int(10) unsigned DEFAULT '0' COMMENT '上级菜单',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `href` varchar(255) DEFAULT NULL COMMENT 'url',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '类型',
  `opentype` varchar(100) DEFAULT '_iframe' COMMENT '打开方式',
  `weight` int(11) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `plugin_pid` (`plugin`,`pid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='权限规则';

CREATE TABLE IF NOT EXISTS `__PREFIX__sms_temp`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT '0' COMMENT '平台ID',
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  `template_id` varchar(255) DEFAULT NULL COMMENT '模板id',
  `sign` varchar(255) DEFAULT NULL COMMENT '签名',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='短信模板';

CREATE TABLE IF NOT EXISTS `__PREFIX__statistics`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `model` varchar(32) NOT NULL COMMENT '模型',
  `count` varchar(40) DEFAULT '0' COMMENT '数量',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `mc` (`model`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='数据统计';

CREATE TABLE IF NOT EXISTS `__PREFIX__uploads` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(128) NOT NULL COMMENT '名称',
  `url` varchar(255) NOT NULL COMMENT '文件',
  `origin_name` varchar(255) DEFAULT NULL COMMENT '原名称',
  `unique_id` varchar(100) DEFAULT NULL COMMENT '加密ID',
  `admin_id` int(11) DEFAULT NULL COMMENT '管理员',
  `save_path` varchar(255) DEFAULT NULL COMMENT '保存路径',
  `file_size` int(11) DEFAULT NULL COMMENT '文件大小',
  `mime_type` varchar(255) DEFAULT NULL COMMENT 'mime类型',
  `image_width` int(11) DEFAULT NULL COMMENT '图片宽度',
  `image_height` int(11) DEFAULT NULL COMMENT '图片高度',
  `ext` varchar(128) DEFAULT NULL COMMENT '扩展名',
  `storage` varchar(255) DEFAULT 'local' COMMENT '存储位置',
  `category` varchar(128) DEFAULT NULL COMMENT '类别',
  `created_at` date DEFAULT NULL COMMENT '上传时间',
  `updated_at` date DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `admin_id` (`admin_id`),
  KEY `name` (`name`),
  KEY `ext` (`ext`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='附件';

CREATE TABLE IF NOT EXISTS `__PREFIX__users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `nickname` varchar(40) NOT NULL COMMENT '昵称',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `sex` enum('0','1') NOT NULL DEFAULT '1' COMMENT '性别',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `email` varchar(128) DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(16) DEFAULT NULL COMMENT '手机',
  `level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '等级',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额(元)',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `last_time` datetime DEFAULT NULL COMMENT '登录时间',
  `last_ip` varchar(50) DEFAULT NULL COMMENT '登录ip',
  `join_time` datetime DEFAULT NULL COMMENT '注册时间',
  `join_ip` varchar(50) DEFAULT NULL COMMENT '注册ip',
  `token` varchar(50) DEFAULT NULL COMMENT 'token',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `role` int(11) NOT NULL DEFAULT '1' COMMENT '角色',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '禁用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `join_time` (`join_time`),
  KEY `mobile` (`mobile`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户表';

LOCK TABLES `__PREFIX__options` WRITE;
INSERT INTO `__PREFIX__options` VALUES (NULL,'dict','dict', 'dict_upload','[{\"value\":\"0\",\"name\":\"无分类\"},{\"value\":\"1\",\"name\":\"图片\"},{\"value\":\"2\",\"name\":\"媒体\"},{\"value\":\"3\",\"name\":\"文件\"}]','1988-06-15 23:59:59', '1988-06-15 23:59:59');
INSERT INTO `__PREFIX__options` VALUES (NULL,'dict','dict', 'dict_sex','[{\"value\":\"0\",\"name\":\"女\"},{\"value\":\"1\",\"name\":\"男\"}]','1988-06-15 23:59:59', '1988-06-15 23:59:59');
INSERT INTO `__PREFIX__options` VALUES (NULL,'dict','dict', 'dict_status','[{\"value\":\"1\",\"name\":\"正常\"},{\"value\":\"0\",\"name\":\"禁用\"}]','1988-06-15 23:59:59', '1988-06-15 23:59:59');
INSERT INTO `__PREFIX__options` VALUES (NULL,'dict','dict', 'dict_dict_name','[{\"value\":\"dict_name\",\"name\":\"字典名称\"},{\"value\":\"status\",\"name\":\"启禁用状态\"},{\"value\":\"sex\",\"name\":\"性别\"},{\"value\":\"upload\",\"name\":\"附件分类\"}]','1988-06-15 23:59:59', '1988-06-15 23:59:59');
UNLOCK TABLES;
 
LOCK TABLES `__PREFIX__roles` WRITE;
INSERT INTO `__PREFIX__roles` VALUES (1,'超级管理员','*','2022-08-13 16:15:01','2022-12-23 12:05:07',NULL);
UNLOCK TABLES;

LOCK TABLES `__PREFIX__article_category` WRITE;
INSERT INTO `__PREFIX__article_category` VALUES (NULL,'系统公告');
UNLOCK TABLES;

LOCK TABLES `__PREFIX__plugin` WRITE;
INSERT INTO `__PREFIX__plugin` VALUES (NULL,'webniu','1','网牛引擎','网牛引擎','webniu','webniu','/app/webniu/avatar.png',NULL,'/app/webniu/web/index/dashboard','0','1.0.2',1,0,0,'1.0.2','1988-06-15 23:59:59', '1988-06-15 23:59:59');
UNLOCK TABLES;