@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/accomplishment">
                    <span>
                       Accomplishment
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Component
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
                    <a href="/accomplishment/project/{{$project->id}}">{{$project->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Section</th>
                <td>
                    <a href="/accomplishment/section/{{$section->id}}">{{$section->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Contract Item</th>
                <td>
                    <a href="/accomplishment/contract_item/{{$contract_item->id}}">{{$contract_item->name}}</a>
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
        <!-- Blank page for now -->
    </div>

</div>
</div>
@endsection
