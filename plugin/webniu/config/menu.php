<?php
return [
    [
        'path' => 'admin',
        'key' => 'admin',
        'title' => '后台管理',
        'type' => 0,
        'icon' => 'ri:list-view',
        'component' => '/admin/index/index',
        'children' => [
            [
                'path' => 'console',
                'key' => 'console',
                'title' => '控制台',
                'type' => 0,
                'icon' => 'ri:pie-chart-line',
                'component' => '',
                'children' => [
                    [
                        'path' => 'dashboard',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\DashboardController',
                        'title' => '数据面板',
                        'type' => 1,
                        'icon' => 'ri:dashboard-line',
                        'component' => '/admin/dashboard/console',
                    ],
                ],
            ],
            [
                'path' => 'basicsite',
                'key' => 'basicsite',
                'title' => '核心设置',
                'type' => 0,
                'icon' => 'ri:settings-2-line',
                'component' => '',
                'children' => [
                    [
                        'path' => 'siteconfig',
                        'key' => 'plugin\\webniu\\app\\web\\controller\\ConfigController',
                        'title' => '系统设置',
                        'type' => 1,
                        'icon' => 'ri:function-ai-line',
                        'component' => '/admin/system/config/index',
                    ],
                    [
                        'path' => 'menu',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\RuleController',
                        'title' => '菜单管理',
                        'type' => 1,
                        'icon' => 'ri:menu-fill',
                        'component' => '/admin/system/menu'
                    ],
                ],
            ],
            [
                'path' => 'general',
                'key' => 'general',
                'title' => '通用设置',
                'type' => 0,
                'icon' => 'ri:side-bar-line',
                'component' => '',
                'children' => [
                    [
                        'path' => 'upload',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\UploadController',
                        'title' => '附件管理',
                        'type' => 1,
                        'icon' => 'ri:share-2-line',
                        'component' => '/admin/system/upload/index'
                    ],
                    [
                        'path' => 'confset',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\ConfsetController',
                        'title' => '配置管理',
                        'type' => 1,
                        'icon' => 'ri:booklet-line',
                        'component' => '/admin/system/confset/index'
                    ],
                    [
                        'path' => 'article',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\ArticleController',
                        'title' => '系统公告',
                        'type' => 1,
                        'icon' => 'ri:function-ai-line',
                        'component' => '/admin/system/config/demo',
                    ],
                    [
                        'path' => 'emailtemp',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\EmailTempController',
                        'title' => '邮件模板',
                        'type' => 1,
                        'icon' => 'ri:function-ai-line',
                        'component' => '/admin/system/nested/menu3',
                    ],
                    [
                        'path' => 'smstemp',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\SmsTempController',
                        'title' => '短信模板',
                        'type' => 1,
                        'icon' => 'ri:function-ai-line',
                        'component' => '/admin/system/nested/menu3',
                    ],
                    [
                        'path' => 'dict',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\DictController',
                        'title' => '字典管理',
                        'type' => 1,
                        'icon' => 'ri:function-ai-line',
                        'component' => '/admin/system/dict/index'
                    ],
                ],
            ],
            [
                'path' => 'accpower',
                'key' => 'accpower',
                'title' => '账户管理',
                'type' => 0,
                'icon' => 'ri:group-line',
                'component' => '',
                'children' => [
                    [
                        'path' => 'role',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\RoleController',
                        'title' => '权限管理',
                        'type' => 1,
                        'icon' => 'ri:admin-line',
                        'component' => '/admin/system/role'
                    ],
                    [
                        'path' => 'account',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\AccountController',
                        'title' => '基本资料',
                        'type' => 1,
                        'icon' => 'ri:contacts-line',
                        'component' => '/admin/system/user-center'
                    ],
                    [
                        'path' => 'adminlist',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\AdminController',
                        'title' => '用户管理',
                        'type' => 1,
                        'icon' => 'ri:user-star-line',
                        'component' => '/admin/system/user'
                    ],
                    [
                        'path' => 'logtime',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\LogtimeController',
                        'title' => '登录日志',
                        'type' => 1,
                        'icon' => 'ri:book-shelf-line',
                        'component' => '/admin/system/nested/menu3',
                    ],
                ],
            ],
            [
                'path' => 'user',
                'key' => 'user',
                'title' => '会员管理',
                'type' => 0,
                'icon' => 'ri:user-3-line',
                'component' => '',
                'children' => [
                    [
                        'path' => 'userlist',
                        'key' => 'plugin\\webniu\\app\\admin\\controller\\UserController',
                        'title' => '会员列表',
                        'type' => 1,
                        'icon' => 'ri:user-line',
                        'component' => '/admin/system/nested/menu3',
                    ]
                ]
            ]
        ]
    ]
];
