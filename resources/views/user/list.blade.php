@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/users">
                    <span>Users</span>
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
            Users
        </div>
        <div class="folder-form-body">
            <div class="row">
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
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Query</label>
                        <input type="text" id="query" class="form-control"/>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-12 text-end">
                    
                    <button id="searchBtn" class="btn btn-primary me-3">Search</button>
            
                    <button id="createBtn" class="btn btn-warning">Create</button>
                            
                </div>
            </div>

        </div>
    </div>
    
    <hr>

    <div class="container" id="list"></div>

    <div class="row mt-3">
        <div class="col-lg-12">
            <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q,Template,$el} from '/adarna.js';

    const list            = $q('#list').first();
    const query           = $q('#query').first();
    const createBtn       = $q('#createBtn').first();
    const searchBtn       = $q('#searchBtn').first();
    const showMoreBtn     = $q('#showMoreBtn').first();
    const sortSelect      = $q('#sortSelect').first();
    
    
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
                t.div({class:'item-body'},item.email);
            });

            row.onclick = ()=>{
                window.util.navTo('/user/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/user/list',{
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
   
    searchBtn.onclick = ()=>{
        showMoreBtn.style.display = 'block';
        reinitalize();
        showData();
    }

    query.onkeyup = (e)=>{

        if(e.keyCode == 13){
            showMoreBtn.style.display = 'block';
            reinitalize();
            showData();
        }
    }

    createBtn.onclick = ()=>{
        window.util.navTo('user/create');
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
                orderBy = 'code';
                break;
            case 2:
                order   = 'DESC';
                orderBy = 'code';
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

    reinitalize();
    showData();
</script>
</div>
@endsection