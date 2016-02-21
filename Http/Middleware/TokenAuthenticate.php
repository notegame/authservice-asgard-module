<?php namespace Modules\AuthService\Http\Middleware;

use Modules\AuthService\Repositories\TokenRepository;

class TokenAuthenticate
{
    public function handle($request, \Closure $next)
    {
        if(!$request->header('X-Auth-Token'))
        {
            abort(400,trans("authservice::exception.not_found_token"));
        }else{
            $token = TokenRepository::authenticate($request->header('X-Auth-Token'));

            if(!$token)
            {
                abort(400,trans("authservice::exception.not_found_token"));
            }
        }

        return $next($request);
    }
}
