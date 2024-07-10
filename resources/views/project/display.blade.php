@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<h5>Project » Display</h5>
<hr>

    <div class="row">

    <div class="row">
        <div class="col-lg-12">
            <table class="w-100 table">
                <tr>
                    <th>
                        Project
                    </th>
                    <td>
                        <input type="text" disabled="true" id="projectName" value="{{$project->name}}" class="form-control"/>
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
    
    <div class="">
        <h3>Sections</h3>
    </div>
    <div class="row mb-3">
        <div class="col-lg-4">
            <div class="form-group">
                <label>Sort By</label>
                <select class="form-control" id="sortSelect">
                    <option value="1">A-Z name</option>
                    <option value="2">Z-A name</option>
                    <option value="3" selected>Latest Entry</option>
                    <option value="4">Oldest Entry</option>
                </select>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <label>Query</label>
                <input type="text" id="query" class="form-control"/>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-group">
                <label>&nbsp;</label>
                <button id="searchBtn" class="btn w-100 btn-primary">Search</button>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-group">
                <label>&nbsp;</label>
                <button id="createBtn" class="btn w-100 btn-warning">Create</button>
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

    let projectName                 = $q('#projectName').first();
    let status                      = $q('#status').first();
    let createBtn                   = $q('#createBtn').first();
    let searchBtn                   = $q('#searchBtn').first();
    let editBtn                     = $q('#editBtn').first();
    let updateBtn                   = $q('#updateBtn').first();
    let cancelBtn                   = $q('#cancelBtn').first();
    let deleteBtn                   = $q('#deleteBtn').first();
    let list                        = $q('#list').first();
    let showMoreBtn                 = $q('#showMoreBtn').first();

    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        projectName.disabled              = false;
        status.disabled                   = false;
     
        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            document.location.reload(true);
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/project/update',{
            name                            : projectName.value,
            status                           : status.value,
            id: '{{$project->id}}'
        }).then(reply=>{

            if(reply.status <= 0){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            }

            document.location.reload(true);
        });
    }


    cancelBtn.onclick = (e)=>{
        document.location.href = '/projects';
    }

    createBtn.onclick = (e)=>{
        document.location.href = '/project/{{$project->id}}/section/create';
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
                window.util.showMsg(reply.message);
                return false;
            }

            window.location.href = '/projects';
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

            let row = t.div({class:'row mt-1 mb-1 border selectable-div fade-in'},()=>{
                t.div({class:'col-lg-12'},item.name);
            });

            row.onclick = ()=>{
                document.location.href = '/project/section/'+item.id;
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/section/list',{
            project_id: '{{$project->id}}',
            query: query.value,
            page: page,
            order: order,
            order_by: orderBy,
            limit: 10
        }).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                
                let message = reply.message;

                
                alert(message);
                return false;
            };

            page++;

            window.util.unblockUI();

            if(reply.data.length){
                renderRows(reply.data); 
            }else{
                showMoreBtn.style.display = 'none';
            }
            
        });
    }
   
    searchBtn.onclick = ()=>{
        showMoreBtn.style.display = 'block';
        reinitalize();
        showData();
    }

    showMoreBtn.onclick = ()=>{
        showData();
    }

    sortSelect.onchange = ()=>{
        reinitalize();

        let select = parseInt(sortSelect.value);

        switch(select){
            case 1:
                order   = 'ASC';
                orderBy = 'name';
                break;
            case 2:
                order   = 'DESC';
                orderBy = 'name';
                break;
            case 3:
                order   = 'DESC';
                orderBy = 'id';
                break;
            case 4:
                order   = 'ASC';
                orderBy = 'id';
            break;
        }

        showData();
    }


    showData();

</script>
</div>
@endsection