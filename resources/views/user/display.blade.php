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
    <div class="form-container">
        <div class="form-header">
            User
        </div>
        <div class="form-body">
            <div class="row mb-3">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label>ID No.</label>
                        <input type="text" id="name" value="{{str_pad($user->id,6,0,STR_PAD_LEFT)}}" class="form-control" disabled="true"/>
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

            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control editable" id="status" disabled="true">
                            @foreach($status_options as $val=>$text)
                                <option value="{{$val}}" @if($user->status == $val) selected @endif>{{$text}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Reset Password</label>
                        <input type="text" id="reset_password" value="{{$user->reset_password}}" class="form-control" disabled="true"/>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-6">
                    <button class="btn btn-warning" id="resetBtn">Reset Password</button>
                </div>
                <div class="col-6 text-end">
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                </div>
            </div>
        </div>
    </div>
    <hr>

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Roles
        </div>
        <div class="folder-form-body">
            <div class="row">
                <div class="col-lg-8">
                    <div class="form-group">
                        <label>Role</label>
                        <input list="role-list" id="role" class="form-control" />

                            <datalist id="role-list">
                                @foreach($roles as $role)
                                    <option class="role-option" value="{{$role->name}}" data-id="{{$role->id}}"></option>
                                @endforeach
                            </datalist>
                            
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>&nbsp</label>
                        <button class="btn btn-primary form-control" id="addCode">Add</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="role_list" class="row mt-3"></div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const cancelBtn            = $q('#cancelBtn').first();
    const updateBtn            = $q('#updateBtn').first();
    const editBtn              = $q('#editBtn').first();  
    const name                 = $q('#name').first();
    const email                = $q('#email').first();
    const resetBtn             = $q('#resetBtn').first();
    const role                 = $q('#role').first();



    let editable_flag = false;

    resetBtn.onclick = (e) =>{
        window.util.prompt('Are you sure you want to initiate reset password?',(e,result)=>{

            if(!result){
                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/user/reset_password',{
                id: '{{$user->id}}'
            }).then((reply)=>{

                window.util.unblockUI();

                if(reply <= 0){
                    window.util.showMsg(reply);
                    return false;
                }

                window.util.navReload();
            });
        });
    }

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

        editBtn.classList.add('d-none');
        updateBtn.classList.remove('d-none');
    }
    

    addRole.onclick = (e) => {

        let val = $q('.role-option[value="'+role.value+'"]').first();

        if(!val){
            window.util.alert('Code not found');
            
            accessCode.value = '';
            return false;
        }

        val = val.getAttribute('data-id');

        accessCode.value = '';

        window.util.blockUI();

        window.util.$post('/api/role_access_code/add',{
            role_id: '{{$role->id}}',
            access_code_id: val
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0 ){
            
                window.util.showMsg(reply);
                return false;
            };

            reinitalize();
            showData();

        });
    }

</script>
</div>
@endsection