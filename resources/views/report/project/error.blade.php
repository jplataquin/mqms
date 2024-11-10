@extends('layouts.app')

@section('content')
<div id="content">
    <h4>{{$message}}</h4>
    <div class="container">
        {{$validation_error}}
    </div>
</div>
@endsection