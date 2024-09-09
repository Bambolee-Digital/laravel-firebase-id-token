<?php

namespace BamboleeDigital\LaravelFirebaseIdToken\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Illuminate\Support\Facades\Config;

class FirebaseAuthService
{
    protected $auth;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(base64_decode(Config::get('bambolee-firebase.credentials_base64')));
        $this->auth = $factory->createAuth();
    }

    public function verifyIdToken($idToken)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            $claims = $verifiedIdToken->claims()->all();

            foreach (Config::get('bambolee-firebase.custom_claims', []) as $key => $claim) {
                if (isset($claims[$claim])) {
                    $claims[$key] = $claims[$claim];
                }
            }

            return $claims;
        } catch (FailedToVerifyToken $e) {
            return null;
        }
    }
}