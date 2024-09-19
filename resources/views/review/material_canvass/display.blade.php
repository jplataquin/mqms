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

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Review Material Canvass
        </div>
        <div class="folder-form-body">
            <table class="record-table-horizontal">
                <tbody>
                    <tr>
                        <th>Material Request ID</th>
                        <td>{{str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT)}}</td>
                    </tr>
                    <tr>
                        <th>Project</th>
                        <td>{{$project->name}}</td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>{{$section->name}}</td>
                    </tr>
                    <tr>
                        <th>Contract Item</th>
                        <td>{{$contract_item->item_code}} {{$contract_item->description}}</td>
                    </tr>
                    <tr>
                        <th>Component</th>
                        <td>{{$component->name}}</td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td>{{$material_quantity_request->CreatedByUser()->name}} {{$material_quantity_request->created_at}}</td>
                    </tr>

                    @if($material_quantity_request->updated_at)
                    <tr>
                        <th>Updated By</th>
                        <td>{{$material_quantity_request->UpdatedByUser()->name}} {{$material_quantity_request->updated_at}}</td>
                    </tr>
                    @endif

                    <tr>
                        <th>Approved By</th>
                        <td>{{$material_quantity_request->ApprovedByUser()->name}} {{$material_quantity_request->approved_at}}</td>
                    </tr>
                    
                    <tr>
                        <th>Description</th>
                        <td>
                            <textarea disabled="true" class="w-100" id="description">{{$material_quantity_request->description}}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="row mt-3">
                <div class="col-12 text-end">
                    <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="mt-3">
        @foreach($items as $item)

        <div class="form-container mb-5">
            <div class="form-header">
               &nbsp;
            </div>
            <div class="form-body">
                
                    <div class="row mb-5">
                        @php 
                            $material_item = $material_item_arr[$item->material_item_id];
                            $component_item = $component_item_arr[ $item->component_item_id ];

                        @endphp
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Material</label>
                                <input type="text" class="form-control" disabled="true" value="{{$material_item->brand}} {{$material_item->name}} {{$material_item->specification_unit_packaging}}"/>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label>Quantity</label>
                            <input type="text" class="form-control" disabled="true" value="{{ $item->requested_quantity }}"/>
                        </div>

                        
                        <div class="col-lg-3">
                            <label>Budget Price</label>
                            <input type="text" class="form-control" disabled="true" value="P {{ number_format($component_item->budget_price,2) }}"/>
                        </div>
                    </div>
                    
                    <div class="canvass-container" id="canvass_{{$item->id}}"></div>
                
            </div>
        </div>   
        @endforeach


        <datalist id="supplier_list">
            @foreach($suppliers as $supplier)
                <option data-value="{{$supplier->id}}" value="{{$supplier->name}}"/>
            @endforeach
        </datalist>

        <datalist id="payment_terms_list">
            @foreach($payment_terms as $payment_term)
            <option data-value="{{$payment_term->id}}" value="{{$payment_term->text}}"/>
            @endforeach
        </datalist>


        
    </div>
</div>

<script type="module">
    import {$q,$el,Template} from '/adarna.js';
    import CanvassItem from '/ui_components/CanvassItem.js';
    
    const cancelBtn         = $q('#cancelBtn').first();
    const t                 = new Template();

    let supplierList        = null;
    let paymentTermsList    = null;
    let container           = null;
    
    window.util.quickNav = {
        title:'Review Material Canvass',
        url: '/review/material_canvass'
    };

    @foreach($items as $item)

    
        container = $q('#canvass_{{$item->id}}').first();
        
        //This is to prevent duplication of render
        container.innerHTML = '';
        
        @foreach($item->MaterialCanvass as $mcItem)

            //(()=>{
                supplierList        = $q('#supplier_list > option[data-value="{{$mcItem->supplier_id}}"]').first();
                paymentTermsList    = $q('#payment_terms_list > option[data-value="{{$mcItem->payment_term_id}}"]').first();
    
                @if($mcItem->status == 'PEND')
                    
                    $el.append( 

                        t.div({class:'border border-secondary rounded p-3 mb-3'},(el)=>{
                            el.append(
                                CanvassItem({
                                    id:'{{$mcItem->id}}',
                                    material_quantity_request_item_id:'{{$item->id}}',
                                    supplier_list:'supplier_list',
                                    payment_terms_list:'payment_terms_list',
                                    quantity:'{{$item->requested_quantity}}',
                                    supplier_id: '{{$mcItem->supplier_id}}',
                                    supplier_text: supplierList.getAttribute('value'),
                                    payment_term_id: '{{$item->payment_term_id}}',
                                    payment_term_text: paymentTermsList.getAttribute('value'),
                                    status:'{{$mcItem->status}}',
                                    price:'{{$mcItem->price}}',
                                    approvalFlag: true
                                })
                            ); 
                        })
            
                    ).to(container);
                    
                @else

                    $el.append( 
                        t.div({class:'border border-secondary rounded p-3 mb-3'},(el)=>{
                            el.append(
                                CanvassItem({
                                    id:'{{$mcItem->id}}',
                                    material_quantity_request_item_id:'{{$item->id}}',
                                    supplier_list:'supplier_list',
                                    payment_terms_list:'payment_terms_list',
                                    quantity:'{{$item->requested_quantity}}',
                                    supplier_id: '{{$mcItem->supplier_id}}',
                                    supplier_text: supplierList.getAttribute('value'),
                                    payment_term_id: '{{$item->payment_term_id}}',
                                    payment_term_text: paymentTermsList.getAttribute('value'),
                                    status:'{{$mcItem->status}}',
                                    price:'{{$mcItem->price}}',
                                    approvalFlag: false
                                })
                            );
                        })
                    ).to(container);
                    

                @endif
            //})();
        @endforeach


    @endforeach

    cancelBtn.onclick = (e)=>{    
        window.util.navTo('/review/material_canvass');
    }
</script>
</div>
@endsection