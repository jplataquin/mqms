@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">

    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/project/section/{{$section->id}}">
                    <span>
                    Section
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                    Contract Item
                    </span>
                    
                    <i class="ms-2 bi bi-display"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <table class="record-table-horizontal mb-3">
        <tbody>
            <tr>
                <th width="150px">Project</th>
                <td>{{$project->name}}</td>
            </tr>
            <tr>
                <th>Section</th>
                <td>{{$section->name}}</td>
            </tr>
        </tbody>
    </table>
      
    <div class="form-container">
        <div class="form-header text-center mb-3">
            Contract Item
        </div>
        <div class="form-body">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Item Code</label>
                        <input type="text" id="item_code" class="form-control editable" disabled="true" value="{{$contract_item->item_code}}"/>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label>ID</label>
                    <input type="text" class="form-control" disabled="true" value="{{STR_PAD($contract_item->id,6,0,STR_PAD_LEFT)}}"/>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" id="description" class="form-control editable" disabled="true" value="{{$contract_item->description}}"/>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Item Type</label>
                        <select class="form-select editable" id="item_type" disabled="true">
                            <option value="MATR" @if($contract_item->item_type == "MATR") selected @endif>Material</option>
                            <option value="NMAT" @if($contract_item->item_type == "NMAT") selected @endif>Non-Material</option>
                            <option value="OPEX" @if($contract_item->item_type == "OPEX") selected @endif>Operational Expense</option>
                        </select>
                    </div>
                </div>
            </div>
            
            
            
            <div class="row mb-3">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Contract Quantity</label>
                        <input type="text" id="contract_quantity" class="form-control editable" disabled="true" value="{{$contract_item->contract_quantity}}"/>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Contract Unit</label>
                        <select class="form-select editable" id="unit" disabled="true">
                            @foreach($unit_options as $unit)
                            <option value="{{$unit->id}}" 
                                @if($unit->deleted) disabled @endif
                            
                                @if($unit->id == $contract_item->unit_id) selected @endif
                            
                            >{{$unit->text}} @if($unit->deleted) [Deleted] @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Contract Unit Price (PHP)</label>
                        <input type="text" id="contract_unit_price" class="form-control editable" disabled="true" value="{{$contract_item->contract_unit_price}}"/>
                    </div>
                </div>
            </div>

            <div class="row mb-3 ">


                <div class="col-lg-4">
                    <div class="form-group">
                        <label>POW/DUPA Quantity</label>
                        <input type="text" id="ref_1_quantity" class="form-control editable" disabled="true" value="{{$contract_item->ref_1_quantity}}"/>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>POW/DUPA Unit</label>
                        <select class="form-select editable" id="ref_1_unit" disabled="true">
                            <option value=""> - </option>
                            @foreach($unit_options as $unit)
                            
                            <option value="{{$unit->id}}" 
                                @if($unit->deleted) disabled @endif
                            
                                @if($unit->id == $contract_item->ref_1_unit_id) selected @endif
                            
                            >{{$unit->text}} @if($unit->deleted) [Deleted] @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>POW/DUPA Unit Price (PHP)</label>
                        <input type="text" id="ref_1_unit_price" class="form-control editable" disabled="true" value="{{$contract_item->ref_1_unit_price}}"/>
                    </div>
                </div>
                
            </div>
            
            <div class="row mb-3 ">

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Budget Quantity</label>
                        <input type="text" id="budget_quantity" placeholder="Auto" class="form-control editable" disabled="true" value="{{$contract_item->budget_quantity}}"/>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Budget Unit</label>
                        <select class="form-select editable" id="budget_unit" disabled="true">
                            <option value=""> Auto </option>
                            @foreach($unit_options as $unit)
                            
                            <option value="{{$unit->id}}" 
                                @if($unit->deleted) disabled @endif
                            
                                @if($unit->id == $contract_item->budget_unit_id) selected @endif
                            
                            >{{$unit->text}} @if($unit->deleted) [Deleted] @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Budget Unit Price (PHP)</label>
                        <input type="text" placeholder="Auto" id="budget_unit_price" class="form-control editable" disabled="true" value="{{$contract_item->budget_unit_price}}"/>
                    </div>
                </div>

                </div>

            

            <div class="row mb-3">
                <div class="col-lg-12 text-end">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                    <button class="btn btn-warning" id="printBtn">Print</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                  
                </div>
            </div>
        </div>
    </div>
    <hr>

    <div>
        <div class="folder-form-container">
            <div class="folder-form-tab">
                Components
            </div>
            <div class="folder-form-body">
                <div class="row mb-3">
                    <div class="col-lg-12 text-end">
                        <button id="createComponentBtn" class="btn btn-warning">Create</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="component_list" class="mt-3">
            @foreach($components as $component)

                <div class="item item-container fade-in" data-id="{{$component->id}}">
                    <div class="item-header">{{$component->name}}</div>
                    <div class="item-body">
                            @if(isset($unit_options[ $component->unit_id ]))
                                {{$component->quantity}} {{ $unit_options[ $component->unit_id ]->text }}
                            @endif
                    </div>
                </div>

            @endforeach
        </div>

    </div>
   
</div>  
<script type="module">
    import {$q,$el,Template} from '/adarna.js';
    import CreateComponentForm from '/ui_components/create_forms/CreateComponentForm.js';

    const printBtn                    = $q('#printBtn').first();
    const editBtn                     = $q('#editBtn').first();
    const updateBtn                   = $q('#updateBtn').first();
    const cancelBtn                   = $q('#cancelBtn').first();
    const deleteBtn                   = $q('#deleteBtn').first();
    
    const item_code                   = $q('#item_code').first();
    const description                 = $q('#description').first();
    const contract_quantity           = $q('#contract_quantity').first();
    const contract_unit_price         = $q('#contract_unit_price').first();
    
    const ref_1_quantity              = $q('#ref_1_quantity').first();
    const ref_1_unit_price            = $q('#ref_1_unit_price').first();
    const ref_1_unit                  = $q('#ref_1_unit').first();

    const budget_quantity              = $q('#budget_quantity').first();
    const budget_unit_price            = $q('#budget_unit_price').first();
    const budget_unit                  = $q('#budget_unit').first();
    
    const unit                        = $q('#unit').first();
    const item_type                   = $q('#item_type').first();
    
    const createComponentBtn          = $q('#createComponentBtn').first();
    
    const component_list              = $q('#component_list').first();
    
    const t             = new Template();
    const unit_options  = @json($unit_options);



    contract_quantity.onkeypress = (e)=>{
        return window.util.inputNumber(contract_quantity,e,2,false);
    }

    contract_unit_price.onkeypress = (e)=>{
        return window.util.inputNumber(contract_unit_price,e,2,false);
    }

    ref_1_quantity.onkeypress = (e)=>{
        return window.util.inputNumber(ref_1_quantity,e,2,false);
    }

    ref_1_unit_price.onkeypress = (e)=>{
        return window.util.inputNumber(ref_1_unit_price,e,2,false);
    }


    printBtn.onclick = ()=>{
        window.open( '/project/section/contract_item/print/{{$contract_item->id}}','_blank').focus();
    }

    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        $q('.editable').apply((elem)=>{
            elem.disabled = false;
        });

        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            window.util.navReload();
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/contract_item/update',{
            id                  : '{{$contract_item->id}}',
            section_id          : '{{$section->id}}',
            item_code           : item_code.value,
            item_type           : item_type.value,
            description         : description.value,
            contract_quantity   : contract_quantity.value,
            contract_unit_price : contract_unit_price.value,
            ref_1_quantity      : ref_1_quantity.value,
            ref_1_unit_price    : ref_1_unit_price.value,
            ref_1_unit_id       : ref_1_unit.value,
            budget_quantity     : budget_quantity.value,
            budget_unit_price   : budget_unit_price.value,
            budget_unit_id      : budget_unit.value,
            unit_id             : unit.value,
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
        window.util.navTo('/project/section/{{$section->id}}');
    }

    

    deleteBtn.onclick = async (e)=>{

        let answer = await window.util.prompt("Are you sure you want to delete this contract item? \n Type \"{{$contract_item->item_code}}\" to continue");

        if(answer != '{{$contract_item->item_code}}'){
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/contract_item/delete',{
            id: "{{$contract_item->id}}"
        }).then(reply=>{

            window.util.unblockUI();
            
            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navTo('/project/section/{{$section->id}}');
        });
    }

    function Component(id){


        let name = t.txt('Loading...');
        let quantity_unit = t.txt('Loading...');

        let el = t.div({class:'item-container fade-in',dataId:id},()=>{
            t.div({class:'item-header'},(el)=>{
                el.append(name);
            });

            t.div({class:'item-body'},(el)=>{
                el.append(quantity_unit);
            });
        });


        window.util.$get('/api/component',{
            id:id
        }).then(reply=>{

            if(reply.status <= 0){

                window.util.showMsg(reply);
                return false;
            }

            name.nodeValue = reply.data.name;
            quantity_unit.nodeValue = reply.data.quantity+' '+unit_options[reply.data.unit_id].text;

            el.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
        });


        el.onclick = ()=>{
            window.util.navTo('/project/section/contract_item/component/'+id);
        }

        return el;
    }


    $q('.item').apply((el)=>{

        el.onclick = (e)=>{
            window.util.navTo('/project/section/contract_item/component/'+el.getAttribute('data-id'));
        }
    });


    createComponentBtn.onclick = ()=>{

        window.util.drawerModal.content('Create Component',CreateComponentForm({
            section_id: '{{$section->id}}',
            contract_item_id:'{{$contract_item->id}}',
            unit_options: unit_options,
            callback: (id)=>{
                Component(id).to(component_list);
            }
        })).open();
    }

</script>
</div>
@endsection