<?php

namespace App\Http\Controllers;

use App\Models\Clearance;
use Illuminate\Http\Request;

class StaffClearanceController extends Controller
{
    public function index()
    {
        $clearances = Clearance::latest()->get();

        return view('staff.clearances', compact('clearances'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        $clearance = Clearance::findOrFail($id);

        $clearance->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('staff.clearances')
            ->with('success', 'Clearance status updated successfully.');
    }
}   