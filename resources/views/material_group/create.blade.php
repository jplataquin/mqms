@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs">
        <ul>
            <li>
                <a href="#">
                    <span>
                        Master Data
                    </span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span>
                       Material Group
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Create
                    </span>		
                </a>
            </li>
        </ul>
    <div>
    
    <hr>

    <div class="row">

        <div class="col-12">
            <div class="form-group">
                <label>Material Group Name</label>
                <input type="text" id="materialGroup" class="form-control"/>
            </div>
        </div>

    </div>

    <div class="row mt-5">
        <div class="col-12 text-end">
        <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
        <button class="btn btn-primary" id="createBtn">Create</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    let createBtn       = $q('#createBtn').first();
    let cancelBtn       = $q('#cancelBtn').first();
    let materialGroup   = $q('#materialGroup').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/material/group/create',{
            name: materialGroup.value
        }).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                
                let message = reply.message;

                for(let key in reply.data){
                    let value = reply.data[key];

                    console.log(key,value);
                }
                
                alert(message);
                return false;
            };

            window.util.unblockUI();
      
             window.util.navTo('/master_data/material/group/'+reply.data.id);

        
        });
    }

    cancelBtn.onclick = (e) => {
         window.util.navTo('/master_data/material/groups');
    }

</script>
</div>
@endsection