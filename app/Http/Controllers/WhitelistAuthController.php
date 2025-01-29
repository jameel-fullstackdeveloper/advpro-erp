<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WhitelistAuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('admin.whitelist-login');
    }

    // Handle the login attempt
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('whitelist')->attempt($credentials, $request->remember)) {
            $user = Auth::guard('whitelist')->user();

            // Check if the user has the required roles
            if ($user->hasRole('Administrator') || $user->hasRole('Super Admin')) {
                // Redirect to the whitelist management page after login
                return redirect()->route('admin.whitelist');
            } else {
                // Logout and redirect back with an error message
                Auth::guard('whitelist')->logout();
                return redirect()->back()->with('error', 'You do not have the necessary role to access this area');
            }
        }

        return redirect()->back()->with('error', 'Invalid credentials');
    }


    // Logout the admin user from the whitelist section
    public function logout()
    {
        Auth::guard('whitelist')->logout();
        return redirect()->route('whitelist.login');
    }
}
