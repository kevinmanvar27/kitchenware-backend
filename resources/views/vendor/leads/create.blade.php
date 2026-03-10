@extends('vendor.layouts.app')

@section('title', 'Create Lead')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Create Lead'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">Create New Lead</h4>
                                        <p class="mb-0 text-muted small">Add a new lead to your list</p>
                                    </div>
                                    <a href="{{ route('vendor.leads.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <form action="{{ route('vendor.leads.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-pill @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="contact_number" class="form-label fw-medium">Contact Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-pill @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required>
                                        @error('contact_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                                        <select class="form-select rounded-pill @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="new" {{ old('status') == 'new' ? 'selected' : '' }}>New</option>
                                            <option value="contacted" {{ old('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                            <option value="followup" {{ old('status') == 'followup' ? 'selected' : '' }}>Follow Up</option>
                                            <option value="qualified" {{ old('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                                            <option value="converted" {{ old('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                                            <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="note" class="form-label fw-medium">Note</label>
                                        <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="4">{{ old('note') }}</textarea>
                                        @error('note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Create Lead
                                        </button>
                                        <a href="{{ route('vendor.leads.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection