@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs">
            <ul>
                <li>
                    <a href="/report/fulfilment/parameters">
                        <span>
                        Report
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Request to Purchase Timeframe KPI
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr>
        

        <div class="folder-form-container mb-3">
            <div class="folder-form-tab">Request to Purchase Timeframe KPI</div>
            <div class="folder-form-body">
                <table class="record-table-horizontal">
                    <tr>
                        <th>Date Scope</th>
                        <td>{{$from}} - {{$to}}</td>
                    </tr>
                </table>
            </div>
        </div>
        

        <div class="mb-3">
            <h4>KPI: Material Request to Material Purchase within 7 days (Target: 90%)</h4>
        </div>

        <div class="container border border-primary p-5">
            <div class="row">
                <div class="col-sm-4 text-center">
                    <h3>Request</h3>
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

            <div class="row mt-5">
                <div class="col-sm-12 text-center">
                    <h2>Percentage</h2>
                    <h2>{{$percentage}}%</h2>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

    </script>
</div>
@endsection