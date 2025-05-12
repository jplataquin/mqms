import {Template,ComponentV2,Signal} from '/adarna.js';


class MaterialQuantityItem extends ComponentV2{

    model(){
        return {
            id:0,
            name:'',
            material_item_id        : 0,
            quantity                : 0,
            equivalent              : 0,
            component_item_name     : '',
            component_item_quantity : 0,
            component_item_unit_text: '',
            get_grand_total         : ()=>{return 0},
            after_action_callback   : ()=>{}
        }
    }

    view(){
        
        const t = new Template();


        return t.tr(()=>{

            t.td(this._model.name);
            t.td({class:'text-center'}, window.util.numberFormat(this._model.equivalent,2)+' '+this._model.component_item_unit_text );
            t.td({class:'text-center'},()=>{
                        
                t.a({class:'me-5',href:'#'},()=>{
                    this.el.edit_btn = t.i({class:'bi bi-pencil-square'});
                });
                
                

                t.a({class:'me-5',href:'#'},()=>{
                    this.el.report_btn = t.i({class:'bi bi-list-task'});
                });

                t.a({href:'#'},()=>{
                    this.el.delete_btn = t.i({class:'bi bi-trash-fill'});
                });
                
                
            });
        });
    }

    controller(){

        this.el.edit_btn.onclick = (e)=>{
            e.preventDefault();

            this.showUpdateMaterialForm();
        }
        
        this.el.delete_btn.onclick = async (e)=>{

            e.preventDefault();
                    
            let ans = await window.util.confirm('Are you sure you want to delete this entry');

            if(ans){
                window.util.blockUI();
                    
                window.util.$post('/api/material_quantity/delete',{
                    id:this._model.id
                }).then(reply=>{

                    window.util.unblockUI();

                    if(reply.status <= 0){
                        window.util.showMsg(reply);
                        return false;
                    }

                    this._model.after_action_callback();
                });
            }
        }

        this.el.report_btn.onclick = (e)=>{
            e.preventDefault();

            window.open('/material_budget/report/'+this._model.id,'_blank');
        }

    }

    
   

    showUpdateMaterialForm(){

        
        window.ui.primaryModal.hide();

        window.ui.primaryModalTitle.innerHTML    = 'Modify Material Entry';
        window.ui.primaryModalBody.innerHTML     = '';
        window.ui.primaryModalFooter.innerHTML   = '';

        const t = new Template();

        const equivalentInput   = t.input({class:'form-control',value: this._model.equivalent});


        this.calculateTotal(quantityInput,equivalentInput,totalInput);

        window.util.numbersOnlyInput([
            equivalentInput
        ],{
            negative:false,
            precision:2
        });

  
        const content = t.div(()=>{
            
            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-12 mb-3'},()=>{
                    t.table({class:'table borderd'},()=>{
                        t.tr(()=>{
                            t.th('Component Item',);
                            t.td(this._model.component_item_name);
                        });
                        t.tr(()=>{
                            t.th('Component Quantity');
                            t.td( window.util.numberFormat(this._model.component_item_quantity,2)+' '+this._model.component_item_unit_text );
                        });
                 
                    })
                });
            });

            t.div({class:'row'},()=>{
                
              

                t.div({class:'col-sm-12'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label('Equivalent'),
                        el.append(equivalentInput);
                    });
                });

             
            });

        });

        const cancelBtn = t.button({class:'btn btn-secondary me-3'},'Cancel');
        const updateBtn = t.button({class:'btn btn-warning'},'Update');

        const controls =  t.div({class:'row'},()=>{
            t.div({class:'col-12 text-end'},(el)=>{
                el.append(cancelBtn);
                el.append(updateBtn);
            });
        });

        cancelBtn.onclick = (e)=>{
            window.ui.primaryModal.hide();
        }


        updateBtn.onclick = (e)=>{

            window.ui.primaryModal.hide();
            
            window.util.blockUI();

            window.util.$post('/api/material_quantity/update',{
                id                  : this._model.id,
                equivalent          : equivalentInput.value
            }).then(reply=>{
                window.util.unblockUI();

                if(reply.status <= 0){

                    window.util.showMsg(reply);
                    return false;
                }

                this._model.after_action_callback();

            })
        }

        window.ui.primaryModalBody.append(content);

        window.ui.primaryModalFooter.append(controls);

        window.ui.primaryModal.show();
    }
}


export default (data)=>{
    return (new MaterialQuantityItem(data));
}