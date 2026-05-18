<?php
namespace plugin\webniu\app\middleware;

use plugin\webniu\api\Auth;
use ReflectionException;
use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class WechatControl implements MiddlewareInterface
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
        $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        return $response;

    }

}
