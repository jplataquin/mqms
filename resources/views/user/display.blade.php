@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs">
        <ul>
            <li>
                <a href="#">
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
                </a>
            </li>
        </ul>
    </div>
<hr>

    <div class="row mb-3">

        <div class="col-lg-12">
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="name" value="{{$user->name}}" class="editable form-control" disalbed="true"/>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Email</label>
                <input type="text" id="email" value="{{$user->email}}" class="editable form-control" disalbed="true"/>
            </div>
        </div>
    </div>


    <div class="row mt-5">
        <div class="col-12 text-end">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-primary" id="editBtn">Edit</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const editBtn              = $q('#createBtn').first();
    const cancelBtn            = $q('#cancelBtn').first();
    const name                 = $q('#name').first();
    const email                = $q('#email').first();

    
    cancelBtn.onclick = (e) => {
        window.util.navTo('/users');

    }

</script>
</div>
@endsection