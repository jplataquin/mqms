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
                        Material Item
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
                        <button id="submit_btn" class="btn btn-primary">Submit</button>
                        <button id="cancel_btn" class="btn btn-secondary">Cancel</button>
                    </div>
                </div>
            </div><!-- div folder-form-body-->
        </div>
    </div>

    <script type="module">
        import {$q,Template} from '/adarna.js';
//import { Datepicker } from '/datepicker.js'; 

     
        const material_group        = $q('#material_group').first();
      
        const material_item_list    = $q('#material_item_list').first();
        const all_btn               = $q('#allBtn').first();
        const check_icon_on         = $q('#check_icon_on').first();
        const check_icon_off        = $q('#check_icon_off').first();
        const submit_btn            = $q('#submit_btn').first();
        const cancel_btn            = $q('#cancel_btn').first();
        
        const t = new Template();


        let check_all_flag = true;
    
        function reset(){
            check_all_flag = true;
            check_icon_on.classList.remove('d-none');
            check_icon_off.classList.add('d-none');
        }

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
                material_items      : material_item_arr.join(',')
            });

            window.open('/report/material_item/generate?'+query,'_blank').focus();
        }
    </script>
</div>
@endsection