<?php

$router->group(['prefix' => 'auth'], function () {

	post('login', ['uses' => 'RestAuthController@postLogin']);

	post('loginByToken', ['uses' => 'RestAuthController@postLoginByToken']);

	post('register', ['uses' => 'RestAuthController@postRegister']);

	post('activation', ['uses' => 'RestAuthController@postActivation']);

	post('forget_password', ['uses' => 'RestAuthController@postForgetPassword']);

	// Auth Only //
	Route::group(['middleware' => 'auth.token'], function () {

		post('change_password', ['uses' => 'RestAuthController@postChangePassword']);

		post('update', ['uses' => 'RestAuthController@postUpdate']);

		get('logout', ['uses' => 'RestAuthController@getLogout']);

	});

});



