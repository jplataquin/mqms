@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body text-center">
                    
                    <div class="row">
                        <div class="text-center col-6 border border-primary p-5 me-5 rounded" style="width:300px">
                            <h5>Pending</h5>
                            
                            <h3>{{$materialQuantityRequestPendCount}}</h3>
                            
                            <h5>Material Request</h5>
                        </div>

                        <div class="text-center col-6 border border-primary p-5 me-5 rounded" style="width:300px">
                            <h5>Pending</h5>
                            
                            <h3>{{$componentPendCount}}</h3>
                            
                            <h5>Component</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
