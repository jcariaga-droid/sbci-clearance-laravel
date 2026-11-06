<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return view('auth.verify-email');
    }

    public function verify(Request $request, $id, $hash)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Verification link is invalid or expired.');
        }

        $user = User::find($id);

        if (!$user || sha1($user->email) !== $hash) {
            abort(403, 'Invalid verification link.');
        }

        if (empty($user->email_verified_at)) {
            $user->email_verified_at = now();
            $user->save();
        }

        return redirect()->route('login')
            ->with('success', 'Email verified successfully! You can now log in.');
    }

    public function resend(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!empty($user->email_verified_at)) {
            return redirect()->route('dashboard');
        }

        $link = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => (string) $user->_id,
                'hash' => sha1($user->email),
            ]
        );

        \Illuminate\Support\Facades\Mail::raw(
            "Hello {$user->name},\n\nPlease verify your email using this link:\n\n{$link}\n\nThis link expires in 60 minutes.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('SBCI Clearance System - Verify Your Email');
            }
        );

        return back()->with('success', 'A new verification link has been sent to your email.');
    }
}
