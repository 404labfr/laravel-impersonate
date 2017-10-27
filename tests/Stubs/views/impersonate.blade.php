@impersonating
<a href="{{ route('impersonate.leave') }}">Leave impersonation</a>
@endImpersonating

@if (!empty($user))
	@canImpersonate($user)
	<a href="{{ route('impersonate', $user->id) }}">Impersonate this user</a>
	@endCanImpersonate
@endif
