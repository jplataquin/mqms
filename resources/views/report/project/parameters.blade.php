@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/roles">
                        <span>
                        Report
                        </span>                    
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Project
                        </span>                    
                        <i class="ms-2 bi bi-funnel"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container mb-3">
            <div class="folder-form-tab">
                Parameters
            </div>
            <div class="folder-form-body">
                <div class="row mb-3">
                    <div class="col-lg-3 mb-3">
                        <div class="form-group">
                            <label>Project</label>
                            <select class="form-select" id="project">
                                    <option value=""> - </option>
                                @foreach($projects as $project)
                                    <option value="{{$project->id}}">{{$project->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <div class="form-group">
                            <label>Section</label>
                            <select class="form-select" id="section"></select>
                        </div>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <div class="form-group">
                            <label>Contract Item</label>
                            <select class="form-select" id="contract_item"></select>
                        </div>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <div class="form-group">
                            <label>Component</label>
                            <select class="form-select" id="component"></select>
                        </div>
                    </div>
                </div> <!-- div row -->

                <div class="row mb-3">
                    <div class="col-lg-6 mb-3">
                        <div class="form-group">
                            <label>From</label>
                            <input type="text" class="form-control" id="from" readonly="true"/>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="form-group">
                            <label>To</label>
                            <input type="text" class="form-control" id="to" readonly="true"/>
                        </div>
                    </div>
                </div>


                <div class="row mb-3">
                    <div class="col-lg-12 text-end">
                        <button id="submit_btn" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div><!-- div folder-form-body-->
        </div>
    </div>

    <script type="module">
        import {$q,Template} from '/adarna.js';
//import { Datepicker } from '/datepicker.js'; 

        const project               = $q('#project').first();
        const section               = $q('#section').first();
        const contract_item         = $q('#contract_item').first();
        const component             = $q('#component').first();
        const from                  = $q('#from').first();
        const to                    = $q('#to').first();
        const submit_btn            = $q('#submit_btn').first();
        
        const t = new Template();

        const date_config = {
            autohide:true,
        };

        let from_dp = new window.util.Datepicker(from, date_config); 

        let to_dp = new window.util.Datepicker(to, date_config); 

        let check_all_flag = true;
    
    
        project.onchange = ()=>{

            section.innerHTML           = '';
            contract_item.innerHTML     = '';
            component.innerHTML         = '';

            section.append(
                t.option({value:''},'*')
            );

            contract_item.append(
                t.option({value:''},'*')
            );

            component.append(
                t.option({value:''},'*')
            );

            if(project.value == '') return false;

            window.util.blockUI();

            
            
            window.util.$get('/api/section/list',{
                project_id: project.value
            }).then(reply=>{

                window.util.unblockUI();

                if(reply <= 0){
                    window.util.showMsg(reply);
                    return false;
                } 

                reply.data.map(item=>{

                    section.append(
                        t.option({value:item.id},item.name)
                    )
                });
            });

        }//project


        section.onchange = ()=>{
            contract_item.innerHTML     = '';
            component.innerHTML         = '';

            contract_item.append(
                t.option({value:''},'*')
            );

            component.append(
                t.option({value:''},'*')
            );

            if(section.value == '') return false;

            window.util.blockUI();

           
            
            window.util.$get('/api/contract_item/list',{
                section_id: section.value
            }).then(reply=>{

                window.util.unblockUI();

                if(reply <= 0){
                    window.util.showMsg(reply);
                    return false;
                }

                

                reply.data.map(item=>{

                    contract_item.append(
                        t.option({value:item.id},item.item_code+' '+item.description)
                    )
                });
            });
        }//section


        contract_item.onchange = ()=>{
            component.innerHTML         = '';

            component.append(
                t.option({value:''},'*')
            );

            if(contract_item.value == '') return false;

            window.util.blockUI();

            
            
            window.util.$get('/api/component/list',{
                contract_item_id: contract_item.value
            }).then(reply=>{

                window.util.unblockUI();

                if(reply <= 0){
                    window.util.showMsg(reply);
                    return false;
                }

                reply.data.map(item=>{

                    component.append(
                        t.option({value:item.id},item.name)
                    )
                });
            });
        }//contract_item





        submit_btn.onclick = ()=>{

            let material_item_arr = [];

            $q('.material-item').items().map(item=>{

                if(item.checked == true){
                    material_item_arr.push(item.value);
                }
            });

            let query = new URLSearchParams({
                project_id          : project.value,
                section_id          : section.value,
                contract_item_id    : contract_item.value,
                component_id        : component.value,
                from                : from_dp.getDate('yyyy-mm-dd') ?? '',
                to                  : to_dp.getDate('yyyy-mm-dd') ?? ''
            });

            window.open('/report/project/generate?'+query,'_blank').focus();
        }
    </script>
</div>
@endsection