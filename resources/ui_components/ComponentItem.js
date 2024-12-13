import {Template,ComponentV2,Signal} from '/adarna.js';
import MaterialQuantityList from '/ui_components/MaterialQuantityList.js';
import CreateMaterialQuantityForm from '/ui_components/create_forms/CreateMaterialQuantityForm.js';


const signal = new Signal();



class ComponentItem extends ComponentV2{

    state(){
        return {
            component_item_quantity: {
                value: 0,
                target: this.el.component_item_quantity,
                events:['keyup'],
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.component_item_quantity.value = data.value;
                    }

                    
                    this.updateComponentItemValues();     
                }
            },
            component_item_unit:{
                value:'',
                target:this.el.component_item_unit,
                events:['change'],
                onUpdate: (data)=>{
                    
                    if(!data.event){
                        this.el.component_item_unit.value = data.value;
                    }
                }
            },
            component_item_name:{
                value:'',
                target: this.el.component_item_name,
                events:['keyup','paste'],
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.component_item_name.value = data.value;
                    }
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

                    if(!data.event){
                        this.el.component_item_sum_flag.checked = data.value;
                    }
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
                getValue:(val)=>{
                    return parseInt(val);
                },
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.component_item_function_type.value = data.value;
                    }
                    
                    this.updateComponentItemValues();
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
                target: this.el.component_item_ref_1_quantity,
                events:['keyup'],
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate:(data)=>{

                    if(!data.event) { 
                        this.el.component_item_ref_1_quantity.value = window.util.numberFormat(data.value,2);
                    }
                }
            },
            component_item_ref_1_unit_id:{
                value:'',
                target: this.el.component_item_ref_1_unit_id,
                events:['change'],
                onUpdate:(data)=>{

                    if(!data.event){
                        this.el.component_item_ref_1_unit_id.value = data.value;
                    }
                }
            },
            component_item_ref_1_unit_price:{
                value:'',
                target: this.el.component_item_ref_1_unit_price,
                events:['keyup'],
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate:(data)=>{

                    if(!data.event){
                        this.el.component_item_ref_1_unit_price.value = window.util.numberFormat(data.value,2);
                    }
                }
            },

            component_item_total_amount: {
                value:'',
                getValue:(val)=>{
                    return window.util.pureNumber(val);
                },
                onUpdate:(data)=>{
                    this.el.component_item_total_amount.value = 'P '+window.util.numberFormat(data.value,2);
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
            
                  
        }
    }

    model(){
        return {
            id:null,
            component_id:null,
            component_quantity:0,
            component_use_count:1,
            component_unit_text:'',
            material_item_options:[],
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

        return t.div({class:'form-container mb-5 shadow-lg bg-white rounded'},(el)=>{
            
            t.div({class:'form-header'},'');

            t.div({class:'form-body border'},()=>{

                t.div({class:'table-responsive'},()=>{
                
                    this.el.item = t.table({class:'table'},()=>{
                        

                        t.tbody(()=>{

                            t.tr(()=>{

                                t.td({colspan:5},()=>{
                                    t.div({class:'row shadow-lg p-3 bg-body rounded border mb-3'},()=>{
                                        
                                        t.div({class:'col-lg-10 col-sm-10 mb-3'},()=>{
                                            t.div({class:'form-group'},()=>{
                                                t.label('Name');
                                                this.el.component_item_name = t.input({class:'form-control name',type:'text', placeholder:'Item',disabled:true,value:'Loading...'}); 
                                            });
                                        });

                                        t.div({class:'col-lg-1 col-sm-1'},()=>{
                                            t.div({class:'form-group'},()=>{
                                                t.label('Sum Flag');

                                                t.div({class:'form-switch'},()=>{                  
                                                    this.el.component_item_sum_flag = t.input({class:'form-check-input sum_flag',value:1,type:'checkbox', disabled:true});
                                                });

                                            });
                                        });


                                        t.div({class:'col-lg-1 col-sm-1'},()=>{
                                            t.div({class:'form-group'},()=>{
                                                t.br();
                                                t.h4(()=>{
                                                    t.a({class:'me-5',href:'#'},()=>{
                                                        this.el.report_btn = t.i({class:'bi bi-list-task'});
                                                    });
                                                });
                                            });
                                        });
                                    });
                                });
                            });

                            
                            t.tr(()=>{
                                t.th({colspan:5,class:'text-center bg-divider'},'POW/DUPA')
                            });

                            t.tr(()=>{
                                t.th({colspan:2},'Quantity');
                                t.th({colspan:1},'Unit');
                                t.th({colspan:2},'Unit Price');
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
                                t.th({colspan:5,class:'text-center bg-divider'},'Material Budget')
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
                                t.th({colspan:2},'Unit Price');
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
                                t.td({colspan:5},()=>{

                                    this.el.material_quantity_list_container = t.div();//div
                                });
                            });
            
                        
            
                            t.tr(()=>{

                                t.td(()=>{
                                    
                                    this.el.add_material_quantity_button       = t.button({class:'btn btn-warning'},'Add Material');

                                });

                                t.td({colspan:6, class:'text-end'},(el)=>{
                                    
                                    this.el.delete_component_item_button       = t.button({class:'btn btn-danger me-3'},'Delete');
                                    this.el.edit_component_item_button         = t.button({class:'btn btn-primary'},'Edit');
                                    this.el.cancel_edit_component_item_button  = t.button({class:'btn btn-primary me-3'},'Cancel');
                                    this.el.update_component_item_button       = t.button({class:'btn btn-warning'},'Update');

                                });
                            });

                            /**
                         
                            **/
            
                        });//tbody
            
            
                    });//table
                
                });
                 
            });//form-body

        });//form-container
    }

    controller(){
        
        this.getComponentItemData();


        this.initEvents();

    }

    initEvents(){
        
        window.util.numbersOnlyInput([
            this.el.component_item_budget_price,
            this.el.component_item_ref_1_quantity,
            this.el.component_item_ref_1_unit_price,
            this.el.component_item_quantity
        ],{
            negative:false,
            precision:2
        });

        window.util.numbersOnlyInput(this.el.component_item_variable,{
            negative:false,
            precision:6
        });


    
        this.el.delete_component_item_button.onclick = async (e)=>{
            e.preventDefault();

            let answer = await window.util.prompt('Please confirm by entering "'+this.getState('component_item_name')+'"');

            if(answer != this.getState('component_item_name')){
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
                
                signal.broadcast('component-item-update');
            });
        }

        this.el.edit_component_item_button.onclick = (e)=>{
            e.preventDefault();
            this.setState('component_item_editable',true);
        }

        this.el.cancel_edit_component_item_button.onclick = (e)=>{
            
            this.getComponentItemData();

            this.setState('component_item_editable',false);
        }

        this.el.update_component_item_button.onclick = (e)=>{
           this.httpUpdate();
        }


        this.el.add_material_quantity_button.onclick = (e)=>{

            window.util.drawerModal.content('Add Material Quantity',CreateMaterialQuantityForm({
                material_item_options:this._model.material_item_options,
                component_item_id: this._model.id,
                component_item_name: this.getState('component_item_name'),
                component_item_quantity : this.getState('component_item_quantity'),
                component_item_unit_text: this._model.unitOptions[this.getState('component_item_unit')].text,
                get_grand_total: ()=>{
                    return this.el.material_quantity_list.handler.getGrandTotal();
                },
                after_add_callback: ()=>{
                    this.el.material_quantity_list.handler.refreshList();
                    signal.broadcast('component-item-update');
                }
            })).open();
        }


        this.el.report_btn.onclick = (e)=>{
            e.preventDefault();
            window.open('/component_item/report/'+this._model.id,'_blank');
        }

    }

    calculateTotalAmount(){
        

        this.setState('component_item_total_amount', 
            (this.getState('component_item_budget_price') * this.getState('component_item_quantity'))
        );
    }

    httpUpdate(){

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

    
        window.util.blockUI();

        window.util.$post('/api/component_item/update/',data).then(reply=>{

            window.util.unblockUI();


            if(reply.status <= 0){
                window.util.showMsg(reply);

                return false;
            }
            
            this.getComponentItemData();
            
            this.setState('component_item_editable',false);

            signal.broadcast('component-item-update');
        });
    }


    updateComponentItemValues(){
        
        let equivalent                      = 0;
        let quantity                        = 0;
        let component_quantity              = window.util.pureNumber(this._model.component_quantity);
        let variable                        = this.getState('component_item_variable');
        let use_count                       = parseInt(this._model.component_use_count);
        let component_item_quantity         = this.getState('component_item_quantity'); 
        let component_item_function_type    = this.getState('component_item_function_type')


        switch(component_item_function_type){
            case 1: //As Factor

                    if(this.getState('component_item_editable')){
                        this.el.component_item_quantity.disabled = true;
                    }

                    quantity = window.util.roundUp(
                        (component_quantity * variable )  / use_count
                    ,2);
                    
                    if(quantity === Infinity){
                        quantity = 0;
                    }

                    this.setState('component_item_quantity',quantity);
                    
                    this.setState('component_item_equivalent','');

                break;

            case 2: //As Divisor
                    
                    if(this.getState('component_item_editable')){
                        this.el.component_item_quantity.disabled = true;
                    }

                    quantity = window.util.roundUp( 
                        (component_quantity / variable)  / use_count
                    ,2);
                    
                    if(quantity === Infinity){
                        quantity = 0;
                    }

                    this.setState('component_item_quantity',quantity);
                    this.setState('component_item_equivalent','');

                break;

            case 3: //Direct
                    
                    if(this.getState('component_item_editable')){
                        this.el.component_item_variable.disabled = false;
                        this.el.component_item_quantity.disabled = true;
                    }

                    variable = window.util.pureNumber(variable,2);

                    this.setState('component_item_quantity',variable);
                    this.setState('component_item_equivalent','');

                    
                break;
            case 4: //As Equivalent
                    
                if(this.getState('component_item_editable')){
                    this.el.component_item_variable.disabled = false;
                    this.el.component_item_quantity.disabled = false;
                }

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

            this.el.material_quantity_list = MaterialQuantityList({
                component_item_id       : this._model.id,
                component_item_name     : reply.data.name,
                component_item_quantity : reply.data.quantity,
                component_item_unit_text: this._model.unitOptions[reply.data.unit_id].text,
                material_item_options   : this._model.material_item_options
            });

            this.el.material_quantity_list_container.innerHTML = '';
            this.el.material_quantity_list_container.append(this.el.material_quantity_list);
   
        });

    }


}

export default (data)=>{
    return (new ComponentItem(data));
}