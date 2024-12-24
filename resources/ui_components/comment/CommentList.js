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
        console.log(this.el.container);
        data.map(item=>{

            this.el.container.append(
                t.div({class:'mb-3'},()=>{

                    t.div({class:'border border-primary rounded ps-2 pe-1 pt-1'},()=>{
    
                        t.pre({style:{minHeight:'50px'}},item.content);
                        
                        
                        t.div({class:'pe-3 mt-3'},()=>{
                            t.p({class:'mb-0 font-weight-light font-italic blockquote-footer'},item.user.name+' '+item.created_at);
                        });
                    });
                })
                
            );
        });
        
    }

}


export default (data)=>{
    return (new CommentList(data));
}