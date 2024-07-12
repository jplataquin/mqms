import {Template,Component,Signal} from '/adarna.js';

//D.R.Y. coding helpers
function roundTwoDecimal(num) {
    return Math.round((num + Number.EPSILON) * 100) / 100;
}

function calculateTotalEquivalent(a,b){
    return roundTwoDecimal(parseFloat(a) * parseFloat(b));
}


const signal = new Signal();

class ComponentItem extends Component{

    state(){
        return {
            quantity: 0,
            unit:'',
            name:'',
            editable: false,
            function_type_id:'',
            variable:''
        }
    }

    model(){
        return {
            id:null,
            component_id:null,
            materialItemOptions:[],
            unitOptions:[],
            component_quantity:0,
            component_use_count:1
        }
    }

    init(){
        this.materialRegistry = {};
    }

    delete(){
        
    }
    
    view(){
        const t = new Template();

        this.el.materialItemSelect = t.select({class:'form-control'},()=>{
            t.option({value:''},' - ');
        });

        this._model.materialItemOptions.map(item=>{
            
            
            let option = t.option({value:item.id},item.brand+' '+item.name + ' '+item.specification_unit_packaging+''.trim());
            
            this.el.materialItemSelect.t.append(option);

            this.materialRegistry[item.id] = item.brand+' '+item.name +' '+item.specification_unit_packaging+''.trim();
        });

        this.el.materialMenu = t.div(()=>{
    
            t.div({class:'row'},()=>{

                t.div({class:'col-lg-12'},()=>{
                    t.table({class:'table border'},()=>{
                        t.thead(()=>{

                            t.tr(()=>{
                            
                                t.th({style:{width:'40%'}},()=>{
                                    t.div({class:'form-group'},()=>{
                                        t.label('Material');
                                        t.el(this.el.materialItemSelect);
                                    });
                                });
                                
                                t.th(()=>{
                                    t.div({class:'form-group'},()=>{
                                        t.label('Equivalent');
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
                        t.th({class:'bg-primary p-1',colspan:7});
                    });
                 
                    
                })

               t.tbody({class:'mb-3 p-3'},()=>{
                    t.tr(()=>{
                        t.th({colspan:7},'Name');
                    });

                    t.tr(()=>{
                        t.td({colspan:7},()=>{
                            this.el.name = t.input({class:'form-control',type:'text', placeholder:'Item',disabled:true,value:'Loading...'}); 
                        });
                    })

                    t.tr(()=>{
                        t.th('Budget Price');
                        t.th('Function Type');
                        t.th('Variable');
                        t.th('Quantity');
                        t.th('Unit');
                        t.th('Total Amount');
                        t.th('Options');
                    })
                    
                    t.tr(()=>{
                        

                        t.td({class:''},(el)=>{
                            
                            this.el.budget_price = t.input({class:'form-control', type:'text', placeholder:'Budget Price',disabled:true,value:'Loading...'});
                            
                        });

                        t.td({class:''},(el)=>{
                            
                            this.el.function_type = t.select({class:'form-control',disabled:true},()=>{
                                t.option({value:1},'As Factor');
                                t.option({value:2},'As Divisor');
                                t.option({value:3},'As Direct');
                            });

                        });

                        t.td({class:''},(el)=>{
                            
                            this.el.variable = t.input({class:'form-control', type:'text', placeholder:'Variable',disabled:true,value:'Loading...'});

                        });
                    
                        t.td({class:''},(el)=>{
                            
                            this.el.quantity = t.input({class:'form-control', type:'text', placeholder:'Quantity',disabled:true,value:'Loading...'});

                        });

                        t.td({class:''},(el)=>{

                            this.el.unit = t.select({class:'form-control',disabled:true},()=>{
                                for(let i in this._model.unitOptions){


                                    t.option({value:i}, this._model.unitOptions[i].text );
                                }
                            });
        
                        });

                        t.td({},()=>{
                            this.el.total_amount = t.input({class:'form-control',disabled:true});
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

                    });


                });//tbody

                t.tfoot({},()=>{
                    t.tr({},()=>{
                        t.td({colspan:7},()=>{
                            t.div({class:'ms-3 row'},(el)=>{
                                el.append(this.el.materialMenu);
                            });
                        });
                    })
                });

            });//tbody

            

            
        });
    }

    controller(dom){
        
        this.getComponentItemData();

        this.functionVariableQuantity();

        this.el.budget_price.onkeyup = ()=>{    
            this.calculateTotalAmount();
        }

        

        this.el.material_quantity.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.material_quantity,e,2,false);
        }

        this.el.budget_price.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.budget_price,e,2,false);
        }

        
        this.el.material_quantity.onkeyup = ()=>{
            this.el.total.value = calculateTotalEquivalent( this.el.material_quantity.value, this.el.equivalent.value);
        }
        
        this.el.equivalent.onkeyup = ()=>{
            this.el.material_quantity.value = roundTwoDecimal(this.el.quantity.value / this.el.equivalent.value);
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
           this.httpUpdate();
        }


        this.updateMaterialList();
    }

    calculateTotalAmount(){
  
        this.el.total_amount.value = (new Intl.NumberFormat().format(
            parseFloat(this.el.budget_price.value) * parseFloat(this.el.quantity.value)
        ));
    }

    httpUpdate(){

        window.util.blockUI();
        window.util.$post('/api/component_item/update/',{
            id                      : this._model.id,
            component_id            : this._model.component_id,
            name                    : this.el.name.value,
            budget_price            : this.el.budget_price.value,
            quantity                : this.el.quantity.value,
            unit_id                 : this.el.unit.value,
            function_type_id        : this.el.function_type.value,
            function_variable       : this.el.variable.value,

        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                util.showMsg(reply.message);

                return false;
            }
              
            this.setState('quantity',parseFloat(this.el.quantity.value));
            this.setState('unit',this.el.unit.value);
            this.setState('name',this.el.name.value);
            this.setState('function_type_id',this.el.function_type.value);
            this.setState('variable',this.el.variable.value);
            this.setState('editable',false);

            
            signal.broadcast('set-component-status','PEND');
        });
    }

    functionVariableQuantity(){

        this.el.variable.onkeypress = (e)=>{
            return window.util.inputNumber(this.el.variable,e,3,false);
        }
    
        this.el.function_type.onchange = (e) =>{
            this.el.variable.onkeyup();   
        }
    
        this.el.variable.onkeyup = (e)=>{
            
            switch(this.el.function_type.value){
                case '1':
    
                        this.el.quantity.value = Math.ceil( 
                            (this._model.component_quantity * this.el.variable.value)  / this._model.component_use_count
                        );
    
                    break;
    
                case '2':
    
                        this.el.quantity.value = Math.ceil( 
                            (this._model.component_quantity  / this.el.variable.value)  / this._model.component_use_count
                        );
    
                    break;
    
                case '3':
    
                        this.el.quantity.value = this.el.variable.value;
                        
                    break;
            }
            
            this.calculateTotalAmount();
        }
    }


    onStateChange_editable(newVal){
       
        this.el.name.disabled               = !newVal;
        this.el.unit.disabled               = !newVal;
        this.el.budget_price.disabled       = !newVal;
        this.el.function_type.disabled      = !newVal;
        this.el.variable.disabled           = !newVal;

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
            this.setState('unit',reply.data.unit_id);
            this.setState('name',reply.data.name);
            this.setState('function_type_id',reply.data.function_type_id);
            this.setState('variable',reply.data.function_variable);
            console.log(reply.data.unit_id);
            this.el.name.value          = reply.data.name;
            this.el.budget_price.value  = reply.data.budget_price;
            this.el.quantity.value      = reply.data.quantity;
            this.el.unit.value          = reply.data.unit_id;
            this.el.function_type.value = reply.data.function_type_id;
            this.el.variable.value      = reply.data.function_variable;

            this.calculateTotalAmount();
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
        
        let materialItem = t.tr((row)=>{
                    t.td(this.materialRegistry[data.material_item_id]);
                    t.td(''+data.equivalent);
                    t.td(''+roundTwoDecimal(data.quantity));
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
                    });//td
                });//tr
            
        
        if(data.quantity > this._state.quantity){
            materialItem.classList.add('border');
            materialItem.classList.add('border-danger');
        }

        this.el.materialList.append(materialItem);

    }
}

export default (data)=>{
    return (new ComponentItem(data));
}