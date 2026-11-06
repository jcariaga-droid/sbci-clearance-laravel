<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clearance;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalStudents' => User::where('role', 'student')->count(),
            'totalStaff' => User::where('role', 'staff')->count(),
            'pending' => Clearance::where('status', 'Pending')->count(),
            'approved' => Clearance::where('status', 'Approved')->count(),
            'rejected' => Clearance::where('status', 'Rejected')->count(),
        ]);
    }
}