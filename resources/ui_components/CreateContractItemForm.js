import {Template,Component} from '/adarna.js';


class CreateContractItemForm extends Component{

    model(){
        return {
            section_id:'',
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
                        this.el.item_name = t.input({class:'form-control',type:'text'});
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
    }
}


export default (data)=>{
    return (new CreateProjectForm(data));
}