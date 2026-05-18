<?php

namespace plugin\webniu\app\admin\controller;

use support\exception\BusinessException;
use Symfony\Component\Process\Process;
use support\Request;
use support\Response;
use Throwable;

/**
 * 用户管理 
 */
class ApiController extends Crud
{

    /**
     * 不需要登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['login', 'info', 'userlist', 'rolelist', 'menus', 'config', 'build'];

    /**
     * 登录
     * @return Response
     * @throws Throwable
     */
    public function login(): Response
    {
        return $this->json(200, 'success', [
            'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9',
            'refreshToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9',
        ]);
    }

    /**
     * 获取用户信息
     * @return Response
     * @throws Throwable
     */
    public function info(): Response
    {
        return $this->json(200, 'success', [
            "buttons" => ["btn.add", "btn.edit", "btn.delete"],
            "roles" => ["admin"],
            "userId" => 1,
            "userName" => "admin",
            "email" => "admin@example.com",
            "avatar" => "https://example.com/avatar.jpg",
            "fastEnter" => [
                "minWidth" => 1200,
                "applications" => [
                    [
                        "name" => '工作台',
                        "description" => '系统概览与数据统计',
                        "icon" => 'ri:pie-chart-line',
                        "iconColor" => '#377dff',
                        "enabled" => true,
                        "order" => 1,
                        "routeName" => 'Console'
                    ],
                    [
                        "name" => '工作台',
                        "description" => '系统概览与数据统计',
                        "icon" => 'ri:pie-chart-line',
                        "iconColor" => '#377dff',
                        "enabled" => true,
                        "order" => 1,
                        "routeName" => 'Console'
                    ]
                ]
            ],
        ]);
    }

    /**
     * 登录
     * @return Response
     * @throws Throwable
     */
    public function config(): Response
    {
        return $this->json(200, 'success', [
            "systemInfo" => [
                "name" => "Webniu 网牛引擎",
                "description" => "基于webman框架开发的后台管理系统",
                "logo" => "http://127.0.0.1:8787/app/webniu/images/avatar.png",
                "favicon" => "http://127.0.0.1:8787/app/webniu/images/favicon.ico",
                "copyright" => "Powered by webniu.com © 2010-2026.",
                "footerTxt" => "一款兼具设计美学与高效开发的后台系统",
            ],
            "systemSetting" => [
                "isRegister" => true,
                "isCaptcha" => false,
                "forgetPassword" => true,
                "isInstall" => true,
            ],
            "systemPath" => [
                "menuList" => '/admin/rule/get',
            ],
            "systemTheme" => [
                "menuType" => "dual-menu",//
                "menuOpenWidth" => 230,
                "menuOpen" => true,
                "dualMenuShowText" => true,
                "systemThemeType" => "auto",//
                "systemThemeMode" => "auto",
                "menuThemeType" => "design",//
                "systemThemeColor" => '#5D87FF',
                "showMenuButton" => true,
                "showFastEnter" => true,
                "showRefreshButton" => true,
                "showCrumbs" => true,
                "showWorkTab" => true,
                "showLanguage" => true,
                "showNprogress" => true,
                "showSettingGuide" => false,
                "showFestivalText" => false,
                "watermarkVisible" => false,
                "autoClose" => false,
                "uniqueOpened" => true,
                "colorWeak" => false,
                "refresh" => false,
                "holidayFireworksLoaded" => false,
                "boxBorderMode" => true,
                "pageTransition" => 'slide-left',
                "tabStyle" => 'tab-default',
                "customRadius" => '0.75',
                "containerWidth" => "100%",
                "festivalDate" => '',
            ],
        ]);
    }

    /**
     * 获取用户列表
     * @return Response
     * @throws Throwable
     */
    public function userlist(): Response
    {
        return $this->json(200, 'success', [
            'records' => [
                [
                    'id' => 1,
                    'createBy' => '梁秀英',
                    'createTime' => '2007-10-19 15:26:31',
                    'updateBy' => '许艳',
                    'updateTime' => '1983-07-24 11:20:20',
                    'status' => '3',
                    'userName' => 'Shirley',
                    'userGender' => '女',
                    'nickName' => '吕霞',
                    'userPhone' => '16130790744',
                    'userEmail' => 'j.ofzvbcbea@mokwwmpqg.cm',
                    'userRoles' => [
                        'R_ADMIN',
                    ],
                ],
                [
                    'id' => 2,
                    'createBy' => '尹洋',
                    'createTime' => '1971-06-15 08:41:22',
                    'updateBy' => '邱伟',
                    'updateTime' => '2015-06-02 11:58:56',
                    'status' => '4',
                    'userName' => 'Michael',
                    'userGender' => '男',
                    'nickName' => '姚刚',
                    'userPhone' => '14857507317',
                    'userEmail' => 'u.gukqvq@brctnlvlg.nf',
                    'userRoles' => [
                        'R_SUPER',
                    ],
                ],
                [
                    'id' => 2,
                    'createBy' => '尹洋',
                    'createTime' => '1971-06-15 08:41:22',
                    'updateBy' => '邱伟',
                    'updateTime' => '2015-06-02 11:58:56',
                    'status' => '4',
                    'userName' => 'Michael',
                    'userGender' => '男',
                    'nickName' => '姚刚',
                    'userPhone' => '14857507317',
                    'userEmail' => 'u.gukqvq@brctnlvlg.nf',
                    'userRoles' => [
                        'R_USER',
                    ],
                ]
            ],
            'current' => 1,
            'size' => 20,
            'total' => 200,
            '_debug' => [
                'totalPages' => 10,
                'isLastPage' => false,
                'currentPageSize' => 20,
                'startIndex' => 0,
            ],
        ]);
    }

    /**
     * 获取角色列表
     * @return Response
     * @throws Throwable
     */
    public function rolelist(): Response
    {
        return $this->json(200, 'success', [
            'records' => [
                [
                    'roleId' => 1,
                    'roleName' => '运维',
                    'roleCode' => 'R_OPS',
                    'description' => '管理营销活动权限',
                    'enabled' => true,
                    'createTime' => '2018-06-21 11:11:18',
                ],
                [
                    'roleId' => 2,
                    'roleName' => '普通用户',
                    'roleCode' => 'R_FINANCE',
                    'description' => '拥有数据分析权限',
                    'enabled' => false,
                    'createTime' => '1978-06-02 14:48:47',
                ],
                [
                    'roleId' => 3,
                    'roleName' => '超级管理员',
                    'roleCode' => 'R_GUEST',
                    'description' => '管理营销活动权限',
                    'enabled' => true,
                    'createTime' => '2006-12-04 01:16:46',
                ]
            ],
            'current' => 1,
            'size' => 20,
            'total' => 100,
            '_debug' => [
                'totalPages' => 5,
                'isLastPage' => false,
                'currentPageSize' => 20,
                'startIndex' => 0,
            ],
        ]);
    }

    /**
     * 获取菜单
     * @return Response
     * @throws Throwable
     */
    public function menus(): Response
    {
        return $this->json(
            200,
            'success',
            [
                [
                    'name' => 'Dashboard',
                    'path' => 'dashboard',
                    'component' => '/admin/index/index',
                    'meta' => [
                        'title' => 'menus.dashboard.title',
                        'icon' => 'ri:pie-chart-line',
                    ],
                    'children' => [
                        [
                            'path' => 'console',
                            'name' => 'Console',
                            'component' => '/admin/dashboard/console',
                            'meta' => [
                                'title' => 'menus.dashboard.console',
                                'icon' => 'ri:home-smile-2-line',
                                'keepAlive' => false,
                                'fixedTab' => true,
                            ],
                        ],
                    ],
                ],
                [
                    'path' => 'system',
                    'name' => 'System',
                    'component' => '/admin/index/index',
                    'meta' => [
                        'title' => 'menus.system.title',
                        'icon' => 'ri:user-3-line',
                    ],
                    'children' => [
                        [
                            'path' => 'user',
                            'name' => 'User',
                            'component' => '/admin/system/user',
                            'meta' => [
                                'title' => 'menus.system.user',
                                'icon' => 'ri:user-line',
                                'keepAlive' => true,
                                'roles' => [
                                    'R_SUPER',
                                    'R_ADMIN',
                                ],
                            ],
                        ],
                        [
                            'path' => 'role',
                            'name' => 'Role',
                            'component' => '/admin/system/role',
                            'meta' => [
                                'title' => 'menus.system.role',
                                'icon' => 'ri:user-settings-line',
                                'keepAlive' => true,
                                'roles' => [
                                    'R_SUPER',
                                ],
                            ],
                        ],
                        [
                            'path' => 'user-center',
                            'name' => 'UserCenter',
                            'component' => '/admin/system/user-center',
                            'meta' => [
                                'title' => 'menus.system.userCenter',
                                'icon' => 'ri:user-line',
                                'isHide' => true,
                                'keepAlive' => true,
                                'isHideTab' => true,
                            ],
                        ],
                        [
                            'path' => 'menu',
                            'name' => 'Menus',
                            'component' => '/admin/system/menu',
                            'meta' => [
                                'title' => 'menus.system.menu',
                                'icon' => 'ri:menu-line',
                                'keepAlive' => true,
                                'roles' => [
                                    'R_SUPER',
                                ],
                                'authList' => [
                                    [
                                        'title' => '新增',
                                        'authMark' => 'add',
                                    ],
                                    [
                                        'title' => '编辑',
                                        'authMark' => 'edit',
                                    ],
                                    [
                                        'title' => '删除',
                                        'authMark' => 'delete',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'path' => 'result',
                    'name' => 'Result',
                    'component' => '/admin/index/index',
                    'meta' => [
                        'title' => 'menus.result.title',
                        'icon' => 'ri:checkbox-circle-line',
                    ],
                    'children' => [
                        [
                            'path' => 'success',
                            'name' => 'ResultSuccess',
                            'component' => '/admin/result/success',
                            'meta' => [
                                'title' => 'menus.result.success',
                                'icon' => 'ri:checkbox-circle-line',
                                'keepAlive' => true,
                            ],
                        ],
                        [
                            'path' => 'fail',
                            'name' => 'ResultFail',
                            'component' => '/admin/result/fail',
                            'meta' => [
                                'title' => 'menus.result.fail',
                                'icon' => 'ri:close-circle-line',
                                'keepAlive' => true,
                            ],
                        ],
                    ],
                ],
                [
                    'path' => 'exception',
                    'name' => 'Exception',
                    'component' => '/admin/index/index',
                    'meta' => [
                        'title' => 'menus.exception.title',
                        'icon' => 'ri:error-warning-line',
                    ],
                    'children' => [
                        [
                            'path' => '403',
                            'name' => '403',
                            'component' => '/admin/exception/403',
                            'meta' => [
                                'title' => 'menus.exception.forbidden',
                                'keepAlive' => true,
                                'isFullPage' => true,
                            ],
                        ],
                        [
                            'path' => '404',
                            'name' => '404',
                            'component' => '/admin/exception/404',
                            'meta' => [
                                'title' => 'menus.exception.notFound',
                                'keepAlive' => true,
                                'isFullPage' => true,
                            ],
                        ],
                        [
                            'path' => '500',
                            'name' => '500',
                            'component' => '/admin/exception/500',
                            'meta' => [
                                'title' => 'menus.exception.serverError',
                                'keepAlive' => true,
                                'isFullPage' => true,
                            ],
                        ],
                    ],
                ],
                [
                    'path' => 'nested',
                    'name' => 'Nested',
                    'component' => '/admin/index/index',
                    'meta' => [
                        'title' => 'menus.system.nested',
                        'icon' => 'ri:menu-unfold-3-line',
                        'keepAlive' => true,
                    ],
                    'children' => [
                        [
                            'path' => 'menu1',
                            'name' => 'NestedMenu1',
                            'component' => '/admin/system/nested/menu1',
                            'meta' => [
                                'title' => 'menus.system.menu1',
                                'icon' => 'ri:align-justify',
                                'keepAlive' => true,
                            ],
                        ],
                        [
                            'path' => 'menu2',
                            'name' => 'NestedMenu2',
                            'component' => '',
                            'meta' => [
                                'title' => 'menus.system.menu2',
                                'icon' => 'ri:align-justify',
                                'keepAlive' => true,
                            ],
                            'children' => [
                                [
                                    'path' => 'menu2-1',
                                    'name' => 'NestedMenu2-1',
                                    'component' => '/admin/system/nested/menu2',
                                    'meta' => [
                                        'title' => 'menus.system.menu21',
                                        'icon' => 'ri:align-justify',
                                        'keepAlive' => true,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'path' => 'menu3',
                            'name' => 'NestedMenu3',
                            'component' => '',
                            'meta' => [
                                'title' => 'menus.system.menu3',
                                'icon' => 'ri:align-justify',
                                'keepAlive' => true,
                            ],
                            'children' => [
                                [
                                    'path' => 'menu3-1',
                                    'name' => 'NestedMenu3-1',
                                    'component' => '/admin/system/nested/menu3',
                                    'meta' => [
                                        'title' => 'menus.system.menu31',
                                        'keepAlive' => true,
                                    ],
                                ],
                                [
                                    'path' => 'menu3-2',
                                    'name' => 'NestedMenu3-2',
                                    'component' => '',
                                    'meta' => [
                                        'title' => 'menus.system.menu32',
                                        'keepAlive' => true,
                                    ],
                                    'children' => [
                                        [
                                            'path' => 'menu3-2-1',
                                            'name' => 'NestedMenu3-2-1',
                                            'component' => '/admin/system/nested/menu3/menu3-2',
                                            'meta' => [
                                                'title' => 'menus.system.menu321',
                                                'keepAlive' => true,
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'path' => '',
                                    'name' => 'LiteVersion',
                                    'component' => '',
                                    'meta' => [
                                        'title' => '外链1',
                                        'keepAlive' => false,
                                        'isIframe' => false,
                                        'link' => "https://www.artd.pro/docs/zh/guide/lite-version.html",
                                    ],
                                ],
                                [
                                    'path' => '',
                                    'name' => 'outsideChain',
                                    'component' => '',
                                    'meta' => [
                                        'title' => '外链2',
                                        'keepAlive' => false,
                                        'isIframe' => false,
                                        'link' => "https://www.webniu.com",
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    public function build(Request $request): Response
    {
        $vuePath = base_path().'/plugin/webniu/public/vue/webniu'; // Vue 源码路径
        $authKey = '123456';
        // ============================================

        if ($request->get('key') !== $authKey) {
            return response('无权访问', 403);
        }

        $logFile = $vuePath . '/build_log.txt';

        // ===================== 关键修复 =====================
        // Windows 必须用 start /B 才能真正后台，不阻塞PHP
        // ====================================================
        $cmd = "cd /d \"$vuePath\" && start /B cmd /c \"pnpm install --registry=https://registry.npmmirror.com > build_log.txt 2>&1 && pnpm run build >> build_log.txt 2>&1\"";

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(3); // 3秒足够启动命令
        $process->run();

        return raw_view('api/index');
    }
}
