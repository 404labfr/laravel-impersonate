<?php

namespace Lab404\Impersonate\Services;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;
use Lab404\Impersonate\Exceptions\InvalidUserProvider;
use Lab404\Impersonate\Exceptions\MissingUserProvider;

class ImpersonateManager
{
    public const REMEMBER_PREFIX = 'remember_web';

    private Application $app;

    /**
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param  string  $id
     * @param  string|null  $guardName
     *
     * @return Authenticatable
     *
     * @throws InvalidUserProvider
     * @throws MissingUserProvider
     */
    public function findUserById(string $id, ?string $guardName = null): Authenticatable
    {
        if (empty($guardName)) {
            $guardName = $this->app['config']->get('auth.default.guard', 'web');
        }

        $providerName = $this->app['config']->get("auth.guards.$guardName.provider");

        if (empty($providerName)) {
            throw new MissingUserProvider($guardName);
        }

        try {
            /** @var UserProvider $userProvider */
            $userProvider = $this->app['auth']->createUserProvider($providerName);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidUserProvider($guardName);
        }

        if (!($modelInstance = $userProvider->retrieveById($id))) {
            $model = $this->app['config']->get("auth.providers.$providerName.model");

            throw (new ModelNotFoundException())->setModel(
                $model,
                $id
            );
        }

        return $modelInstance;
    }

    /**
     * @return bool
     */
    public function isImpersonating(): bool
    {
        return session()->has($this->getSessionKey());
    }

    /**
     * @return  string|null
     */
    public function getImpersonatorId(): ?string
    {
        return session($this->getSessionKey());
    }

    /**
     * @return Authenticatable
     *
     * @throws InvalidUserProvider
     * @throws MissingUserProvider
     */
    public function getImpersonator(): ?Authenticatable
    {
        $id = session($this->getSessionKey(), null);

        return is_null($id) ? null : $this->findUserById($id, $this->getImpersonatorGuardName());
    }

    /**
     * @return string|null
     */
    public function getImpersonatorGuardName(): ?string
    {
        return session($this->getSessionGuard(), null);
    }

    /**
     * @return string|null
     */
    public function getImpersonatorGuardUsingName(): ?string
    {
        return session($this->getSessionGuardUsing(), null);
    }

    /**
     * @param  Authenticatable  $from
     * @param  Authenticatable  $to
     * @param  string|null  $guardName
     *
     * @return bool
     */
    public function take(Authenticatable $from, Authenticatable $to, ?string $guardName = null): bool
    {
        $this->saveAuthCookieInSession();

        try {
            $currentGuard = $this->getCurrentAuthGuardName();
            session()->put($this->getSessionKey(), $from->getAuthIdentifier());
            session()->put($this->getSessionGuard(), $currentGuard);
            session()->put($this->getSessionGuardUsing(), $guardName);

            $this->app['auth']->guard($currentGuard)->quietLogout();
            $this->app['auth']->guard($guardName)->quietLogin($to);

        } catch (Exception $exception) {
            unset($exception);
            return false;
        }

        $this->app['events']->dispatch(new TakeImpersonation($from, $to));

        return true;
    }

    /**
     * @return bool
     */
    public function leave(): bool
    {
        try {
            $impersonated = $this->app['auth']->guard($this->getImpersonatorGuardUsingName())->user();
            $impersonator = $this->findUserById($this->getImpersonatorId(), $this->getImpersonatorGuardName());

            $this->app['auth']->guard($this->getCurrentAuthGuardName())->quietLogout();
            $this->app['auth']->guard($this->getImpersonatorGuardName())->quietLogin($impersonator);

            $this->extractAuthCookieFromSession();

            $this->clear();

        } catch (Exception $exception) {
            unset($exception);
            return false;
        }

        $this->app['events']->dispatch(new LeaveImpersonation($impersonator, $impersonated));

        return true;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        session()->forget($this->getSessionKey());
        session()->forget($this->getSessionGuard());
        session()->forget($this->getSessionGuardUsing());
    }

    /**
     * @return string
     */
    public function getSessionKey(): string
    {
        return config('laravel-impersonate.session_key');
    }

    /**
     * @return string
     */
    public function getSessionGuard(): string
    {
        return config('laravel-impersonate.session_guard');
    }

    /**
     * @return string
     */
    public function getSessionGuardUsing(): string
    {
        return config('laravel-impersonate.session_guard_using');
    }

    /**
     * @return string
     */
    public function getDefaultSessionGuard(): string
    {
        return config('laravel-impersonate.default_impersonator_guard');
    }

    /**
     * @return string
     */
    public function getTakeRedirectTo(): string
    {
        try {
            $uri = route(config('laravel-impersonate.take_redirect_to'));
        } catch (InvalidArgumentException $exception) {
            $uri = config('laravel-impersonate.take_redirect_to');
        }

        return $uri;
    }

    /**
     * @return string
     */
    public function getLeaveRedirectTo(): string
    {
        try {
            $uri = route(config('laravel-impersonate.leave_redirect_to'));
        } catch (InvalidArgumentException $exception) {
            $uri = config('laravel-impersonate.leave_redirect_to');
        }

        return $uri;
    }

    /**
     * @return string|null
     */
    public function getCurrentAuthGuardName(): ?string
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if ($this->app['auth']->guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    /**
     * @return void
     */
    protected function saveAuthCookieInSession(): void
    {
        $cookie = $this->findByKeyInArray($this->app['request']->cookies->all(), static::REMEMBER_PREFIX);
        $key = $cookie->keys()->first();
        $val = $cookie->values()->first();

        if (!$key || !$val) {
            return;
        }

        session()->put(static::REMEMBER_PREFIX, [
            $key,
            $val,
        ]);
    }

    /**
     * @return void
     */
    protected function extractAuthCookieFromSession(): void
    {
        if (!$session = $this->findByKeyInArray(session()->all(), static::REMEMBER_PREFIX)->first()) {
            return;
        }

        $this->app['cookie']->queue($session[0], $session[1]);
        session()->forget($session);
    }

    /**
     * @param array  $values
     * @param string $search
     * @return Collection
     */
    protected function findByKeyInArray(array $values, string $search): Collection
    {
        return collect($values ?? session()->all())
            ->filter(fn (?string $val, string $key) => strpos($key, $search) !== false);
    }
}
