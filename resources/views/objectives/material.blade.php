@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/objectives/material">
                        <span>
                        Objectives
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            Material
                        </span>
                        <i class="ms-2 bi bi-display"></i>
                    </a>
                </li>
            </ul>
        </div>
    <hr>


    <div class="folder-form-container">
        <div class="folder-form-tab">
            Filter
        </div>
        <div class="folder-form-body">

            <div class="row">          
                <div class="col-lg-12">
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
            </div>

            <div class="row">
                    
                    <div class="col-6">
                        <div class="form-group">
                            <label>From</label>
                            <input id="from" type="text" class="form-control"/>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>To</label>
                            <input id="to" class="form-control"/>
                        </div>
                    </div>
            </div>

            <div class="row mt-5">
                <div class="col-lg-12 text-end">
                    
                    <button id="searchBtn" class="btn btn-primary">Search</button>
                </div>
            </div>

        </div>
    </div>

    
<script type="module">
    import {$q,$el,Template} from '/adarna.js';
    import '/vanilla-datepicker.js';

    const from              = $q('#from').first();
    const to                = $q('#to').first();
    const projectSelect     = $q('#projectSelect').first();
    
    const datepicker_from = new Datepicker(from, {
       clearButton:true,
       format: { 
            toValue(date,format,local) {
                
                let dateObject = Datepicker.parseDate(from.value, 'M dd, yyyy')
                
                return dateObject
            },
            toDisplay(date) {
                let dateString = Datepicker.formatDate(date, 'M dd, yyyy')
        
                return dateString
            },
        },
        todayHighlight: true,
    }); 

     const datepicker_to = new Datepicker(to, {
        clearButton:true,
        format: { 
            toValue(date,format,local) {
                
                let dateObject = Datepicker.parseDate(to.value, 'M dd, yyyy')
                
                return dateObject
            },
            toDisplay(date) {
                let dateString = Datepicker.formatDate(date, 'M dd, yyyy')
        
                return dateString
            },
        },
        todayHighlight: true,
    }); 

    function showData(){
        window.util.blockUI();

        window.util.$get('/api/objectives/material',{
            project_id: projectSelect.value,
            from: from.value,
            to: to.value
        }).then(reply=>{

            window.util.unblockUI();
            
            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            console.log(reply.data);
        });
    }


    showData();

</script>
</div>
@endsection