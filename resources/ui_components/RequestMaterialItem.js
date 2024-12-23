
import {Template,Component,$el} from '/adarna.js';

class RequestMaterialItem extends Component{

    init(){
        
        this.t = new Template();

    }

    model(){
        return {
            id                      :'',
            editable                : true,
            componentId             :'',
            componentItemId         :'',
            materialItemId          :'',
            unit                    :'',
            //prevApprovedQuantity    :'',
            materialBudgetQuantity  :'',
            requestedQuantity       :'',
            componentItemList       :{},
            materialList            :{},
            unitOptions             :{}
        }
    }

    state(){
        return {
            editable                : true,
            componentItemId         : null,
            materialItemId          : null,
            unit                    :'',
            prevApprovedQuantity    :'',
            materialBudgetQuantity  :'',
            requestedQuantity       :'',
            indexNumber             : 0
        }
    }

    view(){

        const t = this.t;

        let el = t.div({class:'items form-container mb-3'},()=>{

            t.div({class:'form-header'},()=>{
                this.el.indexNumber = t.label('Item #');
            });

            t.div ({class:'form-body'},()=>{

                this.el.deleteRow = t.div({class:'row mb-3',style:{
                    display: (this._model.editable) ? 'inline' : 'none'
                }},()=>{
                    t.div({class:'col-lg-12 text-end'},()=>{
                        this.el.deleteBtn = t.a({href:'#'},()=>{
                            t.i({class:'bi bi-x-circle'});
                        });
                    });
                });

                t.div({class:'row mb-3'},()=>{
                    t.div({class:'col-lg-12'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Component Item');
                            
                            this.el.componentItemSelect = t.select({class:'form-control'},()=>{
                                t.option({
                                    value: ''
                                },'-')

                                for(let key in this._model.componentItemList){
                                
                                    let item = this._model.componentItemList[key];

                                    t.option({
                                        value: item.value,
                                    },item.text);
                                }
                            });//select

                            if(this._model.editable){
                                this.el.componentItemSelect.disabled = false;
                            }else{
                                this.el.componentItemSelect.disabled = true;
                            }

                        });//div
                    });//div

                })//div row

                t.div({class:'row mb-3'},()=>{
                    t.div({class:'col-lg-12'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Material');
                            
                            this.el.materialSelect = t.select({class:'form-control'},()=>{
                                
                                t.option({
                                    value: ''
                                },'-')

                            });//select

                            if(this._model.editable){
                                this.el.materialSelect.disabled = false;
                            }else{
                                this.el.materialSelect.disabled = true;
                            }

                        });//div
                    });//div

                })//div row


                t.div({class:'row mb-3'},()=>{
                    
                    t.div({class:'col-lg-4'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Total Budget');
                            this.el.materialBudgetQuantity = t.input({
                                type:'text',
                                disabled:true,
                                class:'form-control text-center',
                                value: window.util.numberFormat(this._model.materialBudgetQuantity,2) 
                            });
                        });              
                    });

                    t.div({class:'col-lg-4'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Approved Request');
                            this.el.prevApprovedQuantity = t.input({
                                type:'text',
                                disabled:true,
                                class:'form-control text-center'
                            });
                        })                
                    });


                    t.div({class:'col-lg-4'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Remaining');
                            this.el.quantityRemaining = t.input({
                                type:'text',
                                disabled:true,
                                class:'form-control text-center',
                                value:''
                            });
                        });                
                    });//div col


                  
                });//div row

                t.div({class:'row mb-3'},()=>{
                    
                    t.div({class:'col-lg-6'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Request');
                           
                            this.el.requestedQuantity = t.input({
                                type:'text',
                                class:'form-control text-center',
                                value:this._model.requestedQuantity
                            });

                            if(this._model.editable){
                                this.el.requestedQuantity.disabled = false;
                            }else{
                                this.el.requestedQuantity.disabled = true;
                            }
                        });//div form-group
                    });//div col

                    t.div({class:'col-lg-6'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label("PO'd");
                            this.el.already_po = t.input({
                                type:'text',
                                disabled:true,
                                class:'form-control text-center',
                                value:''
                            });
                        });              
                    });//div col
                  

                });//div row

            });//div

        });//div

        return el;
    }

    controller(dom){
        
        this.deleteCallback = ()=>{};

        this.el.componentItemSelect.onchange = ()=>{
            this.setState('componentItemId',this.el.componentItemSelect.value,true);
        }

        this.el.materialSelect.onchange = ()=>{
            this.setState('materialItemId',this.el.materialSelect.value,true);

        }

        this.el.requestedQuantity.onkeyup = ()=>{
            if(this._state.editable){
                this.setState('requestedQuantity',this.el.requestedQuantity.value,true);
            }
        }

        this.el.requestedQuantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.requestedQuantity,e,2,false);
        }

        this.el.requestedQuantity.onpaste = (e)=>{
            e.preventDefault();
        }

        this.el.deleteBtn.onclick = (e) =>{
            e.preventDefault();
            this.deleteCallback(dom);
        }

        dom.handler.deleteCallback = (callback)=>{
            this.deleteCallback = callback;
        }

        dom.handler.setIndexNumber = (index)=>{
            this.setState('indexNumber',index,true);
        }

        dom.handler.getValues = ()=>{

            return {
                id                      : this._model.id,
                component_item_id       : this._state.componentItemId,
                material_item_id        : this._state.materialItemId,
                requested_quantity      : this._state.requestedQuantity
            }
        }

        dom.handler.editable = (flag)=>{
            this.setState('editable',flag,true);
        }

        dom.handler.updateApprovedQuantity = ()=>{
            this.el.prevApprovedQuantity.value = 'Calculating...';
            this.calculate_quantities(this._state.componentItemId, this._state.materialItemId,false);
        }

        
        
        this._state.editable                    = this._model.editable;      

        //This must happen in exact order
        this.el.componentItemSelect.value       = this._model.componentItemId;
        this.el.componentItemSelect.onchange();
        
        this.el.materialSelect.value            = this._model.materialItemId;
        this.el.materialSelect.onchange();
        
        this.el.requestedQuantity.value         = this._model.requestedQuantity;        
        this._state.requestedQuantity           = this._model.requestedQuantity;
        this.el.requestedQuantity.onkeyup();
          
        this.el.requestedQuantity.classList.remove('is-invalid');
        
        this.validate();
    }   

    validate(){
        
        //Check if selected material is deleted
        let selected_material_deleted = this.el.materialSelect.options[this.el.materialSelect.selectedIndex].disabled;

        if(selected_material_deleted){
            this.el.materialSelect.classList.add('is-invalid');
        }
    }

    get_total_po_quantity(){

        this.el.already_po.classList.remove('is-invalid');

        //Ignore if no id
        if(!this._model.id) {
            this.el.already_po.value = '';
            return false;
        }

        this.el.already_po.value = 'Calculating...';

        window.util.$get('/api/material_quantity_request/total_po_quantity',{
            material_quantity_request_item_id: this._model.id
        }).then(reply=>{

            if(!reply.status){
                window.util.showMsg(reply);
                return false;
            }


            this.el.already_po.value =  window.util.numberFormat(reply.data.total,2);

            //If empty string then 0
            let prev_approved   = window.util.pureNumber(this.el.prevApprovedQuantity.value);
            let total_budget    = window.util.pureNumber(this.el.materialBudgetQuantity.value);

            prev_approved = isNaN(prev_approved) ? 0 : prev_approved;
            total_budget  = isNaN(total_budget) ? 0 : total_budget;

            if(reply.data.total > total_budget){
                this.el.already_po.classList.add('is-invalid');
            }
        });
    }

    onStateChange_editable(flag){

        if(flag){
            this.el.componentItemSelect.disabled    = false;
            this.el.materialSelect.disabled         = false;
            this.el.requestedQuantity.disabled      = false;
            this.el.requestedQuantity.value         = window.util.numberFormat(this.el.requestedQuantity,2);
        }else{
            this.el.componentItemSelect.disabled    = true;
            this.el.materialSelect.disabled         = true;
            this.el.requestedQuantity.disabled      = true;
            this.el.requestedQuantity.value         = window.util.pureNumber(this.el.requestedQuantity);
        }

        this.el.deleteRow.style.display = (flag) ? 'inline' : 'none';
    }

    onStateChange_indexNumber(num){
        this.el.indexNumber.innerHTML = 'Item #'+num;
    }

    onStateChange_componentItemId(component_item_id){
        
        //Set elements to default
        this.el.materialSelect.innerHTML        = '';        
        this.el.materialBudgetQuantity.value    = '';
        this.el.prevApprovedQuantity.value      = '';
        this.el.requestedQuantity.value         = '';
        //this.el.balanceQuantity.value           = '';
        this.el.quantityRemaining.value         = '';
        this.el.already_po.value                = '';

        this.el.materialSelect.append(
            this.t.option({
                value: ''
            },'-')
        );

        //Repopulate material list based on component_item_id
        for(let key in this._model.materialList[ component_item_id ]){

            let item = this._model.materialList[ component_item_id ][key];

            let option = this.t.option({
                value: item.value
            },item.text);

            if(item.deleted_flag){
                option.innerHTML = item.text+' [Deleted]';
                option.disabled = true;
            }

            this.el.materialSelect.append(option);
        };


        //Set state to default
        this.setState('materialBudgetQuantity','');
        this.setState('totalRequeted','');
        this.setState('requestedQuantity','');
        this.setState('materialItemId','');
     
    }

    calculate_quantities(component_item_id,material_item_id){
        
        this.el.quantityRemaining.classList.remove('is-invalid');
        this.el.quantityRemaining.value = 'Calculating...';

        window.util.$get('/api/material_quantity_request/total_approved_quantity',{
            material_quantity_request_item_id: this._model.id,
            component_item_id:component_item_id,
            material_item_id: material_item_id
        }).then(reply=>{

            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            this.el.prevApprovedQuantity.value = window.util.numberFormat(reply.data.total_approved_quantity,2);

            let budget                         = window.util.pureNumber(this.el.materialBudgetQuantity.value);
            let approved                       = reply.data.total_approved_quantity;
            let balance                        = budget - approved;
            let request                        = window.util.pureNumber(this.el.requestedQuantity.value);
            this.el.quantityRemaining.value    = window.util.numberFormat(
                                                    window.util.roundUp(balance,2)
                                                );

            //If balance is negative
            if(balance < 0){
                this.el.quantityRemaining.addClass('is-invalid');
            }

            //Highlight over budget of request in review 
            if(
                !this._state.editable &&
                budget > approved &&
                balance < request
            ){
                this.el.requestedQuantity.classList.add('is-invalid');
            }
    

            this.get_total_po_quantity();
        });
    }

    onStateChange_requestedQuantity(newVal){

        let prevApprovedQuantity = this.el.prevApprovedQuantity.value;

        if(prevApprovedQuantity != 'Calculating...'){

            let remaining = window.util.pureNumber(this.el.materialBudgetQuantity.value) - window.util.pureNumber(this.el.prevApprovedQuantity.value);
            
            if( window.util.pureNumber(this.el.requestedQuantity.value) > remaining ){
            
                window.util.alert('Error','Requested quantity is out of budget');
            
                this.el.requestedQuantity.value = 0; 
                this.el.requestedQuantity.blur();
            }

        }else{

            window.util.alert('Error','Please wait for the approved quantity to finish calculation');
            
            this.el.requestedQuantity.value = 0; 
            this.el.requestedQuantity.blur();
        }

        
        /** Parang d na need */
        /*
        if(!isNaN(this.el.quantityRemaining.value)){
           
            setTimeout(()=>{
                
                
                let balance_quantity            = window.util.roundUp( this.el.quantityRemaining.valueAsNumber - parseFloat(newVal), 2);
                
                if(!isNaN(balance_quantity)){
                    this.el.balanceQuantity.value   = new Intl.NumberFormat().format(balance_quantity);
                }else{
                    this.el.balanceQuantity.value = '';
                }

            },500);
            
        }else{
            this.el.balanceQuantity.value = '';
        }*/
        
    }

    onStateChange_materialItemId(material_id){

        let material = this._model.materialList[ this._state.componentItemId ] ?? false;

        if(material){
            material = this._model.materialList[ this._state.componentItemId ][material_id] ?? false;
        }

        if(!material){

            this.el.materialBudgetQuantity.value    = '';
            this.el.prevApprovedQuantity.value      = '';
            this.el.requestedQuantity.value         = '';
            //this.el.balanceQuantity.value           = '';
            this.el.already_po.value                = '';

            this.setState('materialBudgetQuantity','');
            this.setState('prevApprovedQuantity','');
            this.setState('requestedQuantity','');

            return false;
        }
        
        this.el.materialBudgetQuantity.value    = window.util.numberFormat(material.quantity,2); 
        this.el.requestedQuantity.value         = '';
        this.el.prevApprovedQuantity.value      = '';
        //this.el.balanceQuantity.value           = '';
        this.el.already_po.value                = '';
        
        this.setState('materialBudgetQuantity',material.quantity);
        this.setState('requestedQuantity','');
        this.setState('prevApprovedQuantity','');
        
        this.calculate_quantities(this._state.componentItemId, material_id);

    }


}


export default (data)=>{
    return (new RequestMaterialItem(data));
}