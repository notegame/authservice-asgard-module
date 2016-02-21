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

    protected $fillable_update_api = [
    	'email',
        'first_name',
        'last_name',
    ];

	public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

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

		//Event on Change Password
		event(new UserActivationWasCreated($activation));

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

    public function recoverPassword()
	{
		$user = $this;

		$new_password = mt_rand(100000, 999999);

		$this->changePassword($new_password);

		$result = new \stdClass();
		$result->new_password = $new_password;
		
		return $result;
    }

	public function changePassword($new_password)
	{
		$user = $this;

		$result = Sentinel::update($user, array('password' => $new_password));

		//Event on Change Password
		event(new UserWasChangedPassword($user,$new_password));

		return $result;
    }

    //Update User Data กรองตาม field ที่ อนุญาตเท่านั้น
    public function updateData($data)
	{
		$user = $this;

		$user_table_updates = [];

		foreach ($data as $field => $value) {

			if(in_array($field, $this->fillable_update_api))
			{
				if(empty($value)) continue;

				$user_table_updates[$field] = $value;
			}

		}

		$result = $user;

		if($user_table_updates)
		{
			$result = Sentinel::update($user, $user_table_updates);
		}

		return $result;
    }


}