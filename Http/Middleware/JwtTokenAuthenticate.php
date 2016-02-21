<?php namespace Modules\AuthService\Http\Middleware;

use JWTAuth;

class JwtTokenAuthenticate
{
    /**
     * @var Authentication
     */
    

    public function __construct()
    {
        
    }

    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $user = JWTAuth::parseToken();

        echo '<pre>';
        print_r($user);
        echo '</pre>';

        /*if (! $this->auth->check()) {
            return redirect()->guest('auth/login');
        }

        return $next($request);*/
    }
}
