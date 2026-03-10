@extends('admin.layouts.app')

@section('title', 'Color Palette - ' . config('app.name', 'Laravel'))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Color Palette'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h2 class="card-title mb-0">Theme Color Palette</h2>
                        <p class="text-secondary mb-0">This page showcases the new color palette for both light and dark themes.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Light Theme Colors -->
                            <div class="col-12">
                                <h3 class="h5 mb-3">Light Theme</h3>
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(255, 107, 0, 1);">
                                                <h4 class="text-white">Primary</h4>
                                                <p class="text-white mb-0">#FF6B00 (Orange)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(245, 245, 245, 1);">
                                                <h4 class="text-dark">Secondary</h4>
                                                <p class="text-dark mb-0">#F5F5F5 (Light Gray)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(255, 255, 255, 1);">
                                                <h4 class="text-dark">Background</h4>
                                                <p class="text-dark mb-0">#FFFFFF (White)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(255, 255, 255, 1);">
                                                <h4 class="text-dark">Surface</h4>
                                                <p class="text-dark mb-0">#FFFFFF (White)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(51, 51, 51, 1);">
                                                <h4 class="text-white">Text</h4>
                                                <p class="text-white mb-0">#333333 (Dark Gray)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(117, 117, 117, 1);">
                                                <h4 class="text-white">Text Secondary</h4>
                                                <p class="text-white mb-0">#757575 (Medium Gray)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(224, 224, 224, 1);">
                                                <h4 class="text-dark">Border</h4>
                                                <p class="text-dark mb-0">#E0E0E0 (Light Gray Border)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(76, 175, 80, 1);">
                                                <h4 class="text-white">Success</h4>
                                                <p class="text-white mb-0">#4CAF50 (Green)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(244, 67, 54, 1);">
                                                <h4 class="text-white">Error</h4>
                                                <p class="text-white mb-0">#F44336 (Red)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(255, 152, 0, 1);">
                                                <h4 class="text-white">Warning</h4>
                                                <p class="text-white mb-0">#FF9800 (Orange)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dark Theme Colors -->
                            <div class="col-12 mt-4">
                                <h3 class="h5 mb-3">Dark Theme</h3>
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(255, 107, 0, 1);">
                                                <h4 class="text-white">Primary</h4>
                                                <p class="text-white mb-0">#FF6B00 (Orange)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(42, 42, 42, 1);">
                                                <h4 class="text-white">Secondary</h4>
                                                <p class="text-white mb-0">#2A2A2A (Dark Gray)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(18, 18, 18, 1);">
                                                <h4 class="text-white">Background</h4>
                                                <p class="text-white mb-0">#121212 (Very Dark Gray)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(30, 30, 30, 1);">
                                                <h4 class="text-white">Surface</h4>
                                                <p class="text-white mb-0">#1E1E1E (Dark Gray)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(255, 255, 255, 1);">
                                                <h4 class="text-dark">Text</h4>
                                                <p class="text-dark mb-0">#FFFFFF (White)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(176, 176, 176, 1);">
                                                <h4 class="text-dark">Text Secondary</h4>
                                                <p class="text-dark mb-0">#B0B0B0 (Light Gray)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(51, 51, 51, 1);">
                                                <h4 class="text-white">Border</h4>
                                                <p class="text-white mb-0">#333333 (Dark Gray Border)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(76, 175, 80, 1);">
                                                <h4 class="text-white">Success</h4>
                                                <p class="text-white mb-0">#4CAF50 (Green)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(244, 67, 54, 1);">
                                                <h4 class="text-white">Error</h4>
                                                <p class="text-white mb-0">#F44336 (Red)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(255, 152, 0, 1);">
                                                <h4 class="text-white">Warning</h4>
                                                <p class="text-white mb-0">#FF9800 (Orange)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Grayscale System -->
                            <div class="col-12 mt-4">
                                <h3 class="h5 mb-3">Grayscale System</h3>
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(250, 250, 250, 1);">
                                                <h4 class="text-dark">Gray 50</h4>
                                                <p class="text-dark mb-0">#FAFAFA (Lightest Gray)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(245, 245, 245, 1);">
                                                <h4 class="text-dark">Gray 100</h4>
                                                <p class="text-dark mb-0">#F5F5F5</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(238, 238, 238, 1);">
                                                <h4 class="text-dark">Gray 200</h4>
                                                <p class="text-dark mb-0">#EEEEEE</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(224, 224, 224, 1);">
                                                <h4 class="text-dark">Gray 300</h4>
                                                <p class="text-dark mb-0">#E0E0E0</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(189, 189, 189, 1);">
                                                <h4 class="text-dark">Gray 400</h4>
                                                <p class="text-dark mb-0">#BDBDBD</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(158, 158, 158, 1);">
                                                <h4 class="text-dark">Gray 500</h4>
                                                <p class="text-dark mb-0">#9E9E9E</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(117, 117, 117, 1);">
                                                <h4 class="text-white">Gray 600</h4>
                                                <p class="text-white mb-0">#757575</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(97, 97, 97, 1);">
                                                <h4 class="text-white">Gray 700</h4>
                                                <p class="text-white mb-0">#616161</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(66, 66, 66, 1);">
                                                <h4 class="text-white">Gray 800</h4>
                                                <p class="text-white mb-0">#424242</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4" style="background-color: rgba(33, 33, 33, 1);">
                                                <h4 class="text-white">Gray 900</h4>
                                                <p class="text-white mb-0">#212121 (Darkest Gray)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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