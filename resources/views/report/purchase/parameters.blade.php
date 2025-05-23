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
                        Purchase
                        </span>                    
                        <i class="ms-2 bi bi-list-ul"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container mb-3">
            <div class="folder-form-tab">
                Purchase Report
            </div>
            <div class="folder-form-body">
                <div class="row mb-3">
                    <div class="col-lg-6 mb-3">
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
                    <div class="col-lg-6 mb-3">
                        <div class="form-group">
                            <label>Section</label>
                            <select class="form-select" id="section"></select>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg-6 mb-3">
                        <div class="form-group">
                            <label>Contract Item</label>
                            <select class="form-select" id="contract_item"></select>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
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
                    <div class="col-lg-12 mb-3">
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
                </div> <!-- div row -->


                <div class="row mb-3">
                    <div class="col-lg-12 mb-3">
                        <div class="form-group">
                            <label>Selected Material</label>
                            <div class="text-end mb-3">
                                <button id="allBtn" class="btn btn-secondary">
                                    All
                                    <i id="check_icon_on" class="bi bi-check-square-fill"></i>
                                    <i id="check_icon_off" class="bi bi-check-square d-none"></i>
                                </button>
                            </div>
                            <ul class="list-group border border-secondary" id="material_item_list">
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-12 text-end">
                        <button id="print_btn" class="btn btn-warning me-3">Print</button>
                        <button id="submit_btn" class="btn btn-primary m3-3">Generate</button>
                        <button id="cancel_btn" class="btn btn-secondary">Cancel</button>
                    </div>
                </div>
            </div><!-- div folder-form-body-->
        </div>
    </div>

    <script type="module">
        import {$q,Template} from '/adarna.js';
     
        const material_group        = $q('#material_group').first();
        const project               = $q('#project').first();
        const section               = $q('#section').first();
        const contract_item         = $q('#contract_item').first();
        const component             = $q('#component').first();
        const from                  = $q('#from').first();
        const to                    = $q('#to').first();
        const material_item_list    = $q('#material_item_list').first();
        const all_btn               = $q('#allBtn').first();
        const check_icon_on         = $q('#check_icon_on').first();
        const check_icon_off        = $q('#check_icon_off').first();
        const submit_btn            = $q('#submit_btn').first();
        const cancel_btn            = $q('#cancel_btn').first();
        const print_btn             = $q('#print_btn').first();

        const t = new Template();

        const date_config = {
            autohide:true,
        };

        let from_dp = new window.util.Datepicker(from, date_config); 

        let to_dp = new window.util.Datepicker(to, date_config); 

        let check_all_flag = true;
    
        function reset(){
            check_all_flag = true;
            check_icon_on.classList.remove('d-none');
            check_icon_off.classList.add('d-none');
        }

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
       
        material_group.onchange = ()=>{
            
            material_item_list.innerHTML = '';


            if(material_group.value == '') return false;

            window.util.blockUI();

            window.util.$get('/api/material/item/list',{
                'material_group_id': material_group.value
            }).then(reply=>{
                window.util.unblockUI();

                if(reply.status <= 0){
                    window.util.showMsg(reply);
                    return false;
                }


                reply.data.map(item=>{

                    material_item_list.append(
                        t.li({class:'list-group-item'},()=>{
                            t.div({class:'form-check form-check-inline'},(el)=>{
                                t.input({class:'material-item  form-check-input',type:'checkbox',value:item.id,checked:true})
                                t.label({class:'form-check-label'},item.brand+' '+item.name+' '+item.specification_unit_packaging)
                            })
                        })
                    );//append
                });

                reset();
            });
        }//material_group


        all_btn.onclick = ()=>{

            $q('.material-item').apply(item=>{

                if(check_all_flag){
                    item.checked    = false;
                    
                }else{
                    item.checked    = true;
                }
                
            });


            if(check_all_flag){
                check_all_flag  = false;
                check_icon_off.classList.remove('d-none');
                check_icon_on.classList.add('d-none');
                
            }else{
                check_all_flag  = true;
                check_icon_on.classList.remove('d-none');
                check_icon_off.classList.add('d-none');
                
            }
        }


        submit_btn.onclick = ()=>{

            let material_item_arr = [];

            $q('.material-item').items().map(item=>{

                if(item.checked == true){
                    material_item_arr.push(item.value);
                }
            });

            let query = new URLSearchParams({
                material_items      : material_item_arr.join(','),
                material_group_id   : material_group.value,
                project_id          : project.value,
                section_id          : section.value,
                contract_item_id    : contract_item.value,
                component_id        : component.value
            });

            let from_val  = from_dp.getDate('yyyy-mm-dd') ?? '';
            let to_val    = to_dp.getDate('yyyy-mm-dd') ?? '';
            
            if(from_val){
                query.append('from',from_val);
            }

            if(to_val){
                query>append('to',to_val);
            }

            window.open('/report/purchase/generate?'+query,'_blank').focus();
        }


        print_btn.onclick = ()=>{

            let material_item_arr = [];

            $q('.material-item').items().map(item=>{

                if(item.checked == true){
                    material_item_arr.push(item.value);
                }
            });

            let query = new URLSearchParams({
                material_items      : material_item_arr.join(','),
                material_group_id   : material_group.value,
                project_id          : project.value,
                section_id          : section.value,
                contract_item_id    : contract_item.value,
                component_id        : component.value
            });

            let from_val  = from_dp.getDate('yyyy-mm-dd') ?? '';
            let to_val    = to_dp.getDate('yyyy-mm-dd') ?? '';

            if(from_val){
                query.append('from',from_val);
            }

            if(to_val){
                query>append('to',to_val);
            }

            window.open('/report/purchase/print?'+query,'_blank').focus();
        }
    </script>
</div>
@endsection