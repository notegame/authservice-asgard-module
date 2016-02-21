<?php namespace Modules\AuthService\Repositories;

use Modules\AuthService\Entities\Token;
use Modules\Core\Repositories\Eloquent\EloquentBaseRepository;

use Sentinel;
use App;

class TokenRepository extends EloquentBaseRepository 
{

    public static function authenticate($token_key)
    {
        $token = Token::valid()->where("token",$token_key)->first();

        if(!$token)
        {
            return null;
        }

        $user = $token->user;

        Sentinel::login($user);

        return $user;
    }


}
