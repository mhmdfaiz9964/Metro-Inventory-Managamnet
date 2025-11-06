<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Override redirectTo method to redirect users based on role.
     *
     * @return string
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            return '/admin/dashboard';
        } elseif ($user->hasRole('User')) {
            return '/user/dashboard';
        }

        return '/home'; 
    }

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
