<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use plugin\webniu\app\web\controller\AccountController;
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
Route::any('/app/webniu/web/account/captcha/{type}', [AccountController::class, 'captcha']);
Route::any('/app/webniu/web/dict/get/{name}', [DictController::class, 'get']);
Route::fallback(function (Request $request) {
    return response($request->uri() . ' not found' , 404);
}, 'webniu');
