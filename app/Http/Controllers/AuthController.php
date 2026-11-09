<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin(Request $request)
{
    Log::info('SBCI LOGIN DIAGNOSTIC', [
        'session_driver' => config('session.driver'),
        'session_cookie' => config('session.cookie'),
        'session_id_present' => filled($request->session()->getId()),
    ]);

    $sessionId = $request->session()->getId();

    return response()
        ->view('auth.login')
        ->withCookie(cookie(
            config('session.cookie'),
            $sessionId,
            config('session.lifetime'),
            config('session.path'),
            config('session.domain'),
            config('session.secure'),
            config('session.http_only'),
            false,
            config('session.same_site')
        ));
}

    public function showRegister()
    {
        return view('auth.register');
    }

    // STUDENT REGISTRATION
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        Auth::login($user);

        $this->sendVerificationEmail($user);

        return redirect()->route('verification.notice')
            ->with('success', 'Account created! Please verify your email.');
    }

    // SEND VERIFICATION EMAIL
    private function sendVerificationEmail($user)
    {
        $link = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => (string) $user->_id,
                'hash' => sha1($user->email),
            ]
        );

        Mail::raw(
            "Hello {$user->name},\n\n"
            . "Thank you for registering in the SBCI Clearance System.\n\n"
            . "Please click the link below to verify your email address:\n\n"
            . $link . "\n\n"
            . "This link will expire in 60 minutes.\n\n"
            . "If you did not create this account, please ignore this email.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('SBCI Clearance System - Verify Your Email');
            }
        );
    }

    // LOGIN
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // STUDENT EMAIL VERIFICATION
            if (
                $user->role === 'student' &&
                empty($user->email_verified_at)
            ) {
                $this->sendVerificationEmail($user);

                return redirect()->route('verification.notice')
                    ->with('success', 'Please verify your email. A new verification link has been sent.');
            }

            // ADMIN
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            // STAFF
            if ($user->role === 'staff') {
                return redirect()->route('staff.clearances');
            }

            // STUDENT
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    // LOGOUT
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // DASHBOARD
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role === 'staff') {
            return redirect()->route('staff.clearances');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $clearances = Clearance::where(
            'student_id',
            $user->email
        )->get();

        return view('dashboard', compact('user', 'clearances'));
    }
}
