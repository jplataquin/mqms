@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs">
        <ul>
            <li>
                <a href="#">
                    <span>
                        Project
                    </span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span>
                       Section
                    </span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span>
                        Contract Item
                    </span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span>
                        Component
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Display
                    </span>		
                </a>
            </li>
        </ul>
    </div>
<hr>

    <div class="row">
        <div class="col-lg-12">
            <table class="w-100 table">
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
                    <td>{{$contract_item->item_code}} - {{$contract_item->description}}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td id="status">
                        {{$component->status}}
                    </td>
                </tr>
                
                <tr>
                    <th>Hash</th>
                    <td>
                        {{$hash}}
                    </td>
                </tr>

                <tr>
                    <th>Created By</th>
                    <td>
                        {{$component->CreatedByUser()->name}} {{$component->created_at}}
                    </td>
                </tr>
                <tr>
                    <th>Updated By</th>
                    <td>
                    {{$component->UpdatedByUser()->name}} {{$component->updated_at}}
                    </td>
                </tr>
                <tr>
                    <th>Approved By</th>
                    <td>
                    {{$component->ApprovedByUser()->name}} {{$component->approved_at}}
                    </td>
                </tr>
                <tr>
                    <th>Rejected By</th>
                    <td>
                    {{$component->rejectedByUser()->name}} {{$component->approved_at}}
                    </td>
                </tr>
                
            </table>    
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Component</label>
                <input class="form-control editable_field" type="text" id="component" value="{{$component->name}}" disabled="true"/>
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


    <div class="row">
        <div class="col-lg-6">
            <button class="btn btn-danger" id="deleteBtn">Delete</button>
        </div>
        <div class="col-lg-6 text-end">
            <button class="btn btn-warning" id="printBtn">Print</button>
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-warning d-none" id="updateBtn">Update</button>
            <button class="btn btn-primary" id="editBtn">Edit</button>
        </div>
    </div>

    <hr>

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Items
        </div>
        <div class="folder-form-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Name</label>
                        <input id="component_item_name" type="text" class="form-control"/>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Function Type</label>
                        <select id="component_item_function_type" class="form-control">
                            <option value="1">As Factor</option>
                            <option value="2">As Divisor</option>
                            <option value="3">As Direct</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Variable</label>
                        <input id="component_item_variable" type="text" class="form-control"/>
                    </div>
                </div>
                
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input id="component_item_quantity" type="text" class="form-control" disabled="true"/>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Unit</label>
                        <select id="component_item_unit" class="form-control">
                            @foreach($unit_options as $opt)
                                @if(!$opt->deleted)
                                    <option value="{{$opt->id}}">{{$opt->text}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Budget Price / Unit</label>
                        <input id="component_item_budget_price" type="text" class="form-control"/>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Sum Flag</label>
                        <div class="form-switch text-center">
                            <input type="checkbox" class="form-check-input" id="component_item_sum_flag" value="1" checked/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
            <div class="col-lg-4">
                <div class="form-group">
                    <label>POW/DUPA Quantity</label>
                    <input type="text" id="component_item_ref_1_quantity" class="form-control"/>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <label>POW/DUPA Unit</label>
                    <select id="component_item_ref_1_unit" class="form-control">
                        <option value=""> - </option>
                        @foreach($unit_options as $opt)
                            @if(!$opt->deleted)
                            <option value="{{$opt->id}}">{{$opt->text}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <label>POW/DUPA Price</label>
                    <input type="text" id="component_item_ref_1_unit_price" class="form-control"/>
                </div>
            </div>
            </div>
            <div class="row mt-3">
                <div class="col-lg-12 text-end">
                    <button id="createBtn" class="btn btn-warning">Create</button>
                </div>
            </div>
        </div>
    </div>
    <div id="component_item_list" class="row mt-3"></div>
</div>

<script type="module">
    import {Template,$q,$el,State,Signal} from '/adarna.js';
    import ComponentItemEl from '/ui_components/ComponentItem.js';

    const materialItemOptions   = @json($materialItems);
    const component             = $q('#component').first();
    const component_item_list   = $q('#component_item_list').first();
    const editBtn               = $q('#editBtn').first();
    const cancelBtn             = $q('#cancelBtn').first();
    const updateBtn             = $q('#updateBtn').first();
    const createBtn             = $q('#createBtn').first();
    const deleteBtn             = $q('#deleteBtn').first();
    const printBtn              = $q('#printBtn').first();
    const status                = $q('#status').first();
    const component_quantity    = $q('#component_quantity').first();
    const use_count             = $q('#use_count').first();
    const component_sum_flag    = $q('#component_sum_flag').first();
    const component_unit        = $q('#component_unit').first();

    const component_item_name          = $q('#component_item_name').first();
    const component_item_budget_price  = $q('#component_item_budget_price').first();
    const component_item_unit          = $q('#component_item_unit').first();
    const component_item_quantity      = $q('#component_item_quantity').first();
    const component_item_function_type = $q('#component_item_function_type').first();
    const component_item_variable      = $q('#component_item_variable').first();
    const component_item_sum_flag      = $q('#component_item_sum_flag').first();

    const component_item_ref_1_quantity     = $q('#component_item_ref_1_quantity').first();
    const component_item_ref_1_unit         = $q('#component_item_ref_1_unit').first();
    const component_item_ref_1_unit_price   = $q('#component_item_ref_1_unit_price').first(); 
    
    const t = new Template();

    const signalR = new Signal();
    const signalB = new Signal();

    signalR.receiver('set-component-status',(value)=>{
        status.innerHTML = value;
    });


    component_item_variable.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_variable,e,6,false);
    }

    component_item_function_type.onchange = (e) =>{
       component_item_variable.onkeyup();    
    }

    component_item_variable.onkeyup = (e)=>{
        
        switch(component_item_function_type.value){
            case '1':

                    component_item_quantity.value = Math.ceil( 
                        (parseFloat('{{$component->quantity}}') * component_item_variable.value)  / parseInt('{{$component->use_count}}')
                    );

                break;

            case '2':

                    component_item_quantity.value = Math.ceil( 
                        (parseFloat('{{$component->quantity}}') / component_item_variable.value)  / parseInt('{{$component->use_count}}')
                    );

                break;

            case '3':

                    component_item_quantity.value = component_item_variable.value;
                    
                break;
        }
        
    }

    component_quantity.onkeypress = (e)=>{
        return window.util.inputNumber(component_quantity,e,6,false);
    }

    component_item_ref_1_quantity.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_ref_1_quantity,e,6,false);
    }

    component_item_ref_1_unit_price.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_ref_1_unit_price,e,2,false);
    }

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
        window.util.navTo('/project/section/contract_item/{{$contract_item->id}}');
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
    
    component_item_budget_price.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_budget_price,e,2,false);
    }

    component_item_quantity.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_quantity,e,2,false);
    }

    createBtn.onclick = ()=>{
        
        window.util.blockUI();

        window.util.$post('/api/component_item/create',{
            component_id                    : '{{$component->id}}',
            name                            : component_item_name.value,
            budget_price                    : component_item_budget_price.value,
            quantity                        : component_item_quantity.value,
            unit_id                         : component_item_unit.value,
            function_type_id                : component_item_function_type.value,
            function_variable               : component_item_variable.value,
            sum_flag                        : component_item_sum_flag.value,
            ref_1_quantity                  : component_item_ref_1_quantity.value,
            ref_1_unit_id                   : component_item_ref_1_unit.value,
            ref_1_unit_price                : component_item_ref_1_unit_price.value
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            //Clear input
            component_item_budget_price.value   = '';
            component_item_name.value           = '';
            component_item_quantity.value       = '';
            component_item_unit.value           = '';
            component_item_function_type.value  = '';
            component_item_variable.value       = '';
            component_sum_flag.checked          = true;

            let item = ComponentItemEl({
                id: reply.data.id,
                component_id:'{{$component->id}}',
                component_quantity: parseFloat('{{$component->quantity}}'),
                component_use_count: parseFloat('{{$component->use_count}}'),
                materialItemOptions: materialItemOptions,
                unitOptions: @json($unit_options)
            });

            $el.append(item).to(component_item_list);

            item.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
            
            signalB.broadcast('set-component-status','PEND');

            
        });
    }

    printBtn.onclick = ()=>{
        window.open( '/project/section/contract_item/component/print/{{$component->id}}','_blank').focus();
    }


    deleteBtn.onclick = ()=>{

        let answer = prompt('Are you sure you want to delete this component? \n If so please type "{{$component->name}}"');

        if(answer != "{{$component->name}}"){
            window.util.showMsg('Invalid answer');
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

    @foreach($componentItems as $item)

        component_item_list.append(
            ComponentItemEl({
                id:'{{$item->id}}',
                component_id:'{{$component->id}}',
                component_quantity: parseFloat('{{$component->quantity}}'),
                component_use_count: parseFloat('{{$component->use_count}}'),
                materialItemOptions: materialItemOptions,
                unitOptions: @json($unit_options)
            })
        );

    @endforeach
</script>
</div>
@endsection