<div id="content">
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
                                Project Name
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
                    <button class="btn btn-warning d-none" id="cancelBtn">Update</button>
                </div>
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
    
    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        project_name.disabled             = false;
        status.disabled                   = false;
     
        updateBtn.classList.remove('d-none');
        cancelBtn.classList.remove('d-none');
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/project/update',{
            name                            : project_name.value,
            status                          : status.value,
            id: '{{$project->id}}'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                
                window.util.showMsg(reply);
                return false;
            }

            studio.onScreen('/project/{{$project->id}}');
        });
    }

    createBtn.onclick = ()=>{
        
        let create_section_form = CreateSectionForm({
            project_id:'{{$project->id}}'
        });

        window.util.drawerModal.content('Create Section',create_section_form).open();
    }

    cancelBtn.onclick = (e)=>{
        studio.onScreen('/project/{{$project->id}}');
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
    
</script>
</div>