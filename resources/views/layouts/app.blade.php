<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page-title', 'Dashboard') | SBCI Clearance System</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            color: #222;
        }

        .sidebar {
            position: fixed;
            width: 250px;
            height: 100vh;
            background: #203f91;
            color: white;
            padding: 25px 18px;
            overflow-y: auto;
        }

        .school-brand {
            text-align: center;
            padding-bottom: 25px;
            margin-bottom: 20px;
            border-bottom: 1px solid #ffffff40;
        }

        .school-logo {
            width: 85px;
            height: 85px;
            object-fit: contain;
            background: white;
            border-radius: 50%;
            padding: 5px;
            display: block;
            margin: 0 auto 12px;
        }

        .school-logo-placeholder {
            width: 85px;
            height: 85px;
            border: 2px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 15px;
            font-weight: bold;
        }

        .school-brand h2 {
            font-size: 17px;
            line-height: 1.4;
            margin: 0;
            letter-spacing: .4px;
        }

        .school-brand p {
            font-size: 11px;
            margin: 7px 0 0;
            color: #dbe4ff;
            letter-spacing: 1px;
        }

        .role {
            color: #dbe4ff;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 8px;
            transition: background .2s;
        }

        .sidebar a:hover {
            background: #3657b5;
        }

        .main {
            margin-left: 250px;
            padding: 30px;
        }

        .main.guest {
            margin-left: 0;
            max-width: 100%;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
        }

        .topbar h2 {
            margin: 0;
        }

        .content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            min-height: 300px;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }

        .logout-button {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
        }

        .logout-button:hover {
            background: #b91c1c;
        }

        @media (max-width: 700px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main {
                margin-left: 0;
                padding: 15px;
            }

            .content {
                padding: 15px;
            }

            .topbar {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>

    @auth
        <aside class="sidebar">

            <div class="school-brand">

                {{-- SBCI LOGO --}}
                @if(file_exists(public_path('images/sbci-logo.png')))
                    <img src="{{ asset('images/sbci-logo.png') }}"
                         alt="SBCI Logo"
                         class="school-logo">
                @else
                    <div class="school-logo-placeholder">SBCI</div>
                @endif

                <h2>SUBIC BAY<br>COLLEGES (INC).</h2>

                <p>STUDENT CLEARANCE SYSTEM</p>
            </div>

            <div class="role">
                {{ ucfirst(auth()->user()->role) }} Portal
            </div>

            @if(auth()->user()->role === 'student')
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('clearance.index') }}">My Clearance</a>

            @elseif(auth()->user()->role === 'staff')
                <a href="{{ route('staff.clearances') }}">Manage Clearances</a>

            @elseif(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.users') }}">Manage Users</a>
                <a href="{{ route('staff.clearances') }}">Manage Clearances</a>
            @endif

            <a href="{{ route('password.edit') }}">Change Password</a>

        </aside>
    @endauth

    <main class="main @guest guest @endguest">

        @auth
            <div class="topbar">
                <h2>@yield('page-title', 'Dashboard')</h2>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">Logout</button>
                </form>
            </div>
        @endauth

        <section class="content">
            @yield('content')
        </section>

    </main>

</body>
</html>
