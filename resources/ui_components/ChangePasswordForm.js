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

        window.util.$post('/api/user/change_password',{
            current_password: this.el.current_password.value,
            new_password    : this.el.new_password.value,
            retype_password : this.el.retype_password.value
        }).then((reply)=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.drawerModal.close();
    
            window.util.alert('Password Change','Successful!');
    
        });
    }
}


export default (data)=>{
    return (new ChangePasswordForm(data));
}