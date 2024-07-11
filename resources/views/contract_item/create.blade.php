@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<h5>Contract Item » Create</h5>
<hr>

    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Project</label>
                <input type="text" id="project" value="{{$project->name}}" class="form-control"/>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Section</label>
                <input type="text" id="section" value="{{$section->name}}" class="form-control"/>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Code Item</label>
                <input type="text" id="code_item" class="form-control"/>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Description</label>
                <input type="text" id="description" class="form-control"/>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label>Contract Quantity</label>
                <input type="text" id="contract_quantity" class="form-control"/>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label>Contract Unit Price</label>
                <input type="text" id="contract_unit_price" class="form-control"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label>POW/DUPA Quantity</label>
                <input type="text" id="ref_1_quantity" class="form-control"/>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label>POW/DUPA Unit Price</label>
                <input type="text" id="ref_1_unit_price" class="form-control"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Unit</label>
                <select class="form-control" id="unit">
                    @foreach($unit_options as $unit)
                    <option value="{{$unit->id}}" @if($unit->deleted) disabled @endif>{{$unit->text}} @if($unit->deleted) [Deleted] @endif</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row mt-5">
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
    
    const item_code                 = $q('#item_code').first();
    const description               = $q('#description').first();
    const contract_quantity         = $q('#contract_quantity').first();
    const contract_unit_price       = $q('#contract_unit_price').first();
    const ref_1_quantity            = $q('#ref_1_quantity').first();
    const ref_1_unit_price          = $q('#ref_1_unit_price').first();
    const unit                      = $q('#unit').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/contract_item/create',{
            item_code           : item_code.value,
            description         : description.value,
            contract_quantity   : contract_quantity.value,
            contract_unit_price : contract_unit_price.value,
            ref_1_quantity      : ref_1_quantity.value,
            ref_1_unit_price    : ref_1_unit_price.value,
            unit_id             : unit.value
        }).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                window.util.showMsg(reply.message);
                return false;
            };

            window.util.unblockUI();
      
            document.location.href = '/contract_item/'+reply.data.id;

        
        });
    }

    cancelBtn.onclick = (e) => {
        document.location.href = '/contract_items';

    }

</script>
</div>
@endsection