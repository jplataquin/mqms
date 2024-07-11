@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">

<h5>Contract Item » Create</h5>
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
        <div class="col-lg-6">
            <div class="form-group">
                <label>Contract Quantity</label>
                <input type="text" id="contract_quantity" class="form-control editable" disabled="true" value="{{$contract_item->contract_quantity}}"/>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label>Contract Unit Price (PHP)</label>
                <input type="text" id="contract_unit_price" class="form-control editable" disabled="true" value="{{$contract_item->contract_unit_price}}"/>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-6">
            <div class="form-group">
                <label>POW/DUPA Quantity</label>
                <input type="text" id="ref_1_quantity" class="form-control editable" disabled="true" value="{{$contract_item->ref_1_quantity}}"/>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label>POW/DUPA Unit Price (PHP)</label>
                <input type="text" id="ref_1_unit_price" class="form-control editable" disabled="true" value="{{$contract_item->ref_1_unit_price}}"/>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Unit</label>
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

   
    
<script type="module">
    import {$q,$el,Template} from '/adarna.js';

    const editBtn                     = $q('#editBtn').first();
    const updateBtn                   = $q('#updateBtn').first();
    const cancelBtn                   = $q('#cancelBtn').first();
    const deleteBtn                   = $q('#deleteBtn').first();
    
    const item_code                 = $q('#item_code').first();
    const description               = $q('#description').first();
    const contract_quantity         = $q('#contract_quantity').first();
    const contract_unit_price       = $q('#contract_unit_price').first();
    const ref_1_quantity            = $q('#ref_1_quantity').first();
    const ref_1_unit_price          = $q('#ref_1_unit_price').first();
    const unit                      = $q('#unit').first();

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

    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        $q('.editable').apply((elem)=>{
            elem.disabled = false;
        });

        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            document.location.reload(true);
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
            unit_id             : unit.value,
        }).then(reply=>{

            if(reply.status <= 0){
                window.util.unblockUI();
                window.util.showMsg(reply.message);
                return false;
            }

            document.location.reload(true);
        });
    }


    cancelBtn.onclick = (e)=>{
        document.location.href = '/contract_item/{{$contract_item->id}}';
    }

    

    deleteBtn.onclick = (e)=>{

        let answer = confirm('Are you sure you want to delete this Contract Item?');

        if(!answer){
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/contract_item/delete',{
            id: "{{$contract_item->id}}"
        }).then(reply=>{

            window.util.unblockUI();
            
            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            window.location.href = '/contract_items';
        });
    }
    


</script>
</div>
@endsection