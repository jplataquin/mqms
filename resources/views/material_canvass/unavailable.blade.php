@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">

        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
                <ul>
                    <li>
                        <a href="/request/material_canvass">
                            <span>
                            Material Canvass
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="active">
                            <span>
                            Display
                            </span>
                            <i class="ms-2 bi bi-display"></i>
                        </a>
                    </li>
                </ul>
        </div>
        <hr>

        <table class="record-table-horizontal" hx-boost="true" hx-select="#content" hx-target="#main">
            <tbody>
                
                <tr>
                    <th>Project ID</th>
                    <td>
                        <a href="/project/{{$project->id}}">{{ str_pad($project->id,6,0,STR_PAD_LEFT) }}</a>
                    </td>
                </tr>
                <tr>
                    <th>Project Name</th>
                    <td>
                        {{$project->name}}
                    </td>
                </tr>
                <tr>
                    <th>
                        Project Status
                    </th>
                    <td>
                        {{$project->status}}
                    </td>
                </tr>
                
                <tr>
                    <th colspan="2">
                        <hr>
                    </th>
                </tr>

                <tr>
                    <th>Section</th>
                    <td>
                        {{$section->name}}
                    </td>
                </tr>
                <tr>
                    <th>Contract Item</th>
                    <td>
                        {{$contract_item->item_code}} {{$contract_item->description}}
                    </td>
                </tr>

                <tr>
                    <th colspan="2">
                        <hr>
                    </th>
                </tr>

                <tr>
                    <th>Component ID</th>
                    <td>
                        <a href="/review/component/{{$contract_item->id}}/{{$component->id}}">{{ str_pad($component->id,6,0,STR_PAD_LEFT) }}</a>
                    </td>
                </tr>
                <tr>
                    <th>Component Name</th>
                    <td>
                        {{$component->name}}
                    </td>
                </tr>
                <tr>
                    <th>Component Status</th>
                    <td>    
                        {{$component->status}}
                    
                    </td>
                </tr>
                
                <tr>
                    <th colspan="2">
                        <hr>
                    </th>
                </tr>

                <tr>
                    <th>Material Request ID</th>
                    <td>
                        <a href="/review/material_quantity_request/{{$material_request->id}}" >{{ str_pad($material_request->id,6,0,STR_PAD_LEFT) }}</a>
                    </td>
                </tr>
                <tr>
                    <th>Material Request Status</th>
                    <td>
                        {{$material_request->status}}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="text-center" hx-boost="true" hx-select="#content" hx-target="#main">
            <h1>Unavailable</h1>
            <h5>*** {{$message}} ***</h5>
            <br>
            <a href="/review/material_canvass">Click here to return to list</a>
        </div>

    </div>


    
    <script type="module" >
        window.util.quickNav = {
            title:'Review Material Canvass',
            url: '/review/material_canvass'
        };
    </script>
</div>

@endsection