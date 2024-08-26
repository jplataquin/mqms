@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="#">
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

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Material Canvass
        </div>
        <div class="folder-form-body">        
            <table class="record-table-horizontal mb-3">
                <tbody>
                    <tr>
                        <th width="230px">Material Request ID</th>
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
                        <th>Component</th>
                        <td>{{$component->name}}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{$material_quantity_request->status}}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>
                            <textarea disabled="true" class="w-100" id="description">{{$material_quantity_request->description}}</textarea>
                        </td>
                    </tr>
                </tbody>
                
            </table>

            
            <div class="row">
                <div class="col-12 text-end">
                    <button class="btn btn-warning" id="printBtn">Print</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-5">
        @foreach($items as $item)
        <div class="border border-primary p-3 mb-3">
            <div class="row mb-5">
               <h5>  {{ $component_item_arr[ $item->component_item_id ]->name }}</h5>
                @php 
                    $component_item = $component_item_arr[ $item->component_item_id ];
                    $material_item = $material_item_arr[$item->material_item_id];
                @endphp
                <div class="col-6">
                    <div class="form-group">
                        <label>Material</label>
                        <input type="text" class="form-control" disabled="true" value="{{$material_item->brand}} {{$material_item->name}} {{$material_item->specification_unit_packaging}}"/>
                    </div>
                </div>

                <div class="col-3">
                    <label>Quantity</label>
                    <input type="text" class="form-control" disabled="true" value="{{ $item->requested_quantity }}"/>
                </div>
                <div class="col-3">
                    <label>Budget Price</label>
                    <input type="text" class="form-control" disabled="true" value="P {{ number_format($component_item->budget_price,2) }}"/>
                </div>
            </div>

            <div class="row">
                <div class="col-12 canvass-container" id="canvass_{{$item->id}}"></div>
                <div class="text-center mt-3">
                    <button data-id="{{$item->id}}" class="add-canvass-btn btn btn-secondary w-100">Add Canvass</button>
                </div>
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


        <div class="row mt-5">
            <div class="col-12 text-end">
                <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
                <button id="submitBtn" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {$q,$el} from '/adarna.js';
    import CanvassItem from '/ui_components/CanvassItem.js';
    
    let addCanvassBtn = $q('.add-canvass-btn');
    let submitBtn     = $q('#submitBtn').first();
    let cancelBtn     = $q('#cancelBtn').first();
    let printBtn      = $q('#printBtn').first();

    @foreach($items as $item)

        @foreach($item->MaterialCanvass as $mcItem)
            
            (()=>{
                let supplierList = $q('#supplier_list > option[data-value="{{$mcItem->supplier_id}}"]').first();
                let paymentTermsList = $q('#payment_terms_list > option[data-value="{{$mcItem->payment_term_id}}"]').first();
                
                $el.append( CanvassItem({
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
                    price:'{{$mcItem->price}}'
                }) ).to($q('#canvass_{{$item->id}}').first());

            })();
        @endforeach


    @endforeach


    addCanvassBtn.apply(el=>{

        el.onclick = (e)=>{
            e.preventDefault();

            let container = el.parentElement.parentElement.querySelector('.canvass-container');
           
            $el.append(
                CanvassItem({
                    material_quantity_request_item_id: el.getAttribute('data-id'),
                    supplier_list:'supplier_list',
                    payment_terms_list:'payment_terms_list',
                    quantity:'{{$item->requested_quantity}}'
                })
            ).to(container);
        }
    });


    submitBtn.onclick = (e)=>{

        e.preventDefault();

        let canvasItems = $q('div[data-component-name="CanvassItem"]').items();

        if(!canvasItems.length) return false;

        
        if(!confirm('Are you sure you want to submit canvass?')){return false}

        let validateFlag = true;
        let itemData     = [];

        canvasItems.map(item=>{
            validateFlag = item.handler.validate();

            let data = item.handler.getData();

            if(data.status == ''){
                itemData.push(data);
            }
        });

        if(!validateFlag) return false;

        if(!itemData.length){
            window.util.showMsg('Error: No data to submit');
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/material_canvass/create',{
            canvassItems: JSON.stringify(itemData),
            material_quantity_request_id: '{{$material_quantity_request->id}}'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){

                window.util.showMsg(reply);
                return false;
            }

            window.util.navReload();
        });
    }


    cancelBtn.onclick = (e)=>{
         window.util.navTo('/material_canvass');
    }

    printBtn.onclick = (e)=>{
        document.location.href = '/material_canvass/print/{{$material_quantity_request->id}}';
    }
</script>
</div>
@endsection