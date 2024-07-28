@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs">
        <ul>
            <li>
                <a href="#">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span>
                       Material Canvass
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        List
                    </span>		
                </a>
            </li>
        </ul>
    </div>
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
    <div class="row mb-3">
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
            <button id="searchBtn" class="btn w-100 btn-primary">Search</button>
        </div>
        <div class="col-lg-2">
            <button id="cancelBtn" class="btn w-100 btn-secondary">Cancel</button>
        </div>
    </div>

</div>

    <div class="container">
        <table class="table border">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Project</th>
                    <th>Section</th>
                    <th>Component</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody  id="list">
            </tbody>
        </table>

        <div class="row">
            <div class="col-lg-12">
                <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
            </div>
        </div>
    </div>

    


<script type="module">
    import {$q,Template,$el,$util} from '/adarna.js';

    let list            = $q('#list').first();
    let query           = $q('#query').first();
    let projectSelect   = $q('#projectSelect').first();
    let sectionSelect   = $q('#sectionSelect').first();
    let componentSelect = $q('#componentSelect').first();
 
    let searchBtn       = $q('#searchBtn').first();
    let showMoreBtn     = $q('#showMoreBtn').first();
    let sortSelect      = $q('#sortSelect').first();
    let cancelBtn       = $q('#cancelBtn').first();
    let createBtn       = $q('#createBtn').first();
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
                t.td(String(item.id).padStart(6,0));
            
                t.td(item.project.name);
                t.td(item.section.name);
                t.td(item.component.name);
                t.td(
                    $util.dateTime(
                        new Date(item.created_at)
                    ).full()
                );
            });
            

            row.onclick = ()=>{
                window.util.navTo('/review/material_canvass/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();

        window.util.$get('/api/review/material_canvass/list',{
            query: query.value,
            page: page,
            order: order,
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
                order   = 'DESC';
                orderBy = 'created_at';
                break;
            case 2:
                order   = 'ASC';
                orderBy = 'created_at';
            break;
        }

        showData();
    }

    cancelBtn.onclick = (e)=>{
        e.preventDefault();
        window.util.navTo('/home');
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


    showData();
</script>
</div>
@endsection