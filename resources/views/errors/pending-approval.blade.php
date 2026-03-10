@extends('frontend.layouts.app')

@section('title', 'Account Pending Approval')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Account Pending Approval') }}</div>

                <div class="card-body">
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">{{ __('Account Pending Approval') }}</h4>
                        <p>{{ $message ?? 'Your account is pending approval. Please wait for admin approval before accessing the site.' }}</p>
                        <hr>
                        <p class="mb-0">{{ __('If you believe this is an error, please contact the site administrator.') }}</p>
                    </div>
                    
                    <div class="d-flex justify-content-between">
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