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
        <div class="form-header">
            User
        </div>
        <div class="form-body">
            <div class="row mb-3">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label>ID No.</label>
                        <input type="text" value="{{str_pad($user->id,6,0,STR_PAD_LEFT)}}" class="form-control" disabled="true"/>
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
                        <select class="form-select editable" id="status" disabled="true">
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
                        <!--
                        <input type="text" id="reset_password" value="{{$user->reset_password}}" class="form-control" disabled="true"/>
                        -->
                        <select class="form-select editable" id="reset_password"  disabled="true">
                            <option value="0" @if(!$user->reset_password) selected @endif >No</option>
                            <option value="1" @if($user->reset_password) selected @endif >Yes</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-lg-12 text-end">
                    <button class="btn btn-warning" id="changeBtn">Change Password</button>
                
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                   
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
                <div class="col-lg-12">
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
            </div>
            <div class="row mt-3">
                <div class="col-lg-12 text-end">
                        <button class="btn btn-primary" id="addRoleBtn">Add</button>   
                </div>
            </div>
        </div>
    </div>

    <div id="list" class="container mt-3"></div>

</div>

<script type="module">
    import {$q,$el,Template} from '/adarna.js';

    const cancelBtn            = $q('#cancelBtn').first();
    const updateBtn            = $q('#updateBtn').first();
    const editBtn              = $q('#editBtn').first();  
    const name                 = $q('#name').first();
    const email                = $q('#email').first();
    const reset_password       = $q('#reset_password').first();
    const changeBtn            = $q('#changeBtn').first();
    const role                 = $q('#role').first();
    const addRoleBtn           = $q('#addRoleBtn').first();
    const list                 = $q('#list').first();
    const status               = $q('#status').first();

    const t = new Template();

    let editable_flag = false;
    

    function reinitalize(){
        $el.clear(list);
    }

    changeBtn.onclick = (e) =>{
        
        window.util.navTo('/change_user_password/{{$user->id}}');

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
    
    updateBtn.onclick = ()=>{
        window.util.blockUI();

        window.util.$post('/api/user/update',{
            name: name.value,
            email: email.value,
            status: status.value,
            reset_password: reset_password.value,
            user_id: '{{$user->id}}'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0 ){
            
                window.util.showMsg(reply);
                return false;
            };
            
            window.util.navReload();
        });
    }

    addRoleBtn.onclick = (e) => {

        let val = $q('.role-option[value="'+role.value+'"]').first();

        if(!val){
            window.util.alert('Error','Role not found');
            
            role.value = '';
            return false;
        }

        val = val.getAttribute('data-id');

        role.value = '';

        window.util.blockUI();

        window.util.$post('/api/user/role/add',{
            role_id: val,
            user_id: '{{$user->id}}'
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

    function renderRows(data){
        
        data.map(item=>{

            let row = t.div({class:'item-container fade-in'},()=>{
                
                t.div({class:'item-header'},()=>{
                    t.div({class:'row'},()=>{
                        t.div({class:'col-6'},()=>{

                            t.txt(item.name);
                        });

                        t.div({class:'col-6 text-end'},()=>{
                            
                            t.button({class:'btn btn-danger'},()=>{
                                t.i({class:'bi bi-trash-fill'});
                            }).onclick = (e)=>{
                                e.stopPropagation();
                               
                                window.util.confirm('Are you sure you want to remove this role?',(e,res)=>{
                                    
                                    if(!res){
                                        return false;
                                    }

                                    window.util.blockUI();

                                    window.util.$post('/api/user/role/remove',{
                                        user_id: '{{$user->id}}',
                                        role_id: item.id
                                    }).then(reply=>{
                                        window.util.unblockUI();

                                        if(reply.status <= 0){
                                            window.util.showMsg(reply);
                                            return false;
                                        }

                                        row.remove();
                                    });
                                });
                            };
                        });
                    })
                });

                t.div({class:'item-body'},()=>{
                    
                    t.txt(item.description);
                  
                     
                });
            });

            row.onclick = ()=>{
                window.util.navTo('/role/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/user/roles/{{$user->id}}').then(reply=>{

            window.util.unblockUI();
                

            if(reply.status <= 0 ){
                
                window.util.showMsg(reply);
                return false;
            };


            if(reply.data.length){
                renderRows(reply.data); 
            }
            
        });
    }

    reinitalize();
    showData();

</script>
</div>
@endsection