@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body text-center">
                    
                    <div class="d-flex flex-wrap justify-content-evenly">
                        
                        <div class="text-center m-3 rounded border border-primary p-5 rounded" style="width:300px">
                            <h5>Component</h5>
                            
                            <h3>{{$componentPendCount}}</h3>
                            
                            <h5>Pending</h5>
                        </div>
                        <div class="text-center m-3 rounded border border-primary p-5 rounded" style="width:300px">
                            <h5>Material Request</h5>    
                            <h3>{{$materialQuantityRequestPendCount}}</h3>
                            <h5>Pending</h5>    
                        </div>

                        <div class="text-center m-3 rounded border border-primary p-5 rounded" style="width:300px">
                            <h5>Material Canvass</h5>    
                            <h3>{{$materialCanvassPendCount}}</h3>
                            <h5>Pending</h5>    
                        </div>


                        <div class="text-center m-3 rounded border border-primary p-5 rounded" style="width:300px">
                            <h5>Purchase Order</h5>    
                            <h3>{{$purchaseOrderPendCount}}</h3>
                            <h5>Pending</h5>    
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
