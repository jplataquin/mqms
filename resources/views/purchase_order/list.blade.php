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

                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Created By</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="createdByInput" list="createdByList" placeholder="Search user..." autocomplete="off" value="{{ auth()->user() ? auth()->user()->name : '' }}">
                                    <button class="btn btn-outline-secondary" type="button" id="clearCreatedByBtn" title="Clear User"><i class="bi bi-x-lg"></i></button>
                                </div>
                                <datalist id="createdByList">
                                    @foreach($users as $user)
                                        <option value="{{$user->name}}" data-id="{{$user->id}}"></option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" id="createdBySelect" value="{{ auth()->user() ? auth()->user()->id : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 mt-3 mb-3">
                            <div class="form-group">
                                <label>Contains</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="materialInput" list="materialList" placeholder="Search material item..." autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" id="clearMaterialBtn" title="Clear Material"><i class="bi bi-x-lg"></i></button>
                                </div>
                                <datalist id="materialList">
                                    @foreach($material_items as $material_item)
                                        <option value="{{$material_item->formattedName}}" data-id="{{$material_item->id}}"></option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" id="materialSelect" value="">
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

        <div class="table-responsive"> 
            <table class="table border" style="width:150%">
                <thead>
                    <th style="width:300px">Project</th>
                    <th>PO #</th>
                    <th>Status</th>
                    <th>Received Status</th>
                    <th>Date Created</th>
                <thead>
                <tbody id="list">
                </tbody>
            </table>
        </div>

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
        const cancelBtn             = $q('#cancelBtn').first();
        const showMoreBtn           = $q('#showMoreBtn').first();
        const sortSelect            = $q('#sortSelect').first();
        const projectSelect         = $q('#projectSelect').first();
        const sectionSelect         = $q('#sectionSelect').first();
        const componentSelect       = $q('#componentSelect').first();
        const contractItemSelect    = $q('#contractItemSelect').first();
        const statusSelect          = $q('#statusSelect').first();
        const materialInput         = $q('#materialInput').first();
        const materialList          = $q('#materialList').first();
        const materialSelect        = $q('#materialSelect').first();
        const clearMaterialBtn      = $q('#clearMaterialBtn').first();
        const createdByInput        = $q('#createdByInput').first();
        const createdByList         = $q('#createdByList').first();
        const createdBySelect       = $q('#createdBySelect').first();
        const clearCreatedByBtn     = $q('#clearCreatedByBtn').first();

        window.util.quickNav = {
            title:'Purchase Order',
            url:'/purchase_order'
        };

        let page            = 1;
        let order           = 'DESC';
        let orderBy         = 'id';
        
        const t = new Template();

        function findOptionId(input, datalist) {
            const val = input.value.trim().toLowerCase();
            if (!val || !datalist) return '';
            const options = datalist.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value.trim().toLowerCase() === val) {
                    return options[i].getAttribute('data-id') || '';
                }
            }
            return '-1';
        }

        function updateCreatedBy() {
            createdBySelect.value = findOptionId(createdByInput, createdByList);
        }

        function updateMaterial() {
            materialSelect.value = findOptionId(materialInput, materialList);
        }

        if (createdByInput) {
            createdByInput.oninput = updateCreatedBy;
            createdByInput.onchange = updateCreatedBy;
            createdByInput.onkeydown = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    updateCreatedBy();
                    searchBtn.click();
                }
            };
        }

        if (clearCreatedByBtn) {
            clearCreatedByBtn.onclick = () => {
                createdByInput.value = '';
                createdBySelect.value = '';
                createdByInput.focus();
            };
        }

        if (materialInput) {
            materialInput.oninput = updateMaterial;
            materialInput.onchange = updateMaterial;
            materialInput.onkeydown = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    updateMaterial();
                    searchBtn.click();
                }
            };
        }

        if (clearMaterialBtn) {
            clearMaterialBtn.onclick = () => {
                materialInput.value = '';
                materialSelect.value = '';
                materialInput.focus();
            };
        }

        if (cancelBtn) {
            cancelBtn.onclick = () => {
                window.util.navTo('/home');
            };
        }
        
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
                        t.txt(item.received_status || 'PEND');
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
                contract_item_id    : contractItemSelect.value,
                component_id        : componentSelect.value,
                status              : statusSelect.value,
                material_item_id    : materialSelect.value,   
                created_by          : createdBySelect.value,
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
            updateCreatedBy();
            updateMaterial();
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
        

        contractItemSelect.onchange = (e)=>{

            e.preventDefault();

            componentSelect.innerHTML       = '';

            window.util.blockUI();

            window.util.$get('/api/component/list',{
                contract_item_id: contractItemSelect.value,
                orderBy:'name',
                order:'ASC'
            }).then(reply=>{
                
                

                if(reply.status <= 0){
                    window.util.unblockUI();
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

                searchBtn.onclick();
            });
        }

        componentSelect.onchange = ()=>{
            searchBtn.onclick();
        }

        createBtn.onclick = ()=>{
        window.util.navTo('/purchase_order/create/select');
        }
        
        reinitalize();
        showData();
    </script>

</div>
@endsection