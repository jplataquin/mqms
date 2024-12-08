import {Template,ComponentV2,Signal} from '/adarna.js';


class MaterialQuantityItem extends ComponentV2{

    model(){
        return {
            id:0,
            name:'',
            material_item_id        : 0,
            quantity                : 0,
            equivalent              : 0,
            after_action_callback   : ()=>{}
        }
    }

    view(){
        
        const t = new Template();


        return t.tr(()=>{

            t.td(this._model.name);
            t.td( window.util.numberFormat(this._model.quantity,2) );
            t.td( window.util.numberFormat(this._model.equivalent,2) );
            t.td( window.util.numberFormat(this._model.equivalent * this._model.quantity,2) );
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

        const quantityInput     = t.input({class:'form-control',value: this._model.quantity});
        const equivalentInput   = t.input({class:'form-control',value: this._model.equivalent});
        const totalInput        = t.input({class:'form-control', disabled:true});

        const content = t.div(()=>{
            
            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-12 mb-3'},()=>{
                    t.table({class:'table borderd'},()=>{
                        t.tr(()=>{
                            t.th('Comp. Item',);
                            t.td('Component item');
                        });
                        t.tr(()=>{
                            t.th('Matt. Item',);
                            t.td(this._model.name)
                        });
                    })
                });
            });

            t.div({class:'row'},()=>{
                
                t.div({class:'col-4'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label('Quantity'),
                        el.append(quantityInput);
                    });
                });

                t.div({class:'col-4'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label('Equivalent / Unit'),
                        el.append(equivalentInput);
                    });
                });

                t.div({class:'col-4'},()=>{
                    t.div({class:'form-group'},(el)=>{
                        t.label('Total'),
                        el.append(totalInput);
                    });
                });
            });

        });

        const cancelBtn = t.button({class:'btn btn-secondary me-3'},'Cancel');
        const onUpdateBtn = t.button({class:'btn btn-warning'},'onUpdate');

        const controls =  t.div({class:'row'},()=>{
            t.div({class:'col-12 text-end'},(el)=>{
                el.append(cancelBtn);
                el.append(onUpdateBtn);
            });
        });

        cancelBtn.onclick = (e)=>{
            window.ui.primaryModal.hide();
        }


        onUpdateBtn.onclick = (e)=>{

            window.ui.primaryModal.hide();
            
            window.util.blockUI();

            window.util.$post('/api/material_quantity/onUpdate',{
                id                  : entry.material_quantity_id,
                material_item_id    : entry.material_item_id,
                quantity            : quantityInput.value,
                equivalent          : equivalentInput.value
            }).then(reply=>{
                window.util.unblockUI();

                if(reply.status <= 0){

                    window.util.showMsg(reply);
                    return false;
                }

                this.onUpdateMaterialList();

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