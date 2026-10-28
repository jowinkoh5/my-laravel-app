<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IssueTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Issues and revokes the Sanctum personal access tokens that the product API
 * authenticates with.
 */
class TokenController extends Controller
{
    /**
     * Exchange credentials for a bearer token.
     */
    public function store(IssueTokenRequest $request): JsonResponse
    {
        $user = $request->authenticate();

        // A fresh token per device keeps revocation granular; issuing a new one
        // for the same device name replaces nothing, so stale tokens for that
        // device are removed first.
        $user->tokens()->where('name', $request->string('device_name'))->delete();

        $token = $user->createToken($request->string('device_name')->toString());

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                ],
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Revoke the token that authenticated the current request.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revoked.']);
    }
}
