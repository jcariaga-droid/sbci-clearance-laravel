
@extends('layouts.app')

@section('page-title', 'Student Dashboard')

@section('content')

@php
    $totalClearances = $clearances->count();
    $approvedCount = $clearances->where('status', 'Approved')->count();
    $pendingCount = $clearances->where('status', 'Pending')->count();
    $rejectedCount = $clearances->where('status', 'Rejected')->count();
@endphp

<style>
    .dashboard-container {
        width: 100%;
    }

    .welcome-card {
        background: linear-gradient(135deg, #203f91, #142b68);
        color: white;
        padding: 30px;
        border-radius: 14px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(32, 63, 145, 0.15);
    }

    .welcome-card h1 {
        margin: 0 0 10px;
        font-size: 27px;
    }

    .welcome-card p {
        margin: 6px 0;
        color: #e2e8f0;
        line-height: 1.6;
    }

    .school-label {
        display: inline-block;
        font-size: 12px;
        font-weight: bold;
        letter-spacing: 1px;
        color: #dbeafe;
        margin-bottom: 12px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }

    .stat-card p {
        color: #64748b;
        font-size: 13px;
        margin: 0 0 10px;
    }

    .stat-card h2 {
        margin: 0;
        font-size: 29px;
        color: #203f91;
    }

    .clearance-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }

    .section-heading {
        margin: 0 0 7px;
        color: #1e293b;
        font-size: 21px;
    }

    .section-description {
        color: #64748b;
        margin: 0 0 20px;
        font-size: 14px;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .clearance-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px;
    }

    .clearance-table th {
        background: #203f91;
        color: white;
        padding: 14px;
        text-align: left;
        font-size: 13px;
    }

    .clearance-table td {
        padding: 15px 14px;
        border-bottom: 1px solid #e5e7eb;
        color: #334155;
        font-size: 14px;
    }

    .clearance-table tr:last-child td {
        border-bottom: none;
    }

    .clearance-table tbody tr:hover {
        background: #f8fafc;
    }

    .status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }

    .status-approved {
        background: #dcfce7;
        color: #166534;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-default {
        background: #e2e8f0;
        color: #475569;
    }

    .clearance-button {
        display: inline-block;
        margin-top: 22px;
        background: #203f91;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        font-size: 14px;
        transition: 0.2s;
    }

    .clearance-button:hover {
        background: #172e70;
        color: white;
        transform: translateY(-1px);
    }

    .empty-state {
        text-align: center;
        color: #64748b;
        padding: 25px !important;
    }

    @media(max-width: 900px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width: 600px) {
        .welcome-card {
            padding: 22px;
        }

        .welcome-card h1 {
            font-size: 22px;
        }

        .stats-grid {
            gap: 10px;
        }

        .stat-card {
            padding: 15px;
        }

        .stat-card h2 {
            font-size: 24px;
        }

        .clearance-card {
            padding: 16px;
        }
    }
</style>

<div class="dashboard-container">

    <div class="welcome-card">
        <span class="school-label">SUBIC BAY COLLEGES (INC).</span>

        <h1>Welcome, {{ $user->name }}!</h1>

        <p>You are now logged in to the SBCI Student Clearance Management System.</p>
        <p>Monitor your clearance requests and check their approval status here.</p>
    </div>

    <div class="stats-grid">

        <div class="stat-card">
            <p>Total Clearance Requests</p>
            <h2>{{ $totalClearances }}</h2>
        </div>

        <div class="stat-card">
            <p>Approved</p>
            <h2>{{ $approvedCount }}</h2>
        </div>

        <div class="stat-card">
            <p>Pending</p>
            <h2>{{ $pendingCount }}</h2>
        </div>

        <div class="stat-card">
            <p>Rejected</p>
            <h2>{{ $rejectedCount }}</h2>
        </div>

    </div>

    <div class="clearance-card">

        <h2 class="section-heading">My Clearance Status</h2>

        <p class="section-description">
            Track the status and remarks of your submitted clearance requests.
        </p>

        <div class="table-wrapper">
            <table class="clearance-table">

                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($clearances as $clearance)
                        @php
                            $statusClass = match(strtolower($clearance->status ?? '')) {
                                'approved' => 'status-approved',
                                'pending' => 'status-pending',
                                'rejected' => 'status-rejected',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td>{{ $clearance->department }}</td>

                            <td>
                                <span class="status {{ $statusClass }}">
                                    {{ $clearance->status }}
                                </span>
                            </td>

                            <td>{{ $clearance->remarks ?? '—' }}</td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">
                                No clearance records assigned yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <a href="{{ route('clearance.index') }}" class="clearance-button">
            Go to My Clearance →
        </a>

    </div>

</div>

@endsection
