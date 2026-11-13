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

        // ADMIN: View all clearances
        if ($actor->role === 'admin') {

            $clearances = Clearance::latest()->get();

            $students = User::withTrashed()
                ->whereIn(
                    'email',
                    $clearances->pluck('student_id')->unique()->all()
                )
                ->get()
                ->keyBy('email');

            return view(
                'staff.clearances',
                compact('clearances', 'students')
            );
        }

        // Only admin and staff are allowed
        abort_unless($actor->role === 'staff', 403);

        $department = trim($actor->department ?? '');

        if (empty($department)) {
            $clearances = collect();
            $students = collect();

            return view(
                'staff.clearances',
                compact('clearances', 'students')
            );
        }

        // Get department clearances
        $clearances = Clearance::where('department', $department)
            ->latest()
            ->get();

        // Get student records
        $studentEmails = $clearances
            ->pluck('student_id')
            ->unique()
            ->values()
            ->all();

        $students = User::withTrashed()
            ->whereIn('email', $studentEmails)
            ->get()
            ->keyBy('email');

        // DEAN PROGRAM restrictions
        if ($department === 'DEAN PROGRAM') {

            $group = $actor->signatory_group;

            $allowedPrograms = self::DEAM_SIGNATORY_GROUPS[$group] ?? [];

            if (empty($allowedPrograms)) {

                $clearances = collect();

            } else {

                $clearances = $clearances->filter(
                    function ($clearance) use (
                        $students,
                        $allowedPrograms
                    ) {
                        $student = $students->get(
                            $clearance->student_id
                        );

                        return $student
                            && in_array(
                                trim($student->program ?? ''),
                                $allowedPrograms,
                                true
                            );
                    }
                )->values();
            }
        }

        return view(
            'staff.clearances',
            compact('clearances', 'students')
        );
    }

    public function update(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        $actor = Auth::user();

        // Find clearance
        $clearance = Clearance::findOrFail($id);

        // ADMIN ACCESS
        if ($actor->role === 'admin') {

            // Admin can update any clearance

        } else {

            // STAFF ACCESS ONLY
            abort_unless($actor->role === 'staff', 403);

            $staffDepartment = trim($actor->department ?? '');
            $clearanceDepartment = trim($clearance->department ?? '');

            // Check department authorization
            abort_unless(
                !empty($staffDepartment) &&
                $clearanceDepartment === $staffDepartment,
                403
            );

            // DEAN PROGRAM authorization
            if ($staffDepartment === 'DEAN PROGRAM') {

                $group = $actor->signatory_group;

                $allowedPrograms =
                    self::DEAM_SIGNATORY_GROUPS[$group] ?? [];

                // Find student, including soft-deleted accounts
                $student = User::withTrashed()
                    ->where('email', $clearance->student_id)
                    ->first();

                // Verify student program
                abort_unless(
                    $student &&
                    in_array(
                        trim($student->program ?? ''),
                        $allowedPrograms,
                        true
                    ),
                    403
                );
            }
        }

        // UPDATE APPROVAL DATE
        if ($request->status === 'Approved') {

            if (
                $clearance->status !== 'Approved' ||
                !$clearance->approved_at
            ) {
                $clearance->approved_at = now();
            }

        } else {

            // Reset approval date when Pending or Rejected
            $clearance->approved_at = null;
        }

        // SAVE CHANGES
        $clearance->status = $request->status;
        $clearance->remarks = $request->remarks;

        $clearance->save();

        return redirect()
            ->route('staff.clearances')
            ->with(
                'success',
                'Clearance status updated successfully.'
            );
    }
}
