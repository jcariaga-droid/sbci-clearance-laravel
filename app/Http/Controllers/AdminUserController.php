<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users', compact('users'));
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
    public function updateRole(Request $request, string $id)
    {
        $request->validate([
            'role' => 'required|in:student,staff,admin',
        ]);

        $user = User::findOrFail($id);

        if ((string) $user->_id === (string) Auth::user()->_id) {
            return back()->with('error', 'You cannot change your own role.');
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
}
