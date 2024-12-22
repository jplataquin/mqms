import {Template,Component} from '/adarna.js';


class CommentForm extends Component{

    model(){
        return {
            comment_id:''
        }
    }

    view(){

        const t = new Template();
        
        return t.div({class:'container'},()=>{

            this.el.comment_list_container = t.div({class:'mb-3'});

            t.div(()=>{
                t.div({class:'form-group'},()=>{
                    this.el.textarea = t.textarea({class:'w-100 form-control',placeholder:'Comment'});
                });

                t.div({class:'text-end'},()=>{
                    t.button({class:'btn btn-primary'},'Submit');
                });
            });

        }); 
    }

}


export default (data)=>{
    return (new CommentForm(data));
}