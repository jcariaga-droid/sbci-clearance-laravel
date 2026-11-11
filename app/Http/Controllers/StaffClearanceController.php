<?php

namespace App\Http\Controllers;

use App\Models\Clearance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffClearanceController extends Controller
{
    private const DEAM_SIGNATORY_GROUPS = [
        'deam_group_1' => [
            'BSEd – Math',
            'BEEd – GenEd',
        ],
        'deam_group_2' => [
            'BSEntrep',
            'BSCA',
        ],
        'deam_group_3' => [
            'BSHM',
            'BSTM',
        ],
        'deam_group_4' => [
            'BSIT',
        ],
        'deam_group_5' => [
            'BSCrim',
        ],
    ];

    public function index()
    {
        $actor = Auth::user();

        // Admin can view all clearance requests.
        if ($actor->role === 'admin') {
            $clearances = Clearance::latest()->get();

            return view('staff.clearances', compact('clearances'));
        }

        // Only assigned staff can access this page.
        abort_unless($actor->role === 'staff', 403);

        $department = $actor->department;

        // Staff without an assigned department cannot view requests.
        if (!$department) {
            $clearances = collect();

            return view('staff.clearances', compact('clearances'));
        }

        $clearances = Clearance::where('department', $department)
            ->latest()
            ->get();

        // Additional filtering for DEAM signatories.
        if ($department === 'DEAM PROGRAM') {
            $group = $actor->signatory_group;
            $allowedPrograms = self::DEAM_SIGNATORY_GROUPS[$group] ?? [];

            if (empty($allowedPrograms)) {
                $clearances = collect();
            } else {
                $studentEmails = $clearances
                    ->pluck('student_id')
                    ->unique()
                    ->values()
                    ->all();

                $students = User::whereIn('email', $studentEmails)
                    ->get()
                    ->keyBy('email');

                $clearances = $clearances->filter(function ($clearance) use (
                    $students,
                    $allowedPrograms
                ) {
                    $student = $students->get($clearance->student_id);

                    return $student
                        && in_array($student->program, $allowedPrograms, true);
                })->values();
            }
        }

        return view('staff.clearances', compact('clearances'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        $actor = Auth::user();

        $clearance = Clearance::findOrFail($id);

        // Admin can update any clearance request.
        if ($actor->role !== 'admin') {
            abort_unless($actor->role === 'staff', 403);

            // Department must match the staff assignment.
            abort_unless(
                $actor->department
                && $clearance->department === $actor->department,
                403
            );

            // DEAM staff can only update students in their assigned group.
            if ($actor->department === 'DEAM PROGRAM') {
                $allowedPrograms = self::DEAM_SIGNATORY_GROUPS[
                    $actor->signatory_group
                ] ?? [];

                $student = User::where('email', $clearance->student_id)
                    ->first();

                abort_unless(
                    $student
                    && in_array($student->program, $allowedPrograms, true),
                    403
                );
            }
        }

        $clearance->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('staff.clearances')
            ->with('success', 'Clearance status updated successfully.');
    }
}
