@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="text-center">
            <h1 class="text-danger">--Access Denied--</h1>
            <h1>MR: {{ str_pad($id,6,0,STR_PAD_LEFT) }}</h1>
            <h2>(Your account does not have the authority to approve this record)</h2>

            <div class="mt-5">
                <button class="btn btn-primary" onclick="window.util.navTo('/material_quantity_request/{{$id}}');">View Record</button>
            </div>
        </div>
    </div>
</div>
@endsection