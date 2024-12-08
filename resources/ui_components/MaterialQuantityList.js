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
            grand_total:{
                value:0,
                target:this.el.grand_total,
                onUpdate:(data)=>{

                    this.el.grand_total.innerText = window.util.numberFormat(data.value,2);
                }
            }
        }
    }

    init(){

        this.material_item_registry = {};
        
    }

    view(){



        const t = new Template();        

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
                            t.td();
                            t.td();
                            t.td('Grand Total');
                            this.el.grand_total = t.th();
                            t.td();
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


            let grand_total = 0;

            reply.data.map(item=>{

                let material_item = this.material_item_registry[item.material_item_id];
                
                this.el.material_quantity_item_container.append(MaterialQuantityItem({
                    id                      : item.id,
                    name                    : material_item.brand+' '+material_item.name+' '+material_item.specification_unit_packaging+''.trim(),
                    material_item_id        : item.material_item_id,
                    quantity                : item.quantity,
                    equivalent              : item.equivalent
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