@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/purchase_orders">
                    <span>
                       Purchase Order
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
                Purchase Orders
            </div>
            <div class="folder-form-body">
                <div class="row mb-3">
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
                                <option value="DRFT">Draft</option>
                                <option value="PEND">Pending</option>
                                <option value="APRV">Approved</option>
                                <option value="DPRV">Disapproved</option>
                                <option value="VOID">Void</option>     
                            </select>
                        </div>
                    </div>
                        
                </div>

                <div class="row mb-3">
                        
                    <div class="col-lg-6">
                        
                        <div class="form-group">
                            <label>Project</label>
                            <select class="form-select" id="projectSelect">
                                <option value=""> - </option>
                                @foreach($projects as $project)
                                    <option value="{{$project->id}}">{{$project->name}}</option>
                                @endforeach
                            </select>
                            
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Section</label>
                            <select class="form-select" id="sectionSelect">
                            </select>
                        </div>
                    </div>
                </div>


                <div class="row">

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Contract Item</label>
                            <select class="form-select" id="contractItemSelect">
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Component</label>
                            <select class="form-select" id="componentSelect">
                            </select>
                        </div>
                    </div>

                        
                </div>

                <div class="row">
                    <div class="col-lg-12 mt-3 mb-3">
                        <div class="form-group">
                            <label>Contains</label>
                            <select class="form-select" id="materialSelect">
                                <option value=""> - </option>
                                @foreach($material_items as $material_item)
                                    <option value="{{$material_item->id}}">{{$material_item->formattedName}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                        <div class="col-lg-12 text-end">    
                            
                                
                                <button id="createBtn" class="btn btn-warning">Create</button>

                                <button id="searchBtn" class="btn btn-primary">Search</button>

                                <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
                    
                        </div> 
                </div>

            </div>
    </div>

    <table class="table border">
        <thead>
            <th>Project</th>
            <th>PO #</th>
            <th>Status</th>
            <th>Date Created</th>
        <thead>
        <tbody id="list">
        </tbody>
    </table>

    <div class="row mt-3">
        <div class="col-lg-12">
            <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
        </div>
    </div>

</div>
<script type="module">
    import {$q,Template,$el} from '/adarna.js';

    const list                  = $q('#list').first();
    const query                 = $q('#query').first();
    const searchBtn             = $q('#searchBtn').first();
    const createBtn             = $q('#createBtn').first();
    const showMoreBtn           = $q('#showMoreBtn').first();
    const sortSelect            = $q('#sortSelect').first();
    const projectSelect         = $q('#projectSelect').first();
    const sectionSelect         = $q('#sectionSelect').first();
    const componentSelect       = $q('#componentSelect').first();
    const contractItemSelect    = $q('#contractItemSelect').first();
    const statusSelect          = $q('#statusSelect').first();
    const materialSelect        = $q('#materialSelect').first();

    window.util.quickNav = {
        title:'Purchase Order',
        url:'/purchase_order'
    };

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
                t.td({},()=>{
                    t.txt(item.project.name);
                });
                t.td({},()=>{
                    t.txt(item.id);
                });
                t.td({},()=>{
                    t.txt(item.status);
                });
                t.td({},()=>{
                    t.txt(item.created_at);
                });
            });

            row.onclick = ()=>{
                window.util.navTo('/purchase_order/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/purchase_order/list',{
            query               : query.value,
            page                : page,
            order               : order,
            order_by            : orderBy,
            project_id          : projectSelect.value,
            section_id          : sectionSelect.value,
            component_id        : componentSelect.value,
            status              : statusSelect.value,
            material_item_id    : materialSelect.value,   
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


    projectSelect.onchange = (e)=>{

        e.preventDefault();

        sectionSelect.innerHTML         = '';
        contractItemSelect.innerHTML    = '';
        componentSelect.innerHTML       = '';

        window.util.blockUI();

        window.util.$get('/api/section/list',{
            project_id: projectSelect.value,
            orderBy:'name',
            order:'ASC'
        }).then(reply=>{
            
           

            if(reply.status <= 0){

                window.util.unblockUI();
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

            searchBtn.onclick();
        });
    }

    sectionSelect.onchange = (e)=>{

        e.preventDefault();

        contractItemSelect.innerHTML    = '';
        componentSelect.innerHTML       = '';

        window.util.blockUI();

        window.util.$get('/api/contract_item/list',{
            section_id: sectionSelect.value,
            orderBy:'code',
            order:'ASC'
        }).then(reply=>{
            
            

            if(reply.status <= 0){
                window.util.unblockUI();
                window.util.showMsg(reply);
                return false;
            }

            contractItemSelect.append(
                t.option({value:''},' - ')
            );

            reply.data.forEach((item)=>{

                contractItemSelect.append(
                    t.option({value:item.id},item.name)
                );

            });

            searchBtn.onclick();
        });
    }               
    
    createBtn.onclick = ()=>{
       window.util.navTo('/purchase_order/create/select');
    }
    
    reinitalize();
    showData();
</script>
</div>
@endsection