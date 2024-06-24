@extends('layouts.app')

@section('content')
<div class="container">
<h5>Role » Display</h5>
<hr>

    <div class="row">

        <div class="col-12">
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
    
    <hr>

    <div class="row">
        <div class="col-lg-8">
            <div class="form-group">
                <label>Access Code</label>
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
    


    <div class="container" id="list">
    </div>


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

        window.util.$post('/api/role_access_code/add',{
            role_id: '{{$role->id}}',
            access_code_id: val
        }).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            };
            
            $el.clear(list);

            showData();
            window.util.unblockUI();

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

            if(reply.status <= 0 ){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            };

            window.util.unblockUI();

            document.location.href = '/role/'+reply.data.id;


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

            if(reply.status <= 0 ){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            };

            document.location.href = '/roles';

        });

    }

    cancelBtn.onclick = (e) => {
        
        e.preventDefault();
        document.location.href = '/roles';

    }


    const t = new Template();
    

    function renderRows(data){
        
        data.map(item=>{
           
            let row = t.div({class:'row mt-1 mb-1 border selectable-div fade-in'},()=>{
                t.div({class:'col-6'},item.code );
                t.div({class:'col-6 text-center'},()=>{
                    
                    t.a({class:'me-3',href:'#'},'[view]');

                    

                    t.a({href:'#'},'[delete]').onclick = (e)=>{
                        e.preventDefault();

                        if(!confirm('Are you sure you want to delete this role?')){
                            return false;
                        }

                        window.util.blockUI();

                        window.util.$post('/api/role_access_code/delete',{
                            role_id:'{{$role->id}}',
                            access_code_id: item.access_code_id
                        }).then(reply=>{

                            if(reply.status <= 0 ){
                                window.util.unblockUI();
                                
                                let message = reply.message;  

                                alert(message);
                                return false;
                            };

                            $el.remove(row);
                            window.util.unblockUI();

                        });
                    };

                });
            });


            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/role_access_code/{{$role->id}}/list',{}).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                
                let message = reply.message;

                
                alert(message);
                return false;
            };


            window.util.unblockUI();

            if(reply.data.length){
                renderRows(reply.data); 
            }else{
                showMoreBtn.style.display = 'none';
            }
            
        });
    }

    showData();

</script>

@endsection