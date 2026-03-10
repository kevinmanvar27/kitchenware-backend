<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class LeadController extends Controller
{
    use LogsActivity;
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        $query = Lead::where('vendor_id', $vendor->id)
            ->with('nextReminder');

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        
        // Status options for filter dropdown
        $statuses = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'followup' => 'Follow Up',
            'qualified' => 'Qualified',
            'converted' => 'Converted',
            'lost' => 'Lost',
        ];

        return view('vendor.leads.index', compact('leads', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vendor.leads.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'note' => 'nullable|string',
            'status' => 'required|in:new,contacted,followup,qualified,converted,lost',
        ]);

        $validated['vendor_id'] = $vendor->id;
        
        $lead = Lead::create($validated);

        // Log the activity
        $this->logVendorActivity($vendor->id, 'created', "Created lead: {$lead->name} (Status: {$lead->status})", $lead);

        return redirect()->route('vendor.leads.index')
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $vendor = $this->getVendor();
        
        if ($lead->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this lead.');
        }
        
        // Eager load relationships
        $lead->load(['reminders', 'pendingReminders', 'nextReminder']);
        
        return view('vendor.leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        $vendor = $this->getVendor();
        
        if ($lead->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this lead.');
        }
        
        return view('vendor.leads.edit', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $vendor = $this->getVendor();
        
        if ($lead->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this lead.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'note' => 'nullable|string',
            'status' => 'required|in:new,contacted,followup,qualified,converted,lost',
        ]);

        $lead->update($validated);

        // Log the activity
        $this->logVendorActivity($vendor->id, 'updated', "Updated lead: {$lead->name} (Status: {$lead->status})", $lead);

        return redirect()->route('vendor.leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Lead $lead)
    {
        $vendor = $this->getVendor();
        
        if ($lead->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this lead.');
        }
        
        $leadName = $lead->name;
        $leadId = $lead->id;
        
        $lead->delete();

        // Log the activity
        $this->logVendorActivity($vendor->id, 'deleted', "Deleted lead: {$leadName} (ID: {$leadId})");

        return redirect()->route('vendor.leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Display a listing of trashed leads.
     */
    public function trashed(Request $request)
    {
        $vendor = $this->getVendor();
        
        $query = Lead::onlyTrashed()->where('vendor_id', $vendor->id);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('deleted_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('deleted_at', '<=', $request->to_date);
        }

        $leads = $query->orderBy('deleted_at', 'desc')->paginate(10)->withQueryString();
        
        $statuses = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'followup' => 'Follow Up',
            'qualified' => 'Qualified',
            'converted' => 'Converted',
            'lost' => 'Lost',
        ];

        return view('vendor.leads.trashed', compact('leads', 'statuses'));
    }

    /**
     * Restore a soft deleted lead.
     */
    public function restore($id)
    {
        $vendor = $this->getVendor();
        
        $lead = Lead::onlyTrashed()->where('vendor_id', $vendor->id)->findOrFail($id);
        $lead->restore();

        // Log the activity
        $this->logVendorActivity($vendor->id, 'restored', "Restored lead: {$lead->name}", $lead);

        return redirect()->route('vendor.leads.trashed')
            ->with('success', 'Lead restored successfully.');
    }

    /**
     * Permanently delete a lead.
     */
    public function forceDelete($id)
    {
        $vendor = $this->getVendor();
        
        $lead = Lead::onlyTrashed()->where('vendor_id', $vendor->id)->findOrFail($id);
        
        $leadName = $lead->name;
        $leadId = $lead->id;
        
        $lead->forceDelete();

        // Log the activity
        $this->logVendorActivity($vendor->id, 'permanently_deleted', "Permanently deleted lead: {$leadName} (ID: {$leadId})");

        return redirect()->route('vendor.leads.trashed')
            ->with('success', 'Lead permanently deleted.');
    }

    /**
     * Display all reminders.
     */
    public function reminders(Request $request)
    {
        $vendor = $this->getVendor();
        
        $query = LeadReminder::where('vendor_id', $vendor->id)
            ->with('lead');

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('status', 'pending')
                      ->where('reminder_at', '<', now());
            } else {
                $query->where('status', $request->status);
            }
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('reminder_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('reminder_at', '<=', $request->to_date);
        }

        $reminders = $query->orderBy('reminder_at', 'asc')->paginate(15)->withQueryString();
        
        // Count due reminders for badge
        $dueCount = LeadReminder::where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->where('reminder_at', '<=', now())
            ->count();

        return view('vendor.leads.reminders', compact('reminders', 'dueCount'));
    }

    /**
     * Get due reminders count (for AJAX).
     */
    public function dueReminders()
    {
        $vendor = $this->getVendor();
        
        $dueReminders = LeadReminder::where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->where('reminder_at', '<=', now())
            ->with('lead')
            ->orderBy('reminder_at', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'count' => $dueReminders->count(),
            'reminders' => $dueReminders->map(function ($reminder) {
                return [
                    'id' => $reminder->id,
                    'title' => $reminder->title,
                    'lead_id' => $reminder->lead_id,
                    'lead_name' => $reminder->lead->name,
                    'reminder_at' => $reminder->reminder_at->format('M d, Y h:i A'),
                    'is_overdue' => $reminder->is_overdue,
                ];
            }),
        ]);
    }

    /**
     * Store a new reminder for a lead.
     */
    public function storeReminder(Request $request, Lead $lead)
    {
        $vendor = $this->getVendor();
        
        if ($lead->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this lead.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_at' => 'required|date|after:now',
        ]);

        $validated['lead_id'] = $lead->id;
        $validated['vendor_id'] = $vendor->id;

        LeadReminder::create($validated);

        return redirect()->back()
            ->with('success', 'Reminder created successfully.');
    }

    /**
     * Update a reminder.
     */
    public function updateReminder(Request $request, LeadReminder $reminder)
    {
        $vendor = $this->getVendor();
        
        if ($reminder->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this reminder.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_at' => 'required|date',
        ]);

        $reminder->update($validated);

        return redirect()->back()
            ->with('success', 'Reminder updated successfully.');
    }

    /**
     * Mark a reminder as completed.
     */
    public function completeReminder(LeadReminder $reminder)
    {
        $vendor = $this->getVendor();
        
        if ($reminder->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this reminder.');
        }

        $reminder->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Reminder marked as completed.');
    }

    /**
     * Dismiss a reminder.
     */
    public function dismissReminder(LeadReminder $reminder)
    {
        $vendor = $this->getVendor();
        
        if ($reminder->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this reminder.');
        }

        $reminder->update([
            'status' => 'dismissed',
        ]);

        return redirect()->back()
            ->with('success', 'Reminder dismissed.');
    }

    /**
     * Delete a reminder.
     */
    public function destroyReminder(LeadReminder $reminder)
    {
        $vendor = $this->getVendor();
        
        if ($reminder->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this reminder.');
        }

        $reminder->delete();

        return redirect()->back()
            ->with('success', 'Reminder deleted successfully.');
    }
}