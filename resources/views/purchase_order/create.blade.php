@extends('layouts.app')

@section('content')
<div id="content">

    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/purchase_orders">
                        <span>
                        Purchase Order
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            Create
                        </span>	
                        <i class="ms-2 bi bi-file-earmark-plus"></i>	
                    </a>
                </li>
            </ul>
        </div>
        <hr>
        <table class="table">
            <tbody>
                <tr>
                    <th>Material Quantity Request ID</th>
                    <td>{{$material_quantity_request->id}}</td>
                </tr>
                <tr>
                    <th>Project</th>
                    <td>{{$project->name}}</td>
                </tr>
                <tr>
                    <th>Section</th>
                    <td>{{$section->name}}</td>
                </tr>
                <tr>
                    <th>Component</th>
                    <td>{{$component->name}}</td>
                </tr>
                
                <tr>
                    <th>Description</th>
                    <td>
                        <textarea disabled="true" class="w-100" id="description">{{$material_quantity_request->description}}</textarea>
                    </td>
                </tr>
            </tbody>
        </table>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Supplier</label>
                        <select id="supplier" class="form-control">
                            <option value=""> - </option>
                            @foreach($supplier_options as $supplier)
                                <option value="{{$supplier->id}}">{{$supplier->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Payment Terms</label>
                        <select id="payment_term" class="form-control">
                            <option value=""> - </option>
                        </select>
                    </div>
                </div>
            </div>
            <hr>
            <div id="item_container"></div>
            
            <div class="d-flex justify-content-end">
                <table class="table w-50 table-border">
                    <tr>
                        <td>
                            <input type="text" disabled="true" value="Sub Total" class="form-control"/>
                        </td>
                        <td>
                            <input type="text" id="sub_total" disabled="true" value="0" class="form-control"/>
                        </td>
                    </tr>
                    <tr class="extra">
                        <td>
                            <input type="text" class="extra_text form-control"/>
                        </td>
                        <td>
                            <input type="text" class="extra_val form-control" onkeypress="return window.util.inputNumber(this,event,2,true)"/>
                        </td>
                    </tr>
                    <tr class="extra">
                        <td>
                            <input type="text" class="extra_text form-control" />
                        </td>
                        <td>
                            <input type="text" class="extra_val form-control" onkeypress="return window.util.inputNumber(this,event,2,true)"/>
                        </td>
                    </tr>
                    <tr class="extra">
                        <td>
                            <input type="text" class="extra_text form-control"/>
                        </td>
                        <td>
                            <input type="text" class="extra_val form-control" onkeypress="return window.util.inputNumber(this,event,2,true)"/>
                        </td>
                    </tr>
                    <tr class="extra">
                        <td>
                            <input type="text" class="extra_text form-control"/>
                        </td>
                        <td>
                            <input type="text" class="extra_val form-control" onkeypress="return window.util.inputNumber(this,event,2,true)"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" value="Grand Total" disabled="true" class="form-control"/>
                        </td>
                        <td>
                            <input type="text" id="grand_total" disabled="true" class="form-control"/>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="row mt-5">
                <div class="col-12 text-end">
                    <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
                    <button id="createBtn" class="btn btn-primary">Create</button>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import {$q,$el,Template} from '/adarna.js';
        
        let supplier        = $q('#supplier').first();
        let payment_term    = $q('#payment_term').first();
        let item_container  = $q('#item_container').first();
        let sub_total       = $q('#sub_total').first();
        let cancelBtn       = $q('#cancelBtn').first();
        let createBtn       = $q('#createBtn').first();

        let data                    = @json($purchase_options);
        let payment_terms_options   = @json($payment_terms_options);
        let component_item_arr      = @json($component_item_arr);

        conosole.log(supplier);
        const t = new Template();

        function clearItems(){
            item_container.innerHTML = '';
        }

        function validate(){
            return true;
        }

        function calculateSubTotal(){
            
            let sub_total_value = 0;

            $q('.line_total').items().map(item=>{
                sub_total_value = sub_total_value + parseFloat( item.getAttribute('data-value') );
            });

            if(isNaN(sub_total_value)){

                sub_total.value = 0;
                return false;
            }
            
            sub_total.value = sub_total_value;

            calculateGrandTotal();
        }
        

        function calculateGrandTotal(){

            let extras = 0;
            
            $q('.extra_val').items().map(item=>{
                
                let item_val = 0
                
                item_val = item.value;
                
                if(item.value == ''){
                    item_val = 0;
                }

                if(isNaN(item.value)){
                    item_val = 0;
                }

                item_val = parseFloat(item_val);
                extras = extras + item_val;

                
            });

            
            let grand_total_value = parseFloat(sub_total.value) + extras;

            if(isNaN(grand_total_value)){

                grand_total.value = 0;
                return false;
            }

            grand_total.value = grand_total_value;
                
        }

        $q('.extra_val').apply(el=>{

            el.onkeyup = ()=>{

                calculateGrandTotal();
            }
        });

        supplier.onchange = (e)=>{
            
            let supplier_id = supplier.value;

            if(supplier_id == ''){
                clearItems();
                return false;
            }

            if(typeof data[supplier_id] == 'undefined'){
                window.util.showMsg('Supplier not found');
                clearItems();
                return false;
            }

            let supplierData         = data[supplier_id];
            let payment_terms_ids    = Object.keys(supplierData);

            clearItems();
            
            payment_term.innerHTML = '';

            payment_term.appendChild(
                t.option({value:''},'-')
            );

            payment_terms_ids.map(id=>{

                payment_term.appendChild(
                    t.option({value:id},payment_terms_options[id].text)
                );
            });

            console.log(payment_term);
        }

        payment_term.onchange = ()=>{
            let component_items = data[supplier.value][payment_term.value];

            clearItems();

            for(let key in component_items){

                let items = component_items[key];
                
                item_container.append(t.h3({class:'mb-3'},component_item_arr[key].name));

                items.map(item=>{


                    let order_quantity = t.input({
                        class:'form-control order-quantity',
                        value:0,
                        onkeypress:"return window.util.inputNumber(this,event,2,true)"
                    });
                    

                    let total = t.input({class:'form-control line_total',value:0,dataValue:0,disabled:true});
                    
                    

                    order_quantity.onkeyup = ()=>{
                    
                        let val = parseFloat(order_quantity.value) * parseFloat(item.price);

                        
                        if(isNaN(val)){
                            val = 0;
                        }

                        total.value = val;

                        total.setAttribute('data-value',val);
                        
                        calculateSubTotal();
                    }

                    let remainingBalnce = t.input({class:'form-control',value:'',disabled:true});

                    let total_ordered_el =  t.div({class:'col-2'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Total Ordered')
                            let total_ordered_input = t.input({class:'form-control',value:'',disabled:true});

                            updateTotalOrdered(
                                total_ordered_input,
                                remainingBalnce,
                                item.material_item.id,
                                item.material_quantity_request_item_id,
                                item.requested_quantity
                            );
                        });
                    });

                    item_container.appendChild(
                        
                        t.div({class:'item row mb-3',
                            dataMaterialQuantityRequestItemId: item.material_quantity_request_item_id,
                            dataMaterialCanvasId: item.material_canvass_id,
                            dataMaterialItemId: item.material_item.id,
                            dataPrice: item.price,
                            dataComponentItemId: key
                        },(el)=>{
                            t.div({class:'col-2'},()=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Material')
                                    t.input({class:'form-control',disabled:true,value:item.material_item.brand+' '+item.material_item.name+' '+item.material_item.specification_unit_packaging+''.trim()})
                                });
                            });
        
                            t.div({class:'col-2'},()=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Price')
                                    t.input({class:'form-control',value:item.price,disabled:true})
                                });
                            });
        
                            
                            
                            el.appendChild(total_ordered_el);
                            
                            t.div({class:'col-2'},(el)=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Remaining Balance')
                                    el.appendChild(remainingBalnce);
                                });
                            });
                            
                            t.div({class:'col-2'},(el)=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Order Quantity')
                                    el.append(order_quantity);
                                });
                            });
                            
                            t.div({class:'col-2'},(el)=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Total')
                                    el.append(total);
                                });
                            });
                        })
                    )//appendChild

                });
                
            };
        }

        cancelBtn.onclick = (e)=>{
            
            window.location.href = '/home';
        }


        function getData(){

            let itemData        = [];
            let extrasData      = [];

            $q('.item').items().map(item=>{

                let componentItemId                 = item.getAttribute('data-component-item-id');
                let materialQuantityRequestItemId   = item.getAttribute('data-material-quantity-request-item-id');
                let materialCanvasId                = item.getAttribute('data-material-canvas-id');
                let materialItemId                  = item.getAttribute('data-material-item-id');
                let price                           = item.getAttribute('data-price');
                let orderQuantity                   = item.querySelector('.order-quantity').value;

                if(orderQuantity != 0 && orderQuantity != ''){
                    itemData.push({
                        component_item_id                   : componentItemId,
                        material_quantity_request_item_id   : materialQuantityRequestItemId,
                        material_canvass_id                 : materialCanvasId,
                        material_item_id                    : materialItemId,
                        price                               : price,
                        order_quantity                      : orderQuantity
                    });
                }
            });


            $q('.extra').items().map(item=>{

                let text    = item.querySelector('.extra_text').value.trim();
                let val     = item.querySelector('.extra_val').value.trim();


                if(text != '' && val != '' && !isNaN(val)){
                    extrasData.push({
                        text: text,
                        value: parseFloat(val).toFixed(2)
                    });
                }
            });

            window.util.blockUI();

            window.util.$post('/api/purchase_order/create',{
                items                           : JSON.stringify(itemData),
                extras                          : JSON.stringify(extrasData),
                supplier_id                     : supplier.value,
                payment_term_id                 : payment_term.value,
                material_quantity_request_id    : '{{$material_quantity_request->id}}',
                project_id                      : '{{$project->id}}',
                section_id                      : '{{$section->id}}',
                component_id                    : '{{$component->id}}'
            }).then(reply=>{

                window.util.unblockUI();

                if(reply.status <= 0){
                    window.util.showMsg(reply);
                    return false;
                }

                window.util.navTo('/purchase_order/'+reply.data.id);
            });
        }

        createBtn.onclick = (e)=>{

            if(!validate()){
                return false;
            }
            
            let data = getData();
        }

        cancelBtn.onclick = (e)=>{
            window.util.navTo('/purchase_orders');
        }


        function updateTotalOrdered(
            inputEl,
            remainingBalanceEl,
            material_item_id,
            material_quantity_request_item_id,
            requested_quantity
        ){
            inputEl.value = 'Fetching data...';
            remainingBalanceEl.value = 'Fetching data...';
            
            window.util.$get('/api/purchase_order/total_ordered',{
                material_item_id: material_item_id,
                material_quantity_request_item_id: material_quantity_request_item_id 
            }).then(reply=>{

                if(reply.status <= 0){
                    window.util.showMsg(reply);
                    return false;
                }

                inputEl.value = reply.data.total_ordered + ' / ' +requested_quantity;

                remainingBalanceEl.value = requested_quantity - reply.data.total_ordered;
            });
        }
    </script>
</div>
@endsection