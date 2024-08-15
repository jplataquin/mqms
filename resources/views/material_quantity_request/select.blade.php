@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/material_quantity_requests">
                    <span>
                       Material Quantity Request
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Select
                    </span>
                    <i class="ms-2 bi bi-ui-checks-grid"></i>		
                </a>
            </li>
        </ul>
    </div>
    <hr>
    
    <div class="row">
        <div class="col-4">
            <div class="form-group">
                <label>Project</label>
                <select id="projectSelect" class="form-control">
                    <option value=""> - </option>
                    @foreach($projects as $project)
                        <option value="{{$project->id}}">{{$project->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                <label>Section</label>
                <select id="sectionSelect" class="form-control"></select>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                <label>Contract Item</label>
                <select id="contractItemSelect" class="form-control"></select>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                <label>Component</label>
                <select id="componentSelect" class="form-control"></select>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-8"></div>
        <div class="col-2">
            <button class="btn w-100 btn-secondary">Cancel</button>
        </div>
        <div class="col-2">
            <button id="createBtn" class="btn w-100 btn-warning">Create</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q,Template} from '/adarna.js';
    
    let projectSelect   = $q('#projectSelect').first();
    let sectionSelect   = $q('#sectionSelect').first();
    let componentSelect = $q('#componentSelect').first();
    let createBtn       = $q('#createBtn').first();
    
    const t = new Template();

    projectSelect.onchange = (e)=>{

        e.preventDefault();

        sectionSelect.innerHTML = '';
        componentSelect.innerHTML = '';

        window.util.blockUI();

        window.util.$get('/api/section/list',{
            project_id: projectSelect.value,
            orderBy:'name',
            status:'ACTV',
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

        });
    }

    sectionSelect.onchange = (e)=>{

        e.preventDefault();

        componentSelect.innerHTML = '';

        window.util.blockUI();

        window.util.$get('/api/component/list',{
            section_id: sectionSelect.value,
            orderBy:'name',
            status:'APRV',
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

    createBtn.onclick = (e)=>{
        e.preventDefault();

        if(projectSelect.value == '' || sectionSelect.value == '' || componentSelect.value == ''){
            window.util.showMsg('All fields are required to create a request');
            return false;
        }
        
        document.location.href = '/material_quantity_request/create/'+projectSelect.value+'/'+sectionSelect.value+'/'+componentSelect.value;
    }
</script>
</div>
@endsection