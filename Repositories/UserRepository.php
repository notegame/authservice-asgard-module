<?php namespace Modules\AuthService\Repositories;

//use Modules\AuthService\Entities\User;
//use Modules\AuthService\Entities\Role;
use Modules\AuthService\Entities\UserActivation;
use Modules\AuthService\Entities\Token;

use Modules\AuthService\Events\UserWasChangedPassword;
use Modules\AuthService\Events\UserActivationWasCreated;
use Modules\AuthService\Events\UserWasActivated;

use Modules\User\Repositories\Sentinel\SentinelUserRepository;

use Sentinel;
use Activation;
use Carbon\Carbon;

class UserRepository extends SentinelUserRepository 
{

    protected $fillable_update_api = [
        'email',
        'first_name',
        'last_name',
    ];

    public static function getAllPermission($model)
    {
        $roles = $model->roles()->get();

        $permissions = $model->permissions;

        foreach ($roles as $role) {
            $permissions = array_merge($role->permissions, $permissions);
        }

        return $permissions;

    }

    public static function cerateToken($model, $device_id=null, $device_os=null)
    {
        //if($device_os == null ) $device_os = BrowserDetect::osFamily();

        $token = Token::where("device_os",$device_os)
        ->where("device_id",$device_id)
        ->first();

        if($token)
        {

            $token->expires_on = Carbon::now()->addMonth()->toDateTimeString();
            $token->save();

        }else{

            $token = new Token;
            $token->user_id = $model->getUserId();
            $token->token = hash('sha256',str_random(10),false);
            $token->device_id = $device_id;
            $token->device_os = strtoupper($device_os);
            $token->expires_on = Carbon::now()->addMonth()->toDateTimeString();
            $token->save();

        }

        return $token->token;
    }

    public static function loginByToken($token_string=null, $device_id=null, $device_os=null)
    {
        $token = Token::valid()->where("token",$token_string)->first();

        if($token)
        {
            $token->device_id = $device_id;
            $token->device_os = $device_os;
            $token->save();

            return $token;
        }
        return false;
    }

    public static function register($credentials)
    {
        $user = Sentinel::register($credentials);
        
        return $user;
    }

    public static function createActivation($user)
    {
        // Find Old activation
        $activation = UserActivation::where("user_id",$user->id)
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
        $activation->user_id = $user->id;
        $activation->ref_id = $code;
        $activation->code = rand(1000, 9999);
        $activation->save();

        //Event on Change Password
        event(new UserActivationWasCreated($activation));

        return $activation;
    }

    public static function activation($ref_id, $code)
    {
        try {
            $activation = UserActivation::where("ref_id",$ref_id)
            ->where("code",$code)
            ->noExpires()
            ->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception(trans("authservice::exception.not_found_activate_code"));
            return false;
        }

        $activation->completed = 1;
        $activation->completed_at = Carbon::now();
        $activation->save();

        $user = $activation->user;

        if($sentinel_activation = Activation::completed($user))
        {
            
        }else{
            $sentinel_activation = Activation::create($user)->code;
            Activation::complete($user,$sentinel_activation);

            //Event on Change Password
            event(new UserWasActivated($user));

        }
        
        return $activation;
    }

    public static function recoverPassword($user)
    {
        $new_password = mt_rand(100000, 999999);

        self::changePassword($user, $new_password);

        $result = new \stdClass();
        $result->new_password = $new_password;
        
        return $result;
    }

    public static function deleteToken($user)
    {
        $token = \Request::header("X-Auth-Token");
        $user->tokens()->where('token',$token)->delete();
    }

    public static function changePassword($user, $new_password)
    {
        $result = Sentinel::update($user, array('password' => $new_password));

        //Event on Change Password
        event(new UserWasChangedPassword($user,$new_password));

        return $result;
    }

    public static function updateData($user, $data)
    {
        $user_table_updates = [];

        $model = new self;

        foreach ($data as $field => $value) {

            if(in_array($field, $model->fillable_update_api))
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
