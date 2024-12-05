import {Template,ComponentV2} from '/adarna.js';


class CreateComponentItemForm extends ComponentV2{

    model(){
        return {
            contract_item_id:'',
            section_id:'',
            unit_options:[]
        }
    }

    view(){
        const t= new Template();

        return t.div(()=>{
            
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
                                t.option({value:3},'As Direct');
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
    
           
        });//div

    }//view

    controller(){

        initEvents();
  
    }

    initEvents(){

        window.util.numbersOnlyInput([
            this.el.budget_price,
            this.el.ref_1_quantity,
            this.el.ref_1_unit_price
        ],{
            negative:false,
            precision:2
        });

        window.util.numbersOnlyInput(this.el.variable,{
            negative:false,
            precision:6
        });

    }

    submit(){

        // window.util.blockUI();

        // window.util.$post('/api/component/create',{
        //     section_id          : this._model.section_id,
        //     contract_item_id    : this._model.contract_item_id,
        //     name                : this.el.name.value,
        //     quantity            : this.el.quantity.value,
        //     use_count           : this.el.use_count.value,
        //     unit_id             : this.el.unit.value,
        //     sum_flag            : (this.el._sum_flag.checked == true) ? 1 : 0
        // }).then(reply=>{

        //     window.util.unblockUI();

        //     if(reply.status <= 0){
        //         window.util.showMsg(reply);
        //         return false;
        //     }

        //     //Component(reply.data.id)).to(component_list)
        //     this._model.callback(reply.data.id);
        //     window.util.drawerModal.close();
        // });

    }
}

export default (data)=>{
    return (new CreateComponentItemForm(data));
}