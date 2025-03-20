<?php
namespace plugin\webniu\app\middleware;

use plugin\webniu\api\Auth;
use ReflectionException;
use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class AccessWeb implements MiddlewareInterface
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
        $action     = $request->action; 
        $code = 0;
        $msg = '';
        if (!Auth::canAccess($controller, $action, $code, $msg)) {
            if ($request->expectsJson()) {
                $response = json(['code' => $code, 'msg' => $msg, 'data' => []]);
            } else {
                if ($code === 401) {
                    $response = response(<<<EOF
<script>
    if (self !== top) {
        parent.location.reload();
    }
</script>
EOF
                    );
                } else { 
                    $request->app       = 'web';
                    $request->plugin    = 'webniu';
                    $response = view('common/error/403')->withStatus(403);
                }
            }

        } else {
            $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        }

        return $response;

    }

}
