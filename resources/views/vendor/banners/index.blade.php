@extends('vendor.layouts.app')

@section('title', 'Banners')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Banner Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Banner Management</h4>
                                        <p class="mb-0 text-muted small">Manage your store banners and promotional images</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('vendor.banners.create') }}" class="btn btn-sm btn-theme rounded-pill px-3">
                                            <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add Banner</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <!-- Stats Cards -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <div class="card border-0 shadow-sm h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-primary rounded-circle p-3">
                                                            <i class="fas fa-images text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h3 class="mb-0 fw-bold">{{ $banners->count() }}</h3>
                                                        <p class="text-muted mb-0 small">Total Banners</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 shadow-sm h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-success rounded-circle p-3">
                                                            <i class="fas fa-check-circle text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h3 class="mb-0 fw-bold">{{ $banners->where('is_active', true)->count() }}</h3>
                                                        <p class="text-muted mb-0 small">Active</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 shadow-sm h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-secondary rounded-circle p-3">
                                                            <i class="fas fa-times-circle text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h3 class="mb-0 fw-bold">{{ $banners->where('is_active', false)->count() }}</h3>
                                                        <p class="text-muted mb-0 small">Inactive</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 shadow-sm h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-info rounded-circle p-3">
                                                            <i class="fas fa-link text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h3 class="mb-0 fw-bold">{{ $banners->whereNotNull('redirect_url')->count() }}</h3>
                                                        <p class="text-muted mb-0 small">With Links</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Banners Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 px-4 py-3" style="width: 50px;">Order</th>
                                                <th class="border-0 py-3" style="width: 120px;">Image</th>
                                                <th class="border-0 py-3">Title</th>
                                                <th class="border-0 py-3">Redirect URL</th>
                                                <th class="border-0 py-3 text-center">Status</th>
                                                <th class="border-0 py-3 text-end pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sortable-banners">
                                            @forelse($banners as $banner)
                                                <tr data-id="{{ $banner->id }}">
                                                    <td class="px-4">
                                                        <i class="fas fa-grip-vertical text-muted" style="cursor: move;"></i>
                                                    </td>
                                                    <td>
                                                        <img src="{{ asset($banner->image_path) }}" 
                                                             alt="{{ $banner->title }}" 
                                                             class="rounded" 
                                                             style="width: 100px; height: 60px; object-fit: cover;">
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold">{{ $banner->title }}</span>
                                                    </td>
                                                    <td>
                                                        @if($banner->redirect_url)
                                                            <span class="text-muted small">
                                                                {{ Str::limit($banner->redirect_url, 40) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted small">No link</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <form action="{{ route('vendor.banners.toggle-status', $banner->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                                                @if($banner->is_active)
                                                                    <span class="badge bg-success text-white">Active</span>
                                                                @else
                                                                    <span class="badge bg-secondary text-white">Inactive</span>
                                                                @endif
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <a href="{{ route('vendor.banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('vendor.banners.destroy', $banner->id) }}" method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-images fa-3x mb-3 opacity-50"></i>
                                                            <p class="mb-0">No banners found. Click "Add Banner" to create your first banner.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    // Initialize SortableJS for drag and drop
    @if($banners->count() > 0)
    const sortable = new Sortable(document.getElementById('sortable-banners'), {
        handle: '.fa-grip-vertical',
        animation: 150,
        onEnd: function(evt) {
            const order = [];
            document.querySelectorAll('#sortable-banners tr[data-id]').forEach((row, index) => {
                order.push({
                    id: row.dataset.id,
                    display_order: index + 1
                });
            });
            
            // Send AJAX request to update order
            fetch('{{ route("vendor.banners.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Optional: Show success message
                    console.log('Order updated successfully');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
    @endif
    
    // Delete confirmation
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if(confirm('Are you sure you want to delete this banner? This action cannot be undone.')) {
                this.submit();
            }
        });
    });
</script>
@endpush
