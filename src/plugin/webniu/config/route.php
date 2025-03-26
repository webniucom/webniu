<?php

use plugin\webniu\app\web\controller\AccountController;
use plugin\webniu\app\web\controller\CaptchaController;
use plugin\webniu\app\web\controller\DictController;
use Webman\Route;
use support\Request;
if(is_file($rewrite_file = base_path('/plugin/webniu/app/support/rewrite.php'))){
    $rewrite = require_once $rewrite_file;
    if($rewrite && is_array($rewrite)) {
        foreach ($rewrite as $key => $value) {
            Route::any($key, $value);
        }
    } 
}
Route::any('/app/webniu/web/captcha/image/{type}', [CaptchaController::class, 'image']); 
Route::any('/app/webniu/web/captcha/email/{type}', [CaptchaController::class, 'email']);
Route::any('/app/webniu/web/captcha/mobile/{type}', [CaptchaController::class, 'mobile']); 
Route::any('/app/webniu/web/account/captcha/{type}', [AccountController::class, 'captcha']);
Route::any('/app/webniu/web/dict/get/{name}', [DictController::class, 'get']);
Route::fallback(function (Request $request) {
    return response($request->uri() . ' not found' , 404);
}, 'webniu');
