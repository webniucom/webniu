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
