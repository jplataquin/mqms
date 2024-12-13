@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="#">
                        <span>
                        Component Item
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
                Component Item Report
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
                            <label>Component Item</label>
                            <input type="text" value="{{$component_item->name}}" class="form-control" disabled="ture"/>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Status</label>
                            <input type="text" value="{{$component_item->status}}" class="form-control" disabled="ture"/>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-lg-12">
                <hr>
                <h3>Material Requests</h3>
                <hr>
            </div>
        </div>

        <div class="row mb-3" hx-boost="true" hx-select="#content" hx-target="#main">
            
            <div class="col-lg-6 mb-3 text-center">
                <h4>Pending ()</h4>
                <div class="list-group">

                        
                    
                </div>
            </div>

            <div class="col-lg-6 mb-3 text-center">
                <h4>Approved ()</h4>
                <div class="list-group">
                    
                </div>
            </div>


        </div>
    </div>
    <script type="module">
    </script>
</div>
@endsection