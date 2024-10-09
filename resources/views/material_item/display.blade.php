@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/master_data/material_items">
                    <span>
                       Material Items
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Display
                    </span>	
                    <i class="ms-2 bi bi-display"></i>	
                </a>
            </li>
        </ul>
    </div>

<hr>
    <div class="form-container">
        <div class="form-header">
            Material Item
        </div>
        <div class="form-body">
            <div class="row">
                <div class="col-lg-12 mt-3">
                    <div class="form-group">
                        <label>Material Group</label>
                        <select disabled="true" class="form-control" id="materialGroup">
                            @foreach($materialGroups as $group)
                                <option value="{{$group->id}}" @if($group->id == $materialItem->material_group_id) selected @endif >{{$group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-group">
                        <label>Material Name</label>
                        <input type="text" disabled="true" id="materialName" value="{{$materialItem->name}}" class="form-control"/>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-group">
                        <label>Specification / Unit Packaging</label>
                        <input type="text" disabled="true" id="specificationUnitPackaging" value="{{$materialItem->specification_unit_packaging}}" class="form-control"/>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-group">
                        <label>Brand</label>
                        <input type="text" disabled="true" id="brandName" value="{{$materialItem->brand}}" class="form-control"/>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12 text-end">
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                </div>
            </div>
            
        </div>
    </div>


</div>

<script type="module">
    import {$q} from '/adarna.js';

    let materialGroup               = $q('#materialGroup').first();
    let materialName                = $q('#materialName').first();
    let specificationUnitPackaging  = $q('#specificationUnitPackaging').first();
    let brandName                   = $q('#brandName').first();
    let editBtn                     = $q('#editBtn').first();
    let updateBtn                   = $q('#updateBtn').first();
    let cancelBtn                   = $q('#cancelBtn').first();


    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        materialGroup.disabled                  = false;
        materialName.disabled                   = false;
        specificationUnitPackaging.disabled     = false;
        brandName.disabled                      = false;

        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            window.util.navReload();
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/material/item/update',{
            name                            : materialName.value,
            brand                           : brandName.value,
            material_group_id               : materialGroup.value,
            specification_unit_packaging    : specificationUnitPackaging.value,
            id: '{{$materialItem->id}}'
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

         window.util.navTo('/master_data/material/items');
      
    }
</script>
</div>
@endsection