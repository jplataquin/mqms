import {Template,Component,$el,$q,$util} from '/adarna.js';

class ChangePasswordForm extends Component{

    view(){
        const t = new Template();

        return t.div(()=>{

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Current Password');
                        this.el.current_password = t.input({class:'form-control',type:'password'});
                    });//
                });//div col
            });//row

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('New Password');
                        this.el.new_password = t.input({class:'form-control',type:'password'});
                    });//
                });//div col
            });//row

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Retype New Password');
                        this.el.retype_password = t.input({class:'form-control',type:'password'});
                    });//
                });//div col
            });//row

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12 text-end'},()=>{
                    this.el.submit_btn = t.button({class:'btn btn-primary me-3'},'Submit');
                    this.el.cancel_btn = t.button({class:'btn btn-secondary'},'Cancel');
                });//div col
            });//row

        });
    }

    controller(){

        this.el.submit_btn.onclick = ()=>{
            this.submit();
        }

        this.el.cancel_btn.onclick = ()=>{
            window.util.drawerModal.close();
        }
    }

    submit(){
        window.util.blockUI();

        window.util.$post('/api/profile/change_password',{

        });
    }
}


export default (data)=>{
    return (new ChangePasswordForm(data));
}