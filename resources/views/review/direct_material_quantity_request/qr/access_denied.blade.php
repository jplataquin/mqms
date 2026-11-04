@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="text-center">
            <h1 class="text-danger">--Access Denied--</h1>
            <h1>DMR: {{ str_pad($id,6,0,STR_PAD_LEFT) }}</h1>
            <h2>(Your account does not have the authority to approve this record)</h2>

            @if(!empty($missing_codes))
            <div class="row justify-content-center mt-4">
                <div class="col-md-6 text-start">
                    <div class="card border-danger shadow-sm">
                        <div class="card-header bg-danger bg-opacity-10 text-danger fw-bold">
                            Required Access Code(s)
                        </div>
                        <div class="card-body">
                            <p class="mb-2">You need at least one of the following access codes:</p>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($missing_codes as $code)
                                    <span class="badge border border-danger text-danger p-2 fs-6">
                                        <code>{{ $code }}</code>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-5">
                <button class="btn btn-primary" onclick="window.util.navTo('/direct_material_quantity_request/{{$id}}');">View Record</button>
            </div>
        </div>
    </div>
</div>
@endsection
