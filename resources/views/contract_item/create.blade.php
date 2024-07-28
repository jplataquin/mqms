@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<h5>Project » Section » Contract Item » Create</h5>
<hr>
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Project</label>
                <input type="text" id="project" value="{{$project->name}}" class="form-control" disabled="true"/>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Section</label>
                <input type="text" id="section" value="{{$section->name}}" class="form-control" disabled="true"/>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Parent Contract Item</label>
                <select class="form-control" id="parent_contract_item">
                    <option value=""> - </option>
                    @foreach($contract_items as $contract_item)
                    <option value="{{$contract_item->id}}">{{$contract_item->item_code}} {{$contract_item->description}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Item Code</label>
                <input type="text" id="item_code" class="form-control"/>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Description</label>
                <input type="text" id="description" class="form-control"/>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-4">
            <div class="form-group">
                <label>Contract Quantity</label>
                <input type="text" id="contract_quantity" class="form-control"/>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-group">
                <label>Contract Unit</label>
                <select class="form-control" id="unit">
                    @foreach($unit_options as $unit)
                    <option value="{{$unit->id}}" 
                        @if($unit->deleted) disabled @endif
                    >{{$unit->text}} @if($unit->deleted) [Deleted] @endif</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-group">
                <label>Contract Unit Price (PHP)</label>
                <input type="text" id="contract_unit_price" class="form-control"/>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-4">
            <div class="form-group">
                <label>POW/DUPA Quantity</label>
                <input type="text" id="ref_1_quantity" class="form-control"/>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-group">
                <label>POW/DUPA Unit</label>
                <select class="form-control" id="ref_1_unit">
                    <option value=""> - </option>
                    @foreach($unit_options as $unit)
                    <option value="{{$unit->id}}" 
                        @if($unit->deleted) disabled @endif
                    >{{$unit->text}} @if($unit->deleted) [Deleted] @endif</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-group">
                <label>POW/DUPA Unit Price (PHP)</label>
                <input type="text" id="ref_1_unit_price" class="form-control"/>
            </div>
        </div>
        
    </div>
   
    
    <div class="row">
        <div class="col-12 text-end">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-primary" id="createBtn">Create</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const createBtn                 = $q('#createBtn').first();
    const cancelBtn                 = $q('#cancelBtn').first();
    
    const parent_contract_item      = $q('#parent_contract_item').first();
    const item_code                 = $q('#item_code').first();
    const description               = $q('#description').first();
    const contract_quantity         = $q('#contract_quantity').first();
    const contract_unit_price       = $q('#contract_unit_price').first();
    const ref_1_quantity            = $q('#ref_1_quantity').first();
    const ref_1_unit_price          = $q('#ref_1_unit_price').first();
    const ref_1_unit                = $q('#ref_1_unit').first();
    const unit                      = $q('#unit').first();
    
    unit.onchange = ()=>{
        ref_1_unit.value = unit.options[unit.selectedIndex].text;
    }

    ref_1_unit.value = unit.options[unit.selectedIndex].text;


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
    

    

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/contract_item/create',{
            section_id                  : '{{$section->id}}',
            parent_contract_item_id     : parent_contract_item.value, 
            item_code                   : item_code.value,
            description                 : description.value,
            contract_quantity           : contract_quantity.value,
            contract_unit_price         : contract_unit_price.value,
            ref_1_quantity              : ref_1_quantity.value,
            ref_1_unit_price            : ref_1_unit_price.value,
            ref_1_unit                  : ref_1_unit.value,
            unit_id                     : unit.value
        }).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                window.util.showMsg(reply.message);
                return false;
            };

            window.util.unblockUI();
      
            window.util.navTo('/project/section/contract_item/'+reply.data.id);

        
        });
    }

    cancelBtn.onclick = (e) => {
        window.util.navTo('/project/section/{{$section->id}}');

    }

</script>
</div>
@endsection