import {Template,ComponentV2,Signal} from '/adarna.js';
import MaterialQuantityItem from '/ui_components/MaterialQuantityItem.js';

// function calculateTotalEquivalent(a,b){
//     return window.util.roundUp(parseFloat(a) * parseFloat(b),2);
// }


const signal = new Signal();



class MaterialQuantityList extends ComponentV2{

    model(){
        return {
            component_item_id:0,
            material_item_options:[]
        };
    }

    state(){
        return {
            material_item:{
                value:'',
                target: this.el.material_item_select,
                events:['change']
            },
            equivalent:{
                value:0,
                target:this.el.equivalent,
                events:['keyup'],
                getValue:(val)=>{
                    return window.util.pureNumber(val);
                },
                onUpdate:(data)=>{

                    this.setState('total',
                        window.util.pureNumber(data.value) * this.getState('quantity')
                    );
                }
            },
            quantity:{
                value:0,
                target:this.el.quantity,
                events:['keyup','change'],
                getValue:(val)=>{
                    return window.util.pureNumber(val);
                },
                onUpdate:(data)=>{

                    this.setState('total',
                        window.util.pureNumber(data.value) * this.getState('equivalent')
                    );
                }
            },
            total:{
                value:0,
                target:this.el.total,
                onUpdate:(data)=>{

                    this.el.total.value = window.util.pureNumber(data.value,2);
                }
            }
        }
    }

    init(){
        this.material_item_registry = {};

        
    }

    view(){

        const t = new Template();

        this.el.material_item_select = t.select({class:'form-control'},()=>{
            t.option({value:''},' - ');
        });

        this._model.material_item_options.map(item=>{
            
            let option = t.option({value:item.id},item.brand+' '+item.name + ' '+item.specification_unit_packaging+''.trim());
            
            this.el.material_item_select.append(option);

            this.material_item_registry[item.id] = item;
        
        });

        
        let rem = t.div({class:'row'},()=>{

            t.div({class:'col-lg-12'},()=>{

                t.div({class:'folder-form-container'},()=>{

                    t.div({class:'folder-form-tab'},'Material Quantity');
                            
                    t.div({class:'folder-form-body'},()=>{
    
                        t.div({class:'row'},()=>{
    
                            t.div({class:'col-lg-5'},()=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Material');
                                    t.el(this.el.material_item_select);
                                });
                            });
                        
                            t.div({class:'col-lg-2'},()=>{             
                                t.div({class:'form-group'},()=>{
                                    t.label('Quantity');
                                    this.el.quantity = t.input({class:'form-control', type:'text'});
                                });
                            });                           
    
                            t.div({class:'col-lg-2'},()=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Equivalent');
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
                });

            });//col

        });//row

        return t.div({class:'container border border-primary'},(el)=>{



            t.div({class:'row'},()=>{
                t.div({class:'col-lg-12'},()=>{
                    t.table({class:'table'},()=>{
                        t.thead(()=>{
                            t.tr(()=>{
                                t.th('Material');
                                t.th('Quantity');
                                t.th('Equivalent');
                                t.th('Total');
                                t.th({class:'text-center'},'Options');
                            });
                        });
                        
                        this.el.material_quantity_item_container = t.tbody(()=>{});

                        t.tfoot(()=>{
                        });//foot
                        
                    });
                });
            });

        });
    }

    controller(){

        window.util.numbersOnlyInput([
            this.el.quantity,
            this.el.equivalent
        ],{
            precision:2
        });

        
        this.el.addBtn.onclick = (e)=>{
            e.preventDefault();
            this.addMaterialQuantity();
        }

        this.getMaterialQuantityList();
    }

    addMaterialQuantity(){

        let data = {
            component_item_id   : this._model.component_item_id,
            material_item_id    : this.getState('material_item'),
            quantity            : this.getState('quantity'),
            equivalent          : this.getState('equivalent')
        };

        
        window.util.blockUI();

        window.util.$post('/api/material_quantity/create',data).then(reply=>{
            
            window.util.unblockUI();
                
            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            
            // this.appendMaterial({
            //     id: reply.data.id,
            //     material_item_id: data.material_item_id,
            //     quantity: data.quantity,
            //     equivalent: data.equivalent
            // });

            
        });
    }

    getMaterialQuantityList(){
        
        window.util.$get('/api/material_quantity/list',{
            component_item_id   :this._model.component_item_id,
            page                :1,
            limit               :0
        }).then(reply=>{
            
            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            }


            reply.data.map(item=>{

                this.el.material_quantity_item_container.append(MaterialQuantityItem({
                    id                      : item.id,
                    name                    : material_item_registry[item.id].brand+' '+material_item_registry[item.id].name+' '+material_item_registry[item.id].specification_unit_packaging+''.trim(),
                    material_item_id        : item.material_item_id,
                    quantity                : item.quantity,
                    equivalent              : item.equivalent
                }));

            });
        })
        
    }
}


export default (data)=>{
    return (new MaterialQuantityList(data));
}