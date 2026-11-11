<?php

namespace App\Http\Controllers;

use App\Models\Clearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClearanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $clearances = Clearance::where('student_id', $user->email)
            ->latest()
            ->get();

        return view('clearance.index', compact('clearances'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department' => [
                'required',
                'string',
                'in:DEAM PROGRAM,LIBRARY,PROPERTY CUSTODIAN,ACCOUNTING,REGISTRAR,OSAS',
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
