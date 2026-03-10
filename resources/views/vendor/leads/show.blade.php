@extends('vendor.layouts.app')

@section('title', 'Lead Details')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', [
                'pageTitle' => 'Lead Details',
                'breadcrumbs' => [
                    'Leads' => route('vendor.leads.index'),
                    $lead->name => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3 mb-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="row">
                    <!-- Lead Details -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0 fw-bold">
                                    {{ $lead->name }}
                                    @php
                                        $statusColors = [
                                            'new' => 'primary',
                                            'contacted' => 'info',
                                            'qualified' => 'warning',
                                            'converted' => 'success',
                                            'lost' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$lead->status] ?? 'secondary' }} ms-2">
                                        {{ ucfirst($lead->status) }}
                                    </span>
                                </h4>
                                <div>
                                    <a href="{{ route('vendor.leads.edit', $lead) }}" class="btn btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Contact Number</h6>
                                        <h5 class="fw-bold">
                                            <a href="tel:{{ $lead->contact_number }}" class="text-decoration-none">
                                                <i class="fas fa-phone me-2 text-success"></i>{{ $lead->contact_number }}
                                            </a>
                                        </h5>
                                    </div>
                                    
                                    @if($lead->email)
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Email</h6>
                                        <h5 class="fw-bold">
                                            <a href="mailto:{{ $lead->email }}" class="text-decoration-none">
                                                <i class="fas fa-envelope me-2 text-primary"></i>{{ $lead->email }}
                                            </a>
                                        </h5>
                                    </div>
                                    @endif
                                    
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Status</h6>
                                        <span class="badge bg-{{ $statusColors[$lead->status] ?? 'secondary' }} fs-6 px-3 py-2">
                                            {{ ucfirst($lead->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Created</h6>
                                        <h5 class="fw-bold">{{ $lead->created_at->format('d M Y, h:i A') }}</h5>
                                    </div>
                                </div>
                                
                                @if($lead->note)
                                <hr>
                                <div class="mt-3">
                                    <h6 class="text-muted mb-2">Notes</h6>
                                    <div class="p-3 bg-light rounded">
                                        {!! nl2br(e($lead->note)) !!}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Reminders Section -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-bell me-2"></i>Reminders
                                    @if($lead->pendingReminders->count() > 0)
                                        <span class="badge bg-warning rounded-pill ms-2">{{ $lead->pendingReminders->count() }}</span>
                                    @endif
                                </h5>
                                <button type="button" class="btn btn-sm btn-theme rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                                    <i class="fas fa-plus me-1"></i>Add Reminder
                                </button>
                            </div>
                            <div class="card-body">
                                @if($lead->reminders->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Date & Time</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lead->reminders()->orderBy('reminder_at', 'desc')->get() as $reminder)
                                                <tr class="{{ $reminder->is_overdue ? 'table-danger' : '' }}">
                                                    <td>
                                                        <div class="fw-medium">{{ $reminder->title }}</div>
                                                        @if($reminder->description)
                                                            <small class="text-muted">{{ Str::limit($reminder->description, 40) }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>{{ $reminder->reminder_at->format('M d, Y') }}</div>
                                                        <small class="text-muted">{{ $reminder->reminder_at->format('h:i A') }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $reminder->status_badge_class }} rounded-pill px-2 py-1">
                                                            {{ $reminder->status_label }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            @if($reminder->status === 'pending')
                                                                <button type="button" class="btn btn-outline-primary rounded-start-pill px-2" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#editReminderModal{{ $reminder->id }}"
                                                                        title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <form action="{{ route('vendor.leads.reminders.complete', $reminder) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-outline-success px-2" title="Complete">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                </form>
                                                                <form action="{{ route('vendor.leads.reminders.dismiss', $reminder) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-outline-secondary px-2" title="Dismiss">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            <form action="{{ route('vendor.leads.reminders.destroy', $reminder) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger {{ $reminder->status !== 'pending' ? 'rounded-pill' : 'rounded-end-pill' }} px-2" 
                                                                        onclick="return confirm('Delete this reminder?')" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Edit Reminder Modal -->
                                                @if($reminder->status === 'pending')
                                                <div class="modal fade" id="editReminderModal{{ $reminder->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header border-0">
                                                                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Reminder</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ route('vendor.leads.reminders.update', $reminder) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label for="edit_title_{{ $reminder->id }}" class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                                                                        <input type="text" class="form-control rounded-pill" id="edit_title_{{ $reminder->id }}" name="title" value="{{ $reminder->title }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_reminder_at_{{ $reminder->id }}" class="form-label fw-medium">Reminder Date & Time <span class="text-danger">*</span></label>
                                                                        <input type="datetime-local" class="form-control rounded-pill" id="edit_reminder_at_{{ $reminder->id }}" name="reminder_at" value="{{ $reminder->reminder_at->format('Y-m-d\TH:i') }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_description_{{ $reminder->id }}" class="form-label fw-medium">Description</label>
                                                                        <textarea class="form-control" id="edit_description_{{ $reminder->id }}" name="description" rows="3">{{ $reminder->description }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-0">
                                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-theme rounded-pill px-4">Update Reminder</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-bell-slash fa-2x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No reminders set for this lead</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <a href="tel:{{ $lead->contact_number }}" class="btn btn-success w-100 rounded-pill mb-2">
                                    <i class="fas fa-phone me-2"></i>Call Lead
                                </a>
                                @if($lead->email)
                                <a href="mailto:{{ $lead->email }}" class="btn btn-primary w-100 rounded-pill mb-2">
                                    <i class="fas fa-envelope me-2"></i>Email Lead
                                </a>
                                @endif
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->contact_number) }}" target="_blank" class="btn btn-outline-success w-100 rounded-pill mb-2">
                                    <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                </a>
                                <button type="button" class="btn btn-outline-warning w-100 rounded-pill mb-2" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                                    <i class="fas fa-bell me-2"></i>Set Reminder
                                </button>
                            </div>
                        </div>
                        
                        <!-- Next Reminder Card -->
                        @if($lead->nextReminder)
                        <div class="card border-0 shadow-sm mb-4 {{ $lead->nextReminder->is_overdue ? 'border-danger' : 'border-warning' }}" style="border-left: 4px solid {{ $lead->nextReminder->is_overdue ? '#dc3545' : '#ffc107' }} !important;">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-clock me-2 {{ $lead->nextReminder->is_overdue ? 'text-danger' : 'text-warning' }}"></i>
                                    {{ $lead->nextReminder->is_overdue ? 'Overdue Reminder' : 'Next Reminder' }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">{{ $lead->nextReminder->title }}</h6>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $lead->nextReminder->reminder_at->format('M d, Y h:i A') }}
                                </p>
                                @if($lead->nextReminder->description)
                                    <p class="small mb-3">{{ $lead->nextReminder->description }}</p>
                                @endif
                                <div class="d-flex gap-2">
                                    <form action="{{ route('vendor.leads.reminders.complete', $lead->nextReminder) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill">
                                            <i class="fas fa-check me-1"></i>Complete
                                        </button>
                                    </form>
                                    <form action="{{ route('vendor.leads.reminders.dismiss', $lead->nextReminder) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100 rounded-pill">
                                            <i class="fas fa-times me-1"></i>Dismiss
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-exchange-alt me-2"></i>Update Status</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.leads.update', $lead) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $lead->name }}">
                                    <input type="hidden" name="contact_number" value="{{ $lead->contact_number }}">
                                    <input type="hidden" name="note" value="{{ $lead->note }}">
                                    <select name="status" class="form-select mb-3" onchange="this.form.submit()">
                                        <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>New</option>
                                        <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                        <option value="qualified" {{ $lead->status === 'qualified' ? 'selected' : '' }}>Qualified</option>
                                        <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted</option>
                                        <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>Lost</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <a href="{{ route('vendor.leads.edit', $lead) }}" class="btn btn-theme w-100 rounded-pill mb-2">
                                    <i class="fas fa-edit me-2"></i>Edit Lead
                                </a>
                                <a href="{{ route('vendor.leads.index') }}" class="btn btn-outline-secondary w-100 rounded-pill mb-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                                <form action="{{ route('vendor.leads.destroy', $lead) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">
                                        <i class="fas fa-trash me-2"></i>Delete Lead
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1" aria-labelledby="addReminderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="addReminderModalLabel"><i class="fas fa-bell me-2"></i>Add Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vendor.leads.reminders.store', $lead) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill" id="title" name="title" placeholder="e.g., Follow up call" required>
                    </div>
                    <div class="mb-3">
                        <label for="reminder_at" class="form-label fw-medium">Reminder Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control rounded-pill" id="reminder_at" name="reminder_at" required min="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-medium">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Add any notes for this reminder..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme rounded-pill px-4">
                        <i class="fas fa-bell me-2"></i>Set Reminder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
