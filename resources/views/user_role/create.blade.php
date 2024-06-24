@extends('layouts.app')

@section('content')
<div class="container">
<h5>Access Code » Create</h5>
<hr>

    <div class="row">

        <div class="col-12">
            <div class="form-group">
                <label>Code</label>
                <input type="text" maxlength="6" id="access_code" class="form-control"/>
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
    let access_code     = $q('#access_code').first();
    let description     = $q('#description').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/access_code/create',{
            code: access_code.value,
            description: description.value
        }).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            };

            window.util.unblockUI();

            document.location.href = '/access_code/'+reply.data.id;


        });
    }

    cancelBtn.onclick = (e) => {
        document.location.href = '/access_codes';

    }

</script>

@endsection