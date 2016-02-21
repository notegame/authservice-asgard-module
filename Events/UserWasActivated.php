<?php namespace Modules\AuthService\Events;

use Modules\AuthService\Entities\User;

class UserWasActivated
{
    /**
     * @var User
     */
    public $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
