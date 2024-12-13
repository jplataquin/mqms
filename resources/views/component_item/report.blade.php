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
                        <div class="form-container">
                            <div class="form-header">
                                POW / DUPA
                            </div>
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Quantity</label>
                                            <input type="text" class="form-control" value="{{ number_format($component_item->ref_1_quantity,2) }}" disabled="true"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Unit</label>
                                            <input type="text" class="form-control" value="{{ $component_item->ref_1_unit_text() }}" disabled="true"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Unit Price</label>
                                            <input type="text" class="form-control" value="{{ number_format($component_item->ref_1_unit_price,2) }}" disabled="true"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-container">
                            <div class="form-header">
                                Material Budget
                            </div>
                            <div class="form-body">
                                
                                <div class="row mb-3">

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>Function Type</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ $component_item->function_type_text() }}"/>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>Variable</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ $component_item->function_variable }}"/>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>Variable</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ $component_item->quantity }}"/>
                                        </div>
                                    </div>
                                    
                                     <div class="col-3">
                                        <div class="form-group">
                                            <label>Unit</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ $component_item->unit_text() }}"/>
                                        </div>
                                    </div>

                                </div>

                            </div>
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