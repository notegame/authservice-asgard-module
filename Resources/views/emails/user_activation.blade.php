<h2>{{trans("authservice::email.this_is_your_activate_code")}}</h2>
<div>
	<div>
		<strong>Email : </strong>{{$user->email}}
	</div>
	<div>
		<strong>Ref ID : </strong>{{$user_activation->ref_id}}
	</div>
	<div>
		<strong>Activate Code : </strong><span style="font-size: 40px;">{{$user_activation->code}}</span>
	</div>
</div>