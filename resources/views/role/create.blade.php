@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs" hx-boost="true">
        <ul>
            <li>
                <a href="/roles">
                    <span>
                       Roles
                    </span>                    
                    
                </a>
            </li>
            <li>
                <a href="#" class="active" >
                    <span>
                        Create
                    </span>
                    <i class="ms-2 bi-plus-circle"></i>
                </a>
            </li>
        </ul>
    </div>
<hr>

    <div class="row">

        <div class="col-12">
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="role" class="form-control"/>
            </div>
        </div>

        <div class="col-12">
            <div class="form-group">
                <label>Description</label>
                <textarea id="description" class="form-control"></textarea>
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

    let createBtn       = $q('#createBtn').first();
    let cancelBtn       = $q('#cancelBtn').first();
    let role            = $q('#role').first();
    let description     = $q('#description').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/role/create',{
            name: role.value,
            description: description.value
        }).then(reply=>{

            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

            window.util.navTo('/role/'+reply.data.id);


        });
    }

    cancelBtn.onclick = (e) => {
        window.util.navTo('/roles');
    }

</script>
</div>
@endsection