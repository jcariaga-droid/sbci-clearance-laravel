<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    private function users()
    {
        return DB::connection('mongodb')
            ->getClient()
            ->selectDatabase('sbci_clearance')
            ->selectCollection('users');
    }

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = $this->users()->findOne([
            'email' => $request->email,
        ]);

        if (!$user) {
            return back()->withErrors([
                'email' => 'Email address not found.',
            ]);
        }

        $token = Str::random(64);

        $this->users()->updateOne(
            ['email' => $request->email],
            ['$set' => [
                'reset_token' => Hash::make($token),
                'reset_token_expires' => time() + 3600,
            ]]
        );

        $link = route('password.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);

        Mail::raw(
            "Hello {$user->name},\n\n"
            . "You requested to reset your password.\n\n"
            . "Click this link to reset your password:\n"
            . $link . "\n\n"
            . "This link will expire in 60 minutes.\n\n"
            . "If you did not request this, ignore this email.",
            function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('SBCI Clearance System - Password Reset');
            }
        );

        return back()->with(
            'success',
            'Password reset link sent to your email!'
        );
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->users()->findOne([
            'email' => $request->email,
        ]);

        if (
            !$user ||
            empty($user->reset_token) ||
            empty($user->reset_token_expires) ||
            $user->reset_token_expires < time() ||
            !Hash::check($request->token, $user->reset_token)
        ) {
            return back()->withErrors([
                'email' => 'Invalid or expired password reset link.',
            ]);
        }

        $this->users()->updateOne(
            ['email' => $request->email],
            [
                '$set' => [
                    'password' => Hash::make($request->password),
                ],
                '$unset' => [
                    'reset_token' => '',
                    'reset_token_expires' => '',
                ],
            ]
        );

        return redirect()->route('login')->with(
            'success',
            'Your password has been reset successfully. You can now log in.'
        );
    }
}
