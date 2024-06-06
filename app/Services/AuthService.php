<?php

namespace App\Services;

use App\Traits\JsonResponseable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthService
{
    use JsonResponseable;

    public function passportPasswordGrant($username, $password): object|null
    {
        try {
            $response = Http::asForm()->post( config('app.url') . '/oauth/token', [
                'grant_type' => 'password',
                'client_id' => '9c2eae5a-a38b-4fb6-b2d2-a976e534c658',
                'client_secret' => 'wSRYC88eYZl9h2xdLqB6a1LNkQMKt3McKIJ9cw9r',
                'username' => $username,
                'password' => $password,
                'scope' => '*',
            ]);
    
            $result = $response->object();
            $statusCode = 200;

            if (($result->error ?? '') === 'invalid_grant') {
                $result->message = 'Username or password wrong.';
                $statusCode = 401;
            }
            
            $this->setJsonResponse(
                response()->json($result, $statusCode)
            );

            return $result;
        } catch (\Throwable $th) {
            Log::error('Error passportPasswordGrant ', ['message' => $th->getMessage()]);
            abort(response()->json(['message' => $th->getMessage()])->status($th->getCode()));
        }
    }
}