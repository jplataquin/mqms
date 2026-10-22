@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/master_data/material/items">
                    <span>
                       Material Items
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
            Material Items
        </div>
        <div class="folder-form-body">
            <div class="row mb-3">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Material Group</label>
                        <select class="form-control" id="materialGroupSelect">
                            <option value="">-- All Material Groups --</option>
                            @foreach($materialGroups as $group)
                                <option value="{{$group->id}}"{{ (!empty($selectedGroupId) && $selectedGroupId == $group->id) ? ' selected' : '' }}>{{$group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
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
                        <input type="text" id="query" class="form-control" placeholder="Search name, brand, or packaging..."/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 text-end">
                        <button id="resetBtn" class="btn btn-secondary">Reset</button>
                        <button id="searchBtn" class="btn btn-primary">Search</button>
                        <button id="createBtn" class="btn btn-warning">Create</button>
                        <a href="/master_data/material/items/maintenance" class="btn btn-outline-info">Maintenance</a>
                        <button id="printBtn" class="btn btn-secondary">Print</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container" id="list"></div>

    <div class="row">
        <div class="col-lg-12">
            <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q,Template,$el} from '/adarna.js';

    const list                = $q('#list').first();
    const materialGroupSelect = $q('#materialGroupSelect').first();
    const query               = $q('#query').first();
    const searchBtn           = $q('#searchBtn').first();
    const resetBtn            = $q('#resetBtn').first();
    const showMoreBtn         = $q('#showMoreBtn').first();
    const sortSelect          = $q('#sortSelect').first();
    const createBtn           = $q('#createBtn').first();
    const printBtn            = $q('#printBtn').first();
    
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
                let title = (item.formatted_name ? item.formatted_name : (item.brand + ' ' + item.name + ' ' + (item.specification_unit_packaging || ''))).trim();
                t.div({class:'item-header'}, title);
                if(item.group && item.group.name){
                    t.div({class:'item-body mt-1'}, ()=>{
                        t.span({class:'badge bg-secondary'}, item.group.name);
                    });
                }
            });

            row.onclick = ()=>{
                 window.util.navTo('/master_data/material/item/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        let params = {
            query: query.value,
            page: page,
            order: order,
            order_by: orderBy,
            limit: 10
        };

        if(materialGroupSelect.value){
            params.material_group_id = materialGroupSelect.value;
        }

        window.util.$get('/api/material/item/list', params).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

            page++;

            if(reply.data.length){
                renderRows(reply.data); 
                if(reply.data.length < 10){
                    showMoreBtn.style.display = 'none';
                }
            }else{
                showMoreBtn.style.display = 'none';
                if(!list.children.length){
                    let emptyMsg = t.div({class:'text-center text-muted py-4 fst-italic'}, 'No material items found.');
                    $el.append(emptyMsg).to(list);
                }
            }
            
        });
    }
   
    searchBtn.onclick = ()=>{
        showMoreBtn.style.display = 'block';
        reinitalize();
        showData();
    };

    resetBtn.onclick = ()=>{
        materialGroupSelect.value = '';
        query.value = '';
        sortSelect.value = '3';
        order = 'DESC';
        orderBy = 'id';
        showMoreBtn.style.display = 'block';
        reinitalize();
        showData();
    };

    materialGroupSelect.onchange = ()=>{
        showMoreBtn.style.display = 'block';
        reinitalize();
        showData();
    };

    query.onkeyup = (e)=>{
        if(e.key === 'Enter' || e.keyCode === 13){
            showMoreBtn.style.display = 'block';
            reinitalize();
            showData();
        }
    };

    showMoreBtn.onclick = ()=>{
        showData();
    };

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
    };

    createBtn.onclick = ()=>{
        let url = '/master_data/material/item/create';
        if (materialGroupSelect.value) {
            url += '?material_group_id=' + materialGroupSelect.value;
        }
        window.util.navTo(url);
    };

    printBtn.onclick = ()=>{
        let params = new URLSearchParams();
        if (materialGroupSelect.value) {
            params.append('material_group_id', materialGroupSelect.value);
        }
        if (query.value.trim()) {
            params.append('query', query.value.trim());
        }
        if (order) {
            params.append('order', order);
        }
        if (orderBy) {
            params.append('order_by', orderBy);
        }

        window.open('/master_data/material/items/print?' + params.toString(), '_blank');
    };

    reinitalize();
    showData();
</script>
</div>
@endsection
