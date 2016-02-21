<?php namespace Modules\AuthService\Http\Controllers\Api;

use Illuminate\Http\Request;
use Validator;
use Sentinel;
use Crypt;
use JWTAuth;

use Modules\AuthService\Entities\User;
use Modules\AuthService\Entities\UserActivation;

class RestAuthController extends ApiBaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function postLogin(Request $request)
    {
        $post = $request->all();

        if($request->has("device_os"))
        {
            $post['device_os'] = strtolower($request->get("device_os"));
        }

        // Validate
        $validator = Validator::make($post, [
            'login' => 'required',
            'password' => 'required',
            'device_id' => 'required',
            'device_os' => 'required|in:android,ios,windows',
        ]);

        if ($validator->fails()) {
            abort(400,$validator->errors()->first());
        }

        try {
            $user = Sentinel::authenticate($request->only(["login","password"]));
        } catch (Exception $e) {
            abort(400,$e->getMessage());
        }

        if(!$user)
        {
            abort(400,trans("authservice::exception.not_found_user"));
        }

        // Get All Permissions
        $user->permissions = User::getAllPermission($user);

        // Get Token
        $user->token = $user->cerateToken($request->get("device_id"),$request->get("device_os"));

        return response()->json($user);
    }

    public function postLoginByToken()
    {
        $post = $request->all();
        
        if($request->has("device_os"))
        {
            $post['device_os'] = strtolower($request->get("device_os"));
        }

        // Validate
        $validator = Validator::make($post, [
            'token' => 'required',
            'device_id' => 'required',
            'device_os' => 'required|in:android,ios,windows',
        ]);

        if ($validator->fails()) {
            abort(400,$validator->errors()->first());
        }

        $token = Token::valid()->where("token",$request->get("token"))->first();

        if($token)
        {
            $token->device_id = $request->get("device_id");
            $token->device_os = $post['device_os'];
            $token->save();

            return response()->json(true);
        }

        return response()->json(false);
    }

    public function postRegister(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            abort(400,$validator->errors()->first());
        }

        $user = Sentinel::register([
            'email' => $request->get('email'),
            'password' => $request->get('password'),
        ]);
        
        // Activate ref_id
        $activation_ref_id = $user->createActivation()->ref_id;
        
        return response()->json([
            'activation' => [
                'ref_id' => $activation_ref_id
            ]
        ]);
    }

    public function postActivation(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'ref_id' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            abort(400,$validator->errors()->first());
        }
        
        try {

            // Activate
            $activation = UserActivation::activate($request->get("ref_id"),$request->get("code"));

        } catch (\Exception $e) {

            abort(400,$e->getMessage());

        }

        return response()->json(true);
    }

    public function postForgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            abort(400,$validator->errors()->first());
        }

        $email = $request->get("email");

        $user = User::whereEmail($email)->first();

        if(!$user)
        {
            abort(400,trans("authservice::exception.not_found_user"));
        }

        //Reset Password
        $forget = $user->recoverPassword();

        return response()->json(true);
    }




    // LONGED IN METHOD //

    public function getLogout()
    {

        $user = Sentinel::getUser();

        $user->deleteToken();
        Sentinel::logout();
        return response()->json(true);

    }

    public function postChangePassword(Request $request)
    {
        $user = Sentinel::getUser();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            abort(400,$validator->errors()->first());
        }
        
        $validate_password = Sentinel::validateCredentials($user, array("password"=>$request->get("current_password")));

        if(!$validate_password)
        {
            abort(400,trans("authservice::exception.password_incorrect"));
        }

        //Change Password
        $user->changePassword($request->get("password"));

        return response()->json(true);
    }

    public function postUpdate(Request $request)
    {
        $user = Sentinel::getUser();

        $validator = Validator::make($request->all(), [
            'email' => 'email|unique:users,email,'.$user->id,
        ]);

        if ($validator->fails()) {
            abort(400,$validator->errors()->first());
        }
        
        $result = $user->updateData($request->all());
        
        return response()->json($result);
    }



}
