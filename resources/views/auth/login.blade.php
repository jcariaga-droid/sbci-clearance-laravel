<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBCI | Student Clearance Management System</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #142b61, #203f91, #3657b5);
            display: grid;
            place-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 38px 35px;
            border-radius: 16px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 12px 35px #0004;
            text-align: center;
        }

        .school-logo {
            width: 95px;
            height: 95px;
            object-fit: contain;
            border-radius: 50%;
            padding: 4px;
            background: white;
            display: block;
            margin: 0 auto 15px;
        }

        .school-name {
            color: #203f91;
            font-size: 19px;
            font-weight: bold;
            margin: 0;
            letter-spacing: .4px;
        }

        .school-subtitle {
            color: #64748b;
            font-size: 11px;
            margin-top: 8px;
            letter-spacing: 1px;
        }

        .divider {
            height: 2px;
            background: #e2e8f0;
            margin: 25px 0;
        }

        h2 {
            color: #203f91;
            margin-bottom: 7px;
            font-size: 25px;
        }

        .description {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 22px;
        }

        input {
            width: 100%;
            padding: 14px;
            margin: 9px 0;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            background: #f8fafc;
        }

        input:focus {
            outline: 2px solid #93c5fd;
            border-color: #203f91;
            background: white;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 18px;
            background: #203f91;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: .2s;
        }

        button:hover {
            background: #142b61;
        }

        a {
            color: #203f91;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: #b91c1c;
            background: #fee2e2;
            padding: 12px;
            border-radius: 7px;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .success {
            color: #065f46;
            background: #d1fae5;
            padding: 12px;
            border-radius: 7px;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .register-text {
            font-size: 13px;
            margin-top: 22px;
            color: #475569;
        }

        .footer {
            color: #94a3b8;
            font-size: 11px;
            margin-top: 25px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 30px 22px;
            }

            .school-name {
                font-size: 17px;
            }
        }
    </style>
</head>

<body>

<div class="card">

    <img
        src="{{ asset('images/sbci-logo.png') }}"
        alt="Subic Bay Colleges Logo"
        class="school-logo"
    >

    <p class="school-name">SUBIC BAY COLLEGES (INC).</p>

    <p class="school-subtitle">
        STUDENT CLEARANCE MANAGEMENT SYSTEM
    </p>

    <div class="divider"></div>

    <h2>Welcome Back!</h2>

    <p class="description">
        Login to access your account
    </p>

    @if (session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p class="error">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <input
            type="email"
            name="email"
            placeholder="Email address"
            value="{{ old('email') }}"
            autocomplete="email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            autocomplete="current-password"
            required
        >

        <button type="submit">LOGIN</button>
    </form>

    <p class="register-text">
        Don't have an account?
        <a href="{{ route('register') }}">Register here</a>
    </p>

    <p class="footer">
        &copy; {{ date('Y') }} Subic Bay Colleges (Inc). All Rights Reserved.
    </p>

</div>

</body>
</html>
