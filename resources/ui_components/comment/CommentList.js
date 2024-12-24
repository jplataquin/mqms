import {Template,ComponentV2} from '/adarna.js';
                

class CommentList extends ComponentV2{

    state(){
        return {
           
        }
    }

    model(){
        return {
            record_type:'',
            record_id:''
        }
    }

    view(){

        const t = new Template();
        
        this.el.container = t.div({class:'container'}); 

        return this.el.container;
    }

    controller(){

        this.getComments();
        
    }

    getComments(){

        window.util.$get('/api/comment/list',{
            record_type     : this._model.record_type,
            record_id       : this._model.record_id
        }).then(reply=>{

            if(!reply.status){
                window.util.showMsg(reply);
                return false;
            }

            this.populateList(reply.data);
        });
    }

    populateList(data){
        const t = new Template();
        console.log(data);
        data.map(item=>{

            this.el.container.append(
                t.div({class:'mb-3 border border-primary p-3'},()=>{
    
                    t.pre(item.content);
                    t.div({class:'text-end'},()=>{
                        t.p('Name '+item.created_at);
                    });
                })
            );
        });
        
    }

}


export default (data)=>{
    return (new CommentList(data));
}