<?php namespace Modules\AuthService\Entities;

use Modules\User\Entities\Sentinel\User as SentinelUser;
use Modules\User\Entities\UserInterface;

use Modules\AuthService\Events\UserWasChangedPassword;
use Modules\AuthService\Events\UserActivationWasCreated;

use Carbon\Carbon;
use BrowserDetect;
use Sentinel;
use Reminder;
use Mail;

class User extends SentinelUser
{
	protected $hidden = ['password'];

	protected $fillable = [
        'email',
        'password',
        'permissions',
        'first_name',
        'last_name',
    ];

    public function getNameAttribute()
    {
        return $this->first_name." ".$this->last_name;
    }


    public function user_activations()
    {
    	return $this->hasMany("Modules\AuthService\Entities\UserActivation");
    }

    public function tokens()
    {
    	return $this->hasMany("Modules\AuthService\Entities\Token");
    }

    public function delete()
    {
        if ($this->exists) {
            $this->user_activations()->delete();
            $this->tokens()->delete();
        }

        parent::delete();
    }
    
}