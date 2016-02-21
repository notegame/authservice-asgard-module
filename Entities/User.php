<?php namespace Modules\AuthService\Entities;

use Modules\User\Entities\Sentinel\User as SentinelUser;
use Modules\User\Entities\UserInterface;

use Carbon\Carbon;
use BrowserDetect;
use Sentinel;
use Reminder;
use Mail;

class User extends SentinelUser
{
	protected $fillable = [
        'email',
        'password',
        'permissions',
        'first_name',
        'last_name',
    ];

	public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function user_activations()
    {
    	return $this->hasMany("Modules\AuthService\Entities\UserActivation");
    }

    public function tokens()
    {
    	return $this->hasMany("Modules\AuthService\Entities\Token");
    }

    public function createActivation()
	{
		
		// Find Old activation
		$activation = UserActivation::where("user_id",$this->id)
		->noExpires()
		->first();

		if($activation)
		{
			return $activation;
		}

		// Create activate ref_id
		do{
			$code = strtoupper(str_random(4));
			$count = UserActivation::where("ref_id",$code)->count();
		}while ($count>0);

		$activation = new UserActivation;
		$activation->user_id = $this->getUserId();
		$activation->ref_id = $code;
		$activation->code = rand(1000, 9999);
		$activation->save();

		return $activation;
	}

	public function cerateToken($device_id=null,$device_os=null)
	{
		if($device_os == null ) $device_os = BrowserDetect::osFamily();

		$token = Token::where("device_os",$device_os)
		->where("device_id",$device_id)
		->first();

		if($token)
		{

			$token->expires_on = Carbon::now()->addMonth()->toDateTimeString();
	        $token->save();

		}else{

			$token = new Token;
			$token->user_id = $this->getUserId();
	        $token->token = hash('sha256',str_random(10),false);
	        $token->device_id = $device_id;
	        $token->device_os = strtoupper($device_os);
	        $token->expires_on = Carbon::now()->addMonth()->toDateTimeString();
	        $token->save();

	    }

        return $token->token;
	}

	public function deleteToken()
	{
		$token = \Request::header("X-Auth-Token");
		$this->tokens()->where('token',$token)->delete();
	}

	public function createMessage($data)
	{
		$message = new Message;
		$message->fill($data);
		$this->messages()->save($message);

	}

	public function messages()
	{
		return $this->hasMany("App\Models\Message");

	}

    public static function getAllPermission($user=null)
	{
		if($user==null)
		{
			$user = Sentinel::getUser();
		}

		$roles = $user->roles()->get();

		$permissions = $user->permissions;

		foreach ($roles as $role) {
		    $permissions = array_merge($role->permissions, $permissions);
		}

		return $permissions;

	}

	public function delete()
    {
        if ($this->exists) {
            $this->user_activations()->delete();
            $this->messages()->delete();
            $this->tokens()->delete();
        }

        parent::delete();
    }
    public function RecoverPassword($user)
	{
		$NewPassword = mt_rand(100000, 999999);
		$CreateReCode = Reminder::create($user);
		$Reminder = Reminder::complete($user, $CreateReCode->code, $NewPassword);

		$emailTitle = "รหัสผ่านระบบ Student Messenger";
		$emailContent = "E-mail : $user->email <br />".
						"Your Password : <strong>$NewPassword</strong>";
		
		$data = array(
					'title'=>$emailTitle,
					'content'=>$emailContent
				);

		Mail::send('emails.template_mail', $data, function ($message) use ($user,$data){
			$message->to($user->email, $user->first_name.' '.$user->last_name);
			$message->subject($data['title']);
		});

		return $Reminder;
    }
	public function ChangePassword($user,$NewPassword)
	{
		$update = Sentinel::update($user, array('password' => $NewPassword));

		$emailTitle = "เปลี่ยนรหัสผ่านระบบ Student Messenger";
		$emailContent = "E-mail : $user->email <br />".
						"New Password : <strong>$NewPassword</strong>";
		
		$data = array(
					'title'=>$emailTitle,
					'content'=>$emailContent
				);

		Mail::send('emails.template_mail', $data, function ($message) use ($user,$data){
			$message->to($user->email, $user->first_name.' '.$user->last_name);
			$message->subject($data['title']);
		});

		return $update;
    }
	public function ActivateSuccess($user)
	{
		$emailTitle = "ลงทะเบียน เรียบร้อยแล้ว";
		$emailContent = "ท่านได้ลงทะเบียนการใช้ Student Messenger สำเร็จแล้ว<br />".
						"ท่านสามารถ Login แอพพลิเคชั่นบนมือถือ เพื่อใช้งาน Student Messenger ได้ทันที<br />".
						"<strong>Name</strong> : $user->first_name $user->last_name<br>".
						"<strong>E-mail</strong> : $user->email <br />".
						"<strong>Mobile</strong> : $user->phone_number";
		
		$data = array(
					'title'=>$emailTitle,
					'content'=>$emailContent
				);

		$send = Mail::send('emails.template_mail', $data, function ($message) use ($user,$data){
			$message->to($user->email, $user->first_name.' '.$user->last_name);
			$message->subject($data['title']);
		});

		return $send;
    }
}