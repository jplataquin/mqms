import {Template,ComponentV2} from '/adarna.js';
import CommentList from '/ui_components/comment/CommentList.js';               

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
            comment_type:'USER',
            record_type:'',
            record_id:''
        }
    }

    view(){

        const t = new Template();
        
        this.el.comment_list = CommentList({
            record_type: this._model.record_type,
            record_id: this._model.record_id
        });

        return t.div({class:'container border border-secondary shadow-lg p-3 mb-5 bg-white rounded'},()=>{

            this.el.comment_list_container = t.div({class:'mb-3'},(el)=>{
                el.append(this.el.comment_list);
            });

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
            comment_type: 'ENTR',
            record_id   : this._model.record_id,
            record_type : this._model.record_type,
            content     : this.getState('content')
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            this.setState('content','');

            this.el.comment_list.handler.appendComment(reply.data);
        });
    }

}


export default (data)=>{
    return (new CommentForm(data));
}