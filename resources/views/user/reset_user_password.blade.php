@extends('layouts.app')

@section('content')
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
                        Reset Password
                    </span>	
                    <i class="ms-2 bi bi-display"></i>	
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="form-container">
        <div class="form-header">
            Reset User Password
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
                    </div>
                </div>
        </div>
    </div>
    <script type="module">
        import {$q} from '/adarna.js';

    </script>
</div>
@endsection