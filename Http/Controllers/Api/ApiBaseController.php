<?php namespace Modules\AuthService\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App;

class ApiBaseController extends Controller
{

    protected $auth;
    public $locale;

    public function __construct()
    {
        $this->locale = App::getLocale();
        $this->auth = app('Modules\Core\Contracts\Authentication');
    }
}
