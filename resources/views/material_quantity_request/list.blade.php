@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs" hx-boost="true">
        <ul>
            <li>
                <a href="#" class="active">
                    <span>
                       Material Quantity Request
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
                
            </li>
        </ul>
    </div>
<hr>

<div class="folder-form-container">
    <div class="folder-form-tab">
        Materail Quantity Request
    </div>
    <div class="folder-form-body">
        <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Sort By</label>
                        <select class="form-control" id="sortSelect">
                            <option value="1" selected>Latest Entry</option>
                            <option value="2">Oldest Entry</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>ID</label>
                        <input type="text" id="query" class="form-control"/>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="statusSelect">
                            <option value=""> - </option>
                            <option value="PEND">Pending</option>
                            <option value="APRV">Approved</option>
                            <option value="DPRV">Disapproved</option>
                        </select>
                    </div>
                </div>
                
        </div>
        <div class="row">
                
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Project</label>
                        <select class="form-control" id="projectSelect">
                            <option value=""> - </option>
                            @foreach($projects as $project)
                                <option value="{{$project->id}}">{{$project->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Section</label>
                        <select class="form-control" id="sectionSelect">
                        </select>
                    </div>
                </div>

                
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Contract Item</label>
                        <select class="form-control" id="contractItemSelect">
                        </select>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Component</label>
                        <select class="form-control" id="componentSelect">
                        </select>
                    </div>
                </div>

             
        </div>
        <div class="row mb-5">
            <div class="col-lg-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button id="createBtn" class="btn w-100 btn-warning">Create</button>
                </div>
            </div>
        
            <div class="col-lg-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button id="cancelBtn" class="btn w-100 btn-secondary">Cancel</button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button id="searchBtn" class="btn w-100 btn-primary">Search</button>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="container">
                <table class="table border">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Status</th>
                            <th>Project</th>
                            <th>Section</th>
                            <th>Contract Item</th>
                            <th>Component</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody  id="list">
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
                </div>
            </div>


<script type="module">
    import {$q,Template,$el,$util} from '/adarna.js';

    const list                  = $q('#list').first();
    const query                 = $q('#query').first();
    const projectSelect         = $q('#projectSelect').first();
    const sectionSelect         = $q('#sectionSelect').first();
    const contractItemSelect    = $q('#contractItemSelect').first();
    const componentSelect       = $q('#componentSelect').first();
    const statusSelect          = $q('#statusSelect').first();    
    const searchBtn             = $q('#searchBtn').first();
    const showMoreBtn           = $q('#showMoreBtn').first();
    const sortSelect            = $q('#sortSelect').first();
    const cancelBtn             = $q('#cancelBtn').first();
    const createBtn             = $q('#createBtn').first();
    
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

            let row = t.tr({class:'selectable-div'},()=>{
                t.td(String(item.id).padStart(6,0));
                t.td(item.status);
                t.td(item.project.name);
                t.td(item.section.name);
                t.td('');
                t.td(item.component.name);
                t.td(
                    $util.dateTime(
                        new Date(item.created_at)
                    ).full()
                );
            });
            

            row.onclick = ()=>{
                window.util.navTo('/material_quantity_request/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/material_quantity_request/list',{
            query: query.value,
            page: page,
            order: order,
            status: statusSelect.value,
            project_id: projectSelect.value,
            section_id: sectionSelect.value,
            component_id: componentSelect.value,
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
                order   = 'DESC';
                orderBy = 'created_at';
                break;
            case 2:
                order   = 'ASC';
                orderBy = 'created_at';
            break;
        }

        showData();
    }

    cancelBtn.onclick = (e)=>{
        e.preventDefault();
        window.util.navTo('/home');
    }

    createBtn.onclick = (e)=>{
        e.preventDefault();
        window.util.navTo('/material_quantity_request/select/create');
    }

    projectSelect.onchange = (e)=>{

        e.preventDefault();

        sectionSelect.innerHTML = '';
        componentSelect.innerHTML = '';

        window.util.blockUI();

        window.util.$get('/api/section/list',{
            project_id: projectSelect.value,
            orderBy:'name',
            order:'ASC'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){

                window.util.showMsg(reply);
                return false;
            }

            sectionSelect.append(
                t.option({value:''},' - ')
            );

            reply.data.forEach((item)=>{

                sectionSelect.append(
                    t.option({value:item.id},item.name)
                );

            });

        });
    }

    sectionSelect.onchange = (e)=>{

        e.preventDefault();

        componentSelect.innerHTML = '';

        window.util.blockUI();

        window.util.$get('/api/contract_item/list',{
            section_id: sectionSelect.value,
            orderBy:'name',
            order:'ASC'
        }).then(reply=>{

            
            window.util.unblockUI();

            if(reply.status <= 0){

                window.util.showMsg(reply);
                return false;
            }

            contractItemSelect.append(
                t.option({value:''},' - ')
            );

            reply.data.forEach((item)=>{

                contractItemSelect.append(
                    t.option({value:item.id},'['+item.code+'] '+item.description)
                );

            });

        });
    }

    reinitalize();
    showData();
</script>
</div>
@endsection