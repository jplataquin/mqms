@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/material_quantity_requests">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Material Request
                    </span>
                    
                    <i class="ms-2 bi bi-list-ul"></i>		
                </a>
            </li>
        </ul>
    </div>
<hr>

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Material Request Review
        </div>
        <div class="folder-form-body">
            <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Sort By</label>
                            <select class="form-control" id="sortSelect">
                                <option value="1" selected>Latest Entry</option>
                                <option value="2">Oldest Entry</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>ID</label>
                            <input type="text" id="query" class="form-control"/>
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-lg-4">
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
                
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Section</label>
                            <select class="form-control" id="sectionSelect">
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Component</label>
                            <select class="form-control" id="componentSelect">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-lg-8"></div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button id="searchBtn" class="btn w-100 btn-primary">Search</button>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button id="cancelBtn" class="btn w-100 btn-secondary">Cancel</button>
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
                    <th>Project - Section</th>
                    <th>Contract Item</th>
                    <th>Component</th>
                    <th>User</th>
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

</div>

<script type="module">
    import {$q,Template,$el,$util} from '/adarna.js';

    let list            = $q('#list').first();
    let query           = $q('#query').first();
    let projectSelect   = $q('#projectSelect').first();
    let sectionSelect   = $q('#sectionSelect').first();
    let componentSelect = $q('#componentSelect').first();
    let searchBtn       = $q('#searchBtn').first();
    let showMoreBtn     = $q('#showMoreBtn').first();
    let sortSelect      = $q('#sortSelect').first();
    let cancelBtn       = $q('#cancelBtn').first();
    let page            = 1;
    let order           = 'DESC';
    let orderBy         = 'id';
    
    window.util.quickNav = {
        title:'Review Material Request',
        url: '/review/material_quantity_request'
    };
            
    const t = new Template();
    
    function reinitalize(){
        page = 1;
        $el.clear(list);   
    }

    function renderRows(data){
        
        data.map(item=>{

            let row = t.tr({class:'selectable-div'},()=>{
                t.td(String(item.id).padStart(6,0));
                t.td(item.project.name+' - '+item.section.name);
                t.td('');
                t.td(item.component.name);
                t.td(item.user.name);
                t.td(
                    $util.dateTime(
                        new Date(item.created_at)
                    ).full()
                );
            });
              

            row.onclick = ()=>{
                window.util.navTo('/review/material_quantity_request/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/review/material_quantity_request/list',{
            query: query.value,
            page: page,
            order: order,
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

            window.util.unblockUI();

            if(reply.data.length){
                renderRows(reply.data); 
            }else{
                showMoreBtn.style.display = 'none';
            }
            
        });
    }
    
    function runFilter(){
        showMoreBtn.style.display = 'block';
        reinitalize();
        showData();
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

        runFilter();
    }

    cancelBtn.onclick = ()=>{
        window.util.navTo('/home');
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

            window.util.unblockUI();

            runFilter();
        });
    }

    sectionSelect.onchange = (e)=>{

        e.preventDefault();

        componentSelect.innerHTML = '';

        window.util.blockUI();

        window.util.$get('/api/component/list',{
            section_id: sectionSelect.value,
            orderBy:'name',
            order:'ASC'
        }).then(reply=>{

            window.util.unblockUI();
                
            if(reply.status <= 0){

                window.util.showMsg(reply);
                return false;
            }

            componentSelect.append(
                t.option({value:''},' - ')
            );

            reply.data.forEach((item)=>{

                componentSelect.append(
                    t.option({value:item.id},item.name)
                );

            });

            runFilter();

        });
    }
    
    componentSelect.onchange = ()=>{
        runFilter();
    }

    runFilter();
</script>
</div>
@endsection