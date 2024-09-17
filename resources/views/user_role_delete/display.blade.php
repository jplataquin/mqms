@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/roles">
                    <span>
                       Roles
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Create
                    </span>	
                    <i class="ms-2 bi bi-display"></i>	
                </a>
            </li>
        </ul>
    </div>
    
    <hr>

    <div class="row">

        <div class="col-12">
            <div class="form-group">
                <label>Name</label>
                <input type="text" disabled="true" value="{{$user->name}}" id="name" class="form-control"/>
            </div>
        </div>

        <div class="col-12">
            <div class="form-group">
                <label>Email</label>
                <input type="text" disabled="true" value="{{$user->email}}" id="email" class="form-control"/>
            </div>
        </div>
    </div>

    <div class="row mt-5 mb-3">
        <div class="col-6 text-start">
        </div>
        <div class="col-6 text-end">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
        </div>
    </div>
    
    <hr>

    <div class="row">
        <div class="col-lg-8">
            <div class="form-group">
                <label>Roles</label>
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
                <button class="btn btn-primary form-control" id="addRole">Add</button>
            </div>
        </div>
    </div>
    


    <div class="container" id="list">
    </div>


</div>

<script type="module">
    import {$q,$el,Template} from '/adarna.js';

    let cancelBtn       = $q('#cancelBtn').first();
    let role            = $q('#role').first();
    let addRole         = $q('#addRole').first();
    let list            = $q('#list').first();

    function reinitialize(){
        
        $el.clear(list);
    }

    addRole.onclick = (e) => {

        let val = $q('.role-option[value="'+role.value+'"]').first();

        if(!val){
            alert('Code not found');
            
            role.value = '';
            return false;
        }

        val = val.getAttribute('data-id');

        role.value = '';

        window.util.blockUI();

        window.util.$post('/api/user_role/add',{
            user_id: '{{$user->id}}',
            role_id: val
        }).then(reply=>{

            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };
            
            reinitialize();
            showData();

        });
    }


   
    cancelBtn.onclick = (e) => {
        window.util.navTo('/user_roles');

    }


    const t = new Template();
    

    function renderRows(data){
        
        data.map(item=>{

            let row = t.div({class:'row mt-1 mb-1 border selectable-div fade-in'},()=>{
                t.div({class:'col-6'},item.name );
                t.div({class:'col-6 text-center'},()=>{
                    
                    t.a({class:'me-3',href:'#'},'[view]').onclick = (e)=>{
                        e.preventDefault();
                        window.util.navTo('/role/'+item.id);
                    };

                    

                    t.a({href:'#'},'[delete]').onclick = (e)=>{
                        e.preventDefault();

                        if(!confirm('Are you sure you want to delete this role?')){
                            return false;
                        }

                        window.util.blockUI();

                        window.util.$post('/api/user_role/delete',{
                            user_id:'{{$user->id}}',
                            role_id: item.id
                        }).then(reply=>{

                            window.util.unblockUI();
                                
                            if(reply.status <= 0 ){
                                
                                window.util.showMsg(reply);
                                return false;
                            };

                            $el.remove(row);

                        });
                    };

                });
            });


            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/user_role/{{$user->id}}/list',{}).then(reply=>{

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

    reinitialize();
    showData();

</script>
</div>
@endsection