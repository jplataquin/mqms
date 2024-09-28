@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/material_quantity_requests">
                        <span>
                        Material Requests
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            PO List
                        </span>
                        <i class="ms-2 bi bi-display"></i>		
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container mb-5">
            <div class="folder-form-tab">
                PO List
            </div>
            <div class="folder-form-body">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Project</label>
                            <input type="text" value="{{$project->name}}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Section</label>
                            <input type="text" value="{{$section->name}}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">

                
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Contract Item</label>
                            <input type="text" value="{{$contract_item->item_code}} - {{$contract_item->description}}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Component</label>
                            <input type="text" value="{{$component->name}}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                </div>

                
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Material Request ID</label>
                            <input type="text" value="{{ str_pad($material_request->id,6,0,STR_PAD_LEFT) }}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <script type="module">
    </script>
</div>
@endsection