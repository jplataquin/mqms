
import {Template,Component,$el} from '/adarna.js';

class RequestMaterialItem extends Component{

    init(){
        
        this.t = new Template();

    }

    model(){
        return {
            id:'',
            editable: true,
            componentId:'',
            componentItemId:'',
            materialItemId:'',
            unit:'',
            componentItemBudget:'',
            equivalent:'',
            prevApprovedQuantity:'',
            materialBudgetQuantity:'',
            requestedQuantity:'',
            componentItemList:{},
            materialList:{},
            unitOptions:{}
        }
    }

    state(){
        return {
            editable: true,

            componentItemId: null,
            materialItemId: null,

            unit:'',
            componentItemBudget:'',
        
            equivalent: '',
            prevApprovedQuantity:'',
            materialBudgetQuantity:'',

            requestedQuantity:'',

            indexNumber: 0
        }
    }

    view(){

        const t = this.t;

        let el = t.div({class:'items border border-primary ps-3 pe-3 pb-3 mt-3'},()=>{
            this.el.deleteRow = t.div({class:'row',style:{
                display: (this._model.editable) ? 'inline' : 'none'
            }},()=>{
                t.div({class:'col-12 text-end'},()=>{
                    this.el.deleteBtn = t.a({href:'#'},'[ X ]');
                });
            });

            t.div({class:'row'},()=>{
                t.div({class:'col-12'},()=>{
                    t.div({class:'form-group'},()=>{
                        this.el.indexNumber = t.label('Item #');
                        
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
                t.div({class:'col-6'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Component Item Budget');
                        this.el.componentItemBudget = t.input({
                            type:'text',
                            disabled:true,
                            class:'form-control',
                            value:this._model.componentItemBudget +' '+this._model.unit
                        });
                        
                    });                
                });

                t.div({class:'col-6'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Material Equivalent');
                        this.el.equivalent = t.input({
                            type:'text',
                            disabled:true,
                            class:'form-control',
                            value:this._model.equivalent+' '+this._model.unit});
                    });               
                });

            });

            t.div({class:'row mt-3'},()=>{
                

                t.div({class:'col-6'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Material Budget Quantity');
                        this.el.materialBudgetQuantity = t.input({
                            type:'text',
                            disabled:true,
                            class:'form-control',
                            value:this.el.materialBudgetQuantity
                        });
                    });              
                });

                t.div({class:'col-6'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Approved Quantity');
                        this.el.prevApprovedQuantity = t.input({
                            type:'text',
                            disabled:true,
                            class:'form-control',
                            value:this._model.prevApprovedQuantity
                        });
                    })                
                });
                
            });


           
            t.div({class:'row mt-3'},()=>{
                
                
                t.div({class:'col-6'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Quantity Remaining');
                        this.el.quantityRemaining = t.input({
                            type:'text',
                            disabled:true,
                            class:'form-control',
                            value:''
                        });
                    });                
                });

                t.div({class:'col-6'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Total Material Equivalent');
                        this.el.totalEquivalent = t.input({type:'text',disabled:true,class:'form-control'});
                    })                
                });
                

            });//row

            t.div({class:'row mt-3'},()=>{
                t.div({class:'col-12'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Requested Quantity');
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
            });

        });//div

        return el;
    }

    controller(dom){
        
        this.el.componentItemSelect.onchange = ()=>{
            this.setState('componentItemId',this.el.componentItemSelect.value);
        }

        this.el.materialSelect.onchange = ()=>{
            
            this.setState('materialItemId',this.el.materialSelect.value);
        }

        this.el.requestedQuantity.onkeyup = ()=>{
            this.el.totalEquivalent.value = parseFloat(this.el.requestedQuantity.value * this._state.equivalent).toFixed(2)+' '+this._state.unit;

            let remaining = this.el.materialBudgetQuantity.value - this.el.prevApprovedQuantity.value;
            
            if( parseFloat(this.el.requestedQuantity.value) > remaining ){
                 this.el.requestedQuantity.value = 0; 
                 this.el.totalEquivalent.value = '';
                 this.el.requestedQuantity.blur();
                 window.util.showMsg('Not enough material budget');
            }

            this.setState('requestedQuantity',this.el.requestedQuantity.value);
        }

        this.el.requestedQuantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.requestedQuantity,e,2,false);
        }

        this.el.requestedQuantity.onpaste = (e)=>{
            e.preventDefault();
        }

        this.deleteCallback = ()=>{};

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

        this.el.componentItemSelect.value       = this._model.componentItemId;
        this.el.componentItemSelect.onchange();
        
        this.el.materialSelect.value            = this._model.materialItemId;
        this.el.materialSelect.onchange();
        
        this.el.requestedQuantity.value         = this._model.requestedQuantity;
        this._state.requestedQuantity           = this._model.requestedQuantity;
        this.el.requestedQuantity.onkeyup();

        this._state.editable                    = this._model.editable;
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

    onStateChange_componentItemId(componentItemId){
        
        this.el.materialSelect.innerHTML        = '';

        this.el.componentItemBudget.value = '';
        this.setState('componentItemBudget','');
       
        this.setState('unit','');

        this.el.equivalent.value = '';
        this.setState('equivalent','');

        this.el.materialBudgetQuantity.value = '';
        this.setState('materialBudgetQuantity','');

        this.el.prevApprovedQuantity.value = '';
        this.setState('totalRequeted','');
        
        this.el.requestedQuantity.value = '';
        this.setState('requestedQuantity');
        
        
        this.el.materialSelect.append(
            this.t.option({
                value: ''
            },'-')
        );

        if(typeof this._model.materialList[ componentItemId ] == 'undefined'){
            return false;
        }

        let componentItem = this._model.componentItemList[ componentItemId ];

        this.setState('unit', this._model.unit_options[componentItem.component_unit_id]->text );

        this.el.componentItemBudget.value = componentItem.quantity +' '+componentItem.unit;
        this.setState('componentItemBudget',componentItem.quantity);
       
        
        this.setState('materialItemId','');
       
        for(let key in this._model.materialList[ componentItemId ]){

            let item = this._model.materialList[ componentItemId ][key];

            this.el.materialSelect.append(
                this.t.option({
                    value: item.value
                },item.text)
            );
        };

        
    }

    getApprovedQuantity(component_item_id,material_item_id,blockUI){
        
        if(blockUI){
            window.util.blockUI();
        }
        
        window.util.$get('/api/material_quantity_request/total_approved_quantity',{
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

            this.el.prevApprovedQuantity.value = reply.data.total_requested;

            this.el.quantityRemaining.value = this.el.materialBudgetQuantity.value - reply.data.total_requested;
        });
    }

    onStateChange_materialItemId(material_id){

        let material = this._model.materialList[ this._state.componentItemId ] ?? false;

        if(material){
            material = this._model.materialList[ this._state.componentItemId ][material_id] ?? false;
        }

        if(!material){
            
            this.el.equivalent.value = '';
            this.setState('equivalent','');

            this.el.materialBudgetQuantity.value = '';
            this.setState('materialBudgetQuantity','');

            this.el.prevApprovedQuantity.value = '';
            this.setState('prevApprovedQuantity','');

            this.el.requestedQuantity.value = '';
            this.setState('requestedQuantity','');

            return false;
        }
        
        this.el.totalEquivalent.value = '';

        this.el.equivalent.value = material.equivalent +' '+this._state.unit;
        this.setState('equivalent',material.equivalent);

        this.el.materialBudgetQuantity.value = material.quantity; 
        this.setState('materialBudgetQuantity',material.quantity);

        this.el.requestedQuantity.value = '';
        this.setState('requestedQuantity','');
            

        this.el.prevApprovedQuantity.value = '';
        this.setState('prevApprovedQuantity','');
        
        this.getApprovedQuantity(this._state.componentItemId, material_id,true);
    }
}


export default (data)=>{
    return (new RequestMaterialItem(data));
}