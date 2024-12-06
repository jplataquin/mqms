import {Template,ComponentV2} from '/adarna.js';


class CreateMaterialQuantityForm extends ComponentV2{

    model(){
        return {
            component_item_id:''
        }
    }
    
    view(){
        const t = Template();

        return t.div(()=>{

            t.div({class:'row'},()=>{

                t.div({clas:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Material');
                    });
                });

                t.div({clas:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Quantity');

                        t.input({class:'form-control'});
                    });
                });

                t.div({clas:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Equivalent / Quantity');

                        
                        t.input({class:'form-control'});
                    });
                });

                t.div({clas:'col-lg-3'},()=>{
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