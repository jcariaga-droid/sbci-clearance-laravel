@extends('layouts.app')

@section('page-title', 'My Clearance')

@section('content')
<style>
    .clearance-card {
        background: #fff;
        padding: 22px;
        border-radius: 10px;
        margin-bottom: 22px;
    }

    .clearance-card select {
        width: 100%;
        max-width: 400px;
        padding: 10px;
        margin: 8px 0 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
    }

    .clearance-card button {
        background: #203f91;
        color: #fff;
        border: 0;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
    }

    .clearance-table {
        width: 100%;
        border-collapse: collapse;
    }

    .clearance-table th,
    .clearance-table td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    .clearance-table th {
        background: #203f91;
        color: white;
    }

    .table-wrapper {
        overflow-x: auto;
    }
</style>

<h2>My Clearance</h2>
<p>Submit your clearance request and monitor its status.</p>

@if(session('success'))
    <div style="color:green;margin:12px 0;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="color:#dc2626;margin:12px 0;">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div style="color:#dc2626;margin:12px 0;">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="clearance-card">
    <h3>Submit Clearance Request</h3>

    <form method="POST" action="{{ route('clearance.store') }}">
        @csrf

        <label for="department">Department</label><br>

        <select name="department" id="department" required>
            <option value="">-- Select Department --</option>
            <option value="DEAM PROGRAM">DEAM PROGRAM</option>
            <option value="LIBRARY">LIBRARY</option>
            <option value="PROPERTY CUSTODIAN">PROPERTY CUSTODIAN</option>
            <option value="ACCOUNTING">ACCOUNTING</option>
            <option value="REGISTRAR">REGISTRAR</option>
            <option value="OSAS">OSAS</option>
        </select>

        <br>
        <button type="submit">Submit Request</button>
    </form>
</div>

<div class="clearance-card">
    <h3>My Clearance Records</h3>

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
                @forelse($clearances as $clearance)
                    <tr>
                        <td>{{ $clearance->department ?? '—' }}</td>
                        <td>{{ $clearance->status ?? 'Pending' }}</td>
                        <td>{{ $clearance->remarks ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No clearance requests submitted yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
