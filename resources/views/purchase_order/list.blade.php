@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs">
        <ul>
            <li>
                <a href="#">
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
                                <option value="VOID">Void</option>     
                            </select>
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
                        <div class="col-lg-12 text-end">    
                            
                                <button id="searchBtn" class="btn btn-primary">Search</button>
                                
                                <button id="createBtn" class="btn btn-warning">Create</button>

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

    let list            = $q('#list').first();
    let query           = $q('#query').first();
    let searchBtn       = $q('#searchBtn').first();
    let createBtn       = $q('#createBtn').first();
    let showMoreBtn     = $q('#showMoreBtn').first();
    let sortSelect      = $q('#sortSelect').first();
    let projectSelect   = $q('#projectSelect').first();
    let sectionSelect   = $q('#sectionSelect').first();
    let componentSelect = $q('#componentSelect').first();
    let statusSelect    = $q('#statusSelect').first();

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
            query: query.value,
            page: page,
            order: order,
            order_by: orderBy,
            project_id: projectSelect.value,
            section_id: sectionSelect.value,
            component_id: componentSelect.value,
            status: statusSelect.value,
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
    
    createBtn.onclick = ()=>{
       window.util.navTo('/purchase_order/create/select');
    }
    
    reinitalize();
    showData();
</script>
</div>
@endsection