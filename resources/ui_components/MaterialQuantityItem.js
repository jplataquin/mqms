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
                    t.i({class:'bi bi-pencil-square'});
                });
                
                // .onclick = (e)=>{
                //     e.preventDefault();

                //     this.onUpdateMaterialEntry({
                //         material_quantity_id: data.id,
                //         material_item_id: data.material_item_id,
                //         equivalent: data.equivalent,
                //         quantity: data.quantity
                //     });
                // }

                t.a({class:'me-5',href:'#'},()=>{
                    this.el.report_btn = t.i({class:'bi bi-list-task'});
                });
                
                // .onclick = (e)=>{
                //     e.preventDefault();

                //     window.open('/material_budget/report/'+data.id,'_blank');
                // }

                t.a({href:'#'},()=>{
                    this.el.delete_btn = t.i({class:'bi bi-trash-fill'});
                });
                
                
            });
        });
    }

    controller(){

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
}


export default (data)=>{
    return (new MaterialQuantityItem(data));
}