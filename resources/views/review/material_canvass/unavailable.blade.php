@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">

    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/review/material_canvass">
                        <span>
                        Review
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Material Canvass
                        </span>
                        <i class="ms-2 bi bi-display"></i>
                    </a>
                </li>
            </ul>
    </div>
    <hr>

    <table class="record-table-horizontal">
        <tbody>
            
            <tr>
                <th>Project ID</th>
                <td>
                    {{ str_pad($project->id,0,6,STR_PAD_LEFT) }}
                </td>
            </tr>
            <tr>
                <th>Project</th>
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
                <th>Component ID</th>
                <td>
                    {{ str_pad($component->id,0,6,STR_PAD_LEFT) }}
                </td>
            </tr>
            <tr>
                <th>Component</th>
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
                <th>Material Request ID</th>
                <td>
                    {{ str_pad($material_request->id,0,6,STR_PAD_LEFT) }}
                </td>
                <td>
                    {{$material_request->status}}
                </td>
            </tr>

        </tbody>
    </table>

    <div class="text-center">
        <h1>Unavailable</h1>
        <h5>*** {{$message}} ***</h5>
        <br>
        <a href="/material_quantity_request/select/create">Click here to return to previous page</a>
    </div>

</div>
</div>
@endsection