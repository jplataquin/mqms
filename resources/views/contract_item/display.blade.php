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
                <a href="#" class="active">
                    <span>
                        Display
                    </span>		
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <table class="table">
        <tbody>
            <tr>
                <th>Project</th>
                <td>{{$project->name}}</td>
            </tr>
            <tr>
                <th>Section</th>
                <td>{{$section->name}}</td>
            </tr>
            <tr>
                <th>Contract Item ID</th>
                <td>{{STR_PAD($contract_item->id,6,0,STR_PAD_LEFT)}}</td>
            </tr>
        </tbody>
    </table>


    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Item Code</label>
                <input type="text" id="item_code" class="form-control editable" disabled="true" value="{{$contract_item->item_code}}"/>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Description</label>
                <input type="text" id="description" class="form-control editable" disabled="true" value="{{$contract_item->description}}"/>
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
                <select class="form-control editable" id="unit" disabled="true">
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

    <div class="row mb-3">
        <div class="col-lg-4">
            <div class="form-group">
                <label>POW/DUPA Quantity</label>
                <input type="text" id="ref_1_quantity" class="form-control editable" disabled="true" value="{{$contract_item->ref_1_quantity}}"/>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-group">
                <label>POW/DUPA Unit</label>
                <select class="form-control editable" id="ref_1_unit" disabled="true">
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
    
    

    <div class="row mb-3">
        <div class="col-6 text-start">
            <button class="btn btn-danger" id="deleteBtn">Delete</button>
        </div>
        <div class="col-6 text-end">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-primary" id="editBtn">Edit</button>
            <button class="btn btn-warning d-none" id="updateBtn">Update</button>
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
                    <div class="col-lg-4 col-sm-12">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" id="component_name" />
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-12">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" class="form-control" id="component_quantity" />
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-12">
                        <div class="form-group">
                            <label>Unit</label>
                            <select class="form-control" id="component_unit">
                                @foreach($unit_options as $unit)
                                    <option value="{{$unit->id}}" @if($unit->deleted) disabled @endif>{{$unit->text}} @if($unit->deleted) [Deleted] @endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-1 col-sm-12">
                        <div class="form-group">
                            <label>Use Count</label>
                            <input type="text" class="form-control" value="1" id="component_use_count" />
                        </div>
                    </div>


                    <div class="col-lg-1">
                        <div class="form-group">
                            <label>Sum Flag</label>
                            <div class="form-switch text-center">
                                <input type="checkbox" class="form-check-input" id="component_sum_flag" value="1" checked/>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-lg-2 col-sm-12">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button id="createComponentBtn" class="btn btn-warning w-100">Create</button>
                        </div>
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
    const unit                        = $q('#unit').first();
    
    const component_name              = $q('#component_name').first();
    const component_unit              = $q('#component_unit').first();
    const quantity                    = $q('#component_quantity').first();
    const component_use_count         = $q('#component_use_count').first();
    const component_sum_flag          = $q('#component_sum_flag').first();
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

    quantity.onkeypress = (e) =>{
        return window.util.inputNumber(quantity,e,2,false);
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
            description         : description.value,
            contract_quantity   : contract_quantity.value,
            contract_unit_price : contract_unit_price.value,
            ref_1_quantity      : ref_1_quantity.value,
            ref_1_unit_price    : ref_1_unit_price.value,
            ref_1_unit_id       : ref_1_unit.value,
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

    

    deleteBtn.onclick = (e)=>{

        let answer = prompt("Are you sure you want to delete this contract item? \n Type \"{{$contract_item->item_code}}\" to continue");

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


    createComponentBtn.onclick = ()=>{

            window.util.blockUI();

            window.util.$post('/api/component/create',{
                section_id          : '{{$section->id}}',
                contract_item_id    : '{{$contract_item->id}}',
                name                : component_name.value,
                quantity            : component_quantity.value,
                use_count           : component_use_count.value,
                unit_id             : component_unit.value,
                sum_flag            : (component_sum_flag.checked == true) ? 1 : 0
            }).then(reply=>{

                window.util.unblockUI();

                if(reply.status <= 0){
                    window.util.showMsg(reply);
                    return false;
                }

                $el.append(Component(reply.data.id)).to(component_list);
            });

    }

    $q('.item').apply((el)=>{

        el.onclick = (e)=>{
            window.util.navTo('/project/section/contract_item/component/'+el.getAttribute('data-id'));
        }
    });

</script>
</div>
@endsection