<?php namespace Modules\AuthService\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'Modules\AuthService\Events\UserWasChangedPassword' => [
            'Modules\AuthService\Events\Handlers\SendNewPasswordToUser',
        ],
        'Modules\AuthService\Events\UserActivationWasCreated' => [
            'Modules\AuthService\Events\Handlers\SendUserActivationToUser',
        ],
        'Modules\AuthService\Events\UserWasActivated' => [
            'Modules\AuthService\Events\Handlers\SendUserDetailToUser',
        ]
    ];
}
