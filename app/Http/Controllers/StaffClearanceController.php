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

        if ($actor->role === 'admin') {
            $clearances = Clearance::latest()->get();

            $students = User::withTrashed()
                ->whereIn('email', $clearances->pluck('student_id')->unique()->all())
                ->get()
                ->keyBy('email');

            return view('staff.clearances', compact('clearances', 'students'));
        }

        abort_unless($actor->role === 'staff', 403);

        $department = $actor->department;

        if (!$department) {
            $clearances = collect();
            $students = collect();

            return view('staff.clearances', compact('clearances', 'students'));
        }

        $clearances = Clearance::where('department', $department)
            ->latest()
            ->get();

        $studentEmails = $clearances
            ->pluck('student_id')
            ->unique()
            ->values()
            ->all();

        $students = User::withTrashed()
            ->whereIn('email', $studentEmails)
            ->get()
            ->keyBy('email');

        if ($department === 'DEAN PROGRAM') {
            $group = $actor->signatory_group;
            $allowedPrograms = self::DEAM_SIGNATORY_GROUPS[$group] ?? [];

            if (empty($allowedPrograms)) {
                $clearances = collect();
            } else {
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

        return view('staff.clearances', compact('clearances', 'students'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        $actor = Auth::user();

        $clearance = Clearance::findOrFail($id);

        if ($actor->role !== 'admin') {
            abort_unless($actor->role === 'staff', 403);

            abort_unless(
                $actor->department
                && $clearance->department === $actor->department,
                403
            );

            if ($actor->department === 'DEAN PROGRAM') {
                $allowedPrograms = self::DEAM_SIGNATORY_GROUPS[
                    $actor->signatory_group
                ] ?? [];

                $student = User::withTrashed()
                    ->where('email', $clearance->student_id)
                    ->first();

                abort_unless(
                    $student
                    && in_array($student->program, $allowedPrograms, true),
                    403
                );
            }
        }

        if ($request->status === 'Approved') {
            if ($clearance->status !== 'Approved' || !$clearance->approved_at) {
                $clearance->approved_at = now();
            }
        } else {
            $clearance->approved_at = null;
        }

        $clearance->status = $request->status;
        $clearance->remarks = $request->remarks;
        $clearance->save();

        return redirect()
            ->route('staff.clearances')
            ->with('success', 'Clearance status updated successfully.');
    }
}
