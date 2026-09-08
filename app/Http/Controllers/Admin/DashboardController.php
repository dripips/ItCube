<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SchoolAnalytics;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(SchoolAnalytics $analytics): View
    {
        return view('admin.dashboard', [
            'headcount' => $analytics->headcount(),
            'attendanceByGroup' => $analytics->attendanceByGroup(),
            'attendanceTrend' => $analytics->attendanceTrend(8),
            'hardest' => $analytics->hardestAssignments(),
            'languages' => $analytics->languageUsage(),
            'submissions' => $analytics->submissionTrend(14),
            'hardestQuestions' => $analytics->hardestQuestions(),
        ]);
    }
}
