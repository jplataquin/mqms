@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<h5>Master Data » Create Material Item</h5>
<hr>

    <div class="row">

        <div class="col-lg-3">
            <div class="form-group">
                <label>Material Group</label>
                <select class="form-control" id="materialGroup">
                    @foreach($materialGroups as $group)
                        <option value="{{$group->id}}" >{{$group->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>


        <div class="col-lg-3">
            <div class="form-group">
                <label>Material Name</label>
                <input type="text" id="materialName" class="form-control"/>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="form-group">
                <label>Specification / Unit Packaging</label>
                <input type="text" id="specificationUnitPackaging" class="form-control"/>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="form-group">
                <label>Brand Name</label>
                <input type="text" id="brandName" class="form-control"/>
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

    let createBtn                   = $q('#createBtn').first();
    let cancelBtn                   = $q('#cancelBtn').first();
    let materialName                = $q('#materialName').first();
    let materialGroup               = $q('#materialGroup').first();
    let specificationUnitPackaging  = $q('#specificationUnitPackaging').first();
    let brandName                   = $q('#brandName').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/material/item/create',{
            name: materialName.value,
            specification_unit_packaging: specificationUnitPackaging.value,
            material_group_id: materialGroup.value,
            brand: brandName.value
        }).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            };

            window.util.unblockUI();
      
            document.location.href = '/master_data/material/item/'+reply.data.id;

        
        });
    }

    cancelBtn.onclick = (e) => {
        document.location.href = '/master_data/materials';
    }

</script>
</div>
@endsection