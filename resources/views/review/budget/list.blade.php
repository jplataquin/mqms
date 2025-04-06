@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/review/purchase_orders">
                        <span>
                        Review
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Budget
                        </span>
                        <i class="ms-2 bi bi-list-ul"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container">
            <div class="folder-form-tab">
                Review Budget
            </div>
            <div class="folder-form-body">
                
                <div class="row">
                    <div class="col-lg-8">
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
                            <label>&nbsp;</label>
                            <button id="searchBtn" class="btn w-100 btn-primary">Search</button>
                        </div>                
                    </div>   
                </div>

            </div>
        </div>

        <div class="container mb-3" id="list"></div>
    
        <div class="row">
            <div class="col-lg-12">
                <button id="showMoreBtn" class="btn w-100 btn-primary d-none">Show More</button>
            </div>
        </div>
    </div>

    <script type="module">
        import {$q,$el,Template} from '/adarna.js';

        const projectSelect = $q('#projectSelect').first();
        const searchBtn     = $q('#searchBtn').first();
        const showMoreBtn   = $q('#showMoreBtn').first();
        const list          = $q('#list').first();


        projectSelect.onchange = ()=>{    
            reinitalize();
            showData();
        };

        searchBtn.onclick = ()=>{
            reinitalize();
            showData();
        };

         /**** LIST ****/
        let page            = 1;
        let order           = 'DESC';
        let orderBy         = 'id';
        
        const t = new Template();
        
        function reinitalize(){
            page = 1;
            $el.clear(list);  
            showMoreBtn.classList.remove('d-none'); 
        }

        function renderRows(data){
            
            data.map(item=>{

                let row = t.div({class:'item-container fade-in'},()=>{ 
                    t.div({class:'item-header'},item.name);
                    t.div({class:'item-body'});
                });

                row.onclick = ()=>{
                    window.util.navTo('/project/section/'+item.id);
                };

                $el.append(row).to(list);
                
            });

        }

        function showData(){

            window.util.blockUI();

            window.util.$get('/api/section/list',{
                project_id: projectSelect.value,
                query: '',
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
                    showMoreBtn.classList.add('d-none'); 
                }
                
            });
        }
    

        showMoreBtn.onclick = ()=>{
            showData();
        }


    </script>
</div>
@endsection