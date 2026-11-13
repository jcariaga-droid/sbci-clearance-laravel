
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SBCI | Student Registration</title>

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
            padding: 35px;
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
            margin: 0 auto 15px;
            display: block;
        }

        .school-name {
            color: #203f91;
            font-size: 19px;
            font-weight: bold;
            margin: 0;
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
            margin: 22px 0;
        }

        h2 {
            color: #203f91;
            margin-bottom: 7px;
            font-size: 23px;
        }

        .description {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 20px;
        }

        input, select {
            width: 100%;
            padding: 14px;
            margin: 8px 0;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            background: #f8fafc;
        }

        input:focus, select:focus {
            outline: 2px solid #93c5fd;
            border-color: #203f91;
            background: white;
        }

        select {
            color: #475569;
            cursor: pointer;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 15px;
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
            padding: 10px;
            border-radius: 7px;
            font-size: 13px;
            text-align: left;
            margin-bottom: 15px;
        }

        .error p {
            margin: 5px 0;
        }

        .login-text {
            color: #475569;
            font-size: 13px;
            margin-top: 22px;
        }

        .footer {
            color: #94a3b8;
            font-size: 11px;
            margin-top: 25px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 28px 22px;
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
        alt="SBCI Logo"
        class="school-logo"
    >

    <p class="school-name">SUBIC BAY COLLEGES (INC).</p>

    <p class="school-subtitle">
        STUDENT CLEARANCE MANAGEMENT SYSTEM
    </p>

    <div class="divider"></div>

    <h2>Create Student Account</h2>

    <p class="description">
        Register to access the clearance system
    </p>

    @if ($errors->any())
        <div class="error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}">
        @csrf

        <input
            type="text"
            name="name"
            placeholder="Full name"
            value="{{ old('name') }}"
            autocomplete="name"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Email address"
            value="{{ old('email') }}"
            autocomplete="email"
            required
        >

        <select name="program" required>
            <option value="">Select your program</option>

            @foreach ([
                'BEEd – GenEd',
                'BSEd – Math',
                'BSHM',
                'BSCA',
                'BSIT',
                'BSTM',
                'BSEntrep',
                'BSCrim',
            ] as $program)
                <option
                    value="{{ $program }}"
                    {{ old('program') === $program ? 'selected' : '' }}
                >
                    {{ $program }}
                </option>
            @endforeach
        </select>

        <input
            type="password"
            name="password"
            placeholder="Password (8+ characters)"
            autocomplete="new-password"
            required
        >

        <input
            type="password"
            name="password_confirmation"
            placeholder="Confirm password"
            autocomplete="new-password"
            required
        >

        <button type="submit">CREATE ACCOUNT</button>
    </form>

    <p class="login-text">
        Already registered?
        <a href="{{ route('login') }}">Login here</a>
    </p>

    <p class="footer">
        © {{ date('Y') }} Subic Bay Colleges (Inc). All Rights Reserved.
    </p>

</div>

</body>
</html>
