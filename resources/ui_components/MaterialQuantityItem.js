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
        });
    }
}


export default (data)=>{
    return (new MaterialQuantityItem(data));
}