import {Template,ComponentV2} from '/adarna.js';
                

class CommentForm extends ComponentV2{

    state(){
        return {
            content:{
                value: '',
                target: this.el.textarea,
                events:['keyup','change'],
                onUpdate: (data)=>{
                    if(!data.event){
                        this.el.textarea.value = data.value;
                    }
                }
            }
        }
    }

    model(){
        return {
            comment_type:'',
            record_type:'',
            record_id:''
        }
    }

    view(){

        const t = new Template();
        
        return t.div({class:'container'},()=>{

            this.el.comment_list_container = t.div({class:'mb-3'});

            t.div(()=>{
                t.div({class:'form-group mb-3'},()=>{
                    this.el.textarea = t.textarea({class:'w-100 form-control',placeholder:'Comment'});
                });

                t.div({class:'text-end'},()=>{
                    this.el.submit_btn = t.button({class:'btn btn-primary'},'Submit');
                });
            });

        }); 
    }

    controller(){

        this.el.submit_btn.onclick = ()=>{
            this.submit();
        }
    }

    submit(){

        window.util.blockUI();

        window.util.$post('/api/comment/create',{
            comment_type: this._model.comment_type, 
            record_id   : this._model.record_id,
            record_type : this._model.record_type,
            content     : this.getState('content')
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

        });
    }

}


export default (data)=>{
    return (new CommentForm(data));
}