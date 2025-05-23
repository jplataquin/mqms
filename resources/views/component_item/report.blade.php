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
                    <div class="col-lg-11">
                        <div class="form-group">
                            <label>Component Item</label>
                            <input type="text" value="{{$component_item->name}}" class="form-control" disabled="ture"/>
                        </div>
                    </div>
                    <div class="col-lg-1">
                        <div class="form-group">
                            <label>Sum Flag</label>
                            <input type="text" value="@if($component_item->sum_flag) ✅ @else ❌ @endif" class="form-control" disabled="ture"/>
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
                                            <input type="text" class="form-control" value="{{ $component_item->ref_1_unit_text }}" disabled="true"/>
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

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Function Type</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ $component_item->function_type_text() }}"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Variable</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ $component_item->function_variable }}"/>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Quantity</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($component_item->quantity,2).' '.$component_item->unit_text }}"/>
                                        </div>
                                    </div>
                                    
                                     <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Equivalent</label>
                                            @php 

                                                $equivalent = '';

                                                if($component_item->function_type_id == 4){

                                                    $equivalent = ($component_item->function_variable * $component_item->quantity) * $component->use_count;
                                                }

                                                if($equivalent){
                                                    $equivalent = $equivalent.' '.$component->unit_text;
                                                }

                                            @endphp
                                            <input type="text" class="form-control" disabled="true" value="{{ $equivalent }}"/>
                                        </div>
                                    </div>

                                </div>
                                <div class="row mb-3">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Unit Price</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($component_item->budget_price,2) }}"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Total Amount</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ number_format(($component_item->quantity * $component_item->budget_price), 2) }}"/>
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
            
            <div class="col-lg-3 mb-3 text-center">
                <h4>Pending ( {{count($material_requests['PEND'])}} )</h4>
                <div class="list-group">                    
                    @foreach($material_requests['PEND'] as $row)
                        <a class="list-group-item list-group-item-action" href="/material_quantity_request/{{$row->id}}">MR{{str_pad($row->id,6,0,STR_PAD_LEFT)}}</a>   
                    @endforeach
                </div>
            </div>

            <div class="col-lg-3 mb-3 text-center">
                <h4>Approved ( {{count($material_requests['APRV'])}} )</h4>
                <div class="list-group">
                    @foreach($material_requests['APRV'] as $row)
                        <a class="list-group-item list-group-item-action" href="/material_quantity_request/{{$row->id}}">MR{{str_pad($row->id,6,0,STR_PAD_LEFT)}}</a>   
                    @endforeach
                </div>
            </div>
            
            <div class="col-lg-3 mb-3 text-center">
                <h4>Rejected ( {{count($material_requests['REJC'])}} )</h4>
                <div class="list-group">
                    @foreach($material_requests['REJC'] as $row)
                        <a class="list-group-item list-group-item-action" href="/material_quantity_request/{{$row->id}}">MR{{str_pad($row->id,6,0,STR_PAD_LEFT)}}</a>   
                    @endforeach
                </div>
            </div>

            <div class="col-lg-3 mb-3 text-center">
                <h4>Deleted ( {{count($material_requests['DELE'])}} )</h4>
                <div class="list-group">
                    @foreach($material_requests['DELE'] as $row)
                        <a class="list-group-item list-group-item-action" href="/material_quantity_request/{{$row->id}}">MR{{str_pad($row->id,6,0,STR_PAD_LEFT)}}</a>   
                    @endforeach
                </div>
            </div>

        </div>
    </div>
    <script type="module">
    </script>
</div>
@endsection