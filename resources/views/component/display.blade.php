@extends('layouts.app')

@section('content')
<div id="content">

<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            
            <li>
                <a href="/project/section/contract_item/{{$component->contract_item_id}}">
                    <span>
                        Contract Item
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Component 
                    </span>
                    <i class="ms-2 bi bi-display"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>


        <div id="callout-non-conforming" class="callout callout-danger d-none">
            <h4>Warning: Non-conforming items</h4> 
        </div>

        <table class="record-table-horizontal">
            <tr>
                <th>Project</th>
                <td>{{$project->name}}</td>
            </tr>
            <tr>
                <th>Section</th>
                <td>{{$section->name}}</td>
            </tr>
            <tr id="component_sticky_trigger">
                <th>Contract Item</th>
                <td>{{$contract_item->item_code}} - {{$contract_item->description}}</td>
            </tr>
                
        </table> 
        <hr>   
        
        <div class="folder-form-container">
            <div class="folder-form-tab">
                Amounts
            </div>
            <div class="folder-form-body">
                            
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-container">
                            <div class="form-header">Grand Total</div>
                            <div class="form-body">
                                <div class="row mb-3">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Contract</label>
                                            <input type="text" disabled="true" class="form-control" value="P {{ number_format($grand_total_amounts['contract'],2) }}"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>DUPA/POW</label>
                                            <input type="text" disabled="true" class="form-control" value="P {{ number_format($grand_total_amounts['reference'],2) }}"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            @php 
                                                $material_grand_total_percentage = 0;

                                                if($grand_total_amounts['contract']){
                                                    $material_grand_total_percentage = ($grand_total_amounts['material'] / $grand_total_amounts['contract']) * 100;
                                                }
                                            @endphp
                                            <label>Material Budget ({{number_format($material_grand_total_percentage)}}%)</label>
                                            <input type="text" disabled="true" class="form-control" value="P {{ number_format($grand_total_amounts['material'],2) }}"/>
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
                                Contract Item
                            </div>
                            <div class="form-body">
                            <div class="row mb-3">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            @php 
                                               $contract_amount = $contract_item->contract_quantity * $contract_item->contract_unit_price;
                                               $contract_amount = number_format($contract_amount,2);
                                            @endphp
                                            <label>Contract</label>
                                            <input type="text" disabled="true" class="form-control" value="P {{$contract_amount}}"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>DUPA/POW</label>
                                            <input type="text" disabled="true" class="form-control" value="P"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            
                                            <label>Material Budget</label>
                                            <input type="text" disabled="true" class="form-control" value="P"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
         </div>
        
        <hr>
    <div class="form-container" id="component_form" >
        <div class="form-header">
            Component
        </div>
        <div class="form-body">
            
          
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Name</label>
                        <input class="form-control editable_field" type="text" id="component" value="{{$component->name}}" disabled="true"/>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Status</label>
                        <input class="form-control" type="text" id="status" value="{{$component->status}}" disabled="true"/>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input class="form-control editable_field" type="text" id="component_quantity" value="{{$component->quantity}}" disabled="true"/>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Unit</label>   
                        <select id="component_unit" class="form-control editable_field" disabled="true">
                            @foreach($unit_options as $opt)
                                <option value="{{$opt->id}}" @if($component->unit_id == $opt->id) selected @endif @if($opt->deleted) disabled="true" @endif>{{$opt->text}} @if($opt->deleted) [Deleted] @endif</option>
                            @endforeach
                        </select>         
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                    <label>Use Count</label>
                    <input class="form-control editable_field" type="text" id="use_count" value="{{$component->use_count}}" disabled="true"/>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Sum Flag</label>
                        <div class="form-switch text-center">
                            <input type="checkbox" class="form-check-input editable_field" id="component_sum_flag" value="1"  disabled="true" @if($component->sum_flag == 1) checked @endif/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-none form-container" id="item_sticky_container">
                <div class="form-header">Item</div>
                <div class="form-body">
                    <div class="row mb-3">
                        
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" id="item_name" class="form-control" disabled="true"/>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Sum Flag</label>
                            
                                <div class="form-switch text-center">
                                    <input type="checkbox" id="item_sum_flag" class="form-check-input" disabled="true"/>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Function Type</label>
                                <input type="text" disabled="true" id="item_function_type" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Variable</label>
                                <input type="text" disabled="true" id="item_variable" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="text" disabled="true" id="item_quantity" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Equivalent</label>
                                <input type="text" disabled="true" id="item_equivalent" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Unit</label>
                                <input type="text" disabled="true" id="item_unit" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Budget Price</label>
                                <input type="text" disabled="true" id="item_budget_price" class="form-control"/>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="row" id="component_controls">
                <div class="col-lg-6">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                </div>
                <div class="col-lg-6 text-end">

                    @if($component->status == 'PEND')
                        <button class="btn btn-outline-primary" id="reviewLinkBtn">
                            Review Link
                            <i class="bi bi-copy"></i>
                        </button>
                    @endif

                    <button class="btn btn-warning" id="printBtn">Print</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>
    

    <hr>

   
    <div class="folder-form-container">
        <div class="folder-form-tab">
            Component Items
        </div>
        <div class="folder-form-body text-end">
            <button class="btn btn-warning" id="addComponentItemBtn">Add</button>
        </div>
    </div>
    <div id="component_item_list" class="mt-3"></div>

    <div id="comment-box"></div>
</div>

<script type="module">
    import {Template,$q,$el,State,Signal} from '/adarna.js';
    import ComponentItemEl from '/ui_components/ComponentItem.js';
    import CreateComponentItemForm from '/ui_components/create_forms/CreateComponentItemForm.js';
    import CommentForm from '/ui_components/comment/CommentForm.js';

    const materialItemOptions       = @json($materialItems);
    const component                 = $q('#component').first();
    const component_item_list       = $q('#component_item_list').first();
    const editBtn                   = $q('#editBtn').first();
    const cancelBtn                 = $q('#cancelBtn').first();
    const updateBtn                 = $q('#updateBtn').first();
    const createBtn                 = $q('#createBtn').first();
    const deleteBtn                 = $q('#deleteBtn').first();
    const printBtn                  = $q('#printBtn').first();
    const status                    = $q('#status').first();
    const component_quantity        = $q('#component_quantity').first();
    const use_count                 = $q('#use_count').first();
    const component_sum_flag        = $q('#component_sum_flag').first();
    const component_unit            = $q('#component_unit').first();
    const component_sticky_trigger  = $q('#component_sticky_trigger').first();
    const component_controls        = $q('#component_controls').first();
    const component_form            = $q('#component_form').first();
    const add_component_item_button = $q('#addComponentItemBtn').first();
    const reviewLinkBtn             = $q('#reviewLinkBtn').first();
    const callout_non_conforming    = $q('#callout-non-conforming').first();
    const comment_box               = $q('#comment-box').first();
   
    const t = new Template();

    const signal = new Signal();

    signal.receiver('material-total-calculated',(data)=>{
        
        
        callout_non_conforming.classList.add('d-none');
        
        if( $q('.non-compliant').exists() ){
            callout_non_conforming.classList.remove('d-none');
        }

    });

    window.util.quickNav = {
        tile: 'Component',
        url: '/project/section/contract_item/component'
    };
    
    //Hack to prevent double comment box when using back button
    comment_box.innerHTML = '';

    comment_box.append(CommentForm({
        record_id       :'{{$component->id}}',
        record_type     :'COMPON'
    }));
    
    editBtn.onclick = ()=>{

        $q('.editable_field').apply((el)=>{
            el.disabled = false;
        });

        editBtn.classList.add('d-none');
        updateBtn.classList.remove('d-none');

        cancelBtn.onclick = ()=>{
            window.util.navReload();
        }
    }

    cancelBtn.onclick = ()=>{

        @if($back)
            history.back();
        @else
            window.util.navTo('/project/section/contract_item/{{$contract_item->id}}');
        @endif
    }

    updateBtn.onclick = ()=>{

        window.util.blockUI();

        window.util.$post('/api/component/update',{
            id          :'{{$component->id}}',
            name        : component.value,
            quantity    : component_quantity.value,
            unit_id     : component_unit.value,
            use_count   : use_count.value,
            sum_flag    : (component_sum_flag.checked == true) ? 1 : 0
        }).then((reply)=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navReload();
        });
    }
   

    add_component_item_button.onclick = ()=>{
        window.util.drawerModal.content('Add Component Item',CreateComponentItemForm({
            component_id:'{{$component->id}}',
            component_quantity: parseFloat('{{$component->quantity}}'),
            component_use_count: parseFloat('{{$component->use_count}}'),
            component_unit_text: '{{ $unit_options[$component->unit_id]->text }}',
            unit_options: @json($unit_options),
            append_component_item:(data)=>{
                
                let item = ComponentItemEl({
                    id: data.id,
                    component_id:'{{$component->id}}',
                    component_quantity: parseFloat('{{$component->quantity}}'),
                    component_use_count: parseFloat('{{$component->use_count}}'),
                    component_unit_text: '{{$unit_options[$component->unit_id]->text}}',
                    material_item_options: materialItemOptions,
                    unitOptions: @json($unit_options)
                });

                $el.append(item).to(component_item_list);

                item.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
                
        
            }
        })).open();
    }

    printBtn.onclick = ()=>{
        window.open( '/project/section/contract_item/component/print/{{$component->id}}','_blank').focus();
    }


    deleteBtn.onclick = ()=>{

        let answer = prompt('Are you sure you want to delete this component? \n If so please type "{{$component->name}}"');

        if(answer != "{{$component->name}}"){
            window.util.alert('Error','Invalid answer');
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/component/delete',{
            id: "{{$component->id}}"
        }).then(reply=>{
            
            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navTo('/project/section/contract_item/{{$contract_item->id}}');
        });
    }


    if(reviewLinkBtn){
        reviewLinkBtn.onclick = async ()=>{
            let test = await window.util.copyToClipboard('{{ url("/review/component/".$contract_item->id."/".$component->id); }}');
            if(test){
                window.util.alert('Clipboard','Review Link for "Component: {{$component->id}}" copied!');
            }else{
                window.util.alert('Clipboard','Failed to copy');
            }
        }
    }

    @foreach($componentItems as $item)

        component_item_list.append(
            ComponentItemEl({
                id:'{{$item->id}}',
                component_id:'{{$component->id}}',
                component_quantity: parseFloat('{{$component->quantity}}'),
                component_use_count: parseFloat('{{$component->use_count}}'),
                component_unit_text: '{{ $unit_options[$component->unit_id]->text }}',
                material_item_options: materialItemOptions,
                unitOptions: @json($unit_options),
                component_item_editable: false
            })
        );

    @endforeach
</script>
</div>
@endsection