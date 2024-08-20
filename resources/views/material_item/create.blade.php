@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true">
        <ul>
            <li>
                <a href="/master_data/material/items" hx-select="#content" hx-target="#main">
                    <span>
                        Material Items
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Create
                    </span>	
                    <i class="ms-2 bi bi-file-earmark-plus"></i>	
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="form-container">
        <div class="form-header">
            Create Material Item
        </div>
        <div class="form-body">
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
            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };
      
             window.util.navTo('/master_data/material/item/'+reply.data.id);

        
        });
    }

    cancelBtn.onclick = (e) => {
         window.util.navTo('/master_data/materials');
    }

</script>
</div>
@endsection