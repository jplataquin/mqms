import {Template,Component,$el,$q,$util} from '/adarna.js';


class CanvassItem extends Component{

    state(){
        return {
            validationError: false
        }
    }

    model(){
        return {
            id:'',
            material_quantity_request_item_id:'',
            status:'',
            supplier_list:'',
            payment_terms_list:'',
            supplier_text:'',
            supplier_id:'',
            payment_term_text:'',
            payment_term_id:'',
            price:'',
            quantity:0,
            approvalFlag: false
        }
    }

    view(){
        const t = new Template();

        this.el.status          = t.input({class:'form-control',disabled:true,value:this._model.status});
        this.el.deleteBtn       = t.button({class:'btn btn-danger form-control'},'X');
        this.el.disapproveBtn   = t.button({class:'btn btn-danger form-control'},'X');
        this.el.approveBtn      = t.button({class:'btn btn-primary form-control'},()=>{
            t.i({class:'bi bi-check-square'});
        });
        this.el.voidBtn         = t.button({class:'btn btn-secondary form-control'},()=>{
            t.i({class:'bi bi-x-square'});
        });
        
        
        return  t.div({},()=>{
        
            t.div({class:'row mb-3'},()=>{
                
                if(!this._model.approvalFlag){
                    
                    t.div({class:'col-1'},()=>{
                    
                        t.div({class:'form-group'},(el)=>{
                            t.label('Status');

                            el.append(this.el.status);
                        });

                    });
                }

                t.div({class:'col-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Supplier');

                        this.el.supplier = t.input({class:'form-control',list:'supplier_list', value:this._model.supplier_text});
                        
                    });
                });
                

                t.div({class:'col-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Payment Terms');

                        this.el.payment_terms = t.input({class:'form-control',list:'payment_terms_list', value:this._model.payment_term_text});
                        
                    });
                });

                t.div({class:'col-2'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Price');

                        this.el.price = t.input({class:'form-control',value:this._model.price,type:'number'});

                    });
                });
                t.div({class:'col-2'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Total');
                        this.el.total = t.input({class:'form-control',disabled:true});
                        
                        let totalVal = (parseFloat(this._model.price) * parseFloat(this._model.quantity));

                        if(! isNaN(totalVal) ){
                            this.el.total.value = $util.numFormat.money('P',totalVal);
                        }
                    });
                });

                if(this._model.approvalFlag && this._model.status == 'PEND'){

                    t.div({class:'col-1'},()=>{
                        t.div({class:'form-group'},(el)=>{
                            t.label('&nbsp;');


                            el.append(this.el.approveBtn); 
                        });
                    });

                    t.div({class:'col-1'},()=>{
                        t.div({class:'form-group'},(el)=>{
                            t.label('&nbsp;');

                            el.append(this.el.disapproveBtn);
                                                        
                        });
                    });

                }else if(!this._model.approvalFlag && (this._model.status == 'PEND' || this._model.status == '')){

                    t.div({class:'col-1'},()=>{
                        t.div({class:'form-group'},(el)=>{
                            t.label('&nbsp;');

                            el.append(this.el.deleteBtn);
                                                        
                        });
                    });
                    
                }else if(this._model.status == 'APRV'){
                    
                    t.div({class:'col-1'},()=>{
                        t.div({class:'form-group'},(el)=>{
                            t.label('&nbsp;');

                            el.append(this.el.voidBtn);
                                                        
                        });
                    });

                }else if(this._model.status == 'DPRV' || this._model.status == 'VOID'){
                    
                    t.div({class:'col-1'},()=>{
                        t.div({class:'form-group'},(el)=>{
                            t.label('&nbsp;');

                           
                                                        
                        });
                    });
                    
                }//if else
            });

            t.div(()=>{
                this.el.errorList = t.ul({class:'text-danger'});
            })
        });
    }

    controller(dom){

        this.dom = dom;
        this.errorMsgs = [];

        
        if(this._model.status != ''){
            
            this.el.supplier.disabled       = true;
            this.el.payment_terms.disabled  = true;
            this.el.price.disabled          = true;

            
        }

          
        this.el.deleteBtn.onclick = ()=>{

            
            if(this._model.status == 'APRV'){
                return false;
            }

            if(this._model.status == 'PEND'){
                return this.httpDeleteRequest();
            }

            $el.remove(dom);
            
        }

        
        this.el.approveBtn.onclick = ()=>{

            if(this._model.status == 'PEND' && confirm('Are you sure you want to "APPROVE" this canvass?')){
                return this.httpApproveRequest();
            }
        }

        this.el.disapproveBtn.onclick = ()=>{

            if(this._model.status == 'PEND' && confirm('Are you sure you want to "DISAPPROVE" this canvass?')){
                return this.httpDisapproveRequest();
            }
        }
        

        this.el.voidBtn.onclick = ()=>{

            if(this._model.status == 'APRV' && confirm('Are you sure you want to "VOID" this canvass?')){
                return this.httpVoidRequest();
            }
        }

        this.el.price.onblur = (e)=>{
            let val = this.el.price.value;

            if(val){

                if(!/^\d*(\.\d{0,2})?$/.test(val)){
                    
                    this.el.price.value = '';
                    this._model.price   = 0;
                    this.el.total.value = '';
                    return false;
                }

            }else{
                
                this.el.price.value = '';
                this._model.price   = 0;
                this.el.total.value = '';
                return false;
            }
            
        }

        this.el.price.onkeyup = (e)=>{

            if(this.el.price.value.trim() == ''){
                this.el.total.value = 0;
                this._model.price = 0;
                return false;
            }

            if(isNaN( this.el.price.value ) ){
                this.el.price.value = '0.00';
            }

            this.el.total.value = $util.numFormat.money('P',
                (parseFloat(this.el.price.value) * parseFloat(this._model.quantity)).toFixed(2)
            );

            this._model.price = this.el.price.value;
        }

        this.el.supplier.onchange = (e)=>{
          
            let option = $q('#'+this._model.supplier_list+' > option[value="'+this.el.supplier.value+'"]').first();
            
            if(!option){
                this.el.supplier.value = '';
                this._model.supplier_id = '';
                return false;
            }

            this._model.supplier_id = option.getAttribute('data-value');

        }


        this.el.payment_terms.onchange = (e)=>{
          
            let option = $q('#'+this._model.payment_terms_list+' > option[value="'+this.el.payment_terms.value+'"]').first();
            

            if(!option){
                this.el.payment_terms.value = '';
                this._model.payment_term_id = '';
                return false;
            }

            this._model.payment_term_id = option.getAttribute('data-value');

        }


        this.dom.handler.validate = ()=>{

            //Reset fields;
            this.setState('validationError',false);
            this.errorMsgs = [];
            
            this.el.errorList.innerHTML = '';

            if(this._model.status != ''){
                
                return true;
            }

            //Check for missing fields
            if(
                this._model.supplier_id == ''     ||
                this.el.supplier.value == ''      ||
                this._model.payment_term_id == '' ||
                this.el.payment_terms.value == '' ||
                this._model.price == ''           ||
                this.el.price.value == ''         
            ){

                this.errorMsgs.push('All fields are required');
            }

            //Check that price is a number
            if(isNaN(this._model.price)){
                this.errorMsgs.push('Invalid price value');
            }

            //Price cannot be zero
            if(this._model.price < 0){
                this.errorMsgs.push('Price cannot be negative or less than zero');
            }

            if(this.errorMsgs.length){
                
                this.setState('validationError',true);

                return false;
            }

            return true;
        }

        this.dom.handler.getData = ()=>{
            return {
                id: this._model.id,
                material_quantity_request_item_id: this._model.material_quantity_request_item_id,
                supplier_id: this._model.supplier_id,
                payment_term_id: this._model.payment_term_id,
                price: parseFloat(this._model.price).toFixed(2),
                status: this._model.status
            }
        }
        
    }//controller

    onStateChange_validationError(value){

        const t = new Template();


        if(value == true){

            this.dom.classList.add('border');
            this.dom.classList.add('border-danger');
        }else{

            this.dom.classList.remove('border');
            this.dom.classList.remove('border-danger');
        }
        
        this.errorMsgs.map(msg =>{

            this.el.errorList.append(
                t.li(msg)
            );
        });
    }

    httpDeleteRequest(){

        window.util.blockUI();

        window.util.$post('/api/material_canvass/delete',{
            id: this._model.id
        }).then(reply=>{

            window.util.unblockUI();

            if(!reply.status){
                window.util.showMsg(reply.message);
                return false;
            }

            
            $el.remove(this.dom);
        });
    }

    httpApproveRequest(){
        
        window.util.blockUI();

        window.util.$post('/api/review/material_canvass/approve',{
            id: this._model.id
        }).then(reply=>{

            window.util.unblockUI();

            if(!reply.status){
                window.util.showMsg(reply.message);
                return false;
            }

            this.setModel({
                status:'APRV',
                approvalFlag: false
            });
        });
    }


    httpDisapproveRequest(){
        
        window.util.blockUI();

        window.util.$post('/api/review/material_canvass/disapprove',{
            id: this._model.id
        }).then(reply=>{

            window.util.unblockUI();

            if(!reply.status){
                window.util.showMsg(reply.message);
                return false;
            }

            this.setModel({
                status:'DPRV',
                approvalFlag: false
            });
        });
    }

    httpVoidRequest(){
        
        window.util.blockUI();

        window.util.$post('/api/material_canvass/void',{
            id: this._model.id
        }).then(reply=>{

            window.util.unblockUI();

            if(!reply.status){
                window.util.showMsg(reply.message);
                return false;
            }

            this.setModel({
                status:'VOID',
                approvalFlag: false
            });
        });
    }
}

export default (data)=>{
    return (new CanvassItem(data));
}