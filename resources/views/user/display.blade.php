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
                <label>ID No.</label>
                <input type="text" id="name" value="{{str_pad($user->id,6,0,STR_PAD_RIGHT)}}" class="form-control" disabled="true"/>
            </div>
        </div>
    </div>

    <div class="row mb-3">

        <div class="col-lg-12">
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="name" value="{{$user->name}}" class="editable form-control" disabled="true"/>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Email</label>
                <input type="text" id="email" value="{{$user->email}}" class="editable form-control" disabled="true"/>
            </div>
        </div>
    </div>


    <div class="row mt-5">
        <div class="col-12 text-end">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-primary" id="editBtn">Edit</button>
            <button class="btn btn-warning d-none" id="updateBtn">Update</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const cancelBtn            = $q('#cancelBtn').first();
    const updateBtn            = $q('#updateBtn').first();
    const editBtn            = $q('#editBtn').first();  
    const name                 = $q('#name').first();
    const email                = $q('#email').first();

    let editable_flag = false;

    cancelBtn.onclick = (e) => {

        if(editable_flag){

            window.util.navReload();
            return false;
        }

        window.util.navTo('/users');

    }

    editBtn.onclick = ()=>{
        
        $q('.editable').apply((el)=>{

            if(!editable_flag){

                el.disabled = false;
            }
        });

        if(!editable_flag){
            editable_flag = true;
        }
    }
    

</script>
</div>
@endsection