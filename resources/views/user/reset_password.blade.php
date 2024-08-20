@extends('layouts.reset_password')

@section('content')
<div class="container text-align mt-5">

    <div class="form-container">
        <div class="form-header">
            Reset Password
        </div>
        <div class="form-body">

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
                        
                        <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        <button class="btn btn-secondary" id="logoutBtn" >Logout</button>
                        <button class="btn btn-primary" id="submitBtn">Submit</button>
                    </div>
                </div>
        </div>
    </div>
    <script type="module">
        import {$q} from '/adarna.js';

        const password      = $q('#password').first();
        const repassword    = $q('#repassword').first();
        const logoutBtn     = $q('#logoutBtn').first();
        const submitBtn     = $q('#submitBtn').first();
        const logoutForm    = $q('#logoutForm').first();
        

        submitBtn.onclick = ()=>{
            window.util.blockUI();

            window.util.$post('/api/user/reset_password',{
                password    : password.value,
                repassword  : repassword.value
            }).then(reply=>{

                window.util.unblockUI();

                if(reply.status <= 0){
                    window.util.showMsg(reply);
                    return false;
                }

                document.location.href = '/home';
            });
        }

        logoutBtn.onclick = (e)=>{
            logoutForm.submit();
        }
    </script>
</div>
@endsection