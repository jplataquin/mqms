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
            component_item_quantity:0,
            component_item_name: '',
            component_item_unit_text:'',
            material_item_options:[]
        };
    }

    state(){
        return {
            grand_total:{
                value:0,
                target:this.el.grand_total,
                onUpdate:(data)=>{

                    if(data.value > this._model.component_item_quantity){
                        this.el.grand_total.classList.add('text-danger');
                    }else{
                        this.el.grand_total.classList.remove('text-danger');
                    }
                    
                    this.el.grand_total.innerText = window.util.numberFormat(data.value,2)+' '+this._model.component_item_unit_text;
                }
            }
        }
    }

    init(){

        this.material_item_registry = {};
        
    }

    view(){

        this._model.material_item_options.map(item=>{
            
            this.material_item_registry[item.id] = item;
        });

        const t = new Template();        

        return t.div({class:'container border border-primary'},(el)=>{

            t.div({class:'row'},()=>{
                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'table-responsive'},()=>{
                        t.table({class:'table'},()=>{
                            t.thead(()=>{
                                t.tr(()=>{
                                    t.th('Material');
                                    t.th({class:'text-center'},'Quantity');
                                    t.th({class:'text-center'},'Equivalent');
                                    t.th({class:'text-center'},'Total');
                                    t.th({class:'text-center'},'Options');
                                });
                            });
                            
                            this.el.material_quantity_item_container = t.tbody(()=>{});

                            t.tfoot(()=>{
                                t.td();
                                t.td();
                                t.th({class:'text-center'},'Material Total');
                                this.el.grand_total = t.td({class:'text-center'});
                                t.td();
                            });//foot
                            
                        });
                    });
                });
            });

        });
    }

    controller(){

        this.getMaterialQuantityList();

        this._dom.handler.refreshList = () =>{
            this.getMaterialQuantityList();
        }

        this._dom.handler.getGrandTotal = ()=>{
            return this.getState('grand_total');
        }
    }

    getMaterialQuantityList(){
        
        this.el.material_quantity_item_container.innerHTML = '';

        window.util.$get('/api/material_quantity/list',{
            component_item_id   :this._model.component_item_id,
            page                :1,
            limit               :0
        }).then(reply=>{
            
            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            }


            let grand_total = 0;
            
            reply.data.map(item=>{

                let material_item = this.material_item_registry[item.material_item_id];
                
                this.el.material_quantity_item_container.append(MaterialQuantityItem({
                    id                      : item.id,
                    name                    : material_item.brand+' '+material_item.name+' '+material_item.specification_unit_packaging+''.trim(),
                    material_item_id        : item.material_item_id,
                    quantity                : item.quantity,
                    equivalent              : item.equivalent,
                    component_item_name     : this._model.component_item_name,
                    component_item_quantity : this._model.component_item_quantity,
                    component_item_unit_text: this._model.component_item_unit_text,
                    get_grand_total         : ()=>{
                        return this.getState('grand_total')
                    },
                    after_action_callback   : ()=>{
                        this.getMaterialQuantityList();
                    }
                }));

                grand_total = grand_total + (item.quantity * item.equivalent);
            });

            this.setState('grand_total',grand_total);
        });
        
    }
}


export default (data)=>{
    return (new MaterialQuantityList(data));
}