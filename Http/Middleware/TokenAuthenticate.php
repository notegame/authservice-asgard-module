<?php namespace Modules\AuthService\Http\Middleware;

use Modules\AuthService\Entities\Token;

class TokenAuthenticate
{
    public function handle($request, \Closure $next)
    {
        if(!$request->header('X-Auth-Token'))
        {
            abort(400,trans("authservice::exception.not_found_token"));
        }else{
            $token = Token::authenticate($request->header('X-Auth-Token'));

            if(!$token)
            {
                abort(400,trans("authservice::exception.not_found_token"));
            }
        }

        return $next($request);
    }
}
