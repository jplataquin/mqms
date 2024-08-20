@extends('layouts.reset_password')

@section('content')
<div class="container text-align">

    <div class="form-container">
        <div class="form-header">
            Reset Password
        </div>
        <div class="form-body">

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" id="password"/>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label>Retype Password</label>
                            <input type="password" class="form-control" id="repassword"/>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-end">
                        <button class="btn btn-secondary">Logout</button>
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </div>
        </div>
    </div>
    <script type="module">
    </script>
</div>
@endsection