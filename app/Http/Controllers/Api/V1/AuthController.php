<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    )
    {
        //
    }

    public function login(Request $request)
    {
        $this->authService->passportPasswordGrant($request->username, $request->password);

        return $this->authService->getJsonResponse();
    }

    public function logout()
    {
        try {
            $userTokens = Auth::user()->tokens;
    
            foreach ($userTokens as $token) {
                $token->revoke();
                $token->delete();
            }
    
            return response()->json(['message' => 'Logout success'], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function me()
    {
        // Load merchant
        Auth::user()->load('merchant');

        return response()->json(Auth::user(), 200);
    }
}
