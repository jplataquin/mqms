import {Template,ComponentV2,Signal} from '/adarna.js';


class MaterialQuantityItem extends ComponentV2{

    model(){
        return {
            id:0,
            material_item_options   : [],
            material_item_id        : 0,
            quantity                : 0,
            equivalent              : 0
        }
    }

    view(){
        
        const t = new Template();

        let material_item                   = this._model.material_item_options[this._model.material_item_id] ?? null;
        let brand                           = material_item.brand ?? null;
        let name                            = material_item.name ?? null;
        let specification_unit_packaging    = material_item.specification_unit_packaging ?? null;
        
        let material_name = brand+' '+name+' '+specification_unit_packaging;

        return t.tr(()=>{

            t.td(material_name);
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
                    t.i({class:'bi bi-list-task'});
                });
                
                // .onclick = (e)=>{
                //     e.preventDefault();

                //     window.open('/material_budget/report/'+data.id,'_blank');
                // }

                t.a({href:'#'},()=>{
                    t.i({class:'bi bi-trash-fill'});
                });
                
                // .onclick = (e)=>{
                //     e.preventDefault();
                    
                //     if(confirm('Are you sure you want to delete this entry')){
                        
                //         window.util.blockUI();
                        
                //         window.util.$post('/api/material_quantity/delete',{
                //             id:data.id
                //         }).then(reply=>{

                //             window.util.unblockUI();

                //             if(reply.status <= 0){
                //                 window.util.showMsg(reply);
                //                 return false;
                //             }

                //             row.t.remove();
                //         });
                //     }
                // };
                
            });
        });
    }
}


export default (data)=>{
    return (new MaterialQuantityItem(data));
}