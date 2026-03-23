<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Le credenziali fornite non sono corrette.'],
            ]);
        }
        $abilites = $this->getAbilitiesByRole($user->role);

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'abilities' => $abilites,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revocato.']);
    }
    /**
     * Restituisce le abilità associate a un ruolo
     *
     * @param string $role
     * @return array
    */
    public function getAbilitiesByRole(string $role): array
    {
        return match($role){
            'pi', 'manager' => [
                'export:projects',
                'export:publications',
                'export:users',
            ],
            'researcher' => [
                'export:projects',
                'export:publications',
            ],
            default => [],
        };
    }
}