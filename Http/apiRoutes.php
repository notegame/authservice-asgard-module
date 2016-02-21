<?php

$router->group(['prefix' => 'auth'], function () {

	post('login', ['uses' => 'RestAuthController@postLogin']);

	post('loginByToken', ['uses' => 'RestAuthController@postLoginByToken']);

	post('register', ['uses' => 'RestAuthController@postRegister']);

	post('activation', ['uses' => 'RestAuthController@postActivation']);

	post('forget_password', ['uses' => 'RestAuthController@postForgetPassword']);

	get('logout', ['uses' => 'RestAuthController@getLogout']);

});

$router->group(['prefix' => 'auth', 'middleware' => 'auth.token'], function () {
	get('change_password', ['uses' => 'RestAuthController@postChangePassword']);
});

