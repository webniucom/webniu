<?php
return [
    'user.insert' => [
        [plugin\webniu\app\event\User::class, 'insert'],
        // ...其它事件处理函数...
    ],
    'user.update' => [
        [plugin\webniu\app\event\User::class, 'update'],
        // ...其它事件处理函数...
    ],
    'user.delete' => [
        [plugin\webniu\app\event\User::class, 'delete'],
        // ...其它事件处理函数...
    ]
];