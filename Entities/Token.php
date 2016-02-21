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

	public function user()
	{
		return $this->belongsTo("User");
	}
	
}
