
@extends('layouts.app')

@section('page-title', 'Admin Dashboard')

@section('content')

<style>
    .admin-container {
        width: 100%;
    }

    .welcome-card {
        background: linear-gradient(135deg, #203f91, #142b68);
        color: white;
        padding: 30px;
        border-radius: 14px;
        margin-bottom: 28px;
        box-shadow: 0 5px 15px rgba(32, 63, 145, 0.15);
    }

    .school-label {
        display: inline-block;
        color: #dbeafe;
        font-size: 12px;
        font-weight: bold;
        letter-spacing: 1px;
        margin-bottom: 12px;
    }

    .welcome-card h1 {
        margin: 0 0 10px;
        font-size: 27px;
    }

    .welcome-card p {
        color: #e2e8f0;
        margin: 6px 0;
        line-height: 1.6;
        font-size: 14px;
    }

    .section-heading {
        color: #1e293b;
        font-size: 21px;
        margin: 0 0 7px;
    }

    .section-description {
        color: #64748b;
        font-size: 14px;
        margin: 0 0 22px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 18px;
    }

    .stat-card {
        background: white;
        padding: 23px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.07);
    }

    .stat-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 17px;
    }

    .stat-card h3 {
        font-size: 13px;
        margin: 0;
        color: #64748b;
        font-weight: 600;
    }

    .stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: bold;
    }

    .stat-card h2 {
        font-size: 32px;
        margin: 0;
        color: #1e293b;
    }

    .students .stat-icon {
        background: #dbeafe;
        color: #203f91;
    }

    .staff .stat-icon {
        background: #e2e8f0;
        color: #475569;
    }

    .pending .stat-icon {
        background: #fef3c7;
        color: #d97706;
    }

    .approved .stat-icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .rejected .stat-icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .dashboard-note {
        margin-top: 28px;
        padding: 20px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-left: 4px solid #203f91;
        border-radius: 9px;
        color: #1e3a8a;
    }

    .dashboard-note strong {
        font-size: 15px;
    }

    .dashboard-note p {
        margin: 7px 0 0;
        font-size: 14px;
        line-height: 1.6;
    }

    @media(max-width: 600px) {
        .welcome-card {
            padding: 22px;
        }

        .welcome-card h1 {
            font-size: 22px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 11px;
        }

        .stat-card {
            padding: 16px;
        }

        .stat-card h2 {
            font-size: 27px;
        }

        .stat-card h3 {
            font-size: 12px;
        }
    }
</style>

<div class="admin-container">

    <div class="welcome-card">

        <span class="school-label">
            SUBIC BAY COLLEGES (INC).
        </span>

        <h1>Welcome, {{ auth()->user()->name }}!</h1>

        <p>You are logged in as an Administrator.</p>

        <p>
            Manage student and staff accounts and monitor clearance requests
            through the SBCI Student Clearance Management System.
        </p>

    </div>

    <h2 class="section-heading">System Overview</h2>

    <p class="section-description">
        Summary of registered users and student clearance requests.
    </p>

    <div class="stats-grid">

        <div class="stat-card students">
            <div class="stat-top">
                <h3>Total Students</h3>
                <div class="stat-icon">S</div>
            </div>
            <h2>{{ $totalStudents }}</h2>
        </div>

        <div class="stat-card staff">
            <div class="stat-top">
                <h3>Total Staff</h3>
                <div class="stat-icon">T</div>
            </div>
            <h2>{{ $totalStaff }}</h2>
        </div>

        <div class="stat-card pending">
            <div class="stat-top">
                <h3>Pending Clearances</h3>
                <div class="stat-icon">P</div>
            </div>
            <h2>{{ $pending }}</h2>
        </div>

        <div class="stat-card approved">
            <div class="stat-top">
                <h3>Approved Clearances</h3>
                <div class="stat-icon">A</div>
            </div>
            <h2>{{ $approved }}</h2>
        </div>

        <div class="stat-card rejected">
            <div class="stat-top">
                <h3>Rejected Clearances</h3>
                <div class="stat-icon">R</div>
            </div>
            <h2>{{ $rejected }}</h2>
        </div>

    </div>

    <div class="dashboard-note">
        <strong>Administrator Access</strong>

        <p>
            Use the sidebar to manage registered student and staff accounts,
            update user roles, and monitor student clearance requests.
        </p>
    </div>

</div>

@endsection
