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
                'client_id' => env('CLIENT_ID'),
                'client_secret' => env('CLIENT_SECRET'),
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