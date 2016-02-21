<?php namespace Modules\AuthService\Events;

use Modules\AuthService\Entities\UserActivation;

class UserActivationWasCreated
{
    /**
     * @var UserActivation
     */
    public $user_activation;

    /**
     * @param UserActivation $user_activation
     */
    public function __construct(UserActivation $user_activation)
    {
        $this->user_activation = $user_activation;
    }
}
