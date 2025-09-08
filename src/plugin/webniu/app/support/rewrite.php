<?php
return [
    '/webniu'   => [
        plugin\webniu\app\web\controller\IndexController::class,
        'index'
    ],
    '/index/vuemc.vue'   => [
        app\controller\IndexController::class,
        'vuemc'
    ]
];