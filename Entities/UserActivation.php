<?php namespace Modules\AuthService\Entities;

use Illuminate\Database\Eloquent\Model;

use Modules\AuthService\Events\UserWasActivated;

use Activation;
use Carbon\Carbon;

class UserActivation extends Model
{

	protected $expired_time = 60;

	public function scopeNoExpires($query)
	{
		return $query
		->where('created_at', '>', Carbon::now()->subMinutes($this->expired_time))
		->where('completed', false);
	}

	public function user()
	{
		return $this->belongsTo("User");
	}

}
