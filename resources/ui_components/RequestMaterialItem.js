
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
            prevApprovedQuantity    :'',
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

                this.el.deleteRow = t.div({class:'row',style:{
                    display: (this._model.editable) ? 'inline' : 'none'
                }},()=>{
                    t.div({class:'col-12 text-end'},()=>{
                        this.el.deleteBtn = t.a({href:'#'},()=>{
                            t.i({class:'bi bi-x-circle'});
                        });
                    });
                });

                t.div({class:'row'},()=>{
                    t.div({class:'col-12'},()=>{
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

                t.div({class:'row mt-3'},()=>{
                    t.div({class:'col-12'},()=>{
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


                t.div({class:'row mt-3'},()=>{
                    
                    t.div({class:'col-4'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Budget');
                            this.el.materialBudgetQuantity = t.input({
                                type:'text',
                                disabled:true,
                                class:'form-control text-center',
                                value:this._model.materialBudgetQuantity
                            });
                        });              
                    });

                    t.div({class:'col-4'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Approved');
                            this.el.prevApprovedQuantity = t.input({
                                type:'text',
                                disabled:true,
                                class:'form-control text-center',
                                value:this._model.prevApprovedQuantity
                            });
                        })                
                    });


                    t.div({class:'col-4'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Remaining');
                            this.el.quantityRemaining = t.input({
                                type:'text',
                                disabled:true,
                                class:'form-control text-center',
                                value:''
                            });
                        });                
                    });

                });//div row


                t.div({class:'row mt-3'},()=>{
                    t.div({class:'col-6'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Request Quantity');
                           
                            this.el.requestedQuantity = t.input({
                                type:'text',
                                class:'form-control',
                                value:this._model.requestedQuantity
                            });

                            if(this._model.editable){
                                this.el.requestedQuantity.disabled = false;
                            }else{
                                this.el.requestedQuantity.disabled = true;
                            }
                        });
                    });

                    t.div({class:'col-6'},()=>{
                        t.div({class:'form-group'},()=>{
                            t.label('Balance Quantity');
                           
                            this.el.balanceQuantity = t.input({
                                type:'text',
                                class:'form-control',
                                value: '',
                                disabled:true
                            });

                        });
                    });
                });

            });//div

        });//div

        return el;
    }

    controller(dom){
        
        this.deleteCallback = ()=>{};

        this.el.componentItemSelect.onchange = ()=>{
            this.setState('componentItemId',this.el.componentItemSelect.value);
        }

        this.el.materialSelect.onchange = ()=>{
            this.setState('materialItemId',this.el.materialSelect.value);
        }

        this.el.requestedQuantity.onkeyup = ()=>{
            this.setState('requestedQuantity',this.el.requestedQuantity.value);
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
            this.setState('indexNumber',index);
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
            this.setState('editable',flag);
        }

        dom.handler.updateApprovedQuantity = ()=>{
            this.el.prevApprovedQuantity.value = 'fetching data...';
            this.getApprovedQuantity(this._state.componentItemId, this._state.materialItemId,false);
        }

        console.log(this._model.materialItemId);
        this.el.componentItemSelect.value       = this._model.componentItemId;
        this.el.materialSelect.value            = this._model.materialItemId;
        this.el.requestedQuantity.value         = this._model.requestedQuantity;
        
        this._state.requestedQuantity           = this._model.requestedQuantity;
        this._state.editable                    = this._model.editable;        

        this.el.componentItemSelect.onchange();
        setTimeout(()=>{
            this.el.materialSelect.onchange();
        },500);
        
        this.el.requestedQuantity.onkeyup();

    }

    onStateChange_editable(flag){

        if(flag){
            this.el.componentItemSelect.disabled    = false;
            this.el.materialSelect.disabled         = false;
            this.el.requestedQuantity.disabled      = false;
        }else{
            this.el.componentItemSelect.disabled    = true;
            this.el.materialSelect.disabled         = true;
            this.el.requestedQuantity.disabled      = true;
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
        this.el.balanceQuantity.value           = '';
        this.el.materialSelect.append(
            this.t.option({
                value: ''
            },'-')
        );

        //Repopulate material list based on component_item_id
        for(let key in this._model.materialList[ component_item_id ]){

            let item = this._model.materialList[ component_item_id ][key];

            this.el.materialSelect.append(
                this.t.option({
                    value: item.value
                },item.text)
            );
        };

        let component_item = this._model.componentItemList[ component_item_id ];

        //Set state to default
        this.setState('unit', this._model.unitOptions[component_item.unit_id].text );
        this.setState('materialBudgetQuantity','');
        this.setState('totalRequeted','');
        this.setState('requestedQuantity','');
        this.setState('materialItemId','');
     
    }

    getApprovedQuantity(component_item_id,material_item_id,blockUI){
        
        if(blockUI){
            window.util.blockUI();
        }
        
        window.util.$get('/api/material_quantity_request/total_approved_quantity',{
            material_quantity_request_item_id: this._model.id,
            component_item_id:component_item_id,
            material_item_id: material_item_id
        }).then(reply=>{

            if(blockUI){
                window.util.unblockUI();
            }

            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            this.el.prevApprovedQuantity.value = reply.data.total_approved_quantity;
            this.el.quantityRemaining.value    = parseFloat(this.el.materialBudgetQuantity.value) - parseFloat(reply.data.total_approved_quantity);

        });
    }

    onStateChange_requestedQuantity(newVal){

        if(!isNaN(this.el.prevApprovedQuantity.value)){

            let remaining = parseFloat(this.el.materialBudgetQuantity.value) - parseFloat(this.el.prevApprovedQuantity.value);
            
            if( parseFloat(this.el.requestedQuantity.value) > remaining ){
            
                window.util.alert('Error','Requested quantity is out of budget');
            
                this.el.requestedQuantity.value = 0; 
                this.el.requestedQuantity.blur();
            }

        }else{

            window.util.alert('Error','Please wait wait for the approved quantity to finish calculation');
            
            this.el.requestedQuantity.value = 0; 
            this.el.requestedQuantity.blur();
        }

            
        if(!isNaN(this.el.quantityRemaining.value)){
            this.el.balanceQuantity.value = parseFloat(this.el.quantityRemaining.value) - parseFloat(newVal);
        }else{
            this.el.balanceQuantity.value = 'Calculating...';
        }
        
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

            this.setState('materialBudgetQuantity','');
            this.setState('prevApprovedQuantity','');
            this.setState('requestedQuantity','');

            return false;
        }
        
        this.el.materialBudgetQuantity.value    = material.quantity; 
        this.el.requestedQuantity.value         = '';
        this.el.prevApprovedQuantity.value      = '';
        this.el.balanceQuantity.value           = '';
        
        this.setState('materialBudgetQuantity',material.quantity);
        this.setState('requestedQuantity','');
        this.setState('prevApprovedQuantity','');
        
        this.getApprovedQuantity(this._state.componentItemId, material_id,true);
    }
}


export default (data)=>{
    return (new RequestMaterialItem(data));
}