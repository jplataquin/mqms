@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
    <div class="breadcrumbs">
        <ul>
            <li>
                <a href="#">
                    <span>
                        Master Data
                    </span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span>
                       Material Group
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

    <div class="row">

        <div class="col-12">
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="materialGroupName" disabled="true" value="{{$name}}" class="form-control"/>
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

    let materialGroupName   = $q('#materialGroupName').first();
    let editBtn             = $q('#editBtn').first();
    let updateBtn           = $q('#updateBtn').first();
    let cancelBtn           = $q('#cancelBtn').first();


    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        materialGroupName.disabled = false;

        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            window.util.navReload();
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/material/group/update',{
            name: materialGroupName.value,
            id: '{{$id}}'
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
         window.util.navTo('/master_data/material/groups');
    }

</script>

</div>
@endsection