@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/material_canvass">
                    <span>
                        Material Canvass
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        List
                    </span>	
                    <i class="ms-2 bi bi-list-ul"></i>	
                </a>
            </li>
        </ul>
    </div>
<hr>

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Material Canvass
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
            <div class="row">
                <div class="col-lg-12 text-end mt-3 mb-3">
                    <button id="searchBtn" class="btn btn-primary">Search</button>
                    <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <table class="table border">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Project</th>
                    <th>Section</th>
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

</div>

<script type="module">
    import {$q,Template,$el,$util} from '/adarna.js';

    const list            = $q('#list').first();
    const query           = $q('#query').first();
    const projectSelect   = $q('#projectSelect').first();
    const sectionSelect   = $q('#sectionSelect').first();
    const componentSelect = $q('#componentSelect').first();
 
    const searchBtn       = $q('#searchBtn').first();
    const showMoreBtn     = $q('#showMoreBtn').first();
    const sortSelect      = $q('#sortSelect').first();
    const cancelBtn       = $q('#cancelBtn').first();
    const createBtn       = $q('#createBtn').first();
    
    let page            = 1;
    let order           = 'DESC';
    let orderBy         = 'id';

    window.util.quickNav = {
        title:'Material Canvass',
        url: '/material_canvass'
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
            
                t.td(item.project.name);
                t.td(item.section.name);
                t.td(item.component.name);
                t.td(
                    $util.dateTime(
                        new Date(item.created_at)
                    ).full()
                );
            });
            

            row.onclick = ()=>{
                 window.util.navTo('/material_canvass/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/material_canvass/list',{
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
                
                wondow.util.showMsg(reply)
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

        });
    }

    reinitalize();
    showData();
</script>
</div>
@endsection