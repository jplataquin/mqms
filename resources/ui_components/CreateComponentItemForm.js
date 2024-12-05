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
            
            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-10'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Name');
                        t.input({class:'form-control',type:'text'});
                    });
                });

                t.div({class:'col-lg-2'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Sum Flag');
                        t.div({class:'form-switch text-center'},()=>{
                            this.el.sum_flag = t.input({type:'checkbox', class:'form-check-input', value:1, checked:true});
                        });
                    });
                });
            });//div row

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-2'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Function Type');
                        t.select({class:'form-select'},()=>{
                            t.option({},'As Direct');
                            t.option({},'As Equivalent');
                            t.option({},'As Factor');
                            t.option({},'As Divisor');
                        });
                    });
                }); //div col

                t.div({class:'col-lg-2'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Variable');
                        this.el.variable = t.input({class:'form-control',type:'text'});
                    });
                }); //div col
                t.div({class:'col-lg-2'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Quantity');
                        this.el.quantity = t.input({class:'form-control',type:'text'});
                    });
                }); //div col
                t.div({class:'col-lg-2'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Equivalent');
                        this.el.equivalent = t.input({class:'form-control',type:'text'});
                    });
                }); //div col
                t.div({class:'col-lg-2'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Unit');
                        this.el.unit_id = t.select({class:'form-select'});
                    });
                }); //div col
                t.div({class:'col-lg-2'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Unit Price');
                        this.el.unit_price = t.input({class:'form-control',type:'text'});
                    });
                }); //div col
            });//div row

            t.div({class:'row mb-3'});
        });//div

    }//view

    controller(){

       
  
    }

    setupUnits(){

        // const t = new Template();

        // this.el.unit.append(
        //     t.option({value:''},' - ')
        // );

    

        // for(let key in this._model.unit_options){

        //     let item = this._model.unit_options[key];

        //     //Skip if deleted
        //     if(item.deleted) continue;

        //     let unit_option    = t.option({value:item.id},item.text);


        //     this.el.unit.append(
        //         unit_option
        //     );

        // };
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