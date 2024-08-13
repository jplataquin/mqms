@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs" hx-boost="true">
        <ul>
            <li>
                <a href="/roles">
                    <span>
                       Roles
                    </span>                    
                    
                </a>
            </li>
            <li>
                <a href="#" class="active" >
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
            Role
        </div>
        <div class="form-body">
            <div class="row">

                <div class="col-12 mb-3">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" disabled="true" value="{{$role->name}}" id="name" class="form-control"/>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="description" disabled="true" class="form-control">{{$role->description}}</textarea>
                    </div>
                </div>
            </div>

            <div class="row mt-5 mb-3">
                <div class="col-6 text-start">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
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
            Access Codes
        </div>
        <div class="folder-form-body">
            <div class="row">
                <div class="col-lg-8">
                    <div class="form-group">
                        <label>Code</label>
                        <input list="access-code-list" id="accessCode" class="form-control" />

                            <datalist id="access-code-list">
                                @foreach($accessCodes as $code)
                                    <option class="code-option" value="{{$code->code}}" data-id="{{$code->id}}"></option>
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


    <div class="container" id="list"></div>


</div>

<script type="module">
    import {$q,$el,Template} from '/adarna.js';

    let cancelBtn       = $q('#cancelBtn').first();
    let name            = $q('#name').first();
    let description     = $q('#description').first();
    let editBtn         = $q('#editBtn').first();
    let updateBtn       = $q('#updateBtn').first();
    let addCode         = $q('#addCode').first();
    let accessCode      = $q('#accessCode').first();
    let list            = $q('#list').first();
    let showMoreBtn     = $q('#showMoreBtn').first(); 


    function reinitalize(){
        
        $el.clear(list);
    }
    
    addCode.onclick = (e) => {

        let val = $q('.code-option[value="'+accessCode.value+'"]').first();

        if(!val){
            alert('Code not found');
            
            accessCode.value = '';
            return false;
        }

        val = val.getAttribute('data-id');

        accessCode.value = '';

        window.util.blockUI();

        window.util.$post('/api/role/access_code/add',{
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

    editBtn.onclick = (e) =>{
        e.preventDefault();
        
        name.disabled = false;
        description.disabled = false;
        updateBtn.classList.remove('d-none');
        editBtn.classList.add('d-none');
    }

    updateBtn.onclick = (e) => {

        e.preventDefault();

        window.util.blockUI();

        window.util.$post('/api/role/update',{
            name: name.value,
            description: description.value,
            id: '{{$role->id}}'
        }).then(reply=>{

            window.util.unblockUI();
                
            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };


            window.util.showMsg('/role/'+reply.data.id);


        });
    }

    deleteBtn.onclick = (e) =>{
        e.preventDefault();

        let answer = prompt('To delete record type in "{{$role->name}}"');

        if(answer != '{{$role->name}}'){
            alert('Invalid input');
            return false;
        }


        window.util.$post('/api/role/delete',{
            id: '{{$role->id}}'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0 ){
                
                window.util.showMsg(reply);
                return false;
            };

            window.util.showMsg('/roles');

        });

    }

    cancelBtn.onclick = (e) => {
        
        window.util.navTo('/roles');

    }


    const t = new Template();
    

    function renderRows(data){
        
        data.map(item=>{
           
            let row = t.div({class:'item-container fade-in'},()=>{
               
                t.div({class:'item-header'},()=>{
                    
                    t.div({class:'row'},()=>{

                        t.div({class:'col-6'},()=>{

                            t.txt(item.code);
                        });

                        t.div({class:'col-6 text-end'},()=>{
                            
                            t.button({class:'btn btn-danger'},()=>{
                                t.i({class:'bi bi-trash-fill'});
                            }).onclick = (e)=>{
                                e.stopPropagation();
                               
                                window.util.prompt('Are you sure you want to remove this role?',(e,res)=>{
                                    
                                    if(!res){
                                        return false;
                                    }

                                    window.util.blockUI();

                                    window.util.$post('/api/role/access_code/remove',{
                                        role_id: '{{$role->id}}',
                                        access_code_id: item.id
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

                t.div({class:'item-body'},item.description );
            });

            row.onclick = ()=>{
                window.util.navTo('/access_code/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/role/access_codes/{{$role->id}}',{}).then(reply=>{

            window.util.unblockUI();
                

            if(reply.status <= 0 ){
                
                window.util.showMsg(reply);
                return false;
            };


            if(reply.data.length){
                renderRows(reply.data); 
            }else{
                showMoreBtn.style.display = 'none';
            }
            
        });
    }

    reinitalize();
    showData();

</script>
</div>
@endsection