<?php namespace Modules\AuthService\Entities;

use Illuminate\Database\Eloquent\Model;
use Sentinel;
use DB;
use App;

class Token extends Model
{
    public function scopeValid($query)
	{
		return $query->where('expires_on','>=',DB::raw('CURRENT_TIMESTAMP()'));
	}

	public static function authenticate($token_key)
	{
		$token = Token::valid()->where("token",$token_key)->first();

		if(!$token)
		{
			return null;
		}

		$user = $token->getUser();

		Sentinel::login($user);

		App::setLocale($user->locale);

		return $user;
	}

	public function getUser()
	{
		return $this->user()->first();
	}

	public function user()
	{
		return $this->belongsTo("User");
	}
}
