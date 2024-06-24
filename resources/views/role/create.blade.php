@extends('layouts.app')

@section('content')
<div class="container">
<h5>Role » Create</h5>
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

            if(reply.status <= 0 ){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            };

            window.util.unblockUI();

            document.location.href = '/role/'+reply.data.id;


        });
    }

    cancelBtn.onclick = (e) => {
        document.location.href = '/roles';

    }

</script>

@endsection