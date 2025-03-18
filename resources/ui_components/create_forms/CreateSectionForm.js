import {Template,Component} from '/adarna.js';


class CreateSectionForm extends Component{

    model(){
        return {
            project_id:'',
            successCallback: (data)=>{
                window.util.navReload();
            }
        };
    }

    view(){
        const t= new Template();

        return t.div(()=>{

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Section Name');
                        this.el.section_name = t.input({class:'form-control', type:'text'});
                    });//div
                });//div col
            });//div row

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Gross Total Amount');
                        this.el.gross_total_amount = t.input({class:'form-control', type:'text'});
                    });//div
                });//div col
            });//div row

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12 text-end'},()=>{
                    this.el.btn_submit = t.button({class:'btn btn-primary me-3'},'Submit');
                    this.el.btn_cancel = t.button({class:'btn btn-secondary'},'Cancel');
                });//div col
            });//div row


        });
    }

    controller(){

        this.el.btn_cancel.onclick = ()=>{
            window.util.drawerModal.close();
        }

        window.util.numbersOnlyInput([this.el.gross_total_amount],{
            negative:false,
            precision:2
        });
        
        this.el.btn_submit.onclick = ()=>{
            
            window.util.blockUI();

            window.util.$post('/api/section/create',{
                name                : this.el.section_name.value,
                gross_total_amount  : window.util.pureNumber(this.el.gross_total_amount.value,2),
                project_id          : this._model.project_id
            }).then(reply=>{
    
                window.util.unblockUI();
    
                if(reply.status <= 0){
    
                    window.util.showMsg(reply);
                    return false;
                }
                
                window.util.drawerModal.close();

                this._model.successCallback(reply.data);
            });
        }
    }

}

export default (data)=>{
    return (new CreateSectionForm(data));
}