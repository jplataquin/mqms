import {Template,ComponentV2} from '/adarna.js';


class CreateMaterialQuantityForm extends ComponentV2{

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

            this.materialRegistry[item.id] = item.brand+' '+item.name +' '+item.specification_unit_packaging+''.trim();
        });

        return t.div(()=>{

            t.div({class:'row'},()=>{

                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label('Material');

                        el.append(this.el.material_item_select);
                    });
                });

                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Quantity');

                        t.input({class:'form-control'});
                    });
                });

                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Equivalent / Quantity');

                        
                        t.input({class:'form-control'});
                    });
                });

                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Total');
                        
                        t.input({class:'form-control',disabled:true});
                    });
                });

            });

        });
    }
}


export default (data)=>{
    return (new CreateMaterialQuantityForm(data));
}