@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<h5>User Roles</h5>
<hr>
    <div class="row">
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
        <div class="col-lg-4">
            <div class="form-group">
                <label>&nbsp;</label>
                <button id="searchBtn" class="btn w-100 btn-primary">Search</button>
            </div>
        </div>
        
    </div>
    <hr>

    <div class="container" id="list">

    </div>

    <div class="row mt-3">
        <div class="col-lg-12">
            <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q,Template,$el} from '/adarna.js';

    let list            = $q('#list').first();
    let query           = $q('#query').first();
    let searchBtn       = $q('#searchBtn').first();
    let showMoreBtn     = $q('#showMoreBtn').first();
    let sortSelect      = $q('#sortSelect').first();
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
                t.div({class:'item-header'},item.name+' ['+item.email+']' );
            });

            row.onclick = ()=>{
                window.util.navTo('/user_role/'+item.id);
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