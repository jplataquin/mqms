import {Template,Component,Signal} from '/adarna.js';

//D.R.Y. coding helpers
function roundTwoDecimal(num) {
    return +(Math.round(num + "e+2")  + "e-2");
}

function calculateTotalEquivalent(a,b){
    return roundTwoDecimal(parseFloat(a) * parseFloat(b));
}


const signal = new Signal();

class ComponentItem extends Component{

    state(){
        return {
            showList: false,
            quantity: 0,
            unit:'',
            name:'',
            editable: false
        }
    }

    model(){
        return {
            id:null,
            component_id:null,
            materialItemOptions:[]
        }
    }

    init(){
        this.materialRegistry = {};
    }

    delete(){
        
    }
    
    view(){
        const t = new Template();

        this.el.materialItemSelect = t.select({class:'form-control'});

        this._model.materialItemOptions.map(item=>{
            let option = t.option({value:item.id},item.name + ' '+item.specification_unit_packaging+' '+item.brand+''.trim());
            this.el.materialItemSelect.t.append(option);

            this.materialRegistry[item.id] = item.name + ' '+item.specification_unit_packaging+' '+item.brand+''.trim();
        });

        this.el.materialMenu = t.div(()=>{
    
            t.div({class:'row'},()=>{

                t.div({class:'col-lg-12'},()=>{
                    t.table({class:'table border'},()=>{
                        t.thead(()=>{
                            t.th(()=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Material');
                                    t.el(this.el.materialItemSelect);
                                });
                            });
                            
                            t.th(()=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Equivalent / Unit');
                                    this.el.equivalent = t.input({class:'form-control', type:'text'});
                                });
                            });

                            t.th(()=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Quantity');
                                    this.el.material_quantity = t.input({class:'form-control', type:'text'});
                                });
                            });
                            t.th(()=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('Total');
                                    this.el.total = t.input({class:'form-control', type:'number',disabled:true});
                                });
                            });
                            t.th(()=>{
                                t.div({class:'form-group'},()=>{
                                    t.label('&nbsp');
                                    this.el.addBtn = t.button({class:'btn btn-warning w-100'},'Add');
                                })
                            });
                        });
    
                        this.el.materialList = t.tbody();
                    });
                });
                
            });
        });

        return t.div({class:'pb-3'},(el)=>{
            
            this.el.item = t.table({class:'selectable-div fade-in table border'},()=>{
                
                t.thead(()=>{
                    t.tr(()=>{
                        t.th({class:'bg-primary p-1',colspan:5});
                    });
                    t.tr(()=>{
                        t.th('Name');
                        t.th('Budget Price');
                        t.th('Quantity');
                        t.th('Unit');
                        t.th('Options');
                    })
                    
                })

               t.tbody({class:'mb-3 p-3'},()=>{

                    t.td({class:''},(el)=>{
                        
                        this.el.name = t.input({class:'form-control',type:'text', placeholder:'Item',disabled:true,value:'Loading...'});

                    });

                    t.td({class:''},(el)=>{
                        
                        this.el.budget_price = t.input({class:'form-control', type:'text', placeholder:'Budget Price',disabled:true,value:'Loading...'});
                        

                    });

                    t.td({class:''},(el)=>{
                        
                        this.el.quantity = t.input({class:'form-control', type:'text', placeholder:'Quantity',disabled:true,value:'Loading...'});
                        

                    });

                    t.td({class:''},(el)=>{
                        this.el.unit = t.input({class:'form-control', type:'text', placeholder:'Unit', disabled:true, value:'loading...'});    
                    });

                    t.td({class:'text-center'},(el)=>{
                        
                        this.el.deleteComponentButton = t.button({class:'btn btn-danger me-3',style:{
                            display: (()=>{
                                if(this._state.editable == true) return 'none';
                                if(this._state.editable == false) return 'inline';
                            })()
                        }},'Delete');

                        this.el.editComponentButton = t.button({class:'btn btn-primary',style:{
                            display: (()=>{
                                if(this._state.editable == true) return 'none';
                                if(this._state.editable == false) return 'inline';
                            })()
                        }},'Edit');

                        this.el.cancelEditComponentButton = t.button({class:'btn btn-primary me-3',style:{
                            display: (()=>{
                                if(this._state.editable == true) return 'inline';
                                if(this._state.editable == false) return 'none';
                            })()
                        }},'Cancel');

                        this.el.updateComponentButton = t.button({class:'btn btn-warning',style:{
                            display: (()=>{
                                if(this._state.editable == true) return 'inline';
                                if(this._state.editable == false) return 'none';
                            })()
                        }},'Update');
                        
                    });

                    t.tr(()=>{
                        t.td({colspan:4, class:'text-center'},()=>{
                            this.el.showList = t.span('Show Items');
                        });
                    })

                });//tbody

                t.tfoot({},()=>{
                    t.tr({},()=>{
                        t.td({colspan:4},()=>{
                            this.el.materialMenuHolder = t.div({class:'ms-3 row'});
                        });
                    })
                });

            });//tbody

            

            
        });
    }

    controller(dom){
        
        this.getComponentItemData();

        this.el.showList.onclick = ()=>{
         
            if(this._state.showList){
                this.el.showList.innerHTML = 'Show Items';
                this.setState('showList',false);
            }else{
                this.el.showList.innerHTML = 'Hide Items';
                this.setState('showList',true);
            }

        }

        this.el.equivalent.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.equivalent,e,2,false);
        }

        this.el.material_quantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.material_quantity,e,2,false);
        }

        this.el.budget_price.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.budget_price,e,2,false);
        }

        this.el.quantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.quantity,e,2,false);
        }
        
        this.el.material_quantity.onkeyup = ()=>{
            this.el.total.value = calculateTotalEquivalent( this.el.material_quantity.value, this.el.equivalent.value);
        }
        
        this.el.equivalent.onkeyup = ()=>{
            this.el.total.value = calculateTotalEquivalent( this.el.material_quantity.value, this.el.equivalent.value);
        }

        this.el.addBtn.onclick = ()=>{

            if(this.el.total.value > this._state.quantity){
                alert('Total equivalent cannot be greater than the component item quantity ('+this._state.quantity+' '+this._state.unit+')');
                return false;
            }

            this.addMaterial();
        }


        this.el.deleteComponentButton.onclick = (e)=>{
            e.preventDefault();

            let answer = prompt('Please confirm by entering "'+this._state.name+'"');

            if(answer != this._state.name){
                util.showMsg('Invalid answer');
                return false;
            }

            util.blockUI();

            util.$post('/api/component_item/delete',{
                id:this._model.id
            }).then(reply=>{

                util.unblockUI();

                if(reply.status <= 0){
                    util.showMsg(reply.message);
                    return false;
                }

                dom.t.remove();
                
                signal.broadcast('set-component-status','PEND');
            });
        }

        this.el.editComponentButton.onclick = (e)=>{
            e.preventDefault();
            this.setState('editable',true);
        }

        this.el.cancelEditComponentButton.onclick = (e)=>{
            this.setState('editable',false);
            this.el.unit.value = this._state.unit;
            this.el.name.value = this._state.name;
            this.el.quantity.value = this._state.quantity;
        }

        this.el.updateComponentButton.onclick = (e)=>{

            window.util.blockUI();
            window.util.$post('/api/component_item/update/',{
                id          : this._model.id,
                component_id: this._model.component_id,
                name        : this.el.name.value,
                budget_price: this.el.budget_price.value,
                quantity    : this.el.quantity.value,
                unit        : this.el.unit.value
            }).then(reply=>{

                window.util.unblockUI();

                if(reply.status <= 0){
                    util.showMsg(reply.message);

                    return false;
                }

                this.setState('quantity',parseFloat(this.el.quantity.value));
                this.setState('unit',reply.data.unit);
                this.setState('name',this.el.name.value);
                this.setState('editable',false);

                
                signal.broadcast('set-component-status','PEND');
            });
        }
    }

    onStateChange_showList(newVal){
        
        if(newVal){
            this.updateMaterialList();
            this.el.materialMenuHolder.t.append(this.el.materialMenu);
        }else{
            this.el.materialMenu.t.remove();
        }
    }

    onStateChange_editable(newVal){
       
        this.el.name.disabled           = !newVal;
        this.el.quantity.disabled       = !newVal;
        this.el.unit.disabled           = !newVal;
        this.el.budget_price.disabled   = !newVal;
        

        if(newVal){
            this.el.editComponentButton.style.display   = 'none';
            this.el.deleteComponentButton.style.display = 'none';

            this.el.cancelEditComponentButton.style.display = 'inline';
            this.el.updateComponentButton.style.display = 'inline';
            
        }else{
            this.el.editComponentButton.style.display   = 'inline';
            this.el.deleteComponentButton.style.display = 'inline';

            
            this.el.cancelEditComponentButton.style.display = 'none';
            this.el.updateComponentButton.style.display = 'none';
        }
     
    }

    updateMaterialList(){

        this.el.materialList.t.clear();

        window.util.$get('/api/material_quantity/list',{
            component_item_id:this._model.id,
            page:1,
            limit:0
        }).then(reply=>{
            
            if(reply.status <= 0 ){
                window.util.showMsg(reply.message);
                return false;
            }

            reply.data.map(item=>{
                this.appendMaterial({
                    id:item.id,
                    material_item_id: item.material_item_id,
                    quantity: item.quantity,
                    equivalent: item.equivalent
                });
            });
        });
    }

    getComponentItemData(){

        window.util.$get('/api/component_item',{
            id:this._model.id
        }).then(reply=>{

            if(reply.status <= 0){

                alert(reply.message);
                return false;
            }
           
            this.setState('budget_price',parseFloat(reply.data.budget_price));
            this.setState('quantity',parseFloat(reply.data.quantity));
            this.setState('unit',reply.data.unit);
            this.setState('name',reply.data.name);

            
            this.el.name.value          = reply.data.name;
            this.el.budget_price.value  = reply.data.budget_price;
            this.el.quantity.value      = reply.data.quantity;
            this.el.unit.value          = reply.data.unit;

        });

    }

    addMaterial(){

        this.el.addBtn.disabled = true;

        let data = {
            component_item_id: this._model.id,
            material_item_id: this.el.materialItemSelect.value,
            quantity: this.el.material_quantity.value,
            equivalent: this.el.equivalent.value
        };

        window.util.$post('/api/material_quantity/create',data).then(reply=>{
            
            this.el.addBtn.disabled = false;
             
            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            this.el.material_quantity.value = '';
            this.el.equivalent.value = '';

            this.appendMaterial({
                id: reply.data.id,
                material_item_id: data.material_item_id,
                quantity: data.quantity,
                equivalent: data.equivalent
            });

            
            signal.broadcast('set-component-status','PEND');
        });

    }

    appendMaterial(data){
        const t = new Template();

        let materilItem = t.tr((row)=>{
                    t.td(this.materialRegistry[data.material_item_id]);
                    t.td(data.equivalent);
                    t.td(data.quantity);
                    t.td(''+calculateTotalEquivalent(data.quantity,data.equivalent));
                    t.td({class:'text-center'},()=>{
                        t.a({href:'#'},'[X]').onclick = (e)=>{
                            e.preventDefault();
                            
                            if(confirm('Are you sure you want to delete this entry')){
                                
                                window.util.blockUI();
                                
                                window.util.$post('/api/material_quantity/delete',{
                                    id:data.id
                                }).then(reply=>{

                                    window.util.unblockUI();

                                    if(reply.status <= 0){
                                        window.util.showMsg(reply.message);
                                        return false;
                                    }

                                    row.t.remove();
                                });
                            }
                        };
                    });
                });
            
        

        this.el.materialList.t.append(materilItem);
    }
}

export default (data)=>{
    return (new ComponentItem(data));
}