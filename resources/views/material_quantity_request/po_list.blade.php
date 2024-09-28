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
        
        <div class="row mb-3" hx-boost="true" hx-select="#content" hx-target="#main">
            <div class="col-lg-3">
                <h4>Pending ({{count($pending)}})</h4>
                <div class="list-group">
                    @foreach($pending as $pend_po)
                       
                        <a class="list-group-item list-group-item-action" href="/purchase_order/{{$pend_po->id}}">{{str_pad($pend_po->id,6,0,STR_PAD_LEFT)}}</a>
                       
                    @endforeach
                </div>
            </div>

            <div class="col-lg-3">
                <h4>Approved ({{count($approved)}})</h4>
                <div class="list-group">
                    @foreach($approved as $aprv_po)
                       
                        <a class="list-group-item list-group-item-action" href="/purchase_order/{{$aprv_po->id}}">{{str_pad($aprv_po->id,6,0,STR_PAD_LEFT)}}</a>
                       
                    @endforeach
                </div>
            </div>

            <div class="col-lg-3">
                <h4>Rejected ({{count($rejected)}})</h4>
                <div class="list-group">
                    @foreach($rejected as $rejc_po)
                       
                        <a class="list-group-item list-group-item-action" href="/purchase_order/{{$rejc_po->id}}">{{str_pad($rejc_po->id,6,0,STR_PAD_LEFT)}}</a>
                       
                    @endforeach
                </div>
            </div>

            <div class="col-lg-3">
                <h4>Deleted ({{count($deleted)}})</h4>
                <div class="list-group">
                    @foreach($deleted as $del_po)
                       
                        <a class="list-group-item list-group-item-action" href="/purchase_order/{{$del_po->id}}">{{str_pad($del_po->id,6,0,STR_PAD_LEFT)}}</a>
                       
                    @endforeach
                </div>
            </div>

        </div>
    </div>
    <script type="module">
    </script>
</div>
@endsection