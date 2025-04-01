@extends('layouts.app')

@section('content')

<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/access_codes">
                    <span>
                        Access Code
                    </span>		
                </a>
            </li>
            <li>
                <a href="#">
                    <span>Display</span>
                    <i class="ms-2 bi bi-display"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="form-container">
        <div class="form-header">
            Access Code
        </div>

        <div class="form-body">
            <div class="row">

                <div class="col-12 mb-3">
                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" disabled="true" maxlength="256" value="{{$code}}" id="access_code" class="form-control"/>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="description" disabled="true" class="form-control">{{$description}}</textarea>
                    </div>
                </div>
            </div>

            <div class="row mt-5 mb-3">
                <div class="col-6 text-start">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                </div>
                <div class="col-6 text-end">
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const cancelBtn       = $q('#cancelBtn').first();
    const access_code     = $q('#access_code').first();
    const description     = $q('#description').first();
    const editBtn         = $q('#editBtn').first();
    const updateBtn       = $q('#updateBtn').first();
    const deleteBtn       = $q('#deleteBtn').first();

    let backUrl = '{{request(}->get("b")}}';

    console.log(backUrl);

    deleteBtn.onclick = (e) =>{
        e.preventDefault();

        let answer = prompt('Type the code "{{$code}}" to delete this record');

        if(answer != '{{$code}}'){
            alert('Invalid code');
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/access_code/delete',{
            id: '{{$id}}'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

            window.util.navTo('/access_codes');

        });

    }


    editBtn.onclick = (e) =>{
        access_code.disabled = false;
        description.disabled = false;
        updateBtn.classList.remove('d-none');
        editBtn.classList.add('d-none');
    }

    updateBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/access_code/update',{
            code: access_code.value,
            description: description.value,
            id: '{{$id}}'
        }).then(reply=>{
            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

            
            window.util.navReload();


        });
    }

    cancelBtn.onclick = (e) => {
        document.location.href = '/access_codes';

    }

</script>
</div>
@endsection