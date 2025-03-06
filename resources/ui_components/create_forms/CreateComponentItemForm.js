import {Template,ComponentV2,Signal} from '/adarna.js';


class CreateComponentItemForm extends ComponentV2{

    state(){
        return {
            quantity: {
                value: 0,
                target: this.el.quantity,
                events:['keyup'],
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.quantity.value = data.value;
                    }

                    
                    this.updateComponentItemValues();
                }
            },
            unit:{
                value:'',
                target:this.el.unit,
                events:['change'],
                onUpdate: (data)=>{
                    
                    if(!data.event){
                        this.el.unit.value = data.value;
                    }
                }
            },
            name:{
                value:'',
                target: this.el.name,
                events:['keyup','paste'],
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.name.value = data.value;
                    }
                }
            },
            sum_flag:{
                value:true,
                target:this.el.sum_flag,
                events:['change'],
                onEvent:function(){
                    return this.checked;
                },
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.sum_flag.checked = data.value;
                    }
                }
            },
           
            function_type:{
                value:3,
                target: this.el.function_type,
                events:['change'],
                getValue:(val)=>{
                    return parseInt(val);
                },
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.function_type.value = data.value;
                    }
                    
                    this.updateComponentItemValues();
                }
            },
            variable:{
                value:'',
                target: this.el.variable,
                events:['keyup','change'],
                getValue: (val)=>{
                    return window.util.pureNumber(val);
                },
                onUpdate: (data)=>{

                    if(!data.event){
                        this.el.variable.value = window.util.pureNumber(data.value);
                    }


                    this.updateComponentItemValues();
                }
            },

            equivalent:{
                value:'',
                onUpdate:(data)=>{
                    this.el.equivalent.value = window.util.numberFormat(data.value)+' '+this._model.component_unit_text;
                }
            },
           
            ref_1_quantity:{
                value:'',
                target: this.el.ref_1_quantity,
                events:['keyup'],
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate:(data)=>{

                    if(!data.event) { 
                        this.el.ref_1_quantity.value = window.util.numberFormat(data.value,2);
                    }
                }
            },
            ref_1_unit_id:{
                value:'',
                target: this.el.ref_1_unit_id,
                events:['change'],
                onUpdate:(data)=>{

                    if(!data.event){
                        this.el.ref_1_unit_id.value = data.value;
                    }
                }
            },
            ref_1_unit_price:{
                value:'',
                target: this.el.ref_1_unit_price,
                events:['keyup'],
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate:(data)=>{

                    if(!data.event){
                        this.el.ref_1_unit_price.value = window.util.numberFormat(data.value,2);
                    }
                }
            },

            total_amount: {
                value:'',
                getValue:(val)=>{
                    return window.util.pureNumber(val);
                },
                onUpdate:(data)=>{
                    this.el.total_amount.value = 'P '+window.util.numberFormat(data.value,2);
                }
            },

            budget_price:{
                value:'',
                target: this.el.budget_price,
                events:['keyup','change'],
                getValue: (val)=>{
                    return window.util.pureNumber(val,2);
                },
                onUpdate:(data)=>{

                    if(!data.event){
                        this.el.budget_price.value = window.util.numberFormat(data.value,2);
                    }

                    this.setState('total_amount', 
                        ( window.util.pureNumber(data.value) * this.getState('quantity'))
                    );

                }
            },
                  
        }
    }


    model(){
        return {
            component_id:'',
            component_unit_text:'',
            component_quantity:'',
            component_use_count:'',
            unit_options:[],
            successCallback:(data)=>{}
        }
    }

    view(){
        const t= new Template();

        return t.div(()=>{

            t.div()

            t.div({class:'table-responsive'},()=>{
                t.table({class:'table'},()=>{
                        

                    t.tbody(()=>{
                        t.tr(()=>{
                            t.th({colspan:4},'Name');
                            t.th({colspan:1},'Sum Flag');
                        });
        
                        t.tr(()=>{
                            t.td({colspan:4},()=>{
                                this.el.name = t.input({class:'form-control name',type:'text'}); 
                            });
        
                            t.td({colspan:1},()=>{
                                t.div({class:'form-switch text-center'},()=>{                  
                                    this.el.sum_flag = t.input({class:'form-check-input sum_flag',value:1,type:'checkbox',checked:true});
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
                                this.el.ref_1_quantity = t.input({class:'form-control'});
                            });
                
                            t.td({colspan:1},()=>{
                
                                this.el.ref_1_unit_id = t.select({class:'form-control'},()=>{
                                    
                                    t.option({value:''},' - ');
                
                                    for(let i in this._model.unit_options){
                
                                        if(this._model.unit_options[i].deleted){
                                            t.option({value:i,disabled:true},this._model.unit_options[i].text+' [Deleted]');
                                        }else{
                                            t.option({value:i}, this._model.unit_options[i].text );
                                        }
                                    
                                    }
                                });
                
                            });
                
                            t.td({colspan:2},()=>{
                                this.el.ref_1_unit_price = t.input({class:'form-control'});
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
                                
                                this.el.function_type = t.select({class:'form-control function_type'},()=>{
                                    t.option({value:3,selected:true},'As Direct');
                                    t.option({value:4},'As Equivalent');
                                    t.option({value:1},'As Factor');
                                    t.option({value:2},'As Divisor');
                                    
                                });
        
                            });
        
                            
                            t.td({class:''},(el)=>{
                                
                                this.el.variable = t.input({class:'form-control variable', type:'text'});
        
                            });

                            t.td({class:''},(el)=>{
                                
                                this.el.quantity = t.input({class:'form-control quantity', type:'text'});
        
                            });
        
                        
        
                            t.td({class:''},(el)=>{
        
                                this.el.unit = t.select({class:'form-control unit'},()=>{
                                    
                                    for(let i in this._model.unit_options){
        
                                        if(this._model.unit_options[i].deleted){
                                            t.option({value:i,disabled:true},this._model.unit_options[i].text+' [Deleted]');
                                        }else{
                                            t.option({value:i}, this._model.unit_options[i].text );
                                        }
                                    }
                                });
            
                            });
        

                            t.td({class:''},(el)=>{
                                
                                this.el.equivalent = t.input({class:'form-control equivalent', type:'text'});
        
                            });
        
                        
                        });
                        
                        t.tr(()=>{
                            t.th({colspan:2},'Unit Price');
                            t.th({colspan:5},'Total Amount');
                        });
        
                        t.tr(()=>{

                            
                            t.td({colspan:2},(el)=>{
                                
                                this.el.budget_price = t.input({class:'form-control budget_price', type:'text'});
                                
                            });
                            
                            t.td({colspan:5},()=>{
                                this.el.total_amount = t.input({class:'form-control',disabled:true});
                            });
                        });


                    
                    
        
                    });//tbody
        
        
                });//table
            });
    
            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12 text-end'},()=>{
                    this.el.btn_submit = t.button({class:'btn btn-primary me-3'},'Submit');
                    this.el.btn_cancel = t.button({class:'btn btn-secondary'},'Cancel');
                });
            });//div row
        });//div

    }//view

    controller(){

        console.log('herhe',this._model);

        this.el.btn_submit.onclick = ()=>{
            this.submit();
          }
  
          this.el.btn_cancel.onclick = ()=>{
              window.util.drawerModal.close();
          }
  

        this.initEvents();
  
    }

    initEvents(){

        window.util.numbersOnlyInput([
            this.el.budget_price,
            this.el.ref_1_quantity,
            this.el.ref_1_unit_price,
            this.el.quantity
        ],{
            negative:false,
            precision:2
        });

        window.util.numbersOnlyInput(this.el.variable,{
            negative:false,
            precision:6
        });

    }

    updateComponentItemValues(){

        let equivalent_value                = 0;
        let quantity_value                  = 0;

        let component_quantity              = window.util.pureNumber(this._model.component_quantity);
        let component_use_count             = parseInt(this._model.component_use_count);
        
        let variable         = this.getState('variable');
        let quantity         = this.getState('quantity'); 
        let function_type    = this.getState('function_type')


        switch(function_type){
            case 1: //As Factor

                    this.el.quantity.disabled = true;
                    

                    quantity_value = window.util.roundUp(
                        (component_quantity * variable )  / component_use_count
                    ,2);
                    
                    if(quantity_value === Infinity){
                        quantity_value = 0;
                    }

                    this.setState('quantity',quantity_value);
                    
                    this.setState('equivalent','');

                break;

            case 2: //As Divisor
                    
                    this.el.quantity.disabled = true;
                    

                    quantity_value = window.util.roundUp( 
                        (component_quantity / variable)  / component_use_count
                    ,2);
                    
                    if(quantity_value === Infinity){
                        quantity_value = 0;
                    }

                    this.setState('quantity',quantity_value);
                    this.setState('equivalent','');

                break;

            case 3: //Direct
                    
                    this.el.variable.disabled = false;
                    this.el.quantity.disabled = true;
                    

                    variable = window.util.pureNumber(variable,2);

                    this.setState('quantity',variable);
                    this.setState('equivalent','');

                    
                break;
            case 4: //As Equivalent
                    
                this.el.variable.disabled = false;
                this.el.quantity.disabled = false;
                

                equivalent_value = ( variable * quantity ) * component_use_count; 
                

                if(equivalent_value !== Infinity){

                    equivalent_value = window.util.roundUp(equivalent_value,2);

                    this.setState('equivalent',equivalent_value);
                
                }else{

                    this.setState('equivalent','');
                
                }
                
                
                break;
        }



        this.calculateTotalAmount();
    }

    calculateTotalAmount(){
        

        this.setState('total_amount', 
            (this.getState('budget_price') * this.getState('quantity'))
        );
    }

    submit(){

        window.util.blockUI();

        window.util.$post('/api/component_item/create',{
            component_id                    : this._model.component_id,
            name                            : this.getState('name'),
            budget_price                    : this.getState('budget_price'),
            quantity                        : this.getState('quantity'),
            unit_id                         : this.getState('unit'),
            function_type_id                : this.getState('function_type'),
            function_variable               : this.getState('variable'),
            sum_flag                        : this.getState('sum_flag'),
            ref_1_quantity                  : this.getState('ref_1_quantity'),
            ref_1_unit_id                   : this.getState('ref_1_unit_id'),
            ref_1_unit_price                : this.getState('ref_1_unit_price')
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            
            window.util.drawerModal.close();


            this._model.successCallback(reply.data)

        });

    }
}

export default (data)=>{
    return (new CreateComponentItemForm(data));
}