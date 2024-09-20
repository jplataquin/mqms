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
            Projects
        </div>
        <div class="folder-form-body">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Query</label>
                        <input type="text" id="query" class="form-control"/>
                    </div>
                </div>
                <div class="col-lg-6">
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
            </div>
            <div class="row">
                <div class="col-lg-12 mb-3 text-end">
                       <button id="createBtn" class="btn btn-warning">Create</button>
                       <button id="searchBtn" class="btn btn-primary">Search</button> 
                </div>
            </div>
        </div>
    </div>
    
    <div class="container" id="list"></div>

    <div class="row mt-3">
        <div class="col-lg-12">
            <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q,Template,$el} from '/adarna.js';
    import CreateProjectForm from '/ui_components/CreateProjectForm.js'

    const list            = $q('#list').first();
    const query           = $q('#query').first();
    const searchBtn       = $q('#searchBtn').first();
    const showMoreBtn     = $q('#showMoreBtn').first();
    const sortSelect      = $q('#sortSelect').first();
    const createBtn       = $q('#createBtn').first();
    
    const create_project_form = CreateProjectForm();

    let page            = 1;
    let order           = 'DESC';
    let orderBy         = 'id';
    
    window.util.quickNav = {
        title:'Project',
        url:'/project'
    };
    
    const t = new Template();
    
    function reinitalize(){
        page = 1;
        $el.clear(list);   
    }

    function renderRows(data){
        
        data.map(item=>{

            let row = t.div({class:'item-container fade-in'},()=>{

                t.div({class:'item-header'},item.name);
                t.div({class:'item-body'},()=>{
                    t.div({class:'row'},()=>{

                        t.div({class:'col-lg-6'},()=>{

                            t.span( String(item.id).padStart(6,'0') );

                        });//div col

                        t.div({class:'col-lg-6'},()=>{

                            t.span( item.status );

                        });//div col

                    });//div row
                });//div
            
            });//div

            row.onclick = ()=>{
                window.util.navTo('/project/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/project/list',{
            query: query.value,
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
    
    function runQuery(){
        showMoreBtn.style.display = 'block';
        reinitalize();
        showData();
    }

    query.onkeypress = (e)=>{
        if (e.key === "Enter") {
            runQuery();
        }
    }


    searchBtn.onclick = ()=>{
        runQuery();
    }

    showMoreBtn.onclick = ()=>{
        showData();
    }

    sortSelect.onchange = ()=>{

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

        runQuery();
    }

    createBtn.onclick = ()=>{
        window.util.drawerModal.content('Create Project',CreateProjectForm()).open();
    }

    reinitalize();
    showData();
</script>
</div>
@endsection