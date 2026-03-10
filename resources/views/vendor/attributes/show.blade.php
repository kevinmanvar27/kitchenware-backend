@extends('vendor.layouts.app')

@section('title', 'View Attribute')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'View Attribute'])
            
            @section('page-title', 'View Attribute')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">{{ $attribute->name }}</h4>
                                    <p class="mb-0 text-muted">Attribute Details</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('vendor.attributes.edit', $attribute) }}" class="btn btn-outline-primary rounded-pill px-4">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                    <a href="{{ route('vendor.attributes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="text-muted small mb-1">Name</h6>
                                        <p class="fw-bold">{{ $attribute->name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted small mb-1">Slug</h6>
                                        <p><code>{{ $attribute->slug }}</code></p>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="text-muted small mb-1">Type</h6>
                                        <p>
                                            @if($attribute->type == 'select')
                                                <span class="badge bg-primary">Select (Dropdown)</span>
                                            @elseif($attribute->type == 'color')
                                                <span class="badge bg-info">Color (Color Picker)</span>
                                            @elseif($attribute->type == 'button')
                                                <span class="badge bg-secondary">Button (Text Buttons)</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ ucfirst($attribute->type ?? 'Default') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted small mb-1">Status</h6>
                                        <p>
                                            @if($attribute->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="text-muted small mb-1">Sort Order</h6>
                                        <p>{{ $attribute->sort_order }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted small mb-1">Values Count</h6>
                                        <p>{{ $attribute->values->count() }} values</p>
                                    </div>
                                </div>
                                
                                @if($attribute->description)
                                    <div class="mb-4">
                                        <h6 class="text-muted small mb-1">Description</h6>
                                        <p>{{ $attribute->description }}</p>
                                    </div>
                                @endif
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="text-muted small mb-1">Created At</h6>
                                        <p>{{ $attribute->created_at->format('M d, Y H:i A') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted small mb-1">Last Updated</h6>
                                        <p>{{ $attribute->updated_at->format('M d, Y H:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Attribute Values Section -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Attribute Values</h5>
                            </div>
                            <div class="card-body">
                                @if($attribute->values->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Value</th>
                                                    <th>Slug</th>
                                                    @if($attribute->type == 'color')
                                                        <th>Color</th>
                                                    @endif
                                                    <th>Sort Order</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($attribute->values->sortBy('sort_order') as $index => $value)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $value->value }}</td>
                                                        <td><code>{{ $value->slug }}</code></td>
                                                        @if($attribute->type == 'color')
                                                            <td>
                                                                @if($value->color_code)
                                                                    <span class="d-inline-block rounded" style="width: 24px; height: 24px; background-color: {{ $value->color_code }}; border: 1px solid #ddd;"></span>
                                                                    <code class="ms-2">{{ $value->color_code }}</code>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        @endif
                                                        <td>{{ $value->sort_order }}</td>
                                                        <td>
                                                            @if($value->is_active)
                                                                <span class="badge bg-success">Active</span>
                                                            @else
                                                                <span class="badge bg-secondary">Inactive</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-list fa-2x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No values defined for this attribute.</p>
                                        <a href="{{ route('vendor.attributes.edit', $attribute) }}" class="btn btn-outline-primary rounded-pill px-4 mt-3">
                                            <i class="fas fa-plus me-2"></i>Add Values
                                        </a>
                                    </div>
                                @endif
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
