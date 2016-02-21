<?php namespace Modules\AuthService\Events\Handlers;

use Modules\AuthService\Events\UserActivationWasCreated;

use Mail;

class SendUserActivationToUser
{

    public function handle(UserActivationWasCreated $event)
    {
        $user_activation = $event->user_activation;
        $user = $user_activation->user;

        $view_data = [
        	'user' => $user,
        	'user_activation' => $user_activation,
       	];

        try {

            Mail::send('authservice::emails.user_activation', $view_data, function ($message) use ($view_data){

                $message->to($view_data['user']->email, $view_data['user']->name);

                $message->subject(trans("authservice::email.this_is_your_activate_code"));

            });

        } catch (\Exception $e) {
            
        }

        
    }

}
