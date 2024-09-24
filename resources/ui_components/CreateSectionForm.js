import {Template,Component} from '/adarna.js';


class CreateProjectForm extends Component{

    model(){
        return {
            project_id:''
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

        
        this.el.btn_submit.onclick = ()=>{
            
            window.util.blockUI();

            window.util.$post('/api/section/create',{
                name: this.el.section_name.value,
                project_id: this._model.project_id
            }).then(reply=>{
    
                window.util.unblockUI();
    
                if(reply.status <= 0){
    
                    window.util.showMsg(reply);
                    return false;
                }
                
                window.util.drawerModal.close();
                window.util.navReload();
            });
        }
    }

}

export default (data)=>{
    return (new CreateProjectForm(data));
}