# Laravel Firebase ID Token Authentication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bambolee-digital/laravel-firebase-id-token.svg?style=flat-square)](https://packagist.org/packages/bambolee-digital/laravel-firebase-id-token)
[![Total Downloads](https://img.shields.io/packagist/dt/bambolee-digital/laravel-firebase-id-token.svg?style=flat-square)](https://packagist.org/packages/bambolee-digital/laravel-firebase-id-token)
[![License](https://img.shields.io/packagist/l/bambolee-digital/laravel-firebase-id-token.svg?style=flat-square)](https://packagist.org/packages/bambolee-digital/laravel-firebase-id-token)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/bambolee-digital/laravel-firebase-id-token/run-tests?label=tests&style=flat-square)](https://github.com/bambolee-digital/laravel-firebase-id-token/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/bambolee-digital/laravel-firebase-id-token/Check%20&%20fix%20styling?label=code%20style&style=flat-square)](https://github.com/bambolee-digital/laravel-firebase-id-token/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)

This package provides a seamless integration of Firebase ID Token authentication with Laravel, supporting both Laravel 10.x and 11.x. It allows you to easily authenticate users using Firebase ID tokens and optionally integrate with Laravel Sanctum for API token management.

## Features

- Firebase ID Token verification and user authentication
- Automatic user creation and updating based on Firebase data
- Configurable authentication order (Firebase and/or Sanctum)
- Custom claims mapping
- Sanctum integration for API token management
- Compatible with Laravel 10.x and 11.x

## Installation

You can install the package via composer:

```bash
composer require bambolee-digital/laravel-firebase-id-token
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="BamboleeDigital\LaravelFirebaseIdToken\Providers\FirebaseAuthServiceProvider" --tag="config"
```

This will create a `bambolee-firebase.php` configuration file in your `config` directory.

## Database Migration

This package requires an `external_id` column in your users table to store the Firebase User ID. A migration is included to add this column. To run the migration, execute:

```bash
php artisan migrate
```

If you need to customize the migration, you can publish it:

```bash
php artisan vendor:publish --provider="BamboleeDigital\LaravelFirebaseIdToken\Providers\FirebaseAuthServiceProvider" --tag="migrations"
```

Then, you can modify the migration file in your `database/migrations` directory before running `php artisan migrate`.

## Updating User Model

After adding the `external_id` column, make sure to add it to the `$fillable` array in your User model:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'external_id', // Add this line
    ];

    // ...
}
```

### Firebase Credentials

Set your Firebase credentials in your `.env` file:

```
FIREBASE_CREDENTIALS_BASE64=your_base64_encoded_firebase_credentials
```

You can generate this by base64 encoding your Firebase service account JSON file:

```bash
base64 -i path/to/your/firebase-credentials.json
```

### Configuration Options

In `config/bambolee-firebase.php`, you can customize various settings:

```php
return [
    'credentials_base64' => env('FIREBASE_CREDENTIALS_BASE64'),
    'auth_order' => env('AUTH_ORDER', 'firebase,sanctum'),
    'user_model' => \App\Models\User::class,
    'custom_claims' => [
        // 'role' => 'user_role',
    ],
    'auto_create_user' => true,
    'default_user_data' => [
        'name' => 'Dog Dot App User',
    ],
    'sanctum' => [
        'expiration' => null,
        'token_name' => 'firebase-auth-token',
    ],
];
```

## Usage

### Setting up the Guard

In your `config/auth.php` file, add the Firebase guard:

```php
'guards' => [
    // ...
    'firebase' => [
        'driver' => 'firebase',
        'provider' => 'users',
    ],
],
```

### Protecting Routes

You can use the `auth.configurable` middleware to protect your routes:

```php
Route::middleware(['auth.configurable'])->group(function () {
    Route::get('/user', function () {
        return Auth::user();
    });
});
```

This middleware will attempt authentication using the order specified in your configuration.

### Manual Authentication

You can manually authenticate a user using the Firebase guard:

```php
if (Auth::guard('firebase')->check()) {
    $user = Auth::guard('firebase')->user();
    // User is authenticated
}
```

### Custom Claims

You can map custom claims from Firebase to your user model by specifying them in the configuration:

```php
'custom_claims' => [
    'role' => 'firebase_role',
],
```

This will map the 'firebase_role' claim from the Firebase token to the 'role' attribute of your user model.

### Sanctum Integration

If you're using Sanctum, you can configure token expiration and name:

```php
'sanctum' => [
    'expiration' => 60 * 24, // 24 hours
    'token_name' => 'firebase-auth-token',
],
```

This will create a Sanctum token for the user after successful Firebase authentication.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email security@bambolee.digital instead of using the issue tracker.

## Credits

- [Kellvem Barbosa](https://github.com/kellvembarbosa)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.# laravel-firebase-id-token
