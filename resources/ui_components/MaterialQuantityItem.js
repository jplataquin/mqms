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

        console.log(this._model.material_item_options[this._model.material_item_id]);
        return t.tr(()=>{

            t.td('test');
            t.td( window.util.numberFormat(this._model.quantity,2) );
            t.td( window.util.numberFormat(this._model.equivalent,2) );
            t.td( window.util.numberFormat(this._model.equivalent * this._model.quantity,2) );
        });
    }
}


export default (data)=>{
    return (new MaterialQuantityItem(data));
}