import {Template,ComponentV2} from '/adarna.js';


class CreateMaterialQuantityForm extends ComponentV2{

    state(){
        return {
            material_item_id:{
                value:'',
                target: this.el.material_item_select,
                events:['change']
            },
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
            },
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
                    
                    this.calculateTotal();
                }
            },
            total:{
                value:0,
                target:this.el.total,
                onUpate:(data)=>{
                    this.el.total.value = data.value
                }
            }
        }
    }

    model(){
        return {
            component_item_id:'',
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

            t.div({class:'row mb-5'},()=>{

                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label('Material');

                        el.append(this.el.material_item_select);
                    });
                });

                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Quantity');

                        this.el.quantity = t.input({class:'form-control',type:'text'});
                    });
                });

                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Equivalent / Quantity');

                        
                        this.el.equivalent = t.input({class:'form-control'});
                    });
                });

                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Total Equivalent');
                        
                        this.el.total = t.input({class:'form-control',disabled:true});
                    });
                });

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
            this.el.quantity
        ],{
            negative:false,
            precision:2
        });



        this.el.btn_submit.onclick = ()=>{
            window.util.blockUI();

            window.util.$post('/api/project/create',{
                name: this.el.project_name.value,
                status: this.el.project_status.value
            }).then(reply=>{
                
                window.util.unblockUI();
                
                if(reply.status <= 0 ){
                    window.util.showMsg(reply);
                    return false;
                };
                
                window.util.drawerModal.close();
                window.util.navReload();
    
            
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