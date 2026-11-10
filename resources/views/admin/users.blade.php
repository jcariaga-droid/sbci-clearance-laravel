
@extends('layouts.app')

@section('page-title', 'Manage Users')

@section('content')

<style>
    .users-container {
        width: 100%;
    }

    .page-header {
        background: linear-gradient(135deg, #203f91, #142b68);
        color: white;
        padding: 27px 30px;
        border-radius: 14px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(32,63,145,0.12);
    }

    .page-header h2 {
        margin: 0 0 8px;
        font-size: 25px;
    }

    .page-header p {
        margin: 0;
        color: #dbeafe;
        font-size: 14px;
    }

    .users-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 24px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }

    .card-heading {
        color: #1e293b;
        font-size: 20px;
        margin: 0 0 7px;
    }

    .card-description {
        color: #64748b;
        font-size: 14px;
        margin: 0 0 20px;
    }

    .create-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        background: #203f91;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 7px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn:hover {
        background: #162e70;
        color: white;
    }

    .close-btn {
        background: #64748b;
    }

    .close-btn:hover {
        background: #475569;
    }

    .edit-btn {
        background: #d97706;
    }

    .edit-btn:hover {
        background: #b45309;
    }

    .users-table {
        width: 100%;
        min-width: 750px;
        border-collapse: collapse;
    }

    .users-table th,
    .users-table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: middle;
        font-size: 13px;
    }

    .users-table th {
        background: #203f91;
        color: white;
        font-weight: 600;
        white-space: nowrap;
    }

    .users-table tbody tr:hover {
        background: #f8fafc;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .users-table select {
        padding: 8px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: white;
        font-size: 12px;
    }

    .role-badge {
        display: inline-block;
        padding: 6px 11px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
    }

    .student-badge {
        background: #dbeafe;
        color: #1e40af;
    }

    .staff-badge {
        background: #e2e8f0;
        color: #475569;
    }

    .admin-badge {
        background: #ede9fe;
        color: #6d28d9;
    }

    .message {
        padding: 13px 15px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .success-message {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .error-message {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .error-message p {
        margin: 4px 0;
    }

    .section-title {
        margin: 30px 0 12px;
        color: #203f91;
        font-size: 20px;
    }

    .empty-row {
        text-align: center !important;
        color: #64748b;
        padding: 25px !important;
    }

    .role-form {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        flex-wrap: nowrap;
    }

    .staff-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-controls {
        display: flex;
        align-items: end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-controls label {
        display: flex;
        flex-direction: column;
        gap: 5px;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }

    .filter-controls input,
    .filter-controls select {
        min-width: 170px;
        padding: 9px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: white;
        font-size: 13px;
    }

    .program-group-row td,
    .year-group-row td {
        font-weight: 700;
        color: #1e293b;
        background: #eff6ff;
    }

    .year-group-row td {
        background: #f8fafc;
        color: #334155;
    }

    .danger-btn {
        background: #b91c1c;
    }

    .danger-btn:hover {
        background: #991b1b;
    }

    /* MODAL */

    .modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(15,23,42,0.65);
        justify-content: center;
        align-items: center;
        padding: 20px;
        box-sizing: border-box;
        backdrop-filter: blur(3px);
    }

    .modal-content {
        background: white;
        width: 100%;
        max-width: 450px;
        padding: 28px;
        border-radius: 14px;
        box-sizing: border-box;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        animation: modalOpen 0.2s ease;
    }

    @keyframes modalOpen {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-content h3 {
        margin: 0 0 20px;
        color: #203f91;
        font-size: 21px;
    }

    .modal-content form {
        display: flex;
        flex-direction: column;
        gap: 7px;
        width: 100%;
        margin: 0;
    }

    .modal-content label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin: 5px 0 2px;
    }

    .modal-content input,
    .modal-content select {
        display: block;
        width: 100%;
        height: 43px;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        box-sizing: border-box;
        font-size: 14px;
        margin: 0 0 7px;
        outline: none;
        background: white;
    }

    .modal-content input:focus,
    .modal-content select:focus {
        border-color: #203f91;
        box-shadow: 0 0 0 2px rgba(32,63,145,0.10);
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 12px;
    }

    .modal-actions button {
        flex: 1;
    }

    @media(max-width: 600px) {
        .page-header {
            padding: 22px;
        }

        .page-header h2 {
            font-size: 21px;
        }

        .users-card {
            padding: 16px;
        }

        .modal-content {
            padding: 20px;
        }

        .modal-actions {
            flex-direction: column;
        }

        .create-buttons {
            flex-direction: column;
        }

        .create-buttons button {
            width: 100%;
        }
    }
</style>

<div class="users-container">

    <div class="page-header">
        <h2>User Management</h2>
        <p>SUBIC BAY COLLEGES (INC). | Manage student, staff, and administrator accounts.</p>
    </div>

    {{-- MESSAGES --}}

    @if(session('success'))
        <div class="message success-message">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="message error-message">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="message error-message">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- CREATE ACCOUNT BUTTONS --}}

    <div class="users-card">
        <h3 class="card-heading">Create Accounts</h3>

        <p class="card-description">
            Register new student and staff accounts in the system.
        </p>

        <div class="create-buttons">
            <button type="button" class="btn"
                onclick="openModal('studentModal')">
                + Create Student Account
            </button>

            <button type="button" class="btn"
                onclick="openModal('staffModal')">
                + Create Staff Account
            </button>
        </div>
    </div>

    {{-- CREATE STUDENT MODAL --}}

    <div class="modal" id="studentModal">
        <div class="modal-content">
            <h3>Create Student Account</h3>

            <form method="POST" action="{{ route('admin.users.storeStudent') }}">
                @csrf

                <label>Full Name</label>
                <input type="text" name="name" required>

                <label>Student ID</label>
                <input type="text" name="student_id" required>

                <label>Program</label>
                <select name="program" required>
                    <option value="">Select Program</option>
                    <option value="BSIT">BSIT</option>
                    <option value="BSBA">BSBA</option>
                    <option value="BSED">BSED</option>
                    <option value="BEED">BEED</option>
                    <option value="Other">Other</option>
                </select>

                <label>Year Level</label>
                <select name="year_level" required>
                    <option value="">Select Year Level</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" required>

                <div class="modal-actions">
                    <button type="submit" class="btn">Create Student</button>
                    <button type="button" class="btn close-btn"
                        onclick="closeModal('studentModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CREATE STAFF MODAL --}}

    <div class="modal" id="staffModal">
        <div class="modal-content">
            <h3>Create Staff Account</h3>

            <form method="POST" action="{{ route('admin.users.storeStaff') }}">
                @csrf

                <label>Full Name</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" required>

                <div class="modal-actions">
                    <button type="submit" class="btn">Create Staff</button>
                    <button type="button" class="btn close-btn"
                        onclick="closeModal('staffModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- STUDENT ACCOUNTS --}}

    <h2 class="section-title">Student Accounts</h2>

    <div class="users-card">
        <form method="GET" action="{{ route('admin.users') }}" class="filter-controls">
            <label>
                Search name or program
                <input type="search" name="search" value="{{ $search }}"
                    placeholder="Search students or programs">
            </label>

            <label>
                Course / Program
                <select name="program">
                    <option value="">All Programs</option>
                    @foreach($programOptions as $option)
                        <option value="{{ $option['value'] }}"
                            {{ $selectedProgram === $option['value'] ? 'selected' : '' }}>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Year Level
                <select name="year_level">
                    <option value="">All Year Levels</option>
                    @foreach($yearLevelOptions as $option)
                        <option value="{{ $option['value'] }}"
                            {{ $selectedYearLevel === $option['value'] ? 'selected' : '' }}>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </label>

            <button type="submit" class="btn">Apply Filters</button>
            <a href="{{ route('admin.users') }}" class="btn close-btn">Clear</a>
        </form>
    </div>

    <div class="users-card table-wrapper">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Student ID</th>
                    <th>Program</th>
                    <th>Year Level</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($studentsByProgram as $programGroup)
                    <tr class="program-group-row">
                        <td colspan="7">{{ $programGroup['program'] }}</td>
                    </tr>

                    @foreach($programGroup['years'] as $yearGroup)
                        <tr class="year-group-row">
                            <td colspan="7">{{ $yearGroup['level'] }}</td>
                        </tr>

                        @foreach($yearGroup['students'] as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->student_id ?? '—' }}</td>
                            <td>{{ filled($user->program) ? $user->program : 'Not Set' }}</td>
                            <td>{{ filled($user->year_level) ? $user->year_level : 'Not Set' }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="role-badge student-badge">Student</span></td>
                            <td>
                                <div class="staff-actions">
                                    <button type="button" class="btn edit-btn"
                                        onclick="openModal('editStudent{{ $user->_id }}')">
                                        Edit
                                    </button>

                                    <form method="POST"
                                        action="{{ route('admin.users.updateRole', $user->_id) }}"
                                        class="role-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" required>
                                            <option value="student" selected>Student</option>
                                            <option value="staff">Staff</option>
                                            @if($isMainAdmin)
                                                <option value="admin">Admin</option>
                                            @endif
                                        </select>
                                        <button type="submit" class="btn">Change Role</button>
                                    </form>

                                    @if(isset($canDeleteUserIds[(string) $user->getKey()]))
                                        <form method="POST"
                                            action="{{ route('admin.users.destroy', $user->_id) }}"
                                            onsubmit="return confirm('Deactivate this student account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn danger-btn">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- EDIT STUDENT MODAL --}}

                        <div class="modal" id="editStudent{{ $user->_id }}">
                            <div class="modal-content">
                                <h3>Edit Student Information</h3>

                                <form method="POST"
                                    action="{{ route('admin.users.updateStudent', $user->_id) }}">
                                    @csrf
                                    @method('PATCH')

                                    <label>Full Name</label>
                                    <input type="text" name="name"
                                        value="{{ $user->name }}" required>

                                    <label>Student ID</label>
                                    <input type="text" name="student_id"
                                        value="{{ $user->student_id ?? '' }}" required>

                                    <label>Program</label>
                                    <input type="text" name="program"
                                        value="{{ $user->program ?? '' }}" required>

                                    <label>Year Level</label>
                                    <select name="year_level" required>
                                        <option value="1st Year"
                                            {{ ($user->year_level ?? '') === '1st Year' ? 'selected' : '' }}>
                                            1st Year
                                        </option>

                                        <option value="2nd Year"
                                            {{ ($user->year_level ?? '') === '2nd Year' ? 'selected' : '' }}>
                                            2nd Year
                                        </option>

                                        <option value="3rd Year"
                                            {{ ($user->year_level ?? '') === '3rd Year' ? 'selected' : '' }}>
                                            3rd Year
                                        </option>

                                        <option value="4th Year"
                                            {{ ($user->year_level ?? '') === '4th Year' ? 'selected' : '' }}>
                                            4th Year
                                        </option>
                                    </select>

                                    <label>Email</label>
                                    <input type="email" name="email"
                                        value="{{ $user->email }}" required>

                                    <div class="modal-actions">
                                        <button type="submit" class="btn">
                                            Save Changes
                                        </button>

                                        <button type="button" class="btn close-btn"
                                            onclick="closeModal('editStudent{{ $user->_id }}')">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        @endforeach
                    @endforeach
                @empty
                    <tr>
                        <td colspan="7" class="empty-row">
                            No student accounts match these filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- STAFF ACCOUNTS --}}

    <h2 class="section-title">Staff Accounts</h2>

    <div class="users-card table-wrapper">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($staffUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="role-badge staff-badge">Staff</span></td>

                            <td>
                                <div class="staff-actions">

                                    <button type="button" class="btn edit-btn"
                                        onclick="openModal('editStaff{{ $user->_id }}')">
                                        Edit
                                    </button>

                                    <form method="POST"
                                        action="{{ route('admin.users.updateRole', $user->_id) }}"
                                        class="role-form">

                                        @csrf
                                        @method('PATCH')

                                        <select name="role" required>
                                            <option value="student">Student</option>
                                            <option value="staff" selected>Staff</option>
                                            @if($isMainAdmin)
                                                <option value="admin">Admin</option>
                                            @endif
                                        </select>

                                        <button type="submit" class="btn">
                                            Change Role
                                        </button>
                                    </form>

                                    @if(isset($canDeleteUserIds[(string) $user->getKey()]))
                                        <form method="POST"
                                            action="{{ route('admin.users.destroy', $user->_id) }}"
                                            onsubmit="return confirm('Deactivate this staff account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn danger-btn">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- EDIT STAFF MODAL --}}

                        <div class="modal" id="editStaff{{ $user->_id }}">
                            <div class="modal-content">
                                <h3>Edit Staff Information</h3>

                                <form method="POST"
                                    action="{{ route('admin.users.updateStaff', $user->_id) }}">
                                    @csrf
                                    @method('PATCH')

                                    <label>Full Name</label>
                                    <input type="text" name="name"
                                        value="{{ $user->name }}" required>

                                    <label>Email</label>
                                    <input type="email" name="email"
                                        value="{{ $user->email }}" required>

                                    <div class="modal-actions">
                                        <button type="submit" class="btn">
                                            Save Changes
                                        </button>

                                        <button type="button" class="btn close-btn"
                                            onclick="closeModal('editStaff{{ $user->_id }}')">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                @empty
                    <tr>
                        <td colspan="4" class="empty-row">
                            No staff accounts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ADMIN ACCOUNTS --}}

    <h2 class="section-title">Admin Accounts</h2>

    <div class="users-card table-wrapper">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($adminUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="role-badge admin-badge">Admin</span></td>
                            <td>
                                <div class="staff-actions">
                                    @if($isMainAdmin && (string) $user->getKey() !== (string) auth()->user()->getKey())
                                        <form method="POST"
                                            action="{{ route('admin.users.updateRole', $user->_id) }}"
                                            class="role-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" required>
                                                <option value="admin" selected>Admin</option>
                                                <option value="student">Student</option>
                                                <option value="staff">Staff</option>
                                            </select>
                                            <button type="submit" class="btn">Change Role</button>
                                        </form>
                                    @endif

                                    @if(isset($canDeleteUserIds[(string) $user->getKey()]))
                                        <form method="POST"
                                            action="{{ route('admin.users.destroy', $user->_id) }}"
                                            onsubmit="return confirm('Deactivate this admin account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn danger-btn">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-row">
                            No admin accounts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);

        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);

        if (modal) {
            modal.style.display = 'none';
        }
    }

    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });

    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(function(modal) {
                modal.style.display = 'none';
            });
        }
    });
</script>

@endsection
