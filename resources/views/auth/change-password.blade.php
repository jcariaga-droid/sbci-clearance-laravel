
@extends('layouts.app')

@section('page-title', 'Change Password')

@section('content')

<div style="max-width:650px; background:white; padding:30px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08);">

    <h2 style="font-size:24px; font-weight:bold; margin-bottom:8px;">
        Change Password
    </h2>

    <p style="color:#666; margin-bottom:25px;">
        Update your account password securely.
    </p>

    @if(session('success'))
        <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:8px; margin-bottom:20px;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:8px;">
                Current Password
            </label>
            <input type="password" name="current_password" required
                style="width:100%; padding:12px; border:1px solid #ccc; border-radius:7px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:8px;">
                New Password
            </label>
            <input type="password" name="password" required
                style="width:100%; padding:12px; border:1px solid #ccc; border-radius:7px;">
        </div>

        <div style="margin-bottom:25px;">
            <label style="display:block; font-weight:bold; margin-bottom:8px;">
                Confirm New Password
            </label>
            <input type="password" name="password_confirmation" required
                style="width:100%; padding:12px; border:1px solid #ccc; border-radius:7px;">
        </div>

        <button type="submit"
            style="background:#203f91; color:white; padding:12px 25px; border:none; border-radius:7px; cursor:pointer; font-weight:bold;">
            Update Password
        </button>
    </form>

</div>

@endsection
