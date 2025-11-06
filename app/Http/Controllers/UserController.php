<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard()
    {
        if (!auth()->user()->hasRole('User')) {
            abort(403, 'Unauthorized access');
        }

        return view('user.dashboard');
    }
}
