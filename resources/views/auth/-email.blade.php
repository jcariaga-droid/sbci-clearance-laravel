
@extends('layouts.app')

@section('page-title', 'Verify Your Email')

@section('content')

<div style="max-width:650px; margin:35px auto; text-align:center; padding:35px 25px;">

    <div style="width:75px; height:75px; background:#dbeafe; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:32px;">
        ✉
    </div>

    <h2 style="color:#203f91; margin-bottom:12px;">
        Verify Your Email Address
    </h2>

    <p style="color:#555; line-height:1.8; margin-bottom:25px;">
        Thank you for registering in the
        <strong>SUBIC BAY COLLEGES (INC).</strong>
        Student Clearance Management System!
        <br><br>
        Before continuing, please check your Gmail inbox
        and click the verification link we sent to your email address.
    </p>

    @if(session('success'))
        <div style="background:#d1fae5; color:#065f46; padding:13px; border-radius:8px; margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:13px; border-radius:8px; margin-bottom:20px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf

        <button type="submit"
            style="background:#203f91; color:white; border:none; padding:14px 25px; border-radius:8px; cursor:pointer; font-weight:bold; width:100%; max-width:280px;">
            RESEND VERIFICATION EMAIL
        </button>
    </form>

    <div style="background:#f1f5f9; padding:15px; border-radius:8px; margin-top:25px;">
        <p style="color:#475569; font-size:13px; margin:0; line-height:1.7;">
            <strong>Reminder:</strong>
            Please check your Spam or Junk folder if you cannot find the verification email.
        </p>
    </div>

    <form method="POST" action="{{ route('logout') }}" style="margin-top:22px;">
        @csrf

        <button type="submit"
            style="background:transparent; border:none; color:#dc2626; cursor:pointer; text-decoration:underline; font-size:13px;">
            Logout
        </button>
    </form>

</div>

@endsection
