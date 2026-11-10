<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Policies\UserManagementPolicy;
use App\Support\StudentListOrganizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request, StudentListOrganizer $organizer, UserManagementPolicy $userPolicy)
    {
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
            ->filter(fn (User $user): bool => $userPolicy->canDelete(Auth::user(), $user))
            ->mapWithKeys(fn (User $user): array => [(string) $user->getKey() => true])
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
            'isMainAdmin' => Auth::user()->isMainAdmin(),
        ]);
    }

    // SEND STUDENT VERIFICATION EMAIL
    private function sendVerificationEmail($student)
    {
        $link = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => (string) $student->_id,
                'hash' => sha1($student->email),
            ]
        );

        Mail::raw(
            "Hello {$student->name},\n\n"
            . "Your student account has been created in the SBCI Clearance System.\n\n"
            . "Please verify your email by clicking this link:\n\n"
            . $link . "\n\n"
            . "This link will expire in 60 minutes.\n\n"
            . "If you did not expect this account, please contact the school administrator.",
            function ($message) use ($student) {
                $message->to($student->email)
                    ->subject('SBCI Clearance System - Verify Your Email');
            }
        );
    }

    // CREATE STAFF
    public function storeStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $staff = new User();
        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->password = Hash::make($request->password);
        $staff->role = 'staff';
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
        $student->email_verified_at = null;
        $student->save();

        $this->sendVerificationEmail($student);

        return back()->with(
            'success',
            'Student account created successfully. Verification email sent.'
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

            'student_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'student_id')->ignore($student->_id, '_id'),
            ],

            'program' => 'required|string|max:100',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year',

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($student->_id, '_id'),
            ],
        ]);

        $emailChanged = $student->email !== $request->email;

        $student->name = $request->name;
        $student->student_id = $request->student_id;
        $student->program = $request->program;
        $student->year_level = $request->year_level;
        $student->email = $request->email;

        if ($emailChanged) {
            $student->email_verified_at = null;
        }

        $student->save();

        if ($emailChanged) {
            $this->sendVerificationEmail($student);
        }

        return back()->with(
            'success',
            $emailChanged
                ? 'Student updated. Verification email sent to the new email address.'
                : 'Student information updated successfully.'
        );
    }

    // UPDATE STAFF INFORMATION
    public function updateStaff(Request $request, string $id)
    {
        $staff = User::findOrFail($id);

        if ($staff->role !== 'staff') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staff->_id, '_id'),
            ],
        ]);

        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->save();

        return back()->with('success', 'Staff information updated successfully.');
    }

    // UPDATE USER ROLE
    public function updateRole(Request $request, string $id, UserManagementPolicy $userPolicy)
    {
        $request->validate([
            'role' => 'required|in:student,staff,admin',
        ]);

        $user = User::findOrFail($id);
        $actor = Auth::user();

        if (!$userPolicy->canChangeRole($actor, $user, $request->role)) {
            return back()->with('error', 'You are not authorized to change this user role.');
        }

        $oldRole = $user->role;

        $user->role = $request->role;

        if ($oldRole !== 'student' && $request->role === 'student') {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($oldRole !== 'student' && $request->role === 'student') {
            $this->sendVerificationEmail($user);
        }

        return back()->with('success', 'User role updated successfully.');
    }

    public function destroy(string $id, UserManagementPolicy $userPolicy)
    {
        $user = User::findOrFail($id);

        abort_unless($userPolicy->canDelete(Auth::user(), $user), 403);

        $user->delete();

        return back()->with('success', 'User account deactivated successfully.');
    }
}
