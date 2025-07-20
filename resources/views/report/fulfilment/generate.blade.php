@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs">
            <ul>
                <li>
                    <a href="#">
                        <span>
                        Report
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Fulfilment
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr>
        

        <div class="row">
            <div class="col-sm-4 text-center">
                <h3>Target</h3>
                <h3>
                    {{ number_format($request_count,2) }}
                </h3>
            </div>   
            
            <div class="col-sm-4 text-center">
                <h3>Hit</h3>
                <h3>
                   {{ number_format($target_hit,2) }}
                </h3>
            </div>
            
            <div class="col-sm-4 text-center">
                <h3>Missed</h3>
                <h3>
                    {{ number_format($target_missed,2) }}
                </h3>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <h2>Percentage</h2>
                <h2>{{$percentage}}%</h2>
            </div>
        </div>

    </div>

    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

    </script>
</div>
@endsection