@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="text-center mb-5">
            <h3>Resitricted Action, no permission(s) granted</h3>
            <h4>(<a href="#" onclick="history.back()">Back</a>)</h4>
        </div>

        <div class="mb-5">
            Required Access Codes: 
            
            @foreach($required_access_codes as $code)
                <div class="mb-3 ms-3">{{$code}}</div>
            @endforeach
        </div>

        <div class="mb-5">
            Existing Access Codes: 
            
            @foreach($current_access_codes as $code)
                <div class="mb-3 ms-3">{{$code}}</div>
            @endforeach
        </div>
    </div>
    
    <script type="module">
    </script>
</div>
@endsection