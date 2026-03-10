@extends('admin.layouts.app')

@section('title', 'Create Lead')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Create Lead'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Create Lead</h4>
                                    <p class="mb-0 text-muted">Add a new lead</p>
                                </div>
                                <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Leads
                                </a>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <form action="{{ route('admin.leads.store') }}" method="POST" id="lead-form">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill" id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required>
                                                @error('contact_number')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="note" class="form-label">Note</label>
                                                <textarea class="form-control rounded-3" id="note" name="note" rows="4">{{ old('note') }}</textarea>
                                                @error('note')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="card border rounded-3">
                                                <div class="card-header bg-light py-2">
                                                    <h6 class="mb-0">Lead Status</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                                        <select class="form-select rounded-pill" id="status" name="status" required>
                                                            <option value="new" {{ old('status', 'new') == 'new' ? 'selected' : '' }}>New</option>
                                                            <option value="contacted" {{ old('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                                            <option value="followup" {{ old('status') == 'followup' ? 'selected' : '' }}>Follow Up</option>
                                                            <option value="qualified" {{ old('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                                                            <option value="converted" {{ old('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                                                            <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                                                        </select>
                                                        @error('status')
                                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-theme w-100 rounded-pill">
                                                        <i class="fas fa-save me-2"></i>Save Lead
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>
@endsection
