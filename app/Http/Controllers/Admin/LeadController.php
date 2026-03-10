<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use App\Traits\LogsActivity;

class LeadController extends Controller
{
    use LogsActivity;
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lead::query();

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

        $leads = $query->orderBy('created_at', 'desc')->get();
        
        // Status options for filter dropdown
        $statuses = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'followup' => 'Follow Up',
            'qualified' => 'Qualified',
            'converted' => 'Converted',
            'lost' => 'Lost',
        ];

        return view('admin.leads.index', compact('leads', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.leads.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'note' => 'nullable|string',
            'status' => 'required|in:new,contacted,followup,qualified,converted,lost',
        ]);

        $lead = Lead::create($validated);
        
        // Log activity
        $this->logAdminActivity('created', "Created lead: {$lead->name} (Contact: {$lead->contact_number})", $lead);

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        return view('admin.leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        return view('admin.leads.edit', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'note' => 'nullable|string',
            'status' => 'required|in:new,contacted,followup,qualified,converted,lost',
        ]);
        
        $oldStatus = $lead->status;
        $lead->update($validated);
        
        // Log activity
        $statusChanged = $oldStatus !== $lead->status ? " (Status: {$oldStatus} → {$lead->status})" : '';
        $this->logAdminActivity('updated', "Updated lead: {$lead->name}{$statusChanged}", $lead);

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Lead $lead)
    {
        // Capture data before deletion for logging
        $leadName = $lead->name;
        $leadId = $lead->id;
        
        $lead->delete(); // This performs soft delete due to SoftDeletes trait
        
        // Log activity
        $this->logAdminActivity('deleted', "Soft deleted lead: {$leadName} (ID: {$leadId})");

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Display a listing of trashed leads.
     */
    public function trashed(Request $request)
    {
        $query = Lead::onlyTrashed();

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter (on deleted_at for trashed leads)
        if ($request->filled('from_date')) {
            $query->whereDate('deleted_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('deleted_at', '<=', $request->to_date);
        }

        $leads = $query->orderBy('deleted_at', 'desc')->get();
        
        // Status options for filter dropdown
        $statuses = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'followup' => 'Follow Up',
            'qualified' => 'Qualified',
            'converted' => 'Converted',
            'lost' => 'Lost',
        ];

        return view('admin.leads.trashed', compact('leads', 'statuses'));
    }

    /**
     * Restore a soft deleted lead.
     */
    public function restore($id)
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);
        $lead->restore();
        
        // Log activity
        $this->logAdminActivity('restored', "Restored lead: {$lead->name} (ID: {$lead->id})", $lead);

        return redirect()->route('admin.leads.trashed')
            ->with('success', 'Lead restored successfully.');
    }

    /**
     * Permanently delete a lead.
     */
    public function forceDelete($id)
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);
        
        // Capture data before deletion for logging
        $leadName = $lead->name;
        $leadId = $lead->id;
        
        $lead->forceDelete();
        
        // Log activity
        $this->logAdminActivity('force_deleted', "Permanently deleted lead: {$leadName} (ID: {$leadId})");

        return redirect()->route('admin.leads.trashed')
            ->with('success', 'Lead permanently deleted.');
    }
}
