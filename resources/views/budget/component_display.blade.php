@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/budget">
                    <span>
                       Budget
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Component Items
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <table class="record-table-horizontal mb-3"  hx-boost="true" hx-select="#content" hx-target="#main">
        <tbody>
            <tr>
                <th>Project</th>
                <td>
                    <a href="/budget/project/{{$project->id}}">{{$project->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Section</th>
                <td>
                    <a href="/budget/section/{{$section->id}}">{{$section->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Contract Item</th>
                <td>
                    <a href="/budget/contract_item/{{$contract_item->id}}">{{$contract_item->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Component</th>
                <td>
                   {{$component->name}}
                </td>
            </tr>
        </tbody>
    </table>



    <div class="container" id="list">
        
            @foreach($component_item_arr as $ci)
                <div class="border border-secondary mb-3 p-3">
                        {{$ci->data->name}}
            
                        <div class="container overflow-scroll">
                            <table class="table">
                                <tr>
                                    <th style="min-width:200px">
                                        Budget
                                    </th>
                                
                                    <th style="min-width:200px">
                                        Pending Request
                                    </th>
                                    <th style="min-width:200px">
                                        Approved Request
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        {{ number_format($ci->data->quantity,2) }} {{ $ci->unit_text }}
                                    </td>
                                
                                    <td>
                                        {{ number_format($ci->material_request_pending_quantity,2) }} {{ $ci->unit_text }}
                                    </td>
                                    <td>
                                        {{ number_format($ci->material_request_approve_quantity,2) }} {{ $ci->unit_text }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                </div>
            @endforeach
        
    </div>

   

</div>

<script type="module">
    import {$q,Template,$el} from '/adarna.js';


</script>
</div>
@endsection