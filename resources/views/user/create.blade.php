@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/users">
                    <span>
                       Users
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
            Create User
        </div>
        <div class="form-body">
            <div class="row mb-3">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="name" class="form-control"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" id="email" class="form-control"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="password" class="form-control"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Retype Password</label>
                        <input type="password" id="repassword" class="form-control"/>
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

    const createBtn              = $q('#createBtn').first();
    const cancelBtn              = $q('#cancelBtn').first();
    const name                   = $q('#name').first();
    const email                  = $q('#email').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/user/create',{
            name: name.value,
            email: email.value
        }).then(reply=>{

            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

      
            window.util.navTo('/user/'+reply.data.id);

        });
    }

    cancelBtn.onclick = (e) => {
        window.util.navTo('/users');

    }

</script>
</div>
@endsection