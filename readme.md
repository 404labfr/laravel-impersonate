# Laravel Impersonate

[![Build Status](https://travis-ci.org/404labfr/laravel-impersonate.svg?branch=master)](https://travis-ci.org/404labfr/laravel-impersonate) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/404labfr/laravel-impersonate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/404labfr/laravel-impersonate/?branch=master)

**Laravel Impersonate** makes it easy to **authenticate as your users**. Add a simple **trait** to your **user model** and impersonate as on of your users in one click. 

## Requirements

- Laravel >= 5.4
- PHP >= 5.6

## Installation

- Require it with Composer:
```bash
composer require lab404/laravel-impersonate
```

- Add the service provider at the end of your `config/app.php`:
```php
'providers' => [
    // ...
    Lab404\Impersonate\ImpersonateServiceProvider::class,
],
```

- Add the trait `Lab404\Impersonate\Models\Impersonate` to your **User** model.

## Simple usage

Impersonate an user:
```php
Auth::user()->impersonate($other_user);
// You're now logged as the $other_user
```

Leave impersonation:
```php
Auth::user()->leaveImpersonation();
// You're now logged as your original user.
```

### Using the built-in controller

In your routes file you must call the `impersonate` route macro. 
```php
Route::impersonate();
```

```php
// Where $id is the ID of the user you want impersonate
route('impersonate', $id)

// Generate an URL to leave current impersonation
route('impersonate.leave')
```

## Advanced Usage

### Defining impersonation authorization

By default all users can impersonate an user.  
You need to add the method `canImpersonate()` to your user model:

```php
    /**
     * @return bool
     */
    public function canImpersonate()
    {
        // For example
        return $this->attributes['id_admin'] == 1;
    }
```

### Using your own strategy

- Getting the manager:
```php
// With the app helper
app('impersonate')
// Dependency Injection
public function impersonate(ImpersonateManager $manager, $user_id) { /* ... */ }
```

- Working with the manager:
```php
$manager = app('impersonate');

// Find an user by its ID
$manager->findUserById($id);

// TRUE if your are impersonating an user.
$manager->isImpersonating();

// Impersonate an user. Pass the original user and the user you want to impersonate
$manager->take($from, $to);

// Leave current impersonation
$manager->leave();

// Get the impersonator ID
$manager->getImpersonatorId();
```

## Configuration

The package comes with a configuration file.  

Publish it with the following command:
```bash
php artisan vendor:publish --tag=impersonate
```

Available options:
```php
    // The session key used to store the original user id.
    'session_key' => 'impersonated_by',
    // The URI to redirect after taking an impersonation.
    // Only used in the built-in controller.
    'take_redirect_to' => '/',
    // The URI to redirect after leaving an impersonation.
    // Only used in the built-in controller.
    'leave_redirect_to' => '/'
```
## Blade

There is two Blade directives available.

### When the user can impersonate

```blade
@canImpersonate
    <a href="{{ route('impersonate', $user->id) }}">Impersonate this user</a>
@endCanImpersonate
```

### When the user is impersonated

```blade
@impersonating
    <a href="{{ route('impersonate.leave') }}">Leave impersonation</a>
@endImpersonating
```

## Tests

```bash
vendor/bin/phpunit
```

## Contributors

- [MarceauKa](https://github.com/MarceauKa)
- [tghpow](https://github.com/tghpow)
- and all others [contributors](https://github.com/404labfr/laravel-impersonate/graphs/contributors)

## Licence

MIT
