<?php
namespace plugin\webniu\app\middleware;

use plugin\webniu\api\Auth;
use ReflectionException;
use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class AccessAdmin implements MiddlewareInterface
{
    /**
     * @param Request $request
     * @param callable $handler
     * @return Response
     * @throws ReflectionException|BusinessException
     */
    public function process(Request $request, callable $handler): Response
    {   
        $controller = $request->controller;
        $action = $request->action;
        $code = 200;
        $msg = '';
        if (!Auth::canAccess($controller, $action, $code, $msg)) {
            if ($request->expectsJson()) {
                $response = json(['code' => $code, 'msg' => $msg, 'data' => []]);
            } else {
                if ($code === 401) {
                  $response = admin_error_401_script();
                } else {
                    $response = json(['code' => $code, 'msg' => $msg, 'type' => 'error']);
                }
            }

        } else {
            $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        }

        return $response;

    }

}
