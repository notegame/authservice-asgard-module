<?php namespace Modules\AuthService\Events;

use Modules\AuthService\Entities\User;

class UserWasChangedPassword
{
    /**
     * @var User
     */
    public $user;
    /**
     * @var String
     */
    public $new_password;

    /**
     * @param User $user
     * @param String $new_password
     */
    public function __construct(User $user, $new_password)
    {
        $this->user = $user;
        $this->new_password = $new_password;
    }
}
