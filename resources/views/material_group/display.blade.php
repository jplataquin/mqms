@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
    <div class="breadcrumbs" hx-boost="true">
        <ul>
            <li>
                <a href="/master_data/material/groups" hx-select="#content" hx-target="#main">
                    <span>
                       Material Groups
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        List
                    </span>		
                    <i class="ms-2 bi bi-display"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="form-container">
        <div class="form-header">
            Material Group
        </div>
        <div class="form-body">
            <div class="row">

                <div class="col-12">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="materialGroupName" disabled="true" value="{{$name}}" class="form-control"/>
                    </div>
                </div>

            </div>

            <div class="row mt-5">
                <div class="col-12 text-end">
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
            Material Items
        </div>
        <div class="folder-form-body">
            <div class="row mb-5">
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

    const materialGroupName   = $q('#materialGroupName').first();
    const editBtn             = $q('#editBtn').first();
    const updateBtn           = $q('#updateBtn').first();
    const cancelBtn           = $q('#cancelBtn').first();

    const list            = $q('#list').first();
    const query           = $q('#query').first();
    const searchBtn       = $q('#searchBtn').first();
    
    const t             = new Template();
    let page            = 1;
    let order           = 'DESC';
    let orderBy         = 'id';
    

    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        materialGroupName.disabled = false;

        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            window.util.navReload();
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/material/group/update',{
            name: materialGroupName.value,
            id: '{{$id}}'
        }).then(reply=>{

            window.util.unblockUI();
               

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navReload();
        });
    }


    cancelBtn.onclick = (e)=>{
         window.util.navTo('/master_data/material/groups');
    }



    function reinitalize(){
        page = 1;
        $el.clear(list);   
    }

    function renderRows(data){
        
        data.map(item=>{

            let row = t.div({class:'item-container fade-in'},()=>{
                t.div({class:'item-header'},item.brand+' '+item.name +' '+item.specification_unit_packaging+''.trim());
            });

            row.onclick = ()=>{
                 window.util.navTo('/master_data/material/item/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    /*** Material Items ***/
    function showData(){

        window.util.blockUI();

        window.util.$get('/api/material/item/list',{
            query: query.value,
            page: page,
            order: order,
            order_by: orderBy,
            material_group_id: '{{$id}}',
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

    createBtn.onclick = ()=>{
         window.util.navTo('/master_data/material/item/create') ;
    }

    reinitalize();
    showData();
</script>

</div>
@endsection