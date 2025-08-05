@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="text-center">
            <h1 class="text-danger">--Error--</h1>
            <h1>Material Request: {{ str_pad($id,6,0,STR_PAD_LEFT) }}</h1>
            <h2>{{$message}}</h2>

            @if($data)
            <div class="mt-3">
                <ul>
                    @foreach($data as $msg)
                        <li>{{$msg}}</li>
                    @endforeach
                </uL>
            </div>
            @endif

            <div class="mt-5">
                <button class="btn btn-primary" onclick="window.util.navTo('/material_quantity_requests/{{$id}}');">View Record</button>
            </div>
        </div>
    </div>
</div>
@endsection