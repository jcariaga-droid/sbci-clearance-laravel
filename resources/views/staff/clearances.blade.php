
@extends('layouts.app')

@section('page-title', 'Manage Clearances')

@section('content')

<style>
    .staff-container {
        width: 100%;
    }

    .page-header {
        background: linear-gradient(135deg, #203f91, #142b68);
        color: white;
        padding: 25px 30px;
        border-radius: 14px;
        margin-bottom: 22px;
        box-shadow: 0 5px 15px rgba(32, 63, 145, 0.12);
    }

    .page-header h2 {
        margin: 0 0 8px;
        font-size: 25px;
    }

    .page-header p {
        margin: 0;
        color: #e2e8f0;
        font-size: 14px;
    }

    .content-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }

    .section-title {
        color: #1e293b;
        margin: 0 0 7px;
        font-size: 21px;
    }

    .section-description {
        color: #64748b;
        margin: 0 0 20px;
        font-size: 14px;
    }

    .clearance-table {
        width: 100%;
        min-width: 950px;
        border-collapse: collapse;
    }

    .clearance-table th,
    .clearance-table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: top;
        font-size: 13px;
    }

    .clearance-table th {
        background: #203f91;
        color: white;
        font-weight: 600;
        white-space: nowrap;
    }

    .clearance-table tbody tr:hover {
        background: #f8fafc;
    }

    .clearance-table select,
    .clearance-table textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 10px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        margin-bottom: 9px;
        font-family: Arial, sans-serif;
        font-size: 13px;
        outline: none;
    }

    .clearance-table select:focus,
    .clearance-table textarea:focus {
        border-color: #203f91;
        box-shadow: 0 0 0 2px rgba(32,63,145,0.10);
    }

    .clearance-table textarea {
        min-width: 160px;
        resize: vertical;
    }

    .clearance-table button {
        width: 100%;
        background: #203f91;
        color: white;
        border: none;
        padding: 10px 13px;
        border-radius: 7px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.2s;
    }

    .clearance-table button:hover {
        background: #172e70;
    }

    .table-wrapper {
        overflow-x: auto;
        border-radius: 8px;
    }

    .success-message {
        background: #dcfce7;
        color: #166534;
        padding: 13px 15px;
        border-radius: 8px;
        margin: 15px 0;
        font-size: 14px;
        border: 1px solid #bbf7d0;
    }

    .error-message {
        background: #fee2e2;
        color: #991b1b;
        padding: 13px 15px;
        border-radius: 8px;
        margin: 15px 0;
        font-size: 14px;
        border: 1px solid #fecaca;
    }

    .status {
        display: inline-block;
        padding: 6px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        white-space: nowrap;
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

    .student-email {
        color: #475569;
        word-break: break-word;
    }

    .empty-state {
        text-align: center !important;
        color: #64748b;
        padding: 30px !important;
    }

    @media(max-width: 600px) {
        .page-header {
            padding: 22px;
        }

        .page-header h2 {
            font-size: 21px;
        }

        .content-card {
            padding: 15px;
        }
    }
</style>

<div class="staff-container">

    <div class="page-header">
        <h2>Clearance Management</h2>
        <p>SUBIC BAY COLLEGES (INC). | Student Clearance Management System</p>
    </div>

    <div class="content-card">

        <h3 class="section-title">Clearance Records</h3>

        <p class="section-description">
            Review student clearance requests and update their approval status and remarks.
        </p>

        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="table-wrapper">
            <table class="clearance-table">

                <thead>
                    <tr>
                        <th>Student ID / Email</th>
                        <th>Department</th>
                        <th>Current Status</th>
                        <th>Current Remarks</th>
                        <th>Update Clearance</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($clearances as $clearance)

                        @php
                            $statusClass = match(strtolower($clearance->status ?? '')) {
                                'approved' => 'status-approved',
                                'pending' => 'status-pending',
                                'rejected' => 'status-rejected',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td class="student-email">
                                {{ $clearance->student_id }}
                            </td>

                            <td>
                                {{ $clearance->department }}
                            </td>

                            <td>
                                <span class="status {{ $statusClass }}">
                                    {{ $clearance->status }}
                                </span>
                            </td>

                            <td>
                                {{ $clearance->remarks ?? '—' }}
                            </td>

                            <td>
                                <form method="POST"
                                    action="{{ route('staff.clearances.update', $clearance->id) }}">

                                    @csrf
                                    @method('PUT')

                                    <select name="status" required>
                                        <option value="Pending"
                                            {{ $clearance->status === 'Pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>

                                        <option value="Approved"
                                            {{ $clearance->status === 'Approved' ? 'selected' : '' }}>
                                            Approved
                                        </option>

                                        <option value="Rejected"
                                            {{ $clearance->status === 'Rejected' ? 'selected' : '' }}>
                                            Rejected
                                        </option>
                                    </select>

                                    <textarea
                                        name="remarks"
                                        rows="2"
                                        placeholder="Enter remarks">{{ $clearance->remarks }}</textarea>

                                    <button type="submit">
                                        Save Changes
                                    </button>

                                </form>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="empty-state">
                                No clearance records found.
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

</div>

@endsection
