<?php

return [
    [
        'title' => '后台管理',
        'key'   => 'adminmanage',
        'icon'  => 'layui-icon-template-1',
        'weight' => 1000,
        'type'  => 0,
        'children' => [
            [
                'title' => '控制台',
                'key'   => 'console',
                'type' => 0,
                'icon' => 'layui-icon-console',
                'href' => '',
                'children' => [
                    [
                        'title' => '控制台',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\IndexController',
                        'icon' => '',
                        'type' => 1,
                        'href' => '/app/webniu/web/index/dashboard',
                        'weight' => 0,
                    ]
                ]
            ],
            [
                'title' => '核心设置',
                'key'   => 'basicsetting',
                'type' => 0,
                'icon' => 'layui-icon-set',
                'href' => '',
                'children' => [
                    [
                        'title' => '系统设置',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\ConfigController',
                        'icon' => '',
                        'href' => '/app/webniu/web/config/index',
                        'type' => 1,
                        'weight' => 0,
                    ],
                    [
                        'title' => '菜单管理',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\RuleController',
                        'icon' => '',
                        'href' => '/app/webniu/web/rule/index',
                        'type' => 1,
                        'weight' => 0,
                    ]
                ]
            ],
            [
                'title' => '通用设置',
                'key'   => 'general',
                'type' => 0,
                'icon' => 'layui-icon-set',
                'href' => '',
                'children' => [
                    [
                        'title' => '附件管理',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\UploadController',
                        'icon' => '',
                        'href' => '/app/webniu/web/upload/index',
                        'type' => 1,
                        'weight' => 0,
                    ],
                    [
                        'title' => '字典设置',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\DictController',
                        'icon' => '',
                        'href' => '/app/webniu/web/dict/index',
                        'type' => 1,
                        'weight' => 0,
                    ],
                    [
                        'title' => '系统公告',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\ArticleController',
                        'icon' => '',
                        'href' => '/app/webniu/web/article/index',
                        'type' => 1,
                        'weight' => 0,
                    ],
                    [
                        'title' => '邮件模板',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\EmailTempController',
                        'icon' => '',
                        'href' => '/app/webniu/web/emailtemp/index',
                        'type' => 1,
                        'weight' => 0,
                    ],
                    [
                        'title' => '短信模板',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\SmsTempController',
                        'icon' => '',
                        'href' => '/app/webniu/web/smstemp/index',
                        'type' => 1,
                        'weight' => 0,
                    ]
                ]
            ],
            [
                'title' => '账户管理',
                'key'   => 'accpower',
                'type' => 0,
                'icon' => 'layui-icon-username',
                'href' => '',
                'children' => [
                    [
                        'title' => '账户管理',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\AdminController',
                        'icon' => '',
                        'href' => '/app/webniu/web/admin/index',
                        'type' => 1,
                        'weight' => 0,
                    ],
                    [
                        'title' => '权限管理',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\RoleController',
                        'icon' => '',
                        'href' => '/app/webniu/web/role/index',
                        'type' => 1,
                        'weight' => 0,
                    ],
                    [
                        'title' => '基本资料',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\AccountController',
                        'icon' => '',
                        'href' => '/app/webniu/web/account/index',
                        'type' => 1,
                        'weight' => 0,
                    ],
                    [
                        'title' => '登录日志',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\LogtimeController',
                        'icon' => '',
                        'href' => '/app/webniu/web/logtime/index',
                        'type' => 1,
                        'weight' => 0,
                    ]
                ]
            ],
            [
                'title' => '会员管理',
                'key'   => 'user',
                'type' => 0,
                'icon' => 'layui-icon-user',
                'href' => '',
                'children' => [
                    [
                        'title' => '用户列表',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\UserController',
                        'icon' => '',
                        'href' => '/app/webniu/web/user/index',
                        'type' => 1,
                        'weight' => 0,
                    ] 
                ]
            ]
        ]
    ],
    [
        'title' => '应用插件',
        'key'   => 'apps',
        'icon'  => 'layui-icon-app',
        'weight' => 999,
        'type'  => 0,
        'children' => [
            [
                'title' => '应用管理',
                'key'   => 'appmanage',
                'type' => 0,
                'icon' => 'layui-icon-app',
                'href' => '',
                'weight' => 887,
                'children' => [
                    [
                        'title' => '应用列表',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\PluginController',
                        'icon' => '',
                        'href' => '/app/webniu/web/plugin/index',
                        'type' => 1,
                        'weight' => 0,
                    ],
                    [
                        'title' => '权限分组',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\AppRoleController',
                        'icon' => '',
                        'href' => '/app/webniu/web/approle/index',
                        'type' => 1,
                        'weight' => 0,
                    ]
                ]
            ]
            
        ]   
    ],
    [
        'title' => '开发帮助',
        'key'   => 'devhelp',
        'icon'  => 'layui-icon-fonts-code',
        'weight' => 0,
        'type'  => 0,
        'children' => [
            [
                'title' => '数据库',
                'key' => 'plugin\\webniu\\app\\web\\controller\\TableController',
                'icon' => 'layui-icon-component',
                'href' => '/app/webniu/web/table/index',
                'type' => 1,
                'weight' => 0,
            ]
        ]   
    ]
];
