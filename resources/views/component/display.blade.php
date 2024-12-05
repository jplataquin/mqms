@extends('layouts.app')

@section('content')
<div id="content">

<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            
            <li>
                <a href="/project/section/contract_item/{{$component->contract_item_id}}">
                    <span>
                        Contract Item
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Component 
                    </span>
                    <i class="ms-2 bi bi-display"></i>
                </a>
            </li>
        </ul>
    </div>
<hr>

        <table class="record-table-horizontal">
            <tr>
                <th>Project</th>
                <td>{{$project->name}}</td>
            </tr>
            <tr>
                <th>Section</th>
                <td>{{$section->name}}</td>
            </tr>
            <tr id="component_sticky_trigger">
                <th>Contract Item</th>
                <td>{{$contract_item->item_code}} - {{$contract_item->description}}</td>
            </tr>
                
        </table> 
        <hr>   

    <!-- style="position:sticky;top:45px;background-color:#ffffff;z-index:900" -->
    
    
    <div class="form-container" id="component_form" >
        <div class="form-header">
            Component
        </div>
        <div class="form-body">
            
          
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Name</label>
                        <input class="form-control editable_field" type="text" id="component" value="{{$component->name}}" disabled="true"/>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Status</label>
                        <input class="form-control" type="text" id="status" value="{{$component->status}}" disabled="true"/>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input class="form-control editable_field" type="text" id="component_quantity" value="{{$component->quantity}}" disabled="true"/>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Unit</label>   
                        <select id="component_unit" class="form-control editable_field" disabled="true">
                            @foreach($unit_options as $opt)
                                <option value="{{$opt->id}}" @if($component->unit_id == $opt->id) selected @endif @if($opt->deleted) disabled="true" @endif>{{$opt->text}} @if($opt->deleted) [Deleted] @endif</option>
                            @endforeach
                        </select>         
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                    <label>Use Count</label>
                    <input class="form-control editable_field" type="text" id="use_count" value="{{$component->use_count}}" disabled="true"/>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Sum Flag</label>
                        <div class="form-switch text-center">
                            <input type="checkbox" class="form-check-input editable_field" id="component_sum_flag" value="1"  disabled="true" @if($component->sum_flag == 1) checked @endif/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-none form-container" id="item_sticky_container">
                <div class="form-header">Item</div>
                <div class="form-body">
                    <div class="row mb-3">
                        
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" id="item_name" class="form-control" disabled="true"/>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Sum Flag</label>
                            
                                <div class="form-switch text-center">
                                    <input type="checkbox" id="item_sum_flag" class="form-check-input" disabled="true"/>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Function Type</label>
                                <input type="text" disabled="true" id="item_function_type" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Variable</label>
                                <input type="text" disabled="true" id="item_variable" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="text" disabled="true" id="item_quantity" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Equivalent</label>
                                <input type="text" disabled="true" id="item_equivalent" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Unit</label>
                                <input type="text" disabled="true" id="item_unit" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Budget Price</label>
                                <input type="text" disabled="true" id="item_budget_price" class="form-control"/>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="row" id="component_controls">
                <div class="col-lg-6">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                </div>
                <div class="col-lg-6 text-end">

                    @if($component->status == 'PEND')
                        <button class="btn btn-outline-primary" id="reviewLinkBtn">
                            Review Link
                            <i class="bi bi-copy"></i>
                        </button>
                    @endif

                    <button class="btn btn-warning" id="printBtn">Print</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>
    

    <hr>

    <!--
    <div class="folder-form-container d-none">
        <div class="folder-form-tab">
            Items
        </div>
        <div class="folder-form-body">
            <div class="row">
                <div class="col-lg-10">
                    <div class="form-group">
                        <label>Name</label>
                        <input id="component_item_name" type="text" class="form-control"/>
                    </div>
                </div>
                
                <div class="col-lg-2 ">
                    <div class="form-group">
                        <label>Sum Flag</label>
                        <div class="form-switch text-center">
                            <input type="checkbox" class="form-check-input" id="component_item_sum_flag" value="1" checked/>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Function Type</label>
                        <select id="component_item_function_type" class="form-control">
                            <option value="3">As Direct</option>
                            <option value="4">As Equivalent</option>
                            <option value="1">As Factor</option>
                            <option value="2">As Divisor</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Variable</label>
                        <input id="component_item_variable" type="text" class="form-control"/>
                    </div>
                </div>
                
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input id="component_item_quantity" type="text" class="form-control" disabled="true"/>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Equivalent</label>
                        <input id="component_item_equivalent" type="text" class="form-control" disabled="true"/>
                    </div>
                </div>
                
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Unit</label>
                        <select id="component_item_unit" class="form-control">
                            @foreach($unit_options as $opt)
                                @if(!$opt->deleted)
                                    <option value="{{$opt->id}}">{{$opt->text}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Price / Unit</label>
                        <input id="component_item_budget_price" type="text" class="form-control"/>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
            <div class="col-lg-4">
                <div class="form-group">
                    <label>POW/DUPA Quantity</label>
                    <input type="text" id="component_item_ref_1_quantity" class="form-control"/>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <label>POW/DUPA Unit</label>
                    <select id="component_item_ref_1_unit" class="form-control">
                        <option value=""> - </option>
                        @foreach($unit_options as $opt)
                            @if(!$opt->deleted)
                            <option value="{{$opt->id}}">{{$opt->text}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <label>POW/DUPA Price</label>
                    <input type="text" id="component_item_ref_1_unit_price" class="form-control"/>
                </div>
            </div>
            </div>
            <div class="row mt-3">
                <div class="col-lg-12 text-end">
                    <button id="createBtn" class="btn btn-warning">Create</button>
                </div>
            </div>
        </div>
    </div>
    -->
    <div class="folder-form-container">
        <div class="folder-form-tab">
            Component Items
        </div>
        <div class="folder-form-body text-end">
            <button class="btn btn-warning">Add</button>
        </div>
    </div>
    <div id="component_item_list" class="mt-3"></div>
</div>

<script type="module">
    import {Template,$q,$el,State,Signal} from '/adarna.js';
    import ComponentItemEl from '/ui_components/ComponentItem.js';

    const materialItemOptions       = @json($materialItems);
    const component                 = $q('#component').first();
    const component_item_list       = $q('#component_item_list').first();
    const editBtn                   = $q('#editBtn').first();
    const cancelBtn                 = $q('#cancelBtn').first();
    const updateBtn                 = $q('#updateBtn').first();
    const createBtn                 = $q('#createBtn').first();
    const deleteBtn                 = $q('#deleteBtn').first();
    const printBtn                  = $q('#printBtn').first();
    const status                    = $q('#status').first();
    const component_quantity        = $q('#component_quantity').first();
    const use_count                 = $q('#use_count').first();
    const component_sum_flag        = $q('#component_sum_flag').first();
    const component_unit            = $q('#component_unit').first();
    const component_sticky_trigger  = $q('#component_sticky_trigger').first();
    const component_controls        = $q('#component_controls').first();
    const component_form            = $q('#component_form').first();

    /***
    const item_sticky_container = $q('#item_sticky_container').first();
    const item_name             = $q('#item_name').first();
    const item_sum_flag         = $q('#item_sum_flag').first();
    const item_function_type    = $q('#item_function_type').first();
    const item_variable         = $q('#item_variable').first();
    const item_quantity         = $q('#item_quantity').first();
    const item_equivalent       = $q('#item_equivalent').first();
    const item_unit             = $q('#item_unit').first();
    const item_budget_price     = $q('#item_budget_price').first();

    const component_item_name               = $q('#component_item_name').first();
    const component_item_budget_price       = $q('#component_item_budget_price').first();
    const component_item_unit               = $q('#component_item_unit').first();
    const component_item_quantity           = $q('#component_item_quantity').first();
    const component_item_equivalent         = $q('#component_item_equivalent').first();
    const component_item_function_type      = $q('#component_item_function_type').first();
    const component_item_variable           = $q('#component_item_variable').first();
    const component_item_sum_flag           = $q('#component_item_sum_flag').first();

    const component_item_ref_1_quantity     = $q('#component_item_ref_1_quantity').first();
    const component_item_ref_1_unit         = $q('#component_item_ref_1_unit').first();
    const component_item_ref_1_unit_price   = $q('#component_item_ref_1_unit_price').first(); 
    ***/
    const reviewLinkBtn                     = $q('#reviewLinkBtn').first();
   
    const t = new Template();

    const signalR = new Signal();
    const signalB = new Signal();

    window.util.quickNav = {
        tile: 'Component',
        url: '/project/section/contract_item/component'
    };
    
    signalR.receiver('set-component-status',(value)=>{
        status.innerHTML = value;
    });

    /****************************** */
    // const sticky_observer = new IntersectionObserver((entries)=>{
    //     for (let entry of entries) {

    //         // Check if the element is intersecting the viewport
    //         if (!entry.isIntersecting) {
    //             component_controls.style.display = 'none';
    //         }else{
    //             component_controls.style.display = '';
    //             item_sticky_container.classList.add('d-none');
    //         }
    //     }
    // });

    // sticky_observer.observe(component_sticky_trigger);


    // window.addEventListener("scroll", window.util.throttle((event) => {


    //     $q('.component_item_sticky_trigger').items().map(item=>{
            
            
    //         let item_pos = item.getBoundingClientRect().top + document.documentElement.scrollTop;
    //         let form_pos = component_form.getBoundingClientRect().top + document.documentElement.scrollTop +  component_form.offsetHeight;
            

    //         if(form_pos >= item_pos){

    //             let parent = item.parentElement;
            
    //             let name            = parent.querySelector('.name');
    //             let sum_flag        = parent.querySelector('.sum_flag');
    //             let function_type   = parent.querySelector('.function_type');
    //             let variable        = parent.querySelector('.variable');
    //             let quantity        = parent.querySelector('.quantity');
    //             let equivalent      = parent.querySelector('.equivalent');
    //             let unit            = parent.querySelector('.unit');
    //             let budget_price    = parent.querySelector('.budget_price');
                
    //             item_name.value             = name.value;
    //             item_sum_flag.checked       = sum_flag.checked;
    //             item_function_type.value    = function_type.options[function_type.selectedIndex].innerText;
    //             item_variable.value         = variable.value;
    //             item_quantity.value         = quantity.value;
    //             item_equivalent.value       = equivalent.value;
    //             item_unit.value             = unit.value;
    //             item_budget_price.value     = budget_price.value;
                
                
    //             item_sticky_container.classList.remove('d-none');

    //         }
    //     });

    // },300));
    /******************************** */

    /***
    component_item_variable.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_variable,e,6,false);
    }

    component_item_quantity.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_quantity,e,2,false);
    }

    component_item_function_type.onchange = (e) =>{
        
        switch(component_item_function_type.value){
            case '1': //Right hand factor
            case '2': //Right hand divior
            case '3': //Direct

                    component_item_variable.disabled    = false;
                    component_item_quantity.disabled    = true;
                    component_item_equivalent.value     = '';
                break;

            case '4': //Left hand factor

                    component_item_variable.disabled = false;
                    component_item_quantity.disabled = false;
                break;

        }
        
        
        component_item_variable.onkeyup();
    }

    function updateComponentItemValues(){
        let val = 0;

        switch(component_item_function_type.value){
            case '1': 

                    //Component * Variable
                    //--------------------
                    //use count
                    
                    
                    val = window.util.roundUp(
                        (parseFloat('{{$component->quantity}}') * component_item_variable.value)  / parseInt('{{$component->use_count}}')
                    ,2);

                break;

            case '2': //Right hand divior

                    val = window.util.roundUp( 
                        (parseFloat('{{$component->quantity}}') / component_item_variable.value)  / parseInt('{{$component->use_count}}')
                    ,2);

                break;

            case '3': //Direct

                    val = component_item_variable.value;
                    
                break;
            case '4': //As equivalent factor

                
                val = ( parseFloat(component_item_variable.value) *  parseFloat(component_item_quantity.value) ) * parseInt('{{$component->use_count}}'); 
                
                if(isFinite(val)){
                    component_item_equivalent.value = val+' {{ $unit_options[$component->unit_id]->text }}';
                }else{
                    component_item_equivalent.value = '';
                }
                
                return true;
                
                break;
        }

        if(isFinite(val)){
            component_item_quantity.value = val;
        }else{
            component_item_quantity.value = 0;
        }
        
    }


    component_item_variable.onkeyup = (e)=>{   
        updateComponentItemValues();
    }

    component_item_quantity.onkeyup = (e)=>{   
        updateComponentItemValues();
    }

    component_quantity.onkeypress = (e)=>{
        return window.util.inputNumber(component_quantity,e,6,false);
    }

    component_item_ref_1_quantity.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_ref_1_quantity,e,6,false);
    }

    component_item_ref_1_unit_price.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_ref_1_unit_price,e,2,false);
    }
    **/

    editBtn.onclick = ()=>{

        $q('.editable_field').apply((el)=>{
            el.disabled = false;
        });

        editBtn.classList.add('d-none');
        updateBtn.classList.remove('d-none');

        cancelBtn.onclick = ()=>{
            window.util.navReload();
        }
    }

    cancelBtn.onclick = ()=>{

        @if($back)
            history.back();
        @else
            window.util.navTo('/project/section/contract_item/{{$contract_item->id}}');
        @endif
    }

    updateBtn.onclick = ()=>{

        window.util.blockUI();

        window.util.$post('/api/component/update',{
            id          :'{{$component->id}}',
            name        : component.value,
            quantity    : component_quantity.value,
            unit_id     : component_unit.value,
            use_count   : use_count.value,
            sum_flag    : (component_sum_flag.checked == true) ? 1 : 0
        }).then((reply)=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navReload();
        });
    }
    
    /***
    component_item_budget_price.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_budget_price,e,2,false);
    }

    component_item_quantity.onkeypress = (e)=>{
        return window.util.inputNumber(component_item_quantity,e,2,false);
    }

    createBtn.onclick = ()=>{
        
        window.util.blockUI();

        window.util.$post('/api/component_item/create',{
            component_id                    : '{{$component->id}}',
            name                            : component_item_name.value,
            budget_price                    : component_item_budget_price.value,
            quantity                        : component_item_quantity.value,
            unit_id                         : component_item_unit.value,
            function_type_id                : component_item_function_type.value,
            function_variable               : component_item_variable.value,
            sum_flag                        : component_item_sum_flag.value,
            ref_1_quantity                  : component_item_ref_1_quantity.value,
            ref_1_unit_id                   : component_item_ref_1_unit.value,
            ref_1_unit_price                : component_item_ref_1_unit_price.value
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            //Clear input
            component_item_budget_price.value   = '';
            component_item_name.value           = '';
            component_item_quantity.value       = '';
            component_item_unit.value           = '';
            component_item_function_type.value  = '';
            component_item_variable.value       = '';
            component_sum_flag.checked          = true;

            let item = ComponentItemEl({
                id: reply.data.id,
                component_id:'{{$component->id}}',
                component_quantity: parseFloat('{{$component->quantity}}'),
                component_use_count: parseFloat('{{$component->use_count}}'),
                component_unit_text: '{{$unit_options[$component->unit_id]->text}}',
                materialItemOptions: materialItemOptions,
                unitOptions: @json($unit_options)
            });

            $el.append(item).to(component_item_list);

            item.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
            
            signalB.broadcast('set-component-status','PEND');

            
        });
    }
    ***/
    printBtn.onclick = ()=>{
        window.open( '/project/section/contract_item/component/print/{{$component->id}}','_blank').focus();
    }


    deleteBtn.onclick = ()=>{

        let answer = prompt('Are you sure you want to delete this component? \n If so please type "{{$component->name}}"');

        if(answer != "{{$component->name}}"){
            window.util.alert('Error','Invalid answer');
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/component/delete',{
            id: "{{$component->id}}"
        }).then(reply=>{
            
            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navTo('/project/section/contract_item/{{$contract_item->id}}');
        });
    }


    if(reviewLinkBtn){
        reviewLinkBtn.onclick = async ()=>{
            let test = await window.util.copyToClipboard('{{ url("/review/component/".$contract_item->id."/".$component->id); }}');
            if(test){
                window.util.alert('Clipboard','Review Link for "Component: {{$component->id}}" copied!');
            }else{
                window.util.alert('Clipboard','Failed to copy');
            }
        }
    }

    @foreach($componentItems as $item)

        component_item_list.append(
            ComponentItemEl({
                id:'{{$item->id}}',
                component_id:'{{$component->id}}',
                component_quantity: parseFloat('{{$component->quantity}}'),
                component_use_count: parseFloat('{{$component->use_count}}'),
                component_unit_text: '{{ $unit_options[$component->unit_id]->text }}',
                materialItemOptions: materialItemOptions,
                unitOptions: @json($unit_options),
                component_item_editable: false
            })
        );

    @endforeach
</script>
</div>
@endsection