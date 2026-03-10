<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\VendorStaff;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class AttendanceController extends Controller
{
    use LogsActivity;
    
    /**
     * Get the current vendor.
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Get staff users for the current vendor.
     */
    private function getVendorStaff()
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            return collect();
        }

        // Get all vendor staff user IDs
        $staffUserIds = VendorStaff::where('vendor_id', $vendor->id)
            ->pluck('user_id')
            ->toArray();
        
        // Also include the vendor owner
        if ($vendor->user_id) {
            $staffUserIds[] = $vendor->user_id;
        }

        return User::whereIn('id', $staffUserIds)->orderBy('name')->get();
    }

    /**
     * Display attendance listing with calendar view.
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $userId = $request->get('user_id');
        
        // Get staff users for this vendor
        $staffUsers = $this->getVendorStaff();
        
        // Get selected user or first staff user
        $selectedUser = $userId ? User::find($userId) : $staffUsers->first();
        
        // Verify selected user belongs to this vendor
        if ($selectedUser && !$staffUsers->contains('id', $selectedUser->id)) {
            $selectedUser = $staffUsers->first();
        }
        
        // Get attendance for the selected month (vendor-scoped)
        $attendances = [];
        if ($selectedUser) {
            $attendances = Attendance::where('vendor_id', $vendor->id)
                                     ->forUser($selectedUser->id)
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
            'present' => collect($attendances)->where('status', 'present')->count(),
            'absent' => collect($attendances)->where('status', 'absent')->count(),
            'half_day' => collect($attendances)->where('status', 'half_day')->count(),
            'leave' => collect($attendances)->where('status', 'leave')->count(),
            'holiday' => collect($attendances)->where('status', 'holiday')->count(),
        ];
        
        return view('vendor.attendance.index', compact(
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
        $vendor = $this->getVendor();
        if (!$vendor) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }

        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $date = Carbon::parse($date);
        
        // Get staff users for this vendor
        $staffUsers = $this->getVendorStaff();
        
        // Get existing attendance for this date (vendor-scoped)
        $attendances = Attendance::where('vendor_id', $vendor->id)
                                 ->whereDate('date', $date)
                                 ->whereIn('user_id', $staffUsers->pluck('id'))
                                 ->get()
                                 ->keyBy('user_id');
        
        return view('vendor.attendance.bulk', compact('staffUsers', 'date', 'attendances'));
    }

    /**
     * Store bulk attendance.
     */
    public function storeBulk(Request $request)
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }

        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.user_id' => 'required|exists:users,id',
            'attendance.*.status' => 'required|in:present,absent,half_day,leave,holiday',
        ]);
        
        $date = $request->date;
        $staffUserIds = $this->getVendorStaff()->pluck('id')->toArray();
        $count = 0;
        
        foreach ($request->attendance as $item) {
            // Verify user belongs to this vendor
            if (!in_array($item['user_id'], $staffUserIds)) {
                continue;
            }

            Attendance::updateOrCreate(
                [
                    'vendor_id' => $vendor->id,
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
        $this->logVendorActivity($vendor->id, 'marked_bulk_attendance', "Marked bulk attendance for {$count} staff on " . Carbon::parse($date)->format('d M Y'));
        
        return redirect()->back()->with('success', 'Attendance marked successfully for ' . Carbon::parse($date)->format('d M Y'));
    }

    /**
     * Store or update single attendance.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Vendor not found.'], 403);
            }
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,leave,holiday',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Verify user belongs to this vendor
        $staffUserIds = $this->getVendorStaff()->pluck('id')->toArray();
        if (!in_array($request->user_id, $staffUserIds)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'User does not belong to this vendor.'], 403);
            }
            return redirect()->back()->with('error', 'User does not belong to this vendor.');
        }
        
        $attendance = Attendance::updateOrCreate(
            [
                'vendor_id' => $vendor->id,
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
        $this->logVendorActivity($vendor->id, 'marked_attendance', "Marked attendance for {$userName} on " . Carbon::parse($request->date)->format('d M Y') . " as {$request->status}", $attendance);
        
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
        $vendor = $this->getVendor();
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 403);
        }

        $userId = $request->user_id;
        $month = $request->month;
        $year = $request->year;
        
        // Verify user belongs to this vendor
        $staffUserIds = $this->getVendorStaff()->pluck('id')->toArray();
        if (!in_array($userId, $staffUserIds)) {
            return response()->json(['error' => 'User does not belong to this vendor'], 403);
        }
        
        $attendances = Attendance::where('vendor_id', $vendor->id)
                                 ->forUser($userId)
                                 ->forMonth($month, $year)
                                 ->get();
        
        return response()->json($attendances);
    }

    /**
     * Show attendance report.
     */
    public function report(Request $request)
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        // Get staff users with their attendance summary
        $staffUsers = $this->getVendorStaff()
                          ->map(function ($user) use ($vendor, $month, $year) {
                              $attendances = Attendance::where('vendor_id', $vendor->id)
                                                       ->forUser($user->id)
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
        
        return view('vendor.attendance.report', compact('staffUsers', 'month', 'year'));
    }

    /**
     * Delete attendance record.
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }

        $attendance = Attendance::where('vendor_id', $vendor->id)->findOrFail($id);
        
        // Capture data before deletion for logging
        $user = User::find($attendance->user_id);
        $userName = $user ? $user->name : "User #{$attendance->user_id}";
        $date = $attendance->date->format('d M Y');
        $status = $attendance->status;
        
        $attendance->delete();
        
        // Log deletion
        $this->logVendorActivity($vendor->id, 'deleted_attendance', "Deleted attendance record for {$userName} on {$date} (was: {$status})");
        
        return redirect()->back()->with('success', 'Attendance record deleted successfully.');
    }
}