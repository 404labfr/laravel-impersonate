<h1>impersonate.blade.php</h1>

@impersonating
<a href="{{ route('impersonate.leave') }}">Leave impersonation</a>
@endImpersonating

@canImpersonate
<a href="{{ route('impersonate', 2) }}">Impersonate this user</a>
@endCanImpersonate