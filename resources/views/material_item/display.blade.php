@extends('layouts.app')

@section('content')
<div class="container">
<h6>Master Data » Material Item » Display » {{$materialItem->id}}</h6>
<hr>

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
            document.location.reload(true);
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

            if(reply.status <= 0){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            }

            document.location.reload(true);
        });
    }


    cancelBtn.onclick = (e)=>{
        document.location.href = '/master_data/material/items';
    }
</script>

@endsection