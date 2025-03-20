import {Template,Component} from '/adarna.js';


class CreateContractItemForm extends Component{

    model(){
        return {
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
               
                t.div({class:'col-lg-12 mb-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Item Code');
                        this.el.item_code = t.input({class:'form-control',type:'text'});
                    });//div
                });//div col

            });

            t.div({class:'row mb-3'},()=>{
               
                t.div({class:'col-lg-12 mb-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Description');
                        this.el.description = t.input({class:'form-control',type:'text'});
                    });//div
                });//div col

            });

            t.div({class:'row mb-3'},()=>{
               
                t.div({class:'col-lg-12 mb-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Item Type');
                        this.el.item_type = t.select({class:'form-select'},()=>{
                            t.option({value:'MATR'},'Material');
                            t.option({value:'NMAT'},'Non-Material');
                            t.option({value:'OPEX'},'Operational Expense');
                        });
                    });//div
                });//div col

            });

            t.div({class:'row mb-3'},()=>{

                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'folder-form-container'},()=>{
                        t.div({class:'folder-form-tab'},'Contract');

                        t.div({class:'folder-form-body'},()=>{
                
                            t.div({class:'row mb-3'},()=>{
                                
                                t.div({class:'col-lg-4 mb-3'},()=>{
                                    t.div({class:'form-group'},()=>{
                                        t.label('Quantity');
                                        this.el.contract_quantity = t.input({class:'form-control', type:'text'});
                                    });//div
                                });//div col

                                t.div({class:'col-lg-4 mb-3'},()=>{
                                    t.div({class:'form-group'},()=>{
                                        t.label('Unit');
                                        this.el.contract_unit = t.select({class:'form-select'});
                                    });//div
                                });//div col


                                t.div({class:'col-lg-4 mb-3'},()=>{
                                    t.div({class:'form-group'},()=>{
                                        t.label('Unit Price');
                                        this.el.contract_unit_price = t.input({class:'form-control', type:'text'});
                                    });//div
                                });//div col

                            });//div row
                        })//div body

                    })//div container
                });//div col
            });

            t.div({class:'row mb-3'},()=>{

                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'folder-form-container'},()=>{
                        t.div({class:'folder-form-tab'},'POW/DUPA');

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
                t.div({class:'col-lg-12 text-end'},()=>{
                    this.el.btn_submit = t.button({class:'btn btn-primary me-3'},'Submit');
                    this.el.btn_cancel = t.button({class:'btn btn-secondary'},'Cancel');
                });
            });//div row
        });//div
    }

    controller(){

        this.el.btn_submit.onclick = ()=>{
          this.submit();
        }

        this.el.btn_cancel.onclick = ()=>{
            window.util.drawerModal.close();
        }

        this.setupUnits();

        this.setupNumbersOnlyInput();
    }

    submit(){

        window.util.blockUI();

        window.util.$post('/api/contract_item/create',{
            section_id                  : this._model.section_id,
            item_code                   : this.el.item_code.value,
            description                 : this.el.description.value,
            item_type                   : this.el.item_type.value,
            
            contract_quantity           : this.el.contract_quantity.value,
            contract_unit_price         : this.el.contract_unit_price.value,
            unit_id                     : this.el.contract_unit.value,

            ref_1_quantity              : this.el.ref_1_quantity.value,
            ref_1_unit_price            : this.el.ref_1_unit_price.value,
            ref_1_unit_id               : this.el.ref_1_unit.value
            
        }).then(reply=>{
            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };
            
            window.util.drawerModal.close();
            this._model.successCallback(reply.data);
        
        });
    }

    setupNumbersOnlyInput(){

        this.el.contract_quantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.contract_quantity,e,2,false);
        }

        this.el.contract_unit_price.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.contract_unit_price,e,2,false);
        }

        this.el.ref_1_quantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.ref_1_quantity,e,2,false);
        }

        this.el.ref_1_unit_price.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.ref_1_unit_price,e,2,false);
        }
        

    }

    setupUnits(){

        const t = new Template();

        this.el.contract_unit.append(
            t.option({value:''},' - ')
        );

        this.el.ref_1_unit.append(
            t.option({value:''},' - ')
        );
    

        for(let key in this._model.unit_options){

            let item = this._model.unit_options[key];

            //Skip if deleted
            if(item.deleted) continue;

            let contract_unit_option    = t.option({value:item.id},item.text);
            let ref_1_unit_option       = t.option({value:item.id},item.text);


            this.el.contract_unit.append(
                contract_unit_option
            );

            this.el.ref_1_unit.append(
                ref_1_unit_option
            );

        };
    }
}


export default (data)=>{
    return (new CreateContractItemForm(data));
}