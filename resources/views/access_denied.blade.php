@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="mb-4">
                    <h1 class="display-5 text-danger fw-bold">Access Denied</h1>
                    <p class="lead text-muted">You do not have the required permissions to perform this action or view this page.</p>
                </div>

                @if(!empty($missing_codes))
                <div class="card border-danger mb-4 text-start shadow-sm">
                    <div class="card-header bg-danger bg-opacity-10 text-danger fw-bold">
                        Required Access Code(s)
                    </div>
                    <div class="card-body">
                        <p class="mb-2">Your account is missing at least one of the following required access code(s):</p>
                        <div class="d-flex flex-wrap gap-2 pt-2">
                            @foreach($missing_codes as $code)
                                <span class="badge border border-danger text-danger p-2 fs-6">
                                    <code>{{ $code }}</code>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-4">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                        Go Back
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-primary">
                        Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection