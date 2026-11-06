@extends('layouts.app')

@section('page-title', 'Forgot Password')

@section('content')

<div style="max-width:600px; margin:35px auto; padding:35px; text-align:center;">

    <div style="width:75px; height:75px; background:#dbeafe; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:32px;">
        🔑
    </div>

    <h2 style="color:#203f91; margin-bottom:10px;">
        Forgot Your Password?
    </h2>

    <p style="color:#64748b; line-height:1.7; margin-bottom:25px;">
        No worries! Enter your registered email address below.
        We will send you a password reset link so you can create a new password.
    </p>

    @if (session('success'))
        <div style="background:#d1fae5; color:#065f46; padding:13px; border-radius:8px; margin-bottom:18px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:13px; border-radius:8px; margin-bottom:18px; text-align:left;">
            @foreach ($errors->all() as $error)
                <p style="margin:5px 0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST" style="text-align:left;">
        @csrf

        <label style="display:block; color:#334155; font-weight:bold; font-size:14px; margin-bottom:8px;">
            Email Address
        </label>

        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="Enter your registered email"
            required
            style="width:100%; padding:14px; margin-bottom:18px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;"
        >

        <button type="submit"
            style="background:#203f91; color:white; padding:14px; width:100%; border:0; border-radius:8px; cursor:pointer; font-weight:bold;">
            SEND PASSWORD RESET LINK
        </button>
    </form>

    <p style="margin-top:25px; font-size:14px;">
        Remembered your password?
        <a href="{{ route('login') }}" style="color:#203f91; font-weight:bold; text-decoration:none;">
            Back to Login
        </a>
    </p>

    <p style="color:#94a3b8; font-size:11px; margin-top:25px;">
        SUBIC BAY COLLEGES (INC). | STUDENT CLEARANCE MANAGEMENT SYSTEM
    </p>

</div>

@endsection
