<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function index()
    {
        // TOTAL ACTIVE STUDENT ACCOUNTS
        $totalStudents = User::where('role', 'student')->count();

        // ARCHIVED STUDENT ACCOUNTS
        $archivedAccounts = User::onlyTrashed()
            ->where('role', 'student')
            ->count();

        // CURRENTLY ACTIVE SESSIONS
        $activeSince = now()
            ->subMinutes((int) config('session.lifetime', 120))
            ->timestamp;

        $activeSessions = DB::connection('mongodb')
            ->table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $activeSince)
            ->get();

        $currentlyLoggedIn = $activeSessions
            ->pluck('user_id')
            ->unique()
            ->count();

        // STUDENTS PER PROGRAM AND YEAR LEVEL
        $students = User::where('role', 'student')
            ->get(['program', 'year_level']);

        $studentsPerProgram = $students
            ->groupBy(function ($student) {
                return $student->program ?: 'Unassigned';
            })
            ->map(fn ($group) => $group->count());

        $studentsPerYear = $students
            ->groupBy(function ($student) {
                return $student->year_level ?: 'Not Set';
            })
            ->map(fn ($group) => $group->count());

        // LAST 7 DAYS LOGIN AND LOGOUT ACTIVITY
        $dates = collect(range(6, 0))
            ->map(fn ($days) => now()->subDays($days)->toDateString());

        $startTimestamp = now()
            ->subDays(6)
            ->startOfDay()
            ->timestamp;

        $activities = DB::connection('mongodb')
            ->table('user_activities')
            ->where('occurred_at', '>=', $startTimestamp)
            ->get();

        $loginCounts = [];
        $logoutCounts = [];

        foreach ($dates as $date) {
            $dailyActivities = $activities->where('event_date', $date);

            $loginCounts[] = $dailyActivities
                ->where('event_type', 'login')
                ->count();

            $logoutCounts[] = $dailyActivities
                ->where('event_type', 'logout')
                ->count();
        }

        return view('admin.analytics', [
            'totalStudents' => $totalStudents,
            'archivedAccounts' => $archivedAccounts,
            'currentlyLoggedIn' => $currentlyLoggedIn,
            'programLabels' => $studentsPerProgram->keys()->values(),
            'programData' => $studentsPerProgram->values(),
            'yearLabels' => $studentsPerYear->keys()->values(),
            'yearData' => $studentsPerYear->values(),
            'activityDates' => $dates,
            'loginCounts' => $loginCounts,
            'logoutCounts' => $logoutCounts,
        ]);
    }
}
