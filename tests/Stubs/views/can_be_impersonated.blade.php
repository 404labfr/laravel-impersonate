<h1>can_be_impersonated.blade.php</h1>

@canBeImpersonated($user)
<a href="{{ route('impersonate', $user->id) }}">Impersonate this user</a>
@endCanBeImpersonated