<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        $result = DB::connection('mongodb')
            ->getClient()
            ->selectDatabase('sbci_clearance')
            ->selectCollection('users')
            ->updateOne(
                ['email' => $user->email],
                [
                    '$set' => [
                        'password' => Hash::make($request->password),
                    ],
                ]
            );

        if ($result->getMatchedCount() === 0) {
            return back()->withErrors([
                'password' => 'Account not found. Password was not updated.',
            ]);
        }

        return back()->with('success', 'Password changed successfully!');
    }
}
