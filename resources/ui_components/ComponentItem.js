import {Template,Component,Signal} from '/adarna.js';


function calculateTotalEquivalent(a,b){
    return window.util.roundUp(parseFloat(a) * parseFloat(b),2);
}


const signal = new Signal();

class ComponentItem extends Component{

    state(){
        return {
            quantity: 0,
            unit:'',
            name:'',
            sum_flag:true,
            editable: false,
            function_type_id:'',
            variable:'',
            grand_total:0,
            ref_1_quantity:'',
            ref_1_unit_id:'',
            ref_1_unit_price:''
        }
    }

    model(){
        return {
            id:null,
            component_id:null,
            component_quantity:0,
            component_use_count:1,
            component_unit_text:'',
            materialItemOptions:[],
            unitOptions:[]
        }
    }

    init(){
        this.materialRegistry = {};
    }

    delete(){
        
    }
    
    view(){
        const t = new Template();

        this.el.materialItemSelect = t.select({class:'form-control'},()=>{
            t.option({value:''},' - ');
        });

        this._model.materialItemOptions.map(item=>{
            
            let option = t.option({value:item.id},item.brand+' '+item.name + ' '+item.specification_unit_packaging+''.trim());
            
            this.el.materialItemSelect.t.append(option);

            this.materialRegistry[item.id] = item.brand+' '+item.name +' '+item.specification_unit_packaging+''.trim();
        });

        this.el.materialMenu = t.div(()=>{
    
            t.div({class:'folder-form-container'},()=>{

                t.div({class:'folder-form-tab'},'Material Quantity');
                
                t.div({class:'folder-form-body'},()=>{

                    t.div({class:'row'},()=>{

                        t.div({class:'col-lg-6'},()=>{
                            t.div({class:'form-group'},()=>{
                                t.label('Material');
                                t.el(this.el.materialItemSelect);
                            });
                        });
                    
                        t.div({class:'col-lg-1'},()=>{             
                            t.div({class:'form-group'},()=>{
                                t.label('Quantity');
                                this.el.material_quantity = t.input({class:'form-control', type:'text'});
                            });
                        });                           

                        t.div({class:'col-lg-2'},()=>{
                            t.div({class:'form-group'},()=>{
                                t.label('Equivalent / Unit');
                                this.el.equivalent = t.input({class:'form-control', type:'text'});
                            });
                        });
                        
                        
                        t.div({class:'col-lg-2'},()=>{
                            t.div({class:'form-group'},()=>{
                                t.label('Total');
                                this.el.total = t.input({class:'form-control', type:'number',disabled:true});
                            });
                        });

                    
                        t.div({class:'col-lg-1'},()=>{
                            t.div({class:'form-group'},()=>{
                                t.label('&nbsp');
                                this.el.addBtn = t.button({class:'btn btn-warning w-100'},'Add');
                            });
                        });
 
                    });//row

                });//body
                
            });//container


            t.table({class:'table'},()=>{

                t.thead(()=>{
                    t.th('Material');
                    t.th('Quantity');
                    t.th('Equivalent / Unit');
                    t.th('Total');
                });

                this.el.materialList = t.tbody();

                t.tfoot(()=>{
                    t.tr(()=>{
                        t.td();
                        t.td();
                        t.th('Grand Total');
                        this.el.grandTotal = t.td(()=>{
                            t.txt('0');
                        });
                        t.td();
                    });
                });
            })//table
        });



        return t.div({class:'form-container mb-5'},(el)=>{
            
            t.div({class:'form-header component_item_sticky_untrigger'},'Item');

            t.div({class:'form-body'},()=>{

                this.el.item = t.table({class:'table'},()=>{
                    

                    t.tbody(()=>{
                        t.tr({class:'component_item_sticky_trigger'},()=>{
                            t.th({colspan:4},'Name');
                            t.th({colspan:2},'Sum Flag');
                        });
        
                        t.tr(()=>{
                            t.td({colspan:4},()=>{
                                this.el.name = t.input({class:'form-control name',type:'text', placeholder:'Item',disabled:true,value:'Loading...'}); 
                            });
        
                            t.td({colspan:2},()=>{
                                t.div({class:'form-switch text-center'},()=>{                  
                                    this.el.sum_flag = t.input({class:'form-check-input sum_flag',value:1,type:'checkbox', disabled:true});
                                });
                            });
                            
                        });
        
        
                        t.tr(()=>{
                            t.th('Function Type');
                            t.th('Variable');
                            t.th('Quantity');
                            t.th('Equivalent');
                            t.th('Unit');
                            t.th('Budget Price');
                        })
                        
                        t.tr(()=>{
                            
                            t.td({class:''},(el)=>{
                                
                                this.el.function_type = t.select({class:'form-control function_type',disabled:true},()=>{
                                    t.option({value:3},'As Direct');
                                    t.option({value:4},'As Equivalent');
                                    t.option({value:1},'As Factor');
                                    t.option({value:2},'As Divisor');
                                    
                                });
        
                            });
        
                            t.td({class:''},(el)=>{
                                
                                this.el.variable = t.input({class:'form-control variable', type:'text', placeholder:'Variable',disabled:true,value:'Loading...'});
        
                            });
                        
                            t.td({class:''},(el)=>{
                                
                                this.el.quantity = t.input({class:'form-control quantity', type:'text', placeholder:'Quantity',disabled:true,value:'Loading...'});
        
                            });
        
                            t.td({class:''},(el)=>{
                                
                                this.el.component_item_equivalent = t.input({class:'form-control equivalent', type:'text', placeholder:'Equivalent',disabled:true,value:'Loading...'});
        
                            });
        
                            t.td({class:''},(el)=>{
        
                                this.el.unit = t.select({class:'form-control unit',disabled:true},()=>{
                                    for(let i in this._model.unitOptions){
        
                                        if(this._model.unitOptions[i].deleted){
                                            t.option({value:i,disabled:true},this._model.unitOptions[i].text+' [Deleted]');
                                        }else{
                                            t.option({value:i}, this._model.unitOptions[i].text );
                                        }
                                    }
                                });
            
                            });
        
                            t.td({class:''},(el)=>{
                                
                                this.el.budget_price = t.input({class:'form-control budget_price', type:'text', placeholder:'Budget Price',disabled:true,value:'Loading...'});
                                
                            });
        
        
                        });
        
        
                        t.tr(()=>{
                            t.th({colspan:5,class:'text-end'},'Total Amount');
                            t.td({colspan:1},()=>{
                                this.el.total_amount = t.input({class:'form-control',disabled:true});
                            });
                        });


                        t.tr(()=>{
                            t.th({class:'text-center', colspan:2},'POW/DUPA Quantity');
                            t.th({class:'text-center', colspan:2},'POW/DUPA Unit');
                            t.th({class:'text-center', colspan:4},'POW/DUPA Unit Price');
                        });
                
                        t.tr(()=>{
                            
                            t.td({colspan:2},()=>{
                                this.el.ref_1_quantity = t.input({class:'form-control',disabled:true});
                            });
                
                            t.td({colspan:2},()=>{
                
                                this.el.ref_1_unit_id = t.select({class:'form-control',disabled:true},()=>{
                                    
                                    t.option({value:''},' - ');
                
                                    for(let i in this._model.unitOptions){
                
                                        if(this._model.unitOptions[i].deleted){
                                            t.option({value:i,disabled:true},this._model.unitOptions[i].text+' [Deleted]');
                                        }else{
                                            t.option({value:i}, this._model.unitOptions[i].text );
                                        }
                                       
                                    }
                                });
                
                            });
                
                            t.td({colspan:4},()=>{
                                this.el.ref_1_unit_price = t.input({class:'form-control',disabled:true});
                            });
                        });
        
                    
        
                        t.tr(()=>{
                            t.td({colspan:7, class:'text-end'},(el)=>{
                                
                                this.el.deleteComponentButton = t.button({class:'btn btn-danger me-3',style:{
                                    display: (()=>{
                                        if(this._state.editable == true) return 'none';
                                        if(this._state.editable == false) return 'inline';
                                    })()
                                }},'Delete');
        
                                this.el.editComponentButton = t.button({class:'btn btn-primary',style:{
                                    display: (()=>{
                                        if(this._state.editable == true) return 'none';
                                        if(this._state.editable == false) return 'inline';
                                    })()
                                }},'Edit');
        
                                this.el.cancelEditComponentButton = t.button({class:'btn btn-primary me-3',style:{
                                    display: (()=>{
                                        if(this._state.editable == true) return 'inline';
                                        if(this._state.editable == false) return 'none';
                                    })()
                                }},'Cancel');
        
                                this.el.updateComponentButton = t.button({class:'btn btn-warning',style:{
                                    display: (()=>{
                                        if(this._state.editable == true) return 'inline';
                                        if(this._state.editable == false) return 'none';
                                    })()
                                }},'Update');
                                
                            });
                        });
        
        
                    });//tbody
        
        
                });//table
        
                
                t.div((el)=>{
                    el.append(this.el.materialMenu);
                });

            });//form-body

        });//form-container
    }

    controller(dom){
        
        this.getComponentItemData();

        this.functionVariableQuantity();

        this.el.budget_price.onkeyup = ()=>{    
            this.calculateTotalAmount();
        }

        
        this.el.budget_price.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.budget_price,e,2,false);
        }

        
        this.el.material_quantity.onkeyup = ()=>{
            this.el.total.value = calculateTotalEquivalent( this.el.material_quantity.value, this.el.equivalent.value);
        }
        
        this.el.equivalent.onkeyup = ()=>{
            this.el.material_quantity.value = window.util.roundUp(this.el.quantity.value / this.el.equivalent.value,2);
            this.el.total.value = calculateTotalEquivalent( this.el.material_quantity.value, this.el.equivalent.value);
           
        }

        this.el.material_quantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.material_quantity,e,2,false);
        }

        this.el.ref_1_quantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.ref_1_quantity,e,2,false);
        }

        this.el.ref_1_unit_price.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.ref_1_unit_price,e,2,false);
        }

        this.el.addBtn.onclick = ()=>{

            if(this.el.total.value > this._state.quantity){
                alert('Total equivalent cannot be greater than the component item quantity ('+this._state.quantity+' '+this._state.unit+')');
                return false;
            }

            this.addMaterial();
        }


        this.el.deleteComponentButton.onclick = (e)=>{
            e.preventDefault();

            let answer = prompt('Please confirm by entering "'+this._state.name+'"');

            if(answer != this._state.name){
                window.util.alert('Error','Invalid answer');
                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/component_item/delete',{
                id:this._model.id
            }).then(reply=>{

                window.util.unblockUI();

                if(reply.status <= 0){
                    window.util.showMsg(reply);
                    return false;
                }

                dom.t.remove();
                
                signal.broadcast('set-component-status','PEND');
            });
        }

        this.el.editComponentButton.onclick = (e)=>{
            e.preventDefault();
            this.setState('editable',true);
        }

        this.el.cancelEditComponentButton.onclick = (e)=>{
            this.setState('editable',false);
            this.el.unit.value      = this._state.unit;
            this.el.name.value      = this._state.name;
            this.el.quantity.value  = this._state.quantity;

            this.el.ref_1_quantity.value        = this._state.ref_1_quantity;
            this.el.ref_1_unit_id.value         = this._state.ref_1_unit_id;
            this.el.ref_1_unit_price.value      = this._state.ref_1_unit_price;
            
            if(this._state.sum_flag){
                this.el.sum_flag.checked = true;
            }else{
                this.el.sum_flag.checked = false;
            }

            this.updateComponentItemValues();
        }

        this.el.updateComponentButton.onclick = (e)=>{
           this.httpUpdate();
        }

    }

    calculateTotalAmount(){
  
        this.el.total_amount.value = (new Intl.NumberFormat().format(
            parseFloat(this.el.budget_price.value) * parseFloat(this.el.quantity.value)
        ));
    }

    httpUpdate(){

        window.util.blockUI();
        window.util.$post('/api/component_item/update/',{
            id                      : this._model.id,
            component_id            : this._model.component_id,
            name                    : this.el.name.value,
            budget_price            : this.el.budget_price.value,
            quantity                : this.el.quantity.value,
            unit_id                 : this.el.unit.value,
            function_type_id        : this.el.function_type.value,
            function_variable       : this.el.variable.value,
            sum_flag                : (this.el.sum_flag.checked == true) ? 1 : 0,
            ref_1_quantity          : this.el.ref_1_quantity.value,
            ref_1_unit_id           : this.el.ref_1_unit_id.value,
            ref_1_unit_price        : this.el.ref_1_unit_price.value

        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);

                return false;
            }
              
            this.setState('quantity',parseFloat(this.el.quantity.value));
            this.setState('unit',this.el.unit.value);
            this.setState('name',this.el.name.value);
            this.setState('function_type_id',this.el.function_type.value);
            this.setState('variable',this.el.variable.value);
            this.setState('editable',false);

            this.setState('ref_1_quantity',this.el.ref_1_quantity.value);
            this.setState('ref_1_unit_id',this.el.ref_1_unit_id.value);
            this.setState('ref_1_unit_price',this.el.ref_1_unit_price.value);
            
            this.updateMaterialList();

            signal.broadcast('set-component-status','PEND');
        });
    }

    functionVariableQuantity(){


        this.el.variable.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.variable,e,6,false);
        }

        this.el.quantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.quantity,e,2,false);
        }
    
        this.el.function_type.onchange = (e) =>{
            switch(this.el.function_type.value){
                case '1': //As factor
                case '2': //As Divior
                case '3': //Direct
    
                        this.el.variable.disabled = false;
                        this.el.quantity.disabled = true;
    
                    break;
    
                case '4': //As Equivalent
    
                        this.el.variable.disabled = false;
                        this.el.quantity.disabled = false;
                    break;
    
            }

            this.el.variable.onkeyup();   
        }
    
        this.el.variable.onkeyup = (e)=>{
            this.updateComponentItemValues();
        }

        this.el.quantity.onkeyup = (e)=>{
            this.updateComponentItemValues();
        }
    }


    updateComponentItemValues(){
        
        let val = 0;

        switch(this.el.function_type.value){
            case '1': //As Factor

                    val = window.util.roundUp(
                        (parseFloat(this._model.component_quantity) * this.el.variable.value)  / parseInt(this._model.component_use_count)
                    ,2);

                break;

            case '2': //As Divisor

                    val = window.util.roundUp( 
                        (parseFloat(this._model.component_quantity) / this.el.variable.value)  / parseInt(this._model.component_use_count)
                    ,2);

                break;

            case '3': //Direct

                    val = this.el.variable.value;
                    
                break;
            case '4': //As Equivalent

                
                val = ( parseFloat(this.el.variable.value) *  parseFloat(this.el.quantity.value) ) * parseInt(this._model.component_use_count); 
                
                val = window.util.roundUp(val,2);

                if(isFinite(val)){
                    this.el.component_item_equivalent.value = val+' '+this._model.component_unit_text;
                }else{
                    this.el.component_item_equivalent.value = '';
                }
                
                this.calculateTotalAmount();
                
                return true; //exit the function
                
                break;
        }

        
        if(isFinite(val)){

            val = window.util.roundUp(val,2);
            this.el.quantity.value = val;
        }else{
            this.el.quantity.value = 0;
        }

        this.calculateTotalAmount();
    }

    onStateChange_grand_total(newVal){
        this.el.grandTotal.innerText = newVal;

        if(newVal > this._state.quantity){
            this.el.grandTotal.style.color = '#ff0000';

            window.util.alert('Error','The Grand Total quantity is more than the Component quantity');

        }else{
            this.el.grandTotal.style.color = '#000000';
        }
    }

    onStateChange_editable(newVal){
       
        this.el.name.disabled               = !newVal;
        this.el.unit.disabled               = !newVal;
        this.el.budget_price.disabled       = !newVal;
        this.el.function_type.disabled      = !newVal;
        this.el.variable.disabled           = !newVal;
        this.el.sum_flag.disabled           = !newVal;
        this.el.ref_1_quantity.disabled     = !newVal;
        this.el.ref_1_unit_id.disabled      = !newVal;
        this.el.ref_1_unit_price.disabled   = !newVal;

        if(this.el.function_type.value == 4){
            this.el.quantity.disabled = !newVal;
        }

        //Editable (true)
        if(newVal){

            this.el.editComponentButton.style.display   = 'none';
            this.el.deleteComponentButton.style.display = 'none';

            this.el.cancelEditComponentButton.style.display = 'inline';
            this.el.updateComponentButton.style.display = 'inline';
            
        }else{ //Editable (false)
            this.el.editComponentButton.style.display   = 'inline';
            this.el.deleteComponentButton.style.display = 'inline';

            
            this.el.cancelEditComponentButton.style.display = 'none';
            this.el.updateComponentButton.style.display = 'none';
        }
     
    }

    updateMaterialList(){

        this.el.materialList.innerHTML = '';

        window.util.$get('/api/material_quantity/list',{
            component_item_id:this._model.id,
            page:1,
            limit:0
        }).then(reply=>{
            
            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            }

            let grand_total = 0;

            reply.data.map(item=>{
                this.appendMaterial({
                    id:item.id,
                    material_item_id: item.material_item_id,
                    quantity: item.quantity,
                    equivalent: item.equivalent
                });

                grand_total = grand_total + (item.quantity * item.equivalent)
            });

            this.setState('grand_total',grand_total);
        });
    }

    getComponentItemData(){

        window.util.$get('/api/component_item',{
            id:this._model.id
        }).then(reply=>{

            if(reply.status <= 0){

                alert(reply.message);
                return false;
            }
           
            this.setState('budget_price',parseFloat(reply.data.budget_price));
            this.setState('quantity',parseFloat(reply.data.quantity));
            this.setState('unit',reply.data.unit_id);
            this.setState('name',reply.data.name);
            this.setState('function_type_id',reply.data.function_type_id);
            this.setState('variable',reply.data.function_variable);
            this.setState('sum_flag',reply.data.sum_flag);
            this.setState('ref_1_quantity',reply.data.ref_1_quantity);
            this.setState('ref_1_unit_id',reply.data.ref_1_unit_id);
            this.setState('ref_1_unit_price',reply.data.ref_1_unit_price);
            


            this.el.name.value                  = reply.data.name;
            this.el.budget_price.value          = reply.data.budget_price;
            this.el.quantity.value              = reply.data.quantity;
            this.el.unit.value                  = reply.data.unit_id;
            this.el.function_type.value         = reply.data.function_type_id;
            this.el.variable.value              = reply.data.function_variable;
            this.el.ref_1_quantity.value        = reply.data.ref_1_quantity;
            this.el.ref_1_unit_id.value         = reply.data.ref_1_unit_id;
            this.el.ref_1_unit_price.value      = reply.data.ref_1_unit_price;
            
            
            if(reply.data.sum_flag){
                this.el.sum_flag.checked = true;
            }else{
                this.el.sum_flag.checked = false;
            }

            //Not "As Equivalent"
            if(reply.data.function_type_id != 4){
                this.el.component_item_equivalent.value = '';
            }

            this.updateComponentItemValues();
            
            this.updateMaterialList();
   
        });

    }

    addMaterial(){

        this.el.addBtn.disabled = true;

        let data = {
            component_item_id: this._model.id,
            material_item_id: this.el.materialItemSelect.value,
            quantity: this.el.material_quantity.value,
            equivalent: this.el.equivalent.value
        };

        window.util.$post('/api/material_quantity/create',data).then(reply=>{
            
            this.el.addBtn.disabled = false;
             
            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            this.el.material_quantity.value = '';
            this.el.equivalent.value = '';

            this.appendMaterial({
                id: reply.data.id,
                material_item_id: data.material_item_id,
                quantity: data.quantity,
                equivalent: data.equivalent
            });

            
            signal.broadcast('set-component-status','PEND');
        });

    }

    appendMaterial(data){
        const t = new Template();
        
        const materialItem = t.tr((row)=>{
                    t.td(this.materialRegistry[data.material_item_id]);
                    t.td(''+window.util.roundUp(data.quantity,2));
                    t.td(''+data.equivalent);
                    t.td(''+calculateTotalEquivalent(data.quantity,data.equivalent));
                    t.td({class:'text-center'},()=>{
                        
                        t.a({class:'me-5',href:'#'},()=>{
                            t.i({class:'bi bi-pencil-square'});
                        }).onclick = (e)=>{
                            e.preventDefault();

                            this.updateMaterialEntry({
                                material_quantity_id: data.id,
                                material_item_id: data.material_item_id,
                                equivalent: data.equivalent,
                                quantity: data.quantity
                            });
                        }

                        t.a({class:'me-5',href:'#'},()=>{
                            t.i({class:'bi bi-list-task'});
                        }).onclick = (e)=>{
                            e.preventDefault();

                            window.open('/material_budget/report/'+data.id,'_blank');
                        }

                        t.a({href:'#'},()=>{
                            t.i({class:'bi bi-trash-fill'});
                        }).onclick = (e)=>{
                            e.preventDefault();
                            
                            if(confirm('Are you sure you want to delete this entry')){
                                
                                window.util.blockUI();
                                
                                window.util.$post('/api/material_quantity/delete',{
                                    id:data.id
                                }).then(reply=>{

                                    window.util.unblockUI();

                                    if(reply.status <= 0){
                                        window.util.showMsg(reply);
                                        return false;
                                    }

                                    row.t.remove();
                                });
                            }
                        };
                    });//td
                });//tr
            
        
        if(parseFloat(data.quantity) > parseFloat(this._state.quantity)){

            materialItem.classList.add('border');
            materialItem.classList.add('border-danger');
        
        }else{
            
            materialItem.classList.remove('border');
            materialItem.classList.remove('border-danger');
        
        }

        this.el.materialList.append(materialItem);

    }

    updateMaterialEntry(entry){

        
        window.ui.primaryModal.hide();

        window.ui.primaryModalTitle.innerHTML    = 'Modify Material Entry';
        window.ui.primaryModalBody.innerHTML     = '';
        window.ui.primaryModalFooter.innerHTML   = '';

        const t = new Template();

        const quantityInput     = t.input({class:'form-control',value: window.util.roundUp(entry.quantity,2)});
        const equivalentInput   = t.input({class:'form-control',value:entry.equivalent});
        const totalInput        = t.input({ class:'form-control', disabled:true});



        quantityInput.onkeypress = (e)=>{
            return window.util.inputNumber(quantityInput,e,2,false);
        }


        let throttle = false;

        [quantityInput,equivalentInput].map(item=>{

            item.onkeyup = ()=>{

                if(!throttle){
                    
                    throttle = true;

                    setTimeout(()=>{
                            
                        let val = calculateTotalEquivalent(equivalentInput.value,quantityInput.value);

                        totalInput.value = val;

                        throttle = false;
                    },500);
                }
                
            }
        });

        totalInput.value = calculateTotalEquivalent(equivalentInput.value,quantityInput.value);

        const content = t.div(()=>{
            
            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-12 mb-3'},()=>{
                    t.table({class:'table borderd'},()=>{
                        t.tr(()=>{
                            t.th('Comp. Item',);
                            t.td(this._state.name);
                        });
                        t.tr(()=>{
                            t.th('Matt. Item',);
                            t.td(this.materialRegistry[entry.material_item_id])
                        });
                    })
                });
            });

            t.div({class:'row'},()=>{
                
                t.div({class:'col-4'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label('Quantity'),
                        el.append(quantityInput);
                    });
                });

                t.div({class:'col-4'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label('Equivalent / Unit'),
                        el.append(equivalentInput);
                    });
                });

                t.div({class:'col-4'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label('Total'),
                        el.append(totalInput);
                    });
                });
            });

        });

        const cancelBtn = t.button({class:'btn btn-secondary me-3'},'Cancel');
        const updateBtn = t.button({class:'btn btn-warning'},'Update');

        const controls =  t.div({class:'row'},()=>{
            t.div({class:'col-12 text-end'},(el)=>{
                el.append(cancelBtn);
                el.append(updateBtn);
            });
        });

        cancelBtn.onclick = (e)=>{
            window.ui.primaryModal.hide();
        }


        updateBtn.onclick = (e)=>{

            window.ui.primaryModal.hide();
            
            window.util.blockUI();

            window.util.$post('/api/material_quantity/update',{
                id                  : entry.material_quantity_id,
                material_item_id    : entry.material_item_id,
                quantity            : quantityInput.value,
                equivalent          : equivalentInput.value
            }).then(reply=>{
                window.util.unblockUI();

                if(reply.status <= 0){

                    window.util.showMsg(reply);
                    return false;
                }

                this.updateMaterialList();

            })
        }

        window.ui.primaryModalBody.append(content);

        window.ui.primaryModalFooter.append(controls);

        window.ui.primaryModal.show();
    }

}

export default (data)=>{
    return (new ComponentItem(data));
}