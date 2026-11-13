<?php

namespace App\Http\Controllers;

use App\Models\Clearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClearanceController extends Controller
{
    private const REQUIRED_DEPARTMENTS = [
        'DEAN PROGRAM',
        'LIBRARY',
        'PROPERTY CUSTODIAN',
        'ACCOUNTING',
        'REGISTRAR',
        'OSAS',
    ];

    public function index()
    {
        $user = Auth::user();

        $clearances = Clearance::where('student_id', $user->email)
            ->latest()
            ->get();

        $latestClearances = $this->getLatestDepartmentClearances($user->email);

        $isComplete = collect(self::REQUIRED_DEPARTMENTS)->every(
            function ($department) use ($latestClearances) {
                return isset($latestClearances[$department])
                    && $latestClearances[$department]->status === 'Approved';
            }
        );

        return view('clearance.index', compact(
            'clearances',
            'isComplete'
        ));
    }

    public function completion()
    {
        $user = Auth::user();

        $latestClearances = $this->getLatestDepartmentClearances($user->email);

        $isComplete = collect(self::REQUIRED_DEPARTMENTS)->every(
            function ($department) use ($latestClearances) {
                return isset($latestClearances[$department])
                    && $latestClearances[$department]->status === 'Approved';
            }
        );

        abort_unless($isComplete, 403, 'Your clearance is not yet complete.');

        $completionDate = collect(self::REQUIRED_DEPARTMENTS)
            ->map(function ($department) use ($latestClearances) {
                return $latestClearances[$department]->updated_at
                    ?? $latestClearances[$department]->created_at;
            })
            ->filter()
            ->max();

        return view('clearance.completion', [
            'user' => $user,
            'departments' => self::REQUIRED_DEPARTMENTS,
            'completionDate' => $completionDate,
        ]);
    }

    private function getLatestDepartmentClearances(string $email)
    {
        $records = Clearance::where('student_id', $email)
            ->orderBy('updated_at', 'desc')
            ->get();

        $latest = [];

        foreach ($records as $record) {
            $department = strtoupper(trim((string) $record->department));

            if (!array_key_exists($department, $latest)) {
                $latest[$department] = $record;
            }
        }

        return collect($latest);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department' => [
                'required',
                'string',
                'in:DEAN PROGRAM,LIBRARY,PROPERTY CUSTODIAN,ACCOUNTING,REGISTRAR,OSAS',
            ],
        ]);

        $user = Auth::user();

        $existing = Clearance::where('student_id', $user->email)
            ->where('department', $request->department)
            ->whereIn('status', ['Pending', 'Approved'])
            ->first();

        if ($existing) {
            return back()->with(
                'error',
                'You already have a clearance request for this department.'
            );
        }

        $clearance = new Clearance();
        $clearance->student_id = $user->email;
        $clearance->department = $request->department;
        $clearance->status = 'Pending';
        $clearance->remarks = '';
        $clearance->save();

        return back()->with(
            'success',
            'Clearance request submitted successfully.'
        );
    }
}
