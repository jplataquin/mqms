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

    <div class="container" id="list" hx-boost="true" hx-select="#content" hx-target="#main"></div>
    
<script type="module">
    import {$q,$el,Template} from '/adarna.js';
    import '/vanilla-datepicker.js';

    const t = new Template();

    const from              = $q('#from').first();
    const to                = $q('#to').first();
    const projectSelect     = $q('#projectSelect').first();
    const list              = $q('#list').first();
    
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


    function render(result,project){

        for(let project_id in result){
            
            let entry = t.div({class:'border border-primary p-3 mb-5'},()=>{
                t.h4(project[project_id].name);

                
                let group_date = result[project_id];
                
                for(let date_needed in group_date){

                    t.div({class:'ms-3 mb-5 pt'},()=>{

                        t.h6(date_needed);

                        let material_requests = group_date[date_needed];

                        for(let material_request_id in material_requests){
                            
                            let items = material_requests[material_request_id].items;

                            t.div({class:'ms-3 mb-3'},()=>{
                            
                                t.h6(()=>{            
                                    t.span(()=>{
                                        
                                        t.a({href:'/material_quantity_request/'+material_request_id},'MR'+material_request_id);

                                    });

                                });               

                                items.map(item=>{
                                    t.div({class:'ms-3 mt-3'},item);
                                });

                            });
                        }//for
                    });
                    
                
                
                }//for
        

            });

            list.appendChild(entry);
        }//for
    }

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

            render(reply.data.result,reply.data.project_arr);
        });
    }


    showData();

</script>
</div>
@endsection