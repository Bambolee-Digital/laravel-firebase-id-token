<?php

namespace BamboleeDigital\LaravelFirebaseIdToken\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Auth\UserProvider;
use BamboleeDigital\LaravelFirebaseIdToken\Services\FirebaseAuthService;

class FirebaseGuard implements Guard
{
    protected $request;
    protected $provider;
    protected $user;
    protected $firebaseAuth;
    protected $userModel;

    public function __construct(UserProvider $provider, Request $request, FirebaseAuthService $firebaseAuth)
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->firebaseAuth = $firebaseAuth;
        $this->userModel = Config::get('bambolee-firebase.user_model', Config::get('auth.providers.users.model'));
    }

    public function check()
    {
        return ! is_null($this->user());
    }

    public function guest()
    {
        return ! $this->check();
    }

    public function user()
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        $token = $this->request->bearerToken();

        if (!$token) {
            return null;
        }

        $firebaseUser = $this->firebaseAuth->verifyIdToken($token);

        if (!$firebaseUser || !isset($firebaseUser['email'])) {
            return null;
        }

        if (!isset($firebaseUser['name'])) {
            $firebaseUser['name'] = Config::get('bambolee-firebase.default_user_data.name', 'Dog Dot App User');
        }

        try {
            $user = $this->userModel::where('email', $firebaseUser['email'])->first();

            if (!$user && Config::get('bambolee-firebase.auto_create_user', true)) {
                $user = $this->userModel::create([
                    'name' => $firebaseUser['name'],
                    'email' => $firebaseUser['email'],
                    'password' => bcrypt(Str::random(16)),
                    'external_id' => $firebaseUser['user_id'],
                ]);
            } elseif ($user) {
                $updateData = [
                    'name' => $firebaseUser['name'],
                    'external_id' => $firebaseUser['user_id'],
                ];
                $user->update($updateData);
            }

            if ($user && Config::get('bambolee-firebase.sanctum.expiration') !== null) {
                $user->tokens()->delete();
                $token = $user->createToken(
                    Config::get('bambolee-firebase.sanctum.token_name', 'firebase-auth-token'),
                    ['*'],
                    now()->addMinutes(Config::get('bambolee-firebase.sanctum.expiration'))
                );
                $this->request->headers->set('Authorization', 'Bearer ' . $token->plainTextToken);
            }
        } catch (\Exception $e) {
            Log::error('Error updating user', [
                'idToken' => $token,
                'firebaseUser' => $firebaseUser,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }

        $this->user = $user;
        $this->setUser($user);
        return $this->user;
    }

    public function id()
    {
        if ($user = $this->user()) {
            return $user->getAuthIdentifier();
        }
    }

    public function validate(array $credentials = [])
    {
        return ! is_null($this->user());
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function hasUser()
    {
        return $this->user() !== null;
    }
}