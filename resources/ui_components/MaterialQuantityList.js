import {Template,ComponentV2,Signal} from '/adarna.js';


// function calculateTotalEquivalent(a,b){
//     return window.util.roundUp(parseFloat(a) * parseFloat(b),2);
// }


const signal = new Signal();



class MaterialQuantityList extends ComponentV2{

    model(){
        return {
            materialItemOptions:[]
        };
    }

    state(){
        return {
            equivalent:{
                value:0,
                target:this.el.equivalent,
                events:['keyup','change'],
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

    view(){

        const t = new Template();

        this.el.material_item_select = t.select({class:'form-control'},()=>{
            t.option({value:''},' - ');
        });

        this._model.materialItemOptions.map(item=>{
            
            let option = t.option({value:item.id},item.brand+' '+item.name + ' '+item.specification_unit_packaging+''.trim());
            
            this.el.material_item_select.append(option);

        });

        return t.div({class:'container'},()=>{

            t.div({class:'row'},()=>{

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

        });
    }

    controller(){

        window.util.numbersOnlyInput([
            this.el.quantity,
            this.el.equivalent
        ],{
            precision:2
        });

        
        this.el.addBtn.onlcick = (e)=>{
            e.preventDefault();
        }
    }
}


export default (data)=>{
    return (new MaterialQuantityList(data));
}