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
                            Change Password
                        </span>	
                        <i class="ms-2 bi bi-display"></i>	
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="form-container">
            <div class="form-header">
                Change Password
            </div>
            <div class="form-body">

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" disabled="true" value="{{$user->name}}" class="form-control" id="name"/>
                            </div>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" value="{{$user->email}}" disabled="true" class="form-control" id="email"/>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" class="form-control" id="password"/>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Retype Password</label>
                                <input type="password" class="form-control" id="repassword"/>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-end">
                            
                            <button class="btn btn-primary" id="submitBtn">Submit</button>
                            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        </div>
                    </div>
            </div>
        </div>
        <script type="module">
            import {$q} from '/adarna.js';

            const submitBtn     = $q('#submitBtn').first();
            const cancelBtn     = $q('#cancelBtn').first();
            const password      = $q('#password').first();
            const repassword    = $q('#repassword').first();

            submitBtn.onclick = async (e)=>{

                let answer = await window.util.confirm("Are you sure you want to change the user's password?");

                if(!answer){
                    return false;
                }

                window.util.blockUI();

                window.util.$post('/api/user/change_password',{
                    user_id: "{{$user->id}}",
                    password: password.value,
                    repassword: repassword.value
                }).then((reply)=>{

                    window.util.unblockUI();

                    if(reply.status <= 0){
                        window.util.showMsg(reply);
                        return false;
                    }

                    window.util.navTo('/user/{{$user->id}}');
                });

            }
        </script>
    </div>
</div>
@endsection