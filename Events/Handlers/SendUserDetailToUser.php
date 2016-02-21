<?php namespace Modules\AuthService\Events\Handlers;

use Modules\AuthService\Events\UserWasActivated;

use Mail;

class SendUserDetailToUser
{

    public function handle(UserWasActivated $event)
    {

        $view_data = [
        	'user' => $event->user,
       	];

        try {

            Mail::send('authservice::emails.user_profile', $view_data, function ($message) use ($view_data){

                $message->to($view_data['user']->email, $view_data['user']->name);

                $message->subject(trans("authservice::email.register_success"));

            });

        } catch (\Exception $e) {
            
        }

        
    }

}
