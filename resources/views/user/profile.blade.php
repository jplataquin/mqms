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
                            My Profile
                        </span>	
                        <i class="ms-2 bi bi-display"></i>	
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="form-container mb-3">
            <div class="form-header">
                Details
            </div>
            <div class="form-body">
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" id="name" value="{{$user->name}}" disabled="true"/>
                        </div>
                    </div>
                </div> 
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="text" class="form-control" id="email" value="{{$user->email}}" disabled="true"/>
                        </div>
                    </div>
                </div> 
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Status</label>
                            <input type="text" class="form-control" id="status" value="{{$user->status}}" disabled="true"/>
                        </div>
                    </div>
                </div> 
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Created At</label>
                            <input type="text" class="form-control" id="created_at" value="{{$user->created_at}}" disabled="true"/>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg-12 text-end">
                        <button id="change_password_btn" class="btn btn-warning">Change Password</button>
                    </div>
                </div> 
            </div>
        </div>

    </div>
    <script type="module">
        import {$q} from '/adarna.js';
        import ChangePasswordForm from '/ui_components/ChangePasswordForm.js';

        const change_password_btn = $q('#change_password_btn').first();

        change_password_btn.onclick = ()=>{
            window.util.drawerModal.content('Change Password',ChangePasswordForm()).open();
        }

    </script>
</div>
@endsection