@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/projects">
                    <span>
                       Projects
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
            Project
        </div>
        <div class="form-body">
            <div class="row">
                <div class="col-lg-12">
                    <table class="w-100 table">
                        <tr>
                            <th>
                                Project
                            </th>
                            <td>
                                <input type="text" disabled="true" id="project_name" value="{{$project->name}}" class="form-control"/>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Status
                            </th>
                            <td>
                                <select id="status" disabled="true" class="form-control">
                                    <option value="ACTV" @if($project->status == "ACTV") selected @endif>Active</option>
                                    <option value="INAC" @if($project->status == "INAC") selected @endif>Inactive</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>   

            </div>

            <div class="row mt-5 mb-3">
                <div class="col-lg-12 text-end">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                    <button class="btn btn-warning" id="studioBtn">Studio</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
          
                </div>
            </div>
        </div>
    </div>

    <hr>
    

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Section
        </div>
        <div class="folder-form-body">
            <div class="row mb-3">
                <div class="col-lg-12 text-end">
                    <button id="createBtn" class="btn btn-warning">Create</button>
                </div>
            </div>
        </div>
    </div>
    

    <div class="container mb-3" id="list"></div>
    
    <div class="row">
        <div class="col-lg-12">
            <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
        </div>
    </div>
</div>

<script type="module">
    import {$q,$el,Template} from '/adarna.js';
    import CreateSectionForm from '/ui_components/create_forms/CreateSectionForm.js';

    const project_name                = $q('#project_name').first();
    const status                      = $q('#status').first();
    const editBtn                     = $q('#editBtn').first();
    const updateBtn                   = $q('#updateBtn').first();
    const cancelBtn                   = $q('#cancelBtn').first();
    const deleteBtn                   = $q('#deleteBtn').first();
    const studioBtn                   = $q('#studioBtn').first();
    const list                        = $q('#list').first();
    const showMoreBtn                 = $q('#showMoreBtn').first();
    const createBtn                   = $q('#createBtn').first();
    const section_name                = $q('#section_name').first();
    
    window.util.quickNav = {
        title:'Project',
        url:'/project'
    };
    
    studioBtn.onclick = (e)=>{
        window.open('/project/studio/{{$project->id}}','_blank').focus();
    }
    
    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        project_name.disabled             = false;
        status.disabled                   = false;
     
        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            window.util.navReload();
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/project/update',{
            name                            : project_name.value,
            status                           : status.value,
            id: '{{$project->id}}'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                
                window.util.showMsg(reply);
                return false;
            }

            window.util.navReload();
        });
    }

    createBtn.onclick = ()=>{
        
        let create_section_form = CreateSectionForm({
            project_id:'{{$project->id}}'
        });

        window.util.drawerModal.content('Create Section',create_section_form).open();
    }

    cancelBtn.onclick = (e)=>{
        window.util.navTo('/projects');
    }


    deleteBtn.onclick = (e)=>{

        let answer = prompt('Are you sure you want to delete this Project? \n If so please type "{{$project->name}}"');

        if(answer != "{{$project->name}}"){
            window.util.showMsg('Invalid answer');
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/project/delete',{
            id: "{{$project->id}}"
        }).then(reply=>{

            window.util.unblockUI();
            
            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navTo('/projects');
        });
    }
    


    /**** LIST ****/
    let page            = 1;
    let order           = 'DESC';
    let orderBy         = 'id';
    
    const t = new Template();
    
    function reinitalize(){
        page = 1;
        $el.clear(list);   
    }

    function renderRows(data){
        
        data.map(item=>{

            let row = t.div({class:'item-container fade-in'},()=>{ 
                t.div({class:'item-header'},item.name);
                t.div({class:'item-body'});
            });

            row.onclick = ()=>{
                window.util.navTo('/project/section/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/section/list',{
            project_id: '{{$project->id}}',
            query: '',
            page: page,
            order: order,
            order_by: orderBy,
            limit: 10
        }).then(reply=>{

            window.util.unblockUI();
            
            if(reply.status <= 0 ){
                
                window.util.showMsg(reply);
                return false;
            };

            page++;

            if(reply.data.length){
                renderRows(reply.data); 
            }else{
                showMoreBtn.style.display = 'none';
            }
            
        });
    }
   
    // searchBtn.onclick = ()=>{
    //     showMoreBtn.style.display = 'block';
    //     reinitalize();
    //     showData();
    // }

    showMoreBtn.onclick = ()=>{
        showData();
    }

    // sortSelect.onchange = ()=>{
    //     reinitalize();

    //     let select = parseInt(sortSelect.value);

    //     switch(select){
    //         case 1:
    //             order   = 'ASC';
    //             orderBy = 'name';
    //             break;
    //         case 2:
    //             order   = 'DESC';
    //             orderBy = 'name';
    //             break;
    //         case 3:
    //             order   = 'DESC';
    //             orderBy = 'id';
    //             break;
    //         case 4:
    //             order   = 'ASC';
    //             orderBy = 'id';
    //         break;
    //     }

    //     showData();
    // }

    reinitalize();
    showData();

</script>
</div>
@endsection