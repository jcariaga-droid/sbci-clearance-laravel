
@extends('layouts.app')

@section('page-title', 'Verify Your Email')

@section('content')
<div style="max-width:600px; margin:40px auto; text-align:center; padding:30px; background:white; border-radius:12px;">

    <h2 style="color:#203f91; margin-bottom:15px;">
        Verify Your Email Address
    </h2>

    <p style="color:#555; line-height:1.7; margin-bottom:25px;">
        Thank you for registering in the SBCI Clearance System!
        Please check your Gmail inbox and click the verification link
        we sent to your email address.
    </p>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- WARNING MESSAGE --}}
    @if(session('warning'))
        <div style="background:#fef3c7; color:#92400e; padding:12px; border-radius:6px; margin-bottom:20px;">
            {{ session('warning') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:6px; margin-bottom:20px;">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- RESEND VERIFICATION EMAIL --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" style="background:#203f91; color:white; padding:12px 20px; border:0; border-radius:6px; cursor:pointer;">
            Resend Verification Email
        </button>
    </form>

    <p style="color:#777; margin-top:20px;">
        Check your Spam or Junk folder if you cannot find the email.
    </p>

    {{-- LOGOUT --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="background:#6b7280; color:white; padding:10px 20px; border:0; border-radius:6px; cursor:pointer;">
            Logout
        </button>
    </form>

</div>
@endsection
