import {Template,Component} from '/adarna.js';


class CreateComponentForm extends Component{

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
                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Name');
                        this.el.name = t.input({class:'form-control',type:'text'});
                    });
                });//div col
            });//div row

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
                    this.el.submit_btn   = t.div({class:'btn btn-primary'},'Submit');
                    this.el.cancel_btn  = t.div({class:'btn btn-secondary'},'Cancel');
                });
            });//div row
        });//div

    }//view

    controller(){

        this.el.quantity.onkeypress = (e) =>{
            return window.util.inputNumber(this.el.quantity,e,2,false);
        }

        this.el.use_count.onkeypress = (e) =>{
            return window.util.inputNumber(this.el.use_count,e,2,false);
        }

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

        this.el.unit.append(
            t.option({value:''},' - ')
        );

    

        for(let key in this._model.unit_options){

            let item = this._model.unit_options[key];

            //Skip if deleted
            if(item.deleted) continue;

            let unit_option    = t.option({value:item.id},item.text);


            this.el.unit.append(
                unit_option
            );

        };
    }


    submit(){

        window.util.blockUI();

        window.util.$post('/api/component/create',{
            section_id          : this._model.section_id,
            contract_item_id    : this._model.contract_item_id,
            name                : this.el.name.value,
            quantity            : this.el.quantity.value,
            use_count           : this.el.use_count.value,
            unit_id             : this.el.unit.value,
            sum_flag            : (this.el._sum_flag.checked == true) ? 1 : 0
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            //Component(reply.data.id)).to(component_list)
            this._model.callback(reply.data.id);
            window.util.drawerModal.close();
        });

    }
}

export default (data)=>{
    return (new CreateComponentForm(data));
}