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
                       Material Item
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container mb-3">
            <div class="folder-form-tab">Price Report</div>
            <div class="folder-form-body">
                <table class="record-table-horizontal">
                    <tr>
                        <th>Project</th>
                        <td>{{$project_name}}</td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>{{$section_name}}</td>
                    </tr>
                    <tr>
                        <th>Contract Item</th>
                        <td>{{$contract_item_name}}</td>
                    </tr>
                    <tr>
                        <th>Component</th>
                        <td>{{$component_name}}</td>
                    </tr>
                    <tr>
                        <th>Date Scope</th>
                        <td>{{$from}} - {{$to}}</td>
                    </tr>
                    <tr>
                        <th>Material Group</th>
                        <td>{{$material_group->name}}</td>
                    </tr>
                </table>
            </div>
        </div>

        @foreach($result as $material_item_id => $res1)

            <div class="mb-3 ps-3 border border-primary">
                <h2>{{ $material_item_options[$material_item_id]->text }}</h2>

                @foreach($res1 as $supplier_id => $res2)
                    <div class="mb-3 ps-3">
                        <h3>{{ $supplier_options[$supplier_id]->text }}</h3>

                        @foreach($res2 as $payment_term_id => $res3)
                            <div class="mb-3 ps-3">
                                <h4>{{ $payment_term_options[$payment_term_id]->text }}</h4>

                                <ul class="list-group">
                                    @foreach($res3 as $price => $created_at)
                                        <li class="list-group-item">P{{number_format($price,2)}} [ {{$created_at}} ]</li>
                                    @endforeach
                                
                                </ul>
                            </div>
                        @endforeach
                    </div>

                @endforeach

            </div>

        @endforeach
    

    </div>

    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

    </script>
</div>
@endsection