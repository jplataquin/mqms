@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body text-center">
                    
                    <div class="border border-primary p-5">
                        <h5>Pending</h5>
                        
                        <h3>{{$materialQuantityRequestPendCount}}</h3>
                        
                        <h5>Material Quantity Request</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
