@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="#">
                        <span>
                        Material Quantity
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            Report
                        </span>
                        <i class="ms-2 bi bi-display"></i>		
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container mb-5">
            <div class="folder-form-tab">
                Material Quantity Report
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

                
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-lg-12">
                <hr>
                <h3>{{$material_item->formatted_name()}}</h3>
                <hr>
            </div>
        </div>

        <div class="row mb-3" hx-boost="true" hx-select="#content" hx-target="#main">
            
            <div class="col-lg-6 mb-3">
                <h4>Pending ({{ number_format($mqr_pending->total_quantity,2) }})</h4>
                <div class="list-group">
                    @foreach($mqr_pending->mqr_ids as $mqr_pending_id)
                       
                        <a class="list-group-item list-group-item-action" href="/material_quantity_request/{{$mqr_pending_id}}">{{str_pad($mqr_pending_id,6,0,STR_PAD_LEFT)}}</a>
                       
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <h4>Approved ({{ number_format($mqr_approved->total_quantity,2) }})</h4>
                <div class="list-group">
                    @foreach($mqr_approved->mqr_ids as $mqr_approved_id)
                       
                        <a class="list-group-item list-group-item-action" href="/material_quantity_request/{{$mqr_approved_id}}">{{str_pad($mqr_approved_id,6,0,STR_PAD_LEFT)}}</a>
                       
                    @endforeach
                </div>
            </div>


        </div>
    </div>
    <script type="module">
    </script>
</div>
@endsection