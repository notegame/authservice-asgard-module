<?php namespace Modules\AuthService\Entities;

use Tymon\JWTAuth\Providers\Auth\AuthInterface;
use Illuminate\Auth\AuthManager;

class SentinelAuthAdapter implements AuthInterface
{

    /**
     * @param \Illuminate\Auth\AuthManager  $auth
     */
    public function __construct()
    {
        
    }

    /**
     * Check a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function byCredentials(array $credentials = [])
    {
        return Sentinel::authenticate($credentials);
    }

    /**
     * Authenticate a user via the id.
     *
     * @param  mixed  $id
     * @return bool
     */
    public function byId($id)
    {
    	$user = Sentinel::findById($id);
        return Sentinel::login($user);
    }

    /**
     * Get the currently authenticated user.
     *
     * @return mixed
     */
    public function user()
    {
        return Sentinel::getUser();
    }
}
