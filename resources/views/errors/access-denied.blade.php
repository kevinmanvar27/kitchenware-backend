@extends('frontend.layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Access Denied') }}</div>

                <div class="card-body">
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">{{ __('Access Denied') }}</h4>
                        <p>{{ $message ?? 'You do not have access to the admin area.' }}</p>
                        <hr>
                        <p class="mb-0">{{ __('Please contact the site administrator if you believe this is an error.') }}</p>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('frontend.home') }}" class="btn btn-primary">
                            {{ __('Go to Homepage') }}
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection