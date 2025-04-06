import {Template,Component} from '/adarna.js';


class CreateComponentForm extends Component{

    model(){
        return {
            contract_item_id:'',
            section_id:'',
            successCallback: ()=>{
                window.util.navReload();
            },
            unit_options:[]
        }
    }

    view(){
        const t= new Template();

        return t.div(()=>{
            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Name');
                        this.el.name = t.input({class:'form-control',type:'text'});
                    });
                });//div col
            });//div row

            t.div({class:'row mb-3'},()=>{

                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'folder-form-container'},()=>{
                        t.div({class:'folder-form-tab'},'Reference');

                        t.div({class:'folder-form-body'},()=>{
                
                            t.div({class:'row mb-3'},()=>{
                                
                                t.div({class:'col-lg-4 mb-3'},()=>{
                                    t.div({class:'form-group'},()=>{
                                        t.label('Quantity');
                                        this.el.ref_1_quantity = t.input({class:'form-control', type:'text'});
                                    });//div
                                });//div col

                                t.div({class:'col-lg-4 mb-3'},()=>{
                                    t.div({class:'form-group'},()=>{
                                        t.label('Unit');
                                        this.el.ref_1_unit = t.select({class:'form-select'});
                                    });//div
                                });//div col


                                t.div({class:'col-lg-4 mb-3'},()=>{
                                    t.div({class:'form-group'},()=>{
                                        t.label('Unit Price');
                                        this.el.ref_1_unit_price = t.input({class:'form-control', type:'text'});
                                    });//div
                                });//div col

                            });//div row
                        })//div body

                    })//div container
                });//div col
            });


            

            t.div({class:'row mb-3'},()=>{

                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Quantity');
                        this.el.quantity = t.input({class:'form-control',type:'text'});
                    });
                });//div col
                
                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Unit');
                        this.el.unit = t.select({class:'form-select'});
                    });
                });//div col
                
                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Use Count');
                        this.el.use_count = t.input({class:'form-control',type:'text'});
                    });
                });//div col
                
                t.div({class:'col-lg-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Sum Flag');
                        t.div({class:'form-switch text-center'},()=>{
                            this.el.sum_flag = t.input({type:'checkbox', class:'form-check-input', value:1, checked:true});
                        });
                    });
                });//div col

            });//div row

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12 text-end'},()=>{
                    this.el.submit_btn   = t.div({class:'btn btn-primary me-3'},'Submit');
                    this.el.cancel_btn  = t.div({class:'btn btn-secondary'},'Cancel');
                });
            });//div row
        });//div

    }//view

    controller(){



        window.util.numbersOnlyInput([
            this.el.ref_1_quantity,
            this.el.ref_1_unit_price,
            this.el.quantity,
            this.el.use_count
        ],{
            negative: false,
            precision: 2
        }); 

        this.el.submit_btn.onclick = ()=>{
            this.submit();
        }
  
        this.el.cancel_btn.onclick = ()=>{
            window.util.drawerModal.close();
        }
  
        this.setupUnits();
  
    }

    setupUnits(){

        const t = new Template();

        this.el.ref_1_unit.append(
            t.option({value:''},' - ')
        );

        this.el.unit.append(
            t.option({value:''},' - ')
        );

        

        for(let key in this._model.unit_options){

            let item = this._model.unit_options[key];

            //Skip if deleted
            if(item.deleted) continue;

            let unit_option_a    = t.option({value:item.id},item.text);


            let unit_option_b    = t.option({value:item.id},item.text);


            this.el.ref_1_unit.append(
                unit_option_a
            );

            this.el.unit.append(
                unit_option_b
            );

        };
    }


    submit(){

        window.util.blockUI();

        window.util.$post('/api/component/create',{
            section_id          : this._model.section_id,
            contract_item_id    : this._model.contract_item_id,
            name                : this.el.name.value,

            ref_1_quantity      : this.el.ref_1_quantity.value,
            ref_1_unit_id       : this.el.ref_1_unit.value,
            ref_1_unit_price    : this.el.ref_1_unit_price.value,

            quantity            : this.el.quantity.value,
            use_count           : this.el.use_count.value,
            unit_id             : this.el.unit.value,
            sum_flag            : (this.el.sum_flag.checked == true) ? 1 : 0
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.drawerModal.close();

            this._model.successCallback(reply.data);
        });

    }
}

export default (data)=>{
    return (new CreateComponentForm(data));
}