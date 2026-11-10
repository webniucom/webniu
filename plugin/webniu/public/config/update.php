<?php
// 1.2.2.2 插件规则
if (pdo_hasTable('rules')) {
    if (!pdo_hasColumn('rules', 'plugin')) {
        pdo_unprepared("
            ALTER TABLE ".pdo_tablename('rules')." ADD COLUMN `plugin` varchar(255) NOT NULL AFTER `model`;
        ");
    }
    if (!pdo_hasColumn('rules', 'open')) {
        pdo_unprepared("
            ALTER TABLE ".pdo_tablename('rules')." ADD COLUMN `open` int NULL DEFAULT 0 AFTER `href`;
        ");
    }
}

// 1.3.1.2 会员余额/积分变动记录
if (pdo_hasTable('users') && pdo_hasColumn('users', 'money')) {
    // 老版本 money 为整数，统一升级为两位小数（单位：元）
    pdo_unprepared("
        ALTER TABLE ".pdo_tablename('users')." MODIFY COLUMN `money` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '余额(元)';
    ");
}
if (!pdo_hasTable('user_finance_log')) {
    pdo_unprepared("
        CREATE TABLE IF NOT EXISTS `".pdo_tablename('user_finance_log')."` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `uid` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
          `type` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '1=余额 2=积分',
          `change` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '变动值，正增负减',
          `before` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '变动前',
          `after` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '变动后',
          `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
          `source` varchar(20) NOT NULL DEFAULT 'admin' COMMENT '来源 admin/lottery/system',
          `operator_id` int unsigned NOT NULL DEFAULT 0 COMMENT '操作人ID，0=系统',
          `biz_key` varchar(64) DEFAULT NULL COMMENT '业务唯一键，用于幂等，如 lottery:prizelog:123',
          `created_at` datetime DEFAULT NULL,
          `updated_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uk_biz_key` (`biz_key`),
          KEY `idx_uid_type` (`uid`, `type`),
          KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员余额/积分变动记录';
    ");
}
if (pdo_hasTable('user_finance_log') && !pdo_hasColumn('user_finance_log', 'biz_key')) {
    // 已建表的老版本补充幂等字段
    pdo_unprepared("
        ALTER TABLE ".pdo_tablename('user_finance_log')."
            ADD COLUMN `biz_key` varchar(64) DEFAULT NULL COMMENT '业务唯一键，用于幂等，如 lottery:prizelog:123' AFTER `operator_id`,
            ADD UNIQUE KEY `uk_biz_key` (`biz_key`);
    ");
}

// 1.3.1.3 管理员归属字段（记录账户由谁创建，用于非超管的账户列表可见性）
if (pdo_hasTable('admins') && !pdo_hasColumn('admins', 'admin_id')) {
    pdo_unprepared("
        ALTER TABLE ".pdo_tablename('admins')."
            ADD COLUMN `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '创建人(归属管理员ID)' AFTER `id`,
            ADD KEY `idx_admin_id` (`admin_id`);
    ");
}

// 1.3.1.4 管理员可用应用（超管分配的应用上限，供子账户建分组/公众号时选择）
if (pdo_hasTable('admins') && !pdo_hasColumn('admins', 'appset')) {
    pdo_unprepared("
        ALTER TABLE ".pdo_tablename('admins')."
            ADD COLUMN `appset` varchar(500) DEFAULT NULL COMMENT '可用应用(插件标识,逗号分隔;NULL=全部,空=无)' AFTER `admin_id`;
    ");
}

