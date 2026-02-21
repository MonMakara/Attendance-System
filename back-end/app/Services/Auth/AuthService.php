<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Exception;

/**
 * AuthService handles the initial credential verification logic.
 * It abstracts the process of checking if a user exists and if their password matches.
 */
class AuthService
{
    /**
     * Find a user by email or phone.
     */
    public function findUserByLogin(string $login)
    {
        return User::where('email', $login)
                    ->orWhere('phone', $login)
                    ->first();
    }

    /**
     * Verify user credentials (Email/Phone + Password).
     */
    public function verifyCredentials(string $login, string $password)
    {
        // Find user using common lookup logic
        $user = $this->findUserByLogin($login);

        // Check if user exists
        if (!$user) {
            throw new Exception("User not found.", 404);
        }

        // Validate password using Laravel's Hash utility
        if (!Hash::check($password, $user->password)) {
            throw new Exception("Invalid login credentials.", 401);
        }

        return $user;
    }
}
