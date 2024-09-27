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
                            Display
                        </span>	
                        <i class="ms-2 bi bi-display"></i>	
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="form-container">
            <div class="form-title">
                Details
            </div>
            <div class="form-body mb-3">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" id="name" value="{{$user->name}}" disabled="true"/>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="text" class="form-control" id="name" value="{{$user->email}}" disabled="true"/>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Status</label>
                            <input type="text" class="form-control" id="name" value="{{$user->status}}" disabled="true"/>
                        </div>
                    </div>
                </div> 
            </div>
        </div>

    </div>
    <script type="module">
    </script>
</div>
@endsection