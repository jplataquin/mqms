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
                        Material Quantity
                        </span>                    
                        <i class="ms-2 bi bi-list-ul"></i>
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
                            <label>Material Group</label>
                            <select class="form-select" id="material_group">
                                <option value=""> - </option>
                                @foreach($material_groups as $material_group)
                                    <option value="{{$material_group->id}}">{{$material_group->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="form-group">
                            <label>Material Item</label>
                            <select class="form-select" id="material_item_select"></select>
                        </div>
                    </div>
                </div> <!-- div row -->
                <div class="row mb-3">
                    <div class="col-lg-12 mb-3">
                        <div class="form-group">
                            <label>Selected Material</label>
                            <div id="material_item_list"></div>
                        </div>
                    </div>
                </div>
            </div><!-- div folder-form-body-->
        </div>
    </div>

    <script type="module">
        import {$q,Template} from '/adarna.js';

        const project               = $q('#project').first();
        const section               = $q('#section').first();
        const contract_item         = $q('#contract_item').first();
        const component             = $q('#component').first();
        const material_group        = $q('#material_group').first();
        const material_item_select  = $q('#material_item_select').first();
        const material_item_list    = $q('#material_item_list').first();
        
        const t = new Template();

        project.onchange = ()=>{
            section.innerHTML           = '';
            contract_item.innerHTML     = '';
            component.innerHTML         = '';

            section.append(
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

        material_group.onchange = ()=>{
            
            material_item_select.innerHTML = '';
            material_item_list.innerHTML = '';

            material_item_select.append(
                t.option({value:''},' - ')
            );

            if(maateria_group.value == '') return false;

            window.util.blockUI();

            window.util.$get('/api/material_item/list',{
                'material_group_id': material_group.value
            }).then(reply=>{
                window.util.unblockUI();

                if(reply.status <= 0){
                    window.util.showMsg(reply);
                    return false;
                }


                reply.data.map(item=>{

                    material_item_select.append(
                        t.option({value:item.id},item.name)
                    )
                });
            });
        }//material_group


    </script>
</div>
@endsection