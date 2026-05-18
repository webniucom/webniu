<?php
/**
 * This file is part of webman.
 *
 */

use plugin\webniu\app\admin\controller\AccountController;
use plugin\webniu\app\admin\controller\DictController;
use Webman\Route;
use support\Request;

Route::any('/app/webniu/account/captcha/{type}', [AccountController::class, 'captcha']);
Route::any('/app/webniu/dict/get/{name}', [DictController::class, 'get']);
if(is_file($rewrite_file = base_path('/plugin/webniu/app/support/rewrite.php'))){
    $rewrite = require_once $rewrite_file;
    if($rewrite && is_array($rewrite)) {
        foreach (Route::getRoutes() as $route) {
            $reflection = new \ReflectionClass($route);
            $pathProperty = $reflection->getProperty('path');
            $path = $pathProperty->getValue($route);$already[$path] = true;
        }
        foreach ($rewrite as $key => $value) {
            if(!isset($already[$key])){
                Route::any($key, $value);
            } 
        }
    } 
}
Route::fallback(function (Request $request) {
    return response($request->uri() . ' not found' , 404);
}, 'webniu');
