<?php

$customMiddleWares = [];

$customMiddleWares['jwt'] = function ($request, $response, $next) use ($container) {
    // endpoints to be excluded from middleware
    $exclude = ["utils/auth/login", "utils/auth/"];
    
    $uri = $request->getUri()->getPath();
    // leave middleware if endpoint belongs to the excluded endpoints
    if (findExcemptedRoute($exclude,$uri)) {
        return $response = $next($request, $response);
    }

    $jwt = $container->get('jwt');
    //validate token and return on failure
    if (!$user = $jwt->decode()) {
        return $response;
    }
    if (!$user['jti']) {
        return $response;
    }
    
    $response = $next($request, $response);

    return $response;
};

$customMiddleWares['cors'] = function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
};

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

function findExcemptedRoute($exclude,$uri)
{
    $input = preg_quote($uri, '~'); // don't forget to quote input string!
    $return = null;
    foreach ($exclude as $pathtoExclude) {
        $result = preg_filter('~' . $pathtoExclude . '~', '$0', $input);
        if ($result) {
            $return = $result;
        }
    }
    return $return;
}
