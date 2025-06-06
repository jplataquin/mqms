import {Template,ComponentV2} from '/adarna.js';

class CreateMaterialQuantityForm extends ComponentV2{

    state(){
        return {
            material_item_id:{
                value:'',
                target: this.el.material_item_select,
                events:['change']
            },
            /**
            quantity:{
                value:'',
                target: this.el.quantity,
                events:['keyup','change'],
                getValue:(val)=>{
                    return window.util.pureNumber(val);
                },
                onUpdate:(data)=>{

                    if(!data.event){
                        this.el.quantity.value = data.value;
                    }

                    this.calculateTotal();
                }
            },**/
            equivalent:{
                value:'',
                target: this.el.equivalent,
                events:['keyup','change'],
                getValue:(val)=>{
                    return window.util.pureNumber(val);
                },
                onUpdate:(data)=>{

                    if(!data.event){
                        this.el.equivalent.value = data.value;
                    }
                    
                    //this.calculateTotal();
                }
            },
            /** 
            total:{
                value:0,
                target:this.el.total,
                onUpdate:(data)=>{
                    this.el.total.value = data.value+' '+this._model.component_item_unit_text;
                }
            }**/
        }
    }

    model(){
        return {
            component_item_id:'',
            component_item_name: '',
            component_item_quantity:0,
            component_item_unit_text:'',
            get_grand_total:()=>{ return 0; },
            after_add_callback: ()=>{},
            material_item_options:[]
        }
    }
    
    view(){
        const t = new Template();

        this.el.material_item_select = t.select({class:'form-control'},()=>{
            t.option({value:''},' - ');
        });

        this._model.material_item_options.map(item=>{
            
            let option = t.option({value:item.id},item.brand+' '+item.name + ' '+item.specification_unit_packaging+''.trim());
            
            this.el.material_item_select.t.append(option);

            //this.materialRegistry[item.id] = item.brand+' '+item.name +' '+item.specification_unit_packaging+''.trim();
        });

        return t.div(()=>{

            t.div({class:'row mb-3'},()=>{

                t.div({class:'col-lg-12'},()=>{
                    t.table({class:'table'},()=>{
                        t.tr(()=>{
                            t.th({width:'12%'},'Component Name');
                            t.td(this._model.component_item_name);
                        });
                        t.tr(()=>{
                            t.th('Component Quantity');
                            t.td(this._model.component_item_quantity+' '+this._model.component_item_unit_text);
                        });
                    });
                });

            });

            t.div({class:'row mb-5'},()=>{

                t.div({class:'col-lg-4 mb-3'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label({class:'mb-3'},'Material');

                        el.append(this.el.material_item_select);
                    });
                });
                /**
                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Quantity');

                        this.el.quantity = t.input({class:'form-control',type:'text'});
                    });
                });
                **/

                t.div({class:'col-lg-4 mb-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label({class:'mb-3'},'Equivalent');

                        
                        this.el.equivalent = t.input({class:'form-control'});
                    });
                });

                t.div({class:'col-lg-4 mb-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label({class:'mb-3'},'Unit');
                        
                        t.input({class:'form-control',disabled:true,value:this._model.component_item_unit_text});
                    });
                });

                /**
                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Total Equivalent');
                        
                        this.el.total = t.input({class:'form-control',disabled:true});
                    });
                });
                **/

            });


            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12 text-end'},()=>{
                    this.el.btn_submit = t.button({class:'btn btn-primary me-3'},'Submit');
                    this.el.btn_cancel = t.button({class:'btn btn-secondary'},'Cancel');
                });
            });//div row


        });
    }

    controller(){

        window.util.numbersOnlyInput([
            this.el.equivalent,
        ],{
            negative:false,
            precision:2
        });



        this.el.btn_submit.onclick = ()=>{

            let data = {
                component_item_id: this._model.component_item_id,
                material_item_id: this.getState('material_item_id'),
                equivalent: this.getState('equivalent')
            };

            window.util.$post('/api/material_quantity/create',data).then(reply=>{
                
                window.util.unblockUI();
                
                if(reply.status <= 0 ){
                    window.util.showMsg(reply);
                    return false;
                };
                
                window.util.drawerModal.close();
                this._model.after_add_callback();
                
            });
        }

        this.el.btn_cancel.onclick = ()=>{
            window.util.drawerModal.close();
        }

    }

    calculateTotal(){

        this.setState('total',(
            this.getState('quantity') * this.getState('equivalent')
        ));
    }
}


export default (data)=>{
    return (new CreateMaterialQuantityForm(data));
}