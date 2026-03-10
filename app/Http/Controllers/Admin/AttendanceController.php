<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class AttendanceController extends Controller
{
    use LogsActivity;
    
    /**
     * Display attendance listing with calendar view.
     */
    public function index(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $userId = $request->get('user_id');
        
        // Get staff users (non-regular users)
        $staffUsers = User::where('user_role', '!=', 'user')->orderBy('name')->get();
        
        // Get selected user or first staff user
        $selectedUser = $userId ? User::find($userId) : $staffUsers->first();
        
        // Get attendance for the selected month
        $attendances = [];
        if ($selectedUser) {
            $attendances = Attendance::forUser($selectedUser->id)
                                     ->forMonth($month, $year)
                                     ->get()
                                     ->keyBy(function ($item) {
                                         return $item->date->format('Y-m-d');
                                     });
        }
        
        // Generate calendar data
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $calendarDays = [];
        
        $currentDate = $startDate->copy()->startOfWeek(Carbon::SUNDAY);
        while ($currentDate->lte($endDate->copy()->endOfWeek(Carbon::SATURDAY))) {
            $calendarDays[] = [
                'date' => $currentDate->copy(),
                'isCurrentMonth' => $currentDate->month == $month,
                'isToday' => $currentDate->isToday(),
                'isFuture' => $currentDate->isFuture(),
                'attendance' => $attendances[$currentDate->format('Y-m-d')] ?? null,
            ];
            $currentDate->addDay();
        }
        
        // Calculate summary
        $summary = [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'holiday' => $attendances->where('status', 'holiday')->count(),
        ];
        
        return view('admin.attendance.index', compact(
            'staffUsers',
            'selectedUser',
            'calendarDays',
            'month',
            'year',
            'summary',
            'attendances'
        ));
    }

    /**
     * Show bulk attendance marking page.
     */
    public function bulk(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $date = Carbon::parse($date);
        
        // Get staff users
        $staffUsers = User::where('user_role', '!=', 'user')->orderBy('name')->get();
        
        // Get existing attendance for this date
        $attendances = Attendance::whereDate('date', $date)
                                 ->whereIn('user_id', $staffUsers->pluck('id'))
                                 ->get()
                                 ->keyBy('user_id');
        
        return view('admin.attendance.bulk', compact('staffUsers', 'date', 'attendances'));
    }

    /**
     * Store bulk attendance.
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.user_id' => 'required|exists:users,id',
            'attendance.*.status' => 'required|in:present,absent,half_day,leave,holiday',
        ]);
        
        $date = $request->date;
        $count = 0;
        
        foreach ($request->attendance as $item) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $item['user_id'],
                    'date' => $date,
                ],
                [
                    'status' => $item['status'],
                    'check_in' => $item['check_in'] ?? null,
                    'check_out' => $item['check_out'] ?? null,
                    'notes' => $item['notes'] ?? null,
                    'marked_by' => Auth::id(),
                ]
            );
            $count++;
        }
        
        // Log bulk attendance marking
        $this->logAdminActivity('marked_bulk_attendance', "Marked bulk attendance for {$count} users on " . Carbon::parse($date)->format('d M Y'));
        
        return redirect()->back()->with('success', 'Attendance marked successfully for ' . Carbon::parse($date)->format('d M Y'));
    }

    /**
     * Store or update single attendance.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,leave,holiday',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'date' => $request->date,
            ],
            [
                'status' => $request->status,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'notes' => $request->notes,
                'marked_by' => Auth::id(),
            ]
        );
        
        // Calculate working hours if both check-in and check-out are provided
        if ($attendance->check_in && $attendance->check_out) {
            $attendance->working_hours = $attendance->calculateWorkingHours();
            $attendance->save();
        }
        
        // Log attendance marking
        $user = User::find($request->user_id);
        $userName = $user ? $user->name : "User #{$request->user_id}";
        $this->logAdminActivity('marked_attendance', "Marked attendance for {$userName} on " . Carbon::parse($request->date)->format('d M Y') . " as {$request->status}", $attendance);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully',
                'attendance' => $attendance,
            ]);
        }
        
        return redirect()->back()->with('success', 'Attendance marked successfully.');
    }

    /**
     * Get attendance data for AJAX requests.
     */
    public function getAttendance(Request $request)
    {
        $userId = $request->user_id;
        $month = $request->month;
        $year = $request->year;
        
        $attendances = Attendance::forUser($userId)
                                 ->forMonth($month, $year)
                                 ->get();
        
        return response()->json($attendances);
    }

    /**
     * Show attendance report.
     */
    public function report(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        // Get staff users with their attendance summary
        $staffUsers = User::where('user_role', '!=', 'user')
                          ->orderBy('name')
                          ->get()
                          ->map(function ($user) use ($month, $year) {
                              $attendances = Attendance::forUser($user->id)
                                                       ->forMonth($month, $year)
                                                       ->get();
                              
                              $user->attendance_summary = [
                                  'present' => $attendances->where('status', 'present')->count(),
                                  'absent' => $attendances->where('status', 'absent')->count(),
                                  'half_day' => $attendances->where('status', 'half_day')->count(),
                                  'leave' => $attendances->where('status', 'leave')->count(),
                                  'holiday' => $attendances->where('status', 'holiday')->count(),
                                  'total_working' => $attendances->whereIn('status', ['present', 'half_day'])->count(),
                              ];
                              
                              return $user;
                          });
        
        return view('admin.attendance.report', compact('staffUsers', 'month', 'year'));
    }

    /**
     * Delete attendance record.
     */
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        
        // Capture data before deletion for logging
        $user = User::find($attendance->user_id);
        $userName = $user ? $user->name : "User #{$attendance->user_id}";
        $date = $attendance->date->format('d M Y');
        $status = $attendance->status;
        
        $attendance->delete();
        
        // Log deletion
        $this->logAdminActivity('deleted_attendance', "Deleted attendance record for {$userName} on {$date} (was: {$status})");
        
        return redirect()->back()->with('success', 'Attendance record deleted successfully.');
    }
}
