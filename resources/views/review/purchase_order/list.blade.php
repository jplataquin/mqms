@extends('layouts.app')

@section('content')
<div class="container">
<h5>Review » Purchase Order</h5>
<hr>
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
    <div class="row mb-5">
            <div class="col-lg-8"></div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button id="searchBtn" class="btn w-100 btn-primary">Search</button>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button id="cancelBtn" class="btn w-100 btn-secondary">Cancel</button>
                </div>
            </div>
    </div>

    <div class="container">
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
    let cancelBtn       = $q('#cancelBtn').first();
    let showMoreBtn     = $q('#showMoreBtn').first();
    let sortSelect      = $q('#sortSelect').first();
    let projectSelect   = $q('#projectSelect').first();
    let sectionSelect   = $q('#sectionSelect').first();
    let componentSelect = $q('#componentSelect').first();
   
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
                document.location.href = '/review/purchase_order/'+item.id;
            };


            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/review/purchase_order/list',{
            query: query.value,
            page: page,
            order: order,
            order_by: orderBy,
            project_id: projectSelect.value,
            section_id: sectionSelect.value,
            component_id: componentSelect.value,
            limit: 10
        }).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                
                let message = reply.message;

                
                alert(message);
                return false;
            };

            page++;

            window.util.unblockUI();

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

            if(!reply.status){

                window.util.unblockUI()
                alert(reply.message);
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

            if(!reply.status){

                window.util.unblockUI()
                alert(reply.message);
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

            window.util.unblockUI();
        });
    }

    cancelBtn.onclick = ()=>{
        window.location.href = '/home';
    }

    showData();
</script>

@endsection