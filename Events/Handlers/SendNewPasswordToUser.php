<?php namespace Modules\AuthService\Events\Handlers;

use Modules\AuthService\Events\UserWasChangedPassword;

use Mail;

class SendNewPasswordToUser
{

    public function handle(UserWasChangedPassword $event)
    {

        $view_data = [
        	'user' => $event->user,
        	'new_password' => $event->new_password,
       	];

        try {
            
            Mail::send('authservice::emails.new_password', $view_data, function ($message) use ($view_data){

                $message->to($view_data['user']->email, $view_data['user']->name);

                $message->subject(trans("authservice::email.this_is_your_new_password"));

            });

        } catch (\Exception $e) {
            
        }

        
    }

}
