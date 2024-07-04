@extends('layouts.app')

@section('content')
<div class="container">
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
                    <th>Component</th>
                    <td>
                        <input class="form-control editable_field" type="text" id="component" value="{{$component->name}}" disabled="true"/>
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td id="status">
                        {{$component->status}}
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
                <tr>
                    <th>Hash</th>
                    <td>
                        {{$hash}}
                    </td>
                </tr>
                <tr>
                    <th>Quantity / Unit</th>
                    <td>
                        {{$component->quantity}} {{$unit_options[$component->component_unit_id]}}
                    </td>
                </tr>
            </table>    
        </div>
    </div>


    <div class="row mt-5">
        <div class="col-lg-6">
            <button class="btn btn-danger" id="deleteBtn">Delete</button>
        </div>
        <div class="col-lg-6 text-end">
            <button class="btn btn-secondary" id="printBtn">Print</button>
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-warning d-none" id="updateBtn">Update</button>
            <button class="btn btn-primary" id="editBtn">Edit</button>
        </div>
    </div>

    <hr>

    <div class="">
        <h3>Items</h3>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <div class="form-group">
                <label>Name</label>
                <input id="component_item_name" type="text" class="form-control"/>
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
                <label>Quantity</label>
                <input id="component_item_quantity" type="text" class="form-control"/>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-group">
                <label>Unit</label>
                <input id="component_item_unit" type="text" class="form-control"/>
            </div>
        </div>
        <div class="col-lg-2">
            <label>&nbsp;</label>
            <button id="createBtn" class="btn btn-warning w-100">Create</button>
        </div>
    </div>

    <div id="component_item_list" class="row mt-3">
            
  
    </div>
</div>

<script type="module">
    import {Template,$q,$el,State,Signal} from '/adarna.js';
    import ComponentItemEl from '/ui_components/ComponentItem.js';

    let materialItemOptions = @json($materialItems);
    let component           = $q('#component').first();
    let component_item_list = $q('#component_item_list').first();
    let editBtn             = $q('#editBtn').first();
    let cancelBtn           = $q('#cancelBtn').first();
    let updateBtn           = $q('#updateBtn').first();
    let createBtn           = $q('#createBtn').first();
    let deleteBtn           = $q('#deleteBtn').first();
    let printBtn            = $q('#printBtn').first();
    let status              = $q('#status').first();

    let component_item_name         = $q('#component_item_name').first();
    let component_item_budget_price = $q('#component_item_budget_price').first();
    let component_item_unit         = $q('#component_item_unit').first();
    let component_item_quantity     = $q('#component_item_quantity').first();
    
    
    const t = new Template();

    const signalR = new Signal();
    const signalB = new Signal();

    signalR.receiver('set-component-status',(value)=>{
        status.innerHTML = value;
    });

    editBtn.onclick = ()=>{

        $q('.editable_field').apply((el)=>{
            el.disabled = false;
        });

        editBtn.classList.add('d-none');
        updateBtn.classList.remove('d-none');

        cancelBtn.onclick = ()=>{
            document.location.reload(true);
        }
    }

    cancelBtn.onclick = ()=>{
        document.location.href = '/project/section/{{$section->id}}';
    }

    updateBtn.onclick = ()=>{

        window.util.blockUI();

        window.util.$post('/api/component/update',{
            id:'{{$component->id}}',
            section_id:'{{$section->id}}',
            name:component.value
        }).then((reply)=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            document.location.reload(true);
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
            component_id: '{{$component->id}}',
            name: component_item_name.value,
            budget_price: component_item_budget_price.value,
            quantity: component_item_quantity.value,
            unit: component_item_unit.value
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            component_item_budget_price.value   = '';
            component_item_name.value           = '';
            component_item_quantity.value       = '';
            component_item_unit.value           = '';

            let item = ComponentItemEl({
                id: reply.data.id,
                component_id:'{{$component->id}}',
                materialItemOptions: materialItemOptions
            });

            $el.append(item).to(component_item_list);

            item.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
            
            signalB.broadcast('set-component-status','PEND');

            
        });
    }

    printBtn.onclick = ()=>{
        window.open( '/component/print/{{$component->id}}','_blank').focus();
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

            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            window.location.href = '/project/section/{{$section->id}}';
        });
    }

    @foreach($componentItems as $item)

        component_item_list.append(
            ComponentItemEl({
                id:'{{$item->id}}',
                component_id:'{{$component->id}}',
                materialItemOptions: materialItemOptions
            })
        );

    @endforeach
</script>

@endsection