<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Policies\UserManagementPolicy;
use App\Support\StudentListOrganizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private const STAFF_DEPARTMENTS = [
        'DEAM PROGRAM',
        'LIBRARY',
        'PROPERTY CUSTODIAN',
        'ACCOUNTING',
        'REGISTRAR',
        'OSAS',
    ];

    private const DEAM_SIGNATORY_GROUPS = [
        'deam_group_1',
        'deam_group_2',
        'deam_group_3',
        'deam_group_4',
        'deam_group_5',
    ];

    public function index(
        Request $request,
        StudentListOrganizer $organizer,
        UserManagementPolicy $userPolicy
    ) {
        $actor = Auth::user();

        abort_unless($actor instanceof User, 403);

        $isMainAdmin = $actor->role === 'admin'
            && (string) $actor->_id === (string) config('admin.main_admin_user_id');

        $users = User::orderBy('name')->get();

        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'program' => 'nullable|string|max:512',
            'year_level' => 'nullable|string|max:512',
        ]);

        $students = $users->where('role', 'student')->values();
        $staffUsers = $users->where('role', 'staff')->values();
        $adminUsers = $users->where('role', 'admin')->values();

        $studentList = $organizer->organize(
            $students,
            $filters['search'] ?? null,
            $filters['program'] ?? null,
            $filters['year_level'] ?? null
        );

        $displayedUsers = $staffUsers->merge($adminUsers);

        foreach ($studentList['groups'] as $group) {
            foreach ($group['years'] as $yearGroup) {
                $displayedUsers = $displayedUsers->merge($yearGroup['students']);
            }
        }

        $canDeleteUserIds = $displayedUsers
            ->filter(fn (User $user): bool => $userPolicy->canDelete($actor, $user))
            ->mapWithKeys(fn (User $user): array => [
                (string) $user->_id => true,
            ])
            ->all();

        return view('admin.users', [
            'studentsByProgram' => $studentList['groups'],
            'programOptions' => $studentList['programOptions'],
            'yearLevelOptions' => $studentList['yearLevelOptions'],
            'studentCount' => $studentList['studentCount'],
            'search' => $filters['search'] ?? '',
            'selectedProgram' => $filters['program'] ?? '',
            'selectedYearLevel' => $filters['year_level'] ?? '',
            'staffUsers' => $staffUsers,
            'adminUsers' => $adminUsers,
            'canDeleteUserIds' => $canDeleteUserIds,
            'isMainAdmin' => $isMainAdmin,
            'staffDepartments' => self::STAFF_DEPARTMENTS,
            'deamSignatoryGroups' => [
                'deam_group_1' => 'BSEd – Math / BEEd – GenEd',
                'deam_group_2' => 'BSEntrep / BSCA',
                'deam_group_3' => 'BSHM / BSTM',
                'deam_group_4' => 'BSIT',
                'deam_group_5' => 'BSCrim',
            ],
        ]);
    }

    // CREATE STAFF
    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'department' => [
                'required',
                'string',
                Rule::in(self::STAFF_DEPARTMENTS),
            ],
            'signatory_group' => [
                'nullable',
                'required_if:department,DEAM PROGRAM',
                'string',
                Rule::in(self::DEAM_SIGNATORY_GROUPS),
            ],
        ]);

        $staff = new User();
        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->password = Hash::make($validated['password']);
        $staff->role = 'staff';
        $staff->department = $validated['department'];
        $staff->signatory_group = $validated['department'] === 'DEAM PROGRAM'
            ? $validated['signatory_group']
            : null;

        $staff->save();

        return back()->with('success', 'Staff account created successfully.');
    }

    // CREATE STUDENT
    public function storeStudent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|max:50|unique:users,student_id',
            'program' => 'required|string|max:100',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $student = new User();
        $student->name = $request->name;
        $student->student_id = $request->student_id;
        $student->program = $request->program;
        $student->year_level = $request->year_level;
        $student->email = $request->email;
        $student->password = Hash::make($request->password);
        $student->role = 'student';
        $student->email_verified_at = now();
        $student->save();

        return back()->with(
            'success',
            'Student account created successfully.'
        );
    }

    // UPDATE STUDENT INFORMATION
    public function updateStudent(Request $request, string $id)
    {
        $student = User::findOrFail($id);

        if ($student->role !== 'student') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|max:50',
            'program' => 'required|string|max:100',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year',
            'email' => 'required|email|max:255',
        ]);

        $currentId = (string) $student->_id;

        $duplicateStudentId = User::where('student_id', $request->student_id)
            ->where('_id', '!=', $currentId)
            ->exists();

        if ($duplicateStudentId) {
            return back()
                ->withErrors(['student_id' => 'This student ID has already been taken.'])
                ->withInput();
        }

        $emailChanged = $student->email !== $request->email;

        if ($emailChanged) {
            $duplicateEmail = User::where('email', $request->email)
                ->where('_id', '!=', $currentId)
                ->exists();

            if ($duplicateEmail) {
                return back()
                    ->withErrors(['email' => 'This email has already been taken.'])
                    ->withInput();
            }
        }

        $student->name = $request->name;
        $student->student_id = $request->student_id;
        $student->program = $request->program;
        $student->year_level = $request->year_level;
        $student->email = $request->email;
        $student->email_verified_at = now();

        $student->save();

        return back()->with(
            'success',
            'Student information updated successfully.'
        );
    }

    // UPDATE STAFF INFORMATION
    public function updateStaff(Request $request, string $id)
    {
        $staff = User::findOrFail($id);

        if ($staff->role !== 'staff') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staff->_id, '_id'),
            ],
            'department' => [
                'required',
                'string',
                Rule::in(self::STAFF_DEPARTMENTS),
            ],
            'signatory_group' => [
                'nullable',
                'required_if:department,DEAM PROGRAM',
                'string',
                Rule::in(self::DEAM_SIGNATORY_GROUPS),
            ],
        ]);

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->department = $validated['department'];
        $staff->signatory_group = $validated['department'] === 'DEAM PROGRAM'
            ? $validated['signatory_group']
            : null;

        $staff->save();

        return back()->with('success', 'Staff information updated successfully.');
    }

    // UPDATE USER ROLE
    public function updateRole(
        Request $request,
        string $id,
        UserManagementPolicy $userPolicy
    ) {
        $request->validate([
            'role' => 'required|in:student,staff,admin',
        ]);

        $user = User::findOrFail($id);
        $actor = Auth::user();

        if (!$actor instanceof User) {
            abort(403);
        }

        if (!$userPolicy->canChangeRole($actor, $user, $request->role)) {
            return back()->with('error', 'You are not authorized to change this user role.');
        }

        $user->role = $request->role;

        if ($request->role === 'student') {
            $user->email_verified_at = now();
        }

        $user->save();

        return back()->with('success', 'User role updated successfully.');
    }

    // DELETE USER
    public function destroy(string $id, UserManagementPolicy $userPolicy)
    {
        $user = User::findOrFail($id);
        $actor = Auth::user();

        if (!$actor instanceof User) {
            abort(403);
        }

        abort_unless($userPolicy->canDelete($actor, $user), 403);

        $user->delete();

        return back()->with('success', 'User account deactivated successfully.');
    }
}
