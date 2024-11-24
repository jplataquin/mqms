import {Template,ComponentV2,Signal} from '/adarna.js';


function calculateTotalEquivalent(a,b){
    return window.util.roundUp(parseFloat(a) * parseFloat(b),2);
}


const signal = new Signal();



class ComponentItem extends ComponentV2{

    state(){
        return {
            component_item_quantity: {
                value: 0,
                target: this.el.component_item_quantity,
                events:['keyup','change'],
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.component_item_quantity.value = data.value;
                    }
                }
            },
            component_item_unit:{
                value:'',
                onUpdate: (data)=>{
                    
                    this.el.component_item_unit.value = data.value;
                }
            },
            component_item_name:{
                value:'',
                onUpdate: (data)=>{
                    this.el.component_item_name.value = data.value;
                }
            },
            component_item_sum_flag:{
                value:true,
                target:this.el.component_item_sum_flag,
                events:['change'],
                onEvent:function(){
                    return this.checked;
                },
                onUpdate: (data)=>{
                    this.el.component_item_sum_flag.checked = data.value;
                }
            },
            component_item_editable: {
                value: false,
                target: this.el.component_item_edit_button,
                events: ['click'],
                onEvent:()=>{
                    return true;
                },
                onUpdate: (data)=>{

                    let newVal = data.value;

                    this.el.component_item_name.disabled               = !newVal;
                    this.el.component_item_unit.disabled               = !newVal;
                    this.el.component_item_budget_price.disabled       = !newVal;
                    this.el.component_item_function_type.disabled      = !newVal;
                    this.el.component_item_variable.disabled           = !newVal;
                    this.el.component_item_sum_flag.disabled           = !newVal;
                    this.el.component_item_ref_1_quantity.disabled     = !newVal;
                    this.el.component_item_ref_1_unit_id.disabled      = !newVal;
                    this.el.component_item_ref_1_unit_price.disabled   = !newVal;



                    if(this.el.component_item_function_type.value == 4){
                        this.el.component_item_quantity.disabled = !newVal;
                    }

                    //Editable (true)
                    if(newVal){

                        this.el.edit_component_item_button.style.display   = 'none';
                        this.el.delete_component_item_button.style.display = 'none';

                        this.el.cancel_edit_component_item_button.style.display = 'inline';
                        this.el.update_component_item_button.style.display      = 'inline';
                        
                    }else{ //Editable (false)
                        this.el.edit_component_item_button.style.display   = 'inline';
                        this.el.delete_component_item_button.style.display = 'inline';

                        this.el.cancel_edit_component_item_button.style.display  = 'none';
                        this.el.update_component_item_button.style.display       = 'none';
                    }
                }
            },
            component_item_function_type:{
                value:'',
                target: this.el.component_item_function_type,
                events:['change'],
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.component_item_function_type.value = data.value;
                    }
                    
                    this.updateComponentItemValues();
                    console.log('here');
                }
            },
            component_item_variable:{
                value:'',
                target: this.el.component_item_variable,
                events:['keyup','change'],
                getValue: (val)=>{
                    return window.util.pureNumber(val);
                },
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.component_item_variable.value = window.util.pureNumber(data.value);
                    }


                    this.updateComponentItemValues();
                }
            },

            component_item_equivalent:{
                value:'',
                onUpdate:(data)=>{
                    this.el.component_item_equivalent.value = window.util.numberFormat(data.value)+' '+this._model.component_unit_text;
                }
            },
           
            component_item_ref_1_quantity:{
                value:'',
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate:(data)=>{
                    this.el.component_item_ref_1_quantity.value = window.util.numberFormat(data.value,2);
                }
            },
            component_item_ref_1_unit_id:{
                value:'',
                onUpdate:(data)=>{
                    this.el.component_item_ref_1_unit_id.value = data.value;
                }
            },
            component_item_ref_1_unit_price:{
                value:'',
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate:(data)=>{
                    this.el.component_item_ref_1_unit_price.value = window.util.numberFormat(data.value,2);
                }
            },

            component_item_total_amount: {
                value:'',
                getValue:(val)=>{
                    return window.util.pureNumber(val);
                },
                onUpdate:(data)=>{
                    this.el.component_item_total_amount.value = window.util.numberFormat(data.value,2);
                }
            },

            component_item_budget_price:{
                value:'',
                target: this.el.component_item_budget_price,
                events:['keyup','change'],
                
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate:(data)=>{

                    if(!data.event){
                        this.el.component_item_budget_price.value = window.util.numberFormat(data.value,2);
                    }

                    this.setState('component_item_total_amount', 
                        ( window.util.pureNumber(data.value) * this.getState('component_item_quantity'))
                    );

                }
            },
            
            /*******************************************************/
            // grand_total:{
            //     value:0,
            //     getValue: (val)=>{
            //         return window.util.pureNumber(val,2);
            //     },
            //     onUpdate: (data)=>{

            //         let newVal = data.value;

            //         this.el.grandTotal.innerText = window.util.numberFormat(newVal,2);

            //         if(newVal > this.getState('quantity')){
                        
            //             this.el.grandTotal.classList.add('text-danger');
            //             this.el.grandTotal.classList.add('overbudget');

            //             window.util.alert('Error','The Grand Total quantity is more than the Component quantity');

            //         }else{
                        
            //             this.el.grandTotal.classList.remove('text-danger');
            //             this.el.grandTotal.classList.remove('overbudget');

            //         }
            //     }
            // },
            // material_quantity:{
            //     value:'',
            //     target: this.el.material_quantity,
            //     events:['keyup','change'],
            //     getValue:(data)=>{
            //         return window.util.pureNumber(data.value);
            //     },
            //     onUpdate:(data)=>{

            //         if(!data.event){
            //             this.el.material_quantity.value = data.value;
            //         }

            //         let material_quantity = this.getState('material_quantity');
            //         let equivalent        = this.getState('equivalent');
                    
            //         this.setState({
            //             total: calculateTotalEquivalent(material_quantity, equivalent)
            //         });
            //     }
            // },
            
            
            // equivalent:{
            //     value:'',
            //     target: this.el.equivalent,
            //     events:['keyup'],
            //     getValue: (val)=>{
            //         return window.util.pureNumber(val);
            //     },
            //     onUpdate:(data)=>{
            //         this.el.equivalent.value = data.value;
            //     }
            // },
            // total:{
            //     value:'',
            //     getValue: (val)=>{
            //         return window.util.pureNumber(val);
            //     },
            //     onUpdate:(data)=>{
            //         this.el.total.value = window.util.numberFormat(data.value,2);
            //     }
            // }           
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

        // this.el.materialItemSelect = t.select({class:'form-control'},()=>{
        //     t.option({value:''},' - ');
        // });

        // this._model.materialItemOptions.map(item=>{
            
        //     let option = t.option({value:item.id},item.brand+' '+item.name + ' '+item.specification_unit_packaging+''.trim());
            
        //     this.el.materialItemSelect.t.append(option);

        //     this.materialRegistry[item.id] = item.brand+' '+item.name +' '+item.specification_unit_packaging+''.trim();
        // });

        // this.el.materialMenu = t.div(()=>{
    
        //     t.div({class:'folder-form-container'},()=>{

        //         t.div({class:'folder-form-tab'},'Material Quantity');
                
        //         t.div({class:'folder-form-body'},()=>{

        //             t.div({class:'row'},()=>{

        //                 t.div({class:'col-lg-6'},()=>{
        //                     t.div({class:'form-group'},()=>{
        //                         t.label('Material');
        //                         t.el(this.el.materialItemSelect);
        //                     });
        //                 });
                    
        //                 t.div({class:'col-lg-1'},()=>{             
        //                     t.div({class:'form-group'},()=>{
        //                         t.label('Quantity');
        //                         this.el.material_quantity = t.input({class:'form-control', type:'text'});
        //                     });
        //                 });                           

        //                 t.div({class:'col-lg-2'},()=>{
        //                     t.div({class:'form-group'},()=>{
        //                         t.label('Equivalent / Unit');
        //                         this.el.equivalent = t.input({class:'form-control', type:'text'});
        //                     });
        //                 });
                        
                        
        //                 t.div({class:'col-lg-2'},()=>{
        //                     t.div({class:'form-group'},()=>{
        //                         t.label('Total');
        //                         this.el.total = t.input({class:'form-control', type:'number',disabled:true});
        //                     });
        //                 });

                    
        //                 t.div({class:'col-lg-1'},()=>{
        //                     t.div({class:'form-group'},()=>{
        //                         t.label('&nbsp');
        //                         this.el.addBtn = t.button({class:'btn btn-warning w-100'},'Add');
        //                     });
        //                 });
 
        //             });//row

        //         });//body
                
        //     });//container


        //     t.table({class:'table'},()=>{

        //         t.thead(()=>{
        //             t.th('Material');
        //             t.th('Quantity');
        //             t.th('Equivalent / Unit');
        //             t.th('Total');
        //         });

        //         this.el.materialList = t.tbody();

        //         t.tfoot(()=>{
        //             t.tr(()=>{
        //                 t.td();
        //                 t.td();
        //                 t.th('Grand Total');
        //                 this.el.grandTotal = t.td('0');
        //                 t.td();
        //             });
        //         });
        //     })//table
        // });



        return t.div({class:'form-container mb-5'},(el)=>{
            
            t.div({class:'form-header component_item_sticky_untrigger'},'Item');

            t.div({class:'form-body'},()=>{

                this.el.item = t.table({class:'table'},()=>{
                    

                    t.tbody(()=>{
                        t.tr({class:'component_item_sticky_trigger'},()=>{
                            t.th({colspan:4},'Name');
                            t.th({colspan:1},'Sum Flag');
                        });
        
                        t.tr(()=>{
                            t.td({colspan:4},()=>{
                                this.el.component_item_name = t.input({class:'form-control name',type:'text', placeholder:'Item',disabled:true,value:'Loading...'}); 
                            });
        
                            t.td({colspan:1},()=>{
                                t.div({class:'form-switch text-center'},()=>{                  
                                    this.el.component_item_sum_flag = t.input({class:'form-check-input sum_flag',value:1,type:'checkbox', disabled:true});
                                });
                            });
                            
                        });
                        
                        t.tr(()=>{
                            t.th({colspan:2},'POW/DUPA Quantity');
                            t.th({colspan:1},'POW/DUPA Unit');
                            t.th({colspan:2},'POW/DUPA Unit Price');
                        });
                
                        t.tr(()=>{
                            
                            t.td({colspan:2},()=>{
                                this.el.component_item_ref_1_quantity = t.input({class:'form-control',disabled:true});
                            });
                
                            t.td({colspan:1},()=>{
                
                                this.el.component_item_ref_1_unit_id = t.select({class:'form-control',disabled:true},()=>{
                                    
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
                
                            t.td({colspan:2},()=>{
                                this.el.component_item_ref_1_unit_price = t.input({class:'form-control',disabled:true});
                            });
                        });
        
                        t.tr(()=>{
                            t.th('Function Type');
                            t.th('Variable');
                            t.th('Quantity');
                            t.th('Unit');
                            t.th('Equivalent');
                        })
                        
                        t.tr(()=>{
                            
                            t.td({class:''},(el)=>{
                                
                                this.el.component_item_function_type = t.select({class:'form-control function_type',disabled:true},()=>{
                                    t.option({value:3},'As Direct');
                                    t.option({value:4},'As Equivalent');
                                    t.option({value:1},'As Factor');
                                    t.option({value:2},'As Divisor');
                                    
                                });
        
                            });
        
                             
                            t.td({class:''},(el)=>{
                                
                                this.el.component_item_variable = t.input({class:'form-control variable', type:'text', placeholder:'Variable',disabled:true,value:'Loading...'});
        
                            });

                            t.td({class:''},(el)=>{
                                
                                this.el.component_item_quantity = t.input({class:'form-control quantity', type:'text', placeholder:'Quantity',disabled:true,value:'Loading...'});
        
                            });
        
                           
        
                            t.td({class:''},(el)=>{
        
                                this.el.component_item_unit = t.select({class:'form-control unit',disabled:true},()=>{
                                    
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
                                
                                this.el.component_item_equivalent = t.input({class:'form-control equivalent', type:'text', placeholder:'Equivalent',disabled:true,value:'Loading...'});
        
                            });
        
                           
                        });
                        
                        t.tr(()=>{
                            t.th({colspan:2},'Budget Price');
                            t.th({colspan:5},'Total Amount');
                        });
        
                        t.tr(()=>{

                            
                            t.td({colspan:2},(el)=>{
                                
                                this.el.component_item_budget_price = t.input({class:'form-control budget_price', type:'text', placeholder:'Budget Price',disabled:true,value:'Loading...'});
                                
                            });
                            
                            t.td({colspan:5},()=>{
                                this.el.component_item_total_amount = t.input({class:'form-control',disabled:true});
                            });
                        });


                       
        
                    
        
                        t.tr(()=>{
                            t.td({colspan:7, class:'text-end'},(el)=>{
                                
                                this.el.delete_component_item_button       = t.button({class:'btn btn-danger me-3'},'Delete');
                                this.el.edit_component_item_button         = t.button({class:'btn btn-primary'},'Edit');
                                this.el.cancel_edit_component_item_button  = t.button({class:'btn btn-primary me-3'},'Cancel');
                                this.el.update_component_item_button       = t.button({class:'btn btn-warning'},'Update');
                                
                            });
                        });
        
        
                    });//tbody
        
        
                });//table
        
                
                // t.div((el)=>{
                //     el.append(this.el.materialMenu);
                // });

            });//form-body

        });//form-container
    }

    controller(dom){
        
        this.getComponentItemData();

       // this.functionVariableQuantity();

        this.initEvents();

        //this.reloadState('editable');
    }

    initEvents(){
        
        window.util.numbersOnlyInput([
            this.el.component_item_budget_price,
            this.el.component_item_ref_1_quantity,
            this.el.component_item_ref_1_unit_price
        ],{
            negative:false,
            precision:2
        });


        // this.el.equivalent.onkeyup = ()=>{

        //     //this.setState('equivalent',this.el.equivalent.value);

        //     let equivalent          = this.getState('equivalent');
        //     let material_quantity   = this.getState('material_quantity');


        //     if(equivalent == 0) return false;

        //     this.setState({
        //         total : calculateTotalEquivalent( material_quantity, equivalent)
        //     });

           
        // }


        // this.el.addBtn.onclick = ()=>{

        //     let total       = this.getState('total');
        //     let quantity    = this.getState('quantity');

        //     if(total > quantity){
        //         window.util.alert('Total equivalent cannot be greater than the component item quantity ('+this.getState('quantity')+' '+this.getState('unit')+')');
        //         return false;
        //     }

        //     this.addMaterial();
        // }


        this.el.delete_component_item_button.onclick = async (e)=>{
            e.preventDefault();

            let answer = await window.util.prompt('Please confirm by entering "'+this.getState('name')+'"');

            if(answer != this.getState('name')){
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

                this._dom.t.remove();
                
                signal.broadcast('set-component-status','PEND');
            });
        }

        this.el.edit_component_item_button.onclick = (e)=>{
            e.preventDefault();
            this.setState('component_item_editable',true);
        }

        this.el.cancel_edit_component_item_button.onclick = (e)=>{
            
            //TODO fix this so it won't reload

            // this.setState('editable',false);

            // this.reloadState([
            //     'unit',
            //     'name',
            //     'quantity',
            //     'ref_1_quantity',
            //     'ref_1_unit_id',
            //     'ref_1_unit_price',
            //     'sum_flag'
            // ]);
         
            // this.onUpdateComponentItemValues();

            window.util.navReload();
        }

        this.el.update_component_item_button.onclick = (e)=>{
           this.httponUpdate();
        }

    }

    calculateTotalAmount(){
        
        console.log(this.getState('component_item_budget_price'), this.getState('component_item_quantity'));
        
        this.setState('component_item_total_amount', 
            (this.getState('component_item_budget_price') * this.getState('component_item_quantity'))
        );
    }

    httponUpdate(){

        let data = {
            id                      : this._model.id,
            component_id            : this._model.component_id,
            name                    : this.getState('component_item_name'),
            budget_price            : this.getState('component_item_budget_price'),
            quantity                : this.getState('component_item_quantity'),
            unit_id                 : this.getState('component_item_unit'),
            function_type_id        : this.getState('component_item_function_type'),
            function_variable       : this.getState('component_item_variable'),
            sum_flag                : (this.getState('component_item_sum_flag') == true) ? 1 : 0,
            ref_1_quantity          : this.getState('component_item_ref_1_quantity'),
            ref_1_unit_id           : this.getState('component_item_ref_1_unit_id'),
            ref_1_unit_price        : this.getState('component_item_ref_1_unit_price')

        };
        console.log(data);

        return false;
        window.util.blockUI();
        window.util.$post('/api/component_item/onUpdate/',{
            id                      : this._model.id,
            component_id            : this._model.component_id,
            name                    : this.getState('name'),
            budget_price            : this.getState('budget_price'),
            quantity                : this.getState('quantity'),
            unit_id                 : this.getState('unit'),
            function_type_id        : this.getState('function_type'),
            function_variable       : this.getState('variable'),
            sum_flag                : (this.getState('sum_flag') == true) ? 1 : 0,
            ref_1_quantity          : this.getState('ref_1_quantity'),
            ref_1_unit_id           : this.getState('ref_1_unit_id'),
            ref_1_unit_price        : this.getState('ref_1_unit_price')

        }).then(reply=>{

            window.util.unblockUI();


            if(reply.status <= 0){
                window.util.showMsg(reply);

                return false;
            }
            
            window.util.navReload();
            // this.setState('editable',false);

            // this.onUpdateMaterialList();

            // signal.broadcast('set-component-status','PEND');
        });
    }

    // functionVariableQuantity(){


    //     this.el.variable.onkeypress = (e)=>{
    //         return window.util.inputNumber(this.el.variable,e,6,false);
    //     }

    //     this.el.quantity.onkeypress = (e)=>{
    //         return window.util.inputNumber(this.el.quantity,e,2,false);
    //     }
    
    //     this.el.function_type.onchange = (e) =>{
    //         this.el.variable.onkeyup();   
    //     }
    

    //     this.el.quantity.onkeyup = (e)=>{
    //         this.setState('quantity',window.util.pureNumber(this.el.quantity.value));
    //         this.onUpdateComponentItemValues();
    //     }
    // }


    updateComponentItemValues(){
        
        let equivalent                      = 0;
        let quantity                        = 0;
        let component_quantity              = window.util.pureNumber(this._model.component_quantity);
        let variable                        = this.getState('component_item_variable');
        let use_count                       = parseInt(this._model.component_use_count);
        let component_item_quantity         = this.getState('component_item_quantity'); 
        let component_item_function_type    = this.getState('component_item_function_type')

        console.log(component_item_function_type);

        switch(component_item_function_type){
            case '1': //As Factor

                    this.el.component_item_quantity.disabled = true;
                   
                    quantity = window.util.roundUp(
                        (component_quantity * variable )  / use_count
                    ,2);
                    
                    this.setState('component_item_quantity',quantity);
                    
                    this.setState('component_item_equivalent','');

                break;

            case '2': //As Divisor
                    
                    this.el.component_item_quantity.disabled = true;
                   
                    quantity = window.util.roundUp( 
                        (component_quantity / variable)  / use_count
                    ,2);
                    
                    this.setState('component_item_quantity',quantity);
                    this.setState('component_item_equivalent','');

                break;

            case '3': //Direct
                    
                    this.el.component_item_variable.disabled = false;
                    this.el.component_item_quantity.disabled = true;
                    
                    this.setState('component_item_quantity',variable);
                    this.setState('component_item_equivalent','');

                    
                break;
            case '4': //As Equivalent

                this.el.component_item_variable.disabled = false;
                this.el.component_item_quantity.disabled = false;

                equivalent = ( variable * component_item_quantity ) * use_count; 
                

                if(equivalent !== Infinity){

                    equivalent = window.util.roundUp(equivalent,2);

                    this.setState('component_item_equivalent',equivalent);
                
                }else{

                    this.setState('component_item_equivalent','');
                
                }
                
                
                break;
        }



        this.calculateTotalAmount();
    }


    // onUpdateMaterialList(){

    //     this.el.materialList.innerHTML = '';

    //     window.util.$get('/api/material_quantity/list',{
    //         component_item_id   :this._model.id,
    //         page                :1,
    //         limit               :0
    //     }).then(reply=>{
            
    //         if(reply.status <= 0 ){
    //             window.util.showMsg(reply);
    //             return false;
    //         }

    //         let grand_total = 0;

    //         reply.data.map(item=>{
    //             this.appendMaterial({
    //                 id:item.id,
    //                 material_item_id: item.material_item_id,
    //                 quantity: item.quantity,
    //                 equivalent: item.equivalent
    //             });

    //             grand_total = grand_total + (item.quantity * item.equivalent)
    //         });

    //         this.setState('grand_total',grand_total);
    //     });
    // }

    getComponentItemData(){

        window.util.$get('/api/component_item',{
            id:this._model.id
        }).then(reply=>{

            if(reply.status <= 0){

                alert(reply.message);
                return false;
            }

            
            this.setState({
                component_item_budget_price        :reply.data.budget_price,
                component_item_quantity            :reply.data.quantity,
                component_item_unit                :reply.data.unit_id,
                component_item_name                :reply.data.name,
                component_item_function_type       :reply.data.function_type_id,
                component_item_variable            :reply.data.function_variable,
                component_item_sum_flag            :reply.data.sum_flag,
                component_item_ref_1_quantity      :reply.data.ref_1_quantity,
                component_item_ref_1_unit_id       :reply.data.ref_1_unit_id,
                component_item_ref_1_unit_price    :reply.data.ref_1_unit_price
            });


           // this.onUpdateComponentItemValues();
            
            //this.onUpdateMaterialList();
   
        });

    }

    // addMaterial(){

    //     this.el.addBtn.disabled = true;

    //     let data = {
    //         component_item_id   : this._model.id,
    //         material_item_id    : this.el.materialItemSelect.value,
    //         quantity            : this.getState('material_quantity'),
    //         equivalent          : this.getState('equivalent')
    //     };

    //     window.util.$post('/api/material_quantity/create',data).then(reply=>{
            
    //         this.el.addBtn.disabled = false;
             
    //         if(reply.status <= 0){
    //             window.util.showMsg(reply);
    //             return false;
    //         }

    //         this.setState({
    //             material_quantity:'',
    //             equivalent:''
    //         });
            
    //         this.appendMaterial({
    //             id: reply.data.id,
    //             material_item_id: data.material_item_id,
    //             quantity: data.quantity,
    //             equivalent: data.equivalent
    //         });

            
    //         signal.broadcast('set-component-status','PEND');
    //     });

    // }

    // appendMaterial(data){
    //     const t = new Template();
        
    //     const materialItem = t.tr((row)=>{
    //                 t.td(this.materialRegistry[data.material_item_id]);
    //                 t.td(''+window.util.roundUp(data.quantity,2));
    //                 t.td(''+data.equivalent);
    //                 t.td(''+calculateTotalEquivalent(data.quantity,data.equivalent));
    //                 t.td({class:'text-center'},()=>{
                        
    //                     t.a({class:'me-5',href:'#'},()=>{
    //                         t.i({class:'bi bi-pencil-square'});
    //                     }).onclick = (e)=>{
    //                         e.preventDefault();

    //                         this.onUpdateMaterialEntry({
    //                             material_quantity_id: data.id,
    //                             material_item_id: data.material_item_id,
    //                             equivalent: data.equivalent,
    //                             quantity: data.quantity
    //                         });
    //                     }

    //                     t.a({class:'me-5',href:'#'},()=>{
    //                         t.i({class:'bi bi-list-task'});
    //                     }).onclick = (e)=>{
    //                         e.preventDefault();

    //                         window.open('/material_budget/report/'+data.id,'_blank');
    //                     }

    //                     t.a({href:'#'},()=>{
    //                         t.i({class:'bi bi-trash-fill'});
    //                     }).onclick = (e)=>{
    //                         e.preventDefault();
                            
    //                         if(confirm('Are you sure you want to delete this entry')){
                                
    //                             window.util.blockUI();
                                
    //                             window.util.$post('/api/material_quantity/delete',{
    //                                 id:data.id
    //                             }).then(reply=>{

    //                                 window.util.unblockUI();

    //                                 if(reply.status <= 0){
    //                                     window.util.showMsg(reply);
    //                                     return false;
    //                                 }

    //                                 row.t.remove();
    //                             });
    //                         }
    //                     };
    //                 });//td
    //             });//tr
            
        
    //     if( parseFloat(data.quantity) > this.getState('quantity') ){

    //         materialItem.classList.add('border');
    //         materialItem.classList.add('border-danger');
    //         materialItem.classList.add('overbudget');
        
    //     }else{
            
    //         materialItem.classList.remove('border');
    //         materialItem.classList.remove('border-danger');
    //         materialItem.classList.remove('overbudget');
        
    //     }

    //     this.el.materialList.append(materialItem);

    // }

    // onUpdateMaterialEntry(entry){

        
    //     window.ui.primaryModal.hide();

    //     window.ui.primaryModalTitle.innerHTML    = 'Modify Material Entry';
    //     window.ui.primaryModalBody.innerHTML     = '';
    //     window.ui.primaryModalFooter.innerHTML   = '';

    //     const t = new Template();

    //     const quantityInput     = t.input({class:'form-control',value: window.util.roundUp(entry.quantity,2)});
    //     const equivalentInput   = t.input({class:'form-control',value:entry.equivalent});
    //     const totalInput        = t.input({class:'form-control', disabled:true});



    //     quantityInput.onkeypress = (e)=>{
    //         return window.util.inputNumber(quantityInput,e,2,false);
    //     }


    //     let throttle = false;

    //     [quantityInput,equivalentInput].map(item=>{

    //         item.onkeyup = ()=>{

    //             if(!throttle){
                    
    //                 throttle = true;

    //                 setTimeout(()=>{
                            
    //                     let val = calculateTotalEquivalent(equivalentInput.value,quantityInput.value);

    //                     totalInput.value = val;

    //                     throttle = false;
    //                 },500);
    //             }
                
    //         }
    //     });

    //     totalInput.value = calculateTotalEquivalent(equivalentInput.value,quantityInput.value);

    //     const content = t.div(()=>{
            
    //         t.div({class:'row mb-3'},()=>{
    //             t.div({class:'col-12 mb-3'},()=>{
    //                 t.table({class:'table borderd'},()=>{
    //                     t.tr(()=>{
    //                         t.th('Comp. Item',);
    //                         t.td(this.getState('name'));
    //                     });
    //                     t.tr(()=>{
    //                         t.th('Matt. Item',);
    //                         t.td(this.materialRegistry[entry.material_item_id])
    //                     });
    //                 })
    //             });
    //         });

    //         t.div({class:'row'},()=>{
                
    //             t.div({class:'col-4'},()=>{
    //                 t.div({class:'form-group'},(el)=>{
    //                     t.label('Quantity'),
    //                     el.append(quantityInput);
    //                 });
    //             });

    //             t.div({class:'col-4'},()=>{
    //                 t.div({class:'form-group'},(el)=>{
    //                     t.label('Equivalent / Unit'),
    //                     el.append(equivalentInput);
    //                 });
    //             });

    //             t.div({class:'col-4'},()=>{
    //                 t.div({class:'form-group'},(el)=>{
    //                     t.label('Total'),
    //                     el.append(totalInput);
    //                 });
    //             });
    //         });

    //     });

    //     const cancelBtn = t.button({class:'btn btn-secondary me-3'},'Cancel');
    //     const onUpdateBtn = t.button({class:'btn btn-warning'},'onUpdate');

    //     const controls =  t.div({class:'row'},()=>{
    //         t.div({class:'col-12 text-end'},(el)=>{
    //             el.append(cancelBtn);
    //             el.append(onUpdateBtn);
    //         });
    //     });

    //     cancelBtn.onclick = (e)=>{
    //         window.ui.primaryModal.hide();
    //     }


    //     onUpdateBtn.onclick = (e)=>{

    //         window.ui.primaryModal.hide();
            
    //         window.util.blockUI();

    //         window.util.$post('/api/material_quantity/onUpdate',{
    //             id                  : entry.material_quantity_id,
    //             material_item_id    : entry.material_item_id,
    //             quantity            : quantityInput.value,
    //             equivalent          : equivalentInput.value
    //         }).then(reply=>{
    //             window.util.unblockUI();

    //             if(reply.status <= 0){

    //                 window.util.showMsg(reply);
    //                 return false;
    //             }

    //             this.onUpdateMaterialList();

    //         })
    //     }

    //     window.ui.primaryModalBody.append(content);

    //     window.ui.primaryModalFooter.append(controls);

    //     window.ui.primaryModal.show();
    // }

}

export default (data)=>{
    return (new ComponentItem(data));
}