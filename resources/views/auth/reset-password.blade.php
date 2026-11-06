
@extends('layouts.app')

@section('page-title', 'Reset Password')

@section('content')

<div style="max-width:600px; margin:35px auto; padding:35px; text-align:center;">

    <div style="width:75px; height:75px; background:#dbeafe; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:32px;">
        🔒
    </div>

    <h2 style="color:#203f91; margin-bottom:10px;">
        Reset Your Password
    </h2>

    <p style="color:#64748b; line-height:1.7; margin-bottom:25px;">
        Create a new password for your SBCI Clearance System account.
        Make sure your password contains at least 8 characters.
    </p>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:13px; border-radius:8px; margin-bottom:18px; text-align:left;">
            @foreach ($errors->all() as $error)
                <p style="margin:5px 0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('password.store') }}" method="POST" style="text-align:left;">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <label style="display:block; color:#334155; font-weight:bold; font-size:14px; margin-bottom:8px;">
            New Password
        </label>

        <input
            type="password"
            name="password"
            placeholder="Enter new password"
            required
            minlength="8"
            autocomplete="new-password"
            style="width:100%; padding:14px; margin-bottom:20px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;"
        >

        <label style="display:block; color:#334155; font-weight:bold; font-size:14px; margin-bottom:8px;">
            Confirm New Password
        </label>

        <input
            type="password"
            name="password_confirmation"
            placeholder="Confirm your new password"
            required
            minlength="8"
            autocomplete="new-password"
            style="width:100%; padding:14px; margin-bottom:20px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;"
        >

        <button type="submit"
            style="background:#203f91; color:white; padding:14px; width:100%; border:0; border-radius:8px; cursor:pointer; font-weight:bold;">
            RESET PASSWORD
        </button>
    </form>

    <p style="margin-top:25px; font-size:14px;">
        <a href="{{ route('login') }}" style="color:#203f91; text-decoration:none; font-weight:bold;">
            Back to Login
        </a>
    </p>

    <p style="color:#94a3b8; font-size:11px; margin-top:25px;">
        SUBIC BAY COLLEGES (INC). | STUDENT CLEARANCE MANAGEMENT SYSTEM
    </p>

</div>

@endsection
